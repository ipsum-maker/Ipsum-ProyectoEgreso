<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Conexion.php';

final class SolicitudLaboratorioDAO
{
    public function __construct(private ?PDO $conexion = null)
    {
        if ($this->conexion === null) {
            $this->conexion = Conexion::obtener();
        }
    }

    public function obtener(?int $usuarioId = null): array
    {
        $sql = $this->consultaLectura();
        $parametros = [];
        if ($usuarioId !== null) {
            $sql .= ' WHERE s.usuario_id = :usuario_id';
            $parametros['usuario_id'] = $usuarioId;
        }
        $sql .= ' ORDER BY s.fecha DESC, s.hora_inicio DESC, s.id DESC';
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conexion->prepare($this->consultaLectura() . ' WHERE s.id = :id');
        $stmt->execute(['id' => $id]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila === false ? null : $fila;
    }

    public function obtenerPendientes(): array
    {
        $sql = $this->consultaLectura() . " WHERE s.estado = 'pendiente' ORDER BY s.fecha ASC, s.hora_inicio ASC";
        $stmt = $this->conexion->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerHistorial(): array
    {
        $sql = $this->consultaLectura() . " WHERE s.estado IN ('aprobada', 'rechazada') ORDER BY s.decidido_en DESC, s.id DESC";
        $stmt = $this->conexion->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function disponibilidad(int $aulaId, string $fecha): array
    {
        $stmtAula = $this->conexion->prepare('SELECT id FROM aulas WHERE id = :id AND activo = 1');
        $stmtAula->execute(['id' => $aulaId]);
        if (!$stmtAula->fetch()) {
            throw new RuntimeException('Seleccione un aula, taller o laboratorio activo.', 422);
        }

        $consulta = $this->conexion->prepare(
            "SELECT hora_inicio, hora_fin FROM solicitudes_laboratorio
             WHERE aula_id = :aula_id AND fecha = :fecha AND estado = 'aprobada'
             ORDER BY hora_inicio ASC, hora_fin ASC"
        );
        $consulta->execute(['aula_id' => $aulaId, 'fecha' => $fecha]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear(array $datos): array
    {
        $this->validarIntervalo($datos);

        $this->conexion->beginTransaction();
        try {
            // Bloqueo del aula para evitar reservas cruzadas
            $stmtAula = $this->conexion->prepare('SELECT id, activo FROM aulas WHERE id = :id FOR UPDATE');
            $stmtAula->execute(['id' => (int) $datos['aula_id']]);
            $aula = $stmtAula->fetch(PDO::FETCH_ASSOC);
            if (!$aula || (int) $aula['activo'] !== 1) {
                throw new RuntimeException('Seleccione un aula, taller o laboratorio activo.', 422);
            }

            // Comprobar usuario
            $stmtUser = $this->conexion->prepare('SELECT id, rol, activo FROM usuarios WHERE id = :id FOR UPDATE');
            $stmtUser->execute(['id' => (int) $datos['usuario_id']]);
            $usuario = $stmtUser->fetch(PDO::FETCH_ASSOC);
            if (!$usuario || (int) $usuario['activo'] !== 1 || $usuario['rol'] !== 'profesor') {
                throw new RuntimeException('No tiene permiso para solicitar un espacio.', 403);
            }

            $this->exigirHorarioFuturo($datos);
            $this->exigirDisponibilidad($datos);

            $stmt = $this->conexion->prepare(
                'INSERT INTO solicitudes_laboratorio
                 (usuario_id, aula_id, fecha, hora_inicio, hora_fin, motivo)
                 VALUES (:usuario_id, :aula_id, :fecha, :hora_inicio, :hora_fin, :motivo)'
            );
            $stmt->execute([
                'usuario_id' => $datos['usuario_id'],
                'aula_id' => $datos['aula_id'],
                'fecha' => $datos['fecha'],
                'hora_inicio' => $datos['hora_inicio'],
                'hora_fin' => $datos['hora_fin'],
                'motivo' => $datos['motivo'],
            ]);

            $id = (int) $this->conexion->lastInsertId();
            $this->conexion->commit();
            return $this->obtenerPorId($id) ?? [];
        } catch (Throwable $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $error;
        }
    }

    public function decidir(int $id, int $revisorId, string $estado, ?string $respuesta): array
    {
        if (!in_array($estado, ['aprobada', 'rechazada'], true)) {
            throw new RuntimeException('La decisión debe ser aprobada o rechazada.', 422);
        }

        $this->conexion->beginTransaction();
        try {
            $stmtSol = $this->conexion->prepare('SELECT * FROM solicitudes_laboratorio WHERE id = :id FOR UPDATE');
            $stmtSol->execute(['id' => $id]);
            $solicitud = $stmtSol->fetch(PDO::FETCH_ASSOC);

            if (!$solicitud) {
                throw new RuntimeException('Solicitud no encontrada.', 404);
            }
            if ($solicitud['estado'] !== 'pendiente') {
                throw new RuntimeException('La solicitud ya fue decidida.', 409);
            }

            // Comprobar rol de revisor
            $stmtRev = $this->conexion->prepare('SELECT id, rol, activo FROM usuarios WHERE id = :id FOR UPDATE');
            $stmtRev->execute(['id' => $revisorId]);
            $revisor = $stmtRev->fetch(PDO::FETCH_ASSOC);
            if (!$revisor || (int) $revisor['activo'] !== 1 || !in_array($revisor['rol'], ['coordinador', 'administrador'], true)) {
                throw new RuntimeException('No tiene permiso para decidir solicitudes.', 403);
            }

            if ($estado === 'aprobada') {
                $this->validarIntervalo($solicitud);
                $this->exigirHorarioFuturo($solicitud);
                $this->exigirDisponibilidad($solicitud, $id);
            }

            $stmtUpdate = $this->conexion->prepare(
                "UPDATE solicitudes_laboratorio SET estado = :estado, respuesta = :respuesta,
                 revisor_id = :revisor_id, decidido_en = CURRENT_TIMESTAMP
                 WHERE id = :id AND estado = 'pendiente'"
            );
            $stmtUpdate->execute([
                'estado' => $estado,
                'respuesta' => ($respuesta !== null && trim($respuesta) !== '') ? trim($respuesta) : null,
                'revisor_id' => $revisorId,
                'id' => $id,
            ]);

            $this->conexion->commit();
            return $this->obtenerPorId($id) ?? [];
        } catch (Throwable $error) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            throw $error;
        }
    }

    private function consultaLectura(): string
    {
        return 'SELECT s.*,
                       a.codigo AS aula_codigo,
                       a.nombre AS aula_nombre,
                       a.tipo AS aula_tipo,
                       u.nombre AS profesor_nombre,
                       u.cedula AS profesor_cedula,
                       r.nombre AS revisor_nombre
                FROM solicitudes_laboratorio s
                INNER JOIN aulas a ON a.id = s.aula_id
                INNER JOIN usuarios u ON u.id = s.usuario_id
                LEFT JOIN usuarios r ON r.id = s.revisor_id';
    }

    private function validarIntervalo(array $datos): void
    {
        $fecha = (string) ($datos['fecha'] ?? '');
        $inicio = (string) ($datos['hora_inicio'] ?? '');
        $fin = (string) ($datos['hora_fin'] ?? '');

        if (strlen($inicio) === 5) $inicio .= ':00';
        if (strlen($fin) === 5) $fin .= ':00';

        $dia = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        if (!$dia || $dia->format('Y-m-d') !== $fecha
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/D', $inicio)
            || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/D', $fin)
            || $inicio >= $fin) {
            throw new RuntimeException('Indique una fecha y un horario válidos; la hora de fin debe ser posterior al inicio del mismo día.', 422);
        }
    }

    private function exigirHorarioFuturo(array $datos): void
    {
        $stmt = $this->conexion->query('SELECT CURRENT_DATE() AS fecha_actual, CURRENT_TIME() AS hora_actual');
        $reloj = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reloj) {
            throw new RuntimeException('No se pudo comprobar la fecha y hora del servidor.', 503);
        }

        $inicio = (string) $datos['hora_inicio'];
        if (strlen($inicio) === 5) $inicio .= ':00';

        if ($datos['fecha'] < $reloj['fecha_actual']
            || ($datos['fecha'] === $reloj['fecha_actual'] && $inicio <= $reloj['hora_actual'])) {
            throw new RuntimeException('El inicio de la solicitud debe ser posterior a la fecha y hora actuales.', 422);
        }
    }

    private function exigirDisponibilidad(array $datos, ?int $excluirId = null): void
    {
        $sql = "SELECT id FROM solicitudes_laboratorio
                WHERE aula_id = :aula_id AND fecha = :fecha AND estado = 'aprobada'
                  AND hora_inicio < :hora_fin AND hora_fin > :hora_inicio";
        $parametros = [
            'aula_id' => (int) $datos['aula_id'],
            'fecha' => (string) $datos['fecha'],
            'hora_inicio' => (string) $datos['hora_inicio'],
            'hora_fin' => (string) $datos['hora_fin'],
        ];
        if ($excluirId !== null) {
            $sql .= ' AND id <> :excluir_id';
            $parametros['excluir_id'] = $excluirId;
        }
        $sql .= ' LIMIT 1 FOR UPDATE';

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        if ($stmt->fetch()) {
            throw new RuntimeException('El espacio ya tiene una solicitud aprobada que se superpone con ese horario.', 409);
        }
    }
}
