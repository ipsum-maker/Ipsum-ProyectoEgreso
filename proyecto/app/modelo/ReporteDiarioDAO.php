<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Conexion.php';

final class ReporteDiarioDAO
{
    public function __construct(private ?PDO $conexion = null)
    {
        if ($this->conexion === null) {
            $this->conexion = Conexion::obtener();
        }
    }

    public function listar(?int $usuarioId): array
    {
        $sql = 'SELECT r.*,
                       a.nombre AS aula,
                       a.nombre AS aula_nombre,
                       a.codigo AS aula_codigo,
                       u.nombre AS docente_nombre,
                       u.nombre AS nombre_profesor,
                       "" AS apellido_profesor,
                       (r.creado_en >= NOW() - INTERVAL 10 MINUTE) AS editable,
                       TIMESTAMPDIFF(MINUTE, r.creado_en, NOW()) AS minutos_transcurridos,
                       (SELECT COUNT(*) FROM reporte_diario_pc p
                        WHERE p.reporte_diario_id = r.id) AS cantidad_equipos
                FROM reportes_diarios r
                INNER JOIN aulas a ON a.id = r.aula_id
                INNER JOIN usuarios u ON u.id = r.usuario_id';

        $parametros = [];
        if ($usuarioId !== null) {
            $sql .= ' WHERE r.usuario_id = :usuario_id';
            $parametros['usuario_id'] = $usuarioId;
        }

        $sql .= ' ORDER BY r.fecha DESC, r.hora DESC, r.id DESC';

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener(int $id, ?int $usuarioId): ?array
    {
        $sql = 'SELECT r.*,
                       a.nombre AS aula,
                       a.nombre AS aula_nombre,
                       a.codigo AS aula_codigo,
                       u.nombre AS docente_nombre,
                       u.nombre AS nombre_profesor,
                       "" AS apellido_profesor,
                       (r.creado_en >= NOW() - INTERVAL 10 MINUTE) AS editable
                FROM reportes_diarios r
                INNER JOIN aulas a ON a.id = r.aula_id
                INNER JOIN usuarios u ON u.id = r.usuario_id
                WHERE r.id = :id';

        $parametros = ['id' => $id];
        if ($usuarioId !== null) {
            $sql .= ' AND r.usuario_id = :usuario_id';
            $parametros['usuario_id'] = $usuarioId;
        }

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        $reporte = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($reporte === false) {
            return null;
        }

        $reporte['equipos'] = $this->obtenerEquipos($id);
        return $reporte;
    }

    public function obtenerEquipos(int $id): array
    {
        $stmt = $this->conexion->prepare('SELECT pc, alumno FROM reporte_diario_pc WHERE reporte_diario_id = :id ORDER BY id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear(int $usuarioId, array $datos): int
    {
        $this->conexion->beginTransaction();
        try {
            $equipos = $datos['equipos'] ?? [];
            $aulaId = $this->resolverAulaId($datos);

            $stmt = $this->conexion->prepare('INSERT INTO reportes_diarios
                (usuario_id, aula_id, fecha, hora, turno, hora_entrada, hora_salida, grupo, asignatura)
                VALUES (:usuario_id, :aula_id, :fecha, :hora, :turno, :hora_entrada, :hora_salida, :grupo, :asignatura)');

            $stmt->execute([
                'usuario_id' => $usuarioId,
                'aula_id' => $aulaId,
                'fecha' => (string) ($datos['fecha'] ?? date('Y-m-d')),
                'hora' => (string) ($datos['hora'] ?? ($datos['hora_entrada'] ?? date('H:i:s'))),
                'turno' => (string) ($datos['turno'] ?? 'matutino'),
                'hora_entrada' => !empty($datos['hora_entrada']) ? (string) $datos['hora_entrada'] : null,
                'hora_salida' => !empty($datos['hora_salida']) ? (string) $datos['hora_salida'] : null,
                'grupo' => !empty($datos['grupo']) ? (string) $datos['grupo'] : null,
                'asignatura' => !empty($datos['asignatura']) ? (string) $datos['asignatura'] : null,
            ]);

            $id = (int) $this->conexion->lastInsertId();

            if (!empty($equipos)) {
                $stmtPc = $this->conexion->prepare('INSERT INTO reporte_diario_pc (reporte_diario_id, pc, alumno) VALUES (:id, :pc, :alumno)');
                foreach ($equipos as $equipo) {
                    $pc = trim((string) ($equipo['pc'] ?? ''));
                    $alumno = trim((string) ($equipo['alumno'] ?? ''));
                    if ($pc !== '' && $alumno !== '') {
                        $stmtPc->execute(['id' => $id, 'pc' => $pc, 'alumno' => $alumno]);
                    }
                }
            }

            $this->conexion->commit();
            return $id;
        } catch (Throwable $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $error;
        }
    }

    public function modificar(int $id, int $usuarioId, array $cambios): string
    {
        $this->conexion->beginTransaction();
        try {
            $stmt = $this->conexion->prepare('SELECT id, (creado_en >= NOW() - INTERVAL 10 MINUTE) AS editable
                FROM reportes_diarios WHERE id = :id AND usuario_id = :uid FOR UPDATE');
            $stmt->execute(['id' => $id, 'uid' => $usuarioId]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$fila || !(bool) $fila['editable']) {
                $this->conexion->rollBack();
                return !$fila ? 'no_encontrado' : 'vencido';
            }

            $asignaciones = [];
            $parametros = ['id' => $id, 'uid' => $usuarioId];

            if (array_key_exists('aula_id', $cambios) || array_key_exists('aula', $cambios)) {
                $aulaId = $this->resolverAulaId($cambios);
                $asignaciones[] = 'aula_id = :aula_id';
                $parametros['aula_id'] = $aulaId;
            }

            foreach (['grupo', 'asignatura', 'turno', 'hora_entrada', 'hora_salida'] as $campo) {
                if (array_key_exists($campo, $cambios)) {
                    $asignaciones[] = "$campo = :$campo";
                    $parametros[$campo] = $cambios[$campo];
                }
            }

            if (empty($asignaciones)) {
                throw new InvalidArgumentException('Sin cambios para actualizar.');
            }

            $stmt = $this->conexion->prepare('UPDATE reportes_diarios SET ' . implode(', ', $asignaciones) . ' WHERE id = :id AND usuario_id = :uid');
            $stmt->execute($parametros);
            $this->conexion->commit();
            return 'actualizado';
        } catch (Throwable $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $error;
        }
    }

    private function resolverAulaId(array $datos): int
    {
        $id = $datos['aula_id'] ?? ($datos['aula'] ?? null);
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

        $stmt = $this->conexion->query('SELECT id FROM aulas WHERE activo = 1 LIMIT 1');
        $primera = $stmt->fetchColumn();
        return $primera !== false ? (int) $primera : 1;
    }
}
