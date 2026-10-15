<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Conexion.php';

final class IncidenciaDAO
{
    private const CAMPOS_EDITABLES = [
        'asignatura', 'grupo', 'turno', 'tipo_solicitud', 'otro_tipo',
        'descripcion', 'codigo_equipo', 'aula_id', 'taller'
    ];

    public function __construct(private ?PDO $conexion = null)
    {
        if ($this->conexion === null) {
            $this->conexion = Conexion::obtener();
        }
    }

    public function listar(?int $usuarioId = null, ?string $estado = null): array
    {
        $condiciones = [];
        $parametros = [];

        if ($usuarioId !== null) {
            $condiciones[] = 'i.usuario_id = :usuario_id';
            $parametros['usuario_id'] = $usuarioId;
        }
        if ($estado !== null) {
            $condiciones[] = 'i.estado = :estado';
            $parametros['estado'] = $estado;
        }

        $sql = 'SELECT i.*,
                       u.nombre AS docente_nombre,
                       u.nombre AS nombre_profesor,
                       "" AS apellido_profesor,
                       a.nombre AS taller,
                       a.nombre AS aula_nombre,
                       a.codigo AS aula_codigo
                FROM reportes_incidencias i
                INNER JOIN usuarios u ON u.id = i.usuario_id
                INNER JOIN aulas a ON a.id = i.aula_id';

        if (!empty($condiciones)) {
            $sql .= ' WHERE ' . implode(' AND ', $condiciones);
        }

        $sql .= ' ORDER BY i.fecha DESC, i.hora DESC, i.id DESC';

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener(int $id, ?int $usuarioId = null, bool $bloquear = false): ?array
    {
        $sql = 'SELECT i.*,
                       u.nombre AS docente_nombre,
                       u.nombre AS nombre_profesor,
                       "" AS apellido_profesor,
                       a.nombre AS taller,
                       a.nombre AS aula_nombre,
                       a.codigo AS aula_codigo
                FROM reportes_incidencias i
                INNER JOIN usuarios u ON u.id = i.usuario_id
                INNER JOIN aulas a ON a.id = i.aula_id
                WHERE i.id = :id';

        $parametros = ['id' => $id];
        if ($usuarioId !== null) {
            $sql .= ' AND i.usuario_id = :usuario_id';
            $parametros['usuario_id'] = $usuarioId;
        }
        if ($bloquear) {
            $sql .= ' FOR UPDATE';
        }

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila === false ? null : $fila;
    }

    public function crear(int $usuarioId, array $datos): int
    {
        $aulaId = $this->resolverAulaId($datos);

        $stmt = $this->conexion->prepare('INSERT INTO reportes_incidencias
            (usuario_id, aula_id, asignatura, grupo, turno, fecha, hora, tipo_solicitud, otro_tipo, descripcion, codigo_equipo)
            VALUES (:usuario_id, :aula_id, :asignatura, :grupo, :turno, :fecha, :hora, :tipo_solicitud, :otro_tipo, :descripcion, :codigo_equipo)');

        $stmt->execute([
            'usuario_id' => $usuarioId,
            'aula_id' => $aulaId,
            'asignatura' => (string) ($datos['asignatura'] ?? ''),
            'grupo' => !empty($datos['grupo']) ? (string) $datos['grupo'] : null,
            'turno' => (string) ($datos['turno'] ?? 'matutino'),
            'fecha' => (string) ($datos['fecha'] ?? date('Y-m-d')),
            'hora' => (string) ($datos['hora'] ?? date('H:i:s')),
            'tipo_solicitud' => (string) ($datos['tipo_solicitud'] ?? 'rotura-equipo'),
            'otro_tipo' => !empty($datos['otro_tipo']) ? (string) $datos['otro_tipo'] : null,
            'descripcion' => !empty($datos['descripcion']) ? (string) $datos['descripcion'] : null,
            'codigo_equipo' => !empty($datos['codigo_equipo']) ? (string) $datos['codigo_equipo'] : null,
        ]);

        return (int) $this->conexion->lastInsertId();
    }

    public function modificar(int $id, int $usuarioId, array $cambios): bool
    {
        $asignaciones = [];
        $parametros = ['id' => $id, 'usuario_id' => $usuarioId];

        // Mapear aula si viene como taller o aula_id
        if (array_key_exists('aula_id', $cambios) || array_key_exists('taller', $cambios)) {
            $aulaId = $this->resolverAulaId($cambios);
            $asignaciones[] = 'aula_id = :aula_id';
            $parametros['aula_id'] = $aulaId;
        }

        foreach (['asignatura', 'grupo', 'turno', 'tipo_solicitud', 'otro_tipo', 'descripcion', 'codigo_equipo'] as $campo) {
            if (array_key_exists($campo, $cambios)) {
                $asignaciones[] = "$campo = :$campo";
                $parametros[$campo] = $cambios[$campo];
            }
        }

        if (empty($asignaciones)) {
            throw new InvalidArgumentException('No hay campos editables.');
        }

        $sql = 'UPDATE reportes_incidencias SET ' . implode(', ', $asignaciones)
            . " WHERE id = :id AND usuario_id = :usuario_id AND estado = 'pendiente'";

        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute($parametros);
    }

    public function marcarEnProceso(int $id): bool
    {
        $stmt = $this->conexion->prepare("UPDATE reportes_incidencias SET estado = 'en_proceso' WHERE id = :id AND estado = 'pendiente'");
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() === 1;
    }

    public function resolver(int $id, string $estado, string $solucion): bool
    {
        $stmt = $this->conexion->prepare('UPDATE reportes_incidencias SET estado = :estado, solucion = :solucion WHERE id = :id');
        return $stmt->execute(['id' => $id, 'estado' => $estado, 'solucion' => $solucion]);
    }

    public function eliminar(int $id, int $usuarioId): bool
    {
        $stmt = $this->conexion->prepare("DELETE FROM reportes_incidencias WHERE id = :id AND usuario_id = :usuario_id AND estado = 'pendiente'");
        $stmt->execute(['id' => $id, 'usuario_id' => $usuarioId]);
        return $stmt->rowCount() === 1;
    }

    public function transaccion(callable $operacion): mixed
    {
        $this->conexion->beginTransaction();
        try {
            $resultado = $operacion();
            $this->conexion->commit();
            return $resultado;
        } catch (Throwable $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $error;
        }
    }

    private function resolverAulaId(array $datos): int
    {
        $id = $datos['aula_id'] ?? ($datos['taller'] ?? null);
        if ($id !== null && is_numeric($id) && (int) $id > 0) {
            return (int) $id;
        }

        if (is_string($id) && trim($id) !== '') {
            $stmt = $this->conexion->prepare('SELECT id FROM aulas WHERE codigo = :c OR nombre = :n LIMIT 1');
            $stmt->execute(['c' => trim($id), 'n' => trim($id)]);
            $col = $stmt->fetchColumn();
            if ($col !== false) {
                return (int) $col;
            }
        }

        // Si no se encontró, buscar la primera aula activa
        $stmt = $this->conexion->query('SELECT id FROM aulas WHERE activo = 1 LIMIT 1');
        $primera = $stmt->fetchColumn();
        return $primera !== false ? (int) $primera : 1;
    }
}
