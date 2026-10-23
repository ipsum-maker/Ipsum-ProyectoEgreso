<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Conexion.php';

class ReporteDiarioModelo
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::obtener();
    }

    // Inserta un reporte diario y sus asignaciones de PCs en una sola transacción
    public function crear(
        int $usuarioId,
        int $aulaId,
        string $fecha,
        string $hora,
        array $equipos,
        string $turno = 'matutino',
        ?string $horaEntrada = null,
        ?string $horaSalida = null,
        ?string $grupo = null,
        ?string $asignatura = null
    ): bool {
        try {
            $this->conexion->beginTransaction();

            $sql = 'INSERT INTO reportes_diarios 
                    (usuario_id, aula_id, fecha, hora, turno, hora_entrada, hora_salida, grupo, asignatura) 
                    VALUES (:usuario_id, :aula_id, :fecha, :hora, :turno, :hora_entrada, :hora_salida, :grupo, :asignatura)';
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute([
                ':usuario_id' => $usuarioId,
                ':aula_id' => $aulaId,
                ':fecha' => $fecha,
                ':hora' => $hora,
                ':turno' => $turno,
                ':hora_entrada' => $horaEntrada,
                ':hora_salida' => $horaSalida,
                ':grupo' => $grupo,
                ':asignatura' => $asignatura,
            ]);

            $reporteId = (int) $this->conexion->lastInsertId();

            if (!empty($equipos)) {
                $sqlPc = 'INSERT INTO reporte_diario_pc (reporte_diario_id, pc, alumno) VALUES (:reporte_id, :pc, :alumno)';
                $stmtPc = $this->conexion->prepare($sqlPc);

                foreach ($equipos as $equipo) {
                    $pc = trim((string) ($equipo['pc'] ?? ''));
                    $alumno = trim((string) ($equipo['alumno'] ?? ''));

                    if ($pc !== '' && $alumno !== '') {
                        $stmtPc->execute([
                            ':reporte_id' => $reporteId,
                            ':pc' => $pc,
                            ':alumno' => $alumno,
                        ]);
                    }
                }
            }

            $this->conexion->commit();
            return true;
        } catch (PDOException $error) {
            $this->conexion->rollBack();
            error_log('Error al crear reporte diario: ' . $error->getMessage());
            throw $error;
        }
    }

    // Obtiene todos los reportes diarios (para coordinación)
    public function obtenerTodos(): array
    {
        $sql = 'SELECT r.*, 
                       u.nombre AS docente_nombre,
                       u.nombre AS nombre_profesor,
                       "" AS apellido_profesor,
                       a.nombre AS aula,
                       a.nombre AS aula_nombre,
                       a.codigo AS aula_codigo,
                       COUNT(p.id) as cantidad_equipos 
                FROM reportes_diarios r 
                INNER JOIN usuarios u ON r.usuario_id = u.id 
                INNER JOIN aulas a ON r.aula_id = a.id 
                LEFT JOIN reporte_diario_pc p ON r.id = p.reporte_diario_id 
                GROUP BY r.id 
                ORDER BY r.fecha DESC, r.hora DESC';
        $stmt = $this->conexion->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    // Obtiene los reportes diarios de un profesor específico
    public function obtenerPorUsuario(int $usuarioId): array
    {
        $sql = 'SELECT r.*, 
                       u.nombre AS docente_nombre,
                       u.nombre AS nombre_profesor,
                       "" AS apellido_profesor,
                       a.nombre AS aula,
                       a.nombre AS aula_nombre,
                       a.codigo AS aula_codigo,
                       COUNT(p.id) as cantidad_equipos 
                FROM reportes_diarios r 
                INNER JOIN usuarios u ON r.usuario_id = u.id 
                INNER JOIN aulas a ON r.aula_id = a.id 
                LEFT JOIN reporte_diario_pc p ON r.id = p.reporte_diario_id 
                WHERE r.usuario_id = :usuario_id 
                GROUP BY r.id 
                ORDER BY r.fecha DESC, r.hora DESC';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPropio(int $reporteId, int $usuarioId): ?array
    {
        $sql = 'SELECT * FROM reportes_diarios WHERE id = :id AND usuario_id = :uid LIMIT 1';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $reporteId, ':uid' => $usuarioId]);
        $reporte = $stmt->fetch(PDO::FETCH_ASSOC);
        return $reporte !== false ? $reporte : null;
    }

    public function actualizar(
        int $reporteId,
        int $usuarioId,
        int $aulaId,
        string $grupo,
        string $asignatura,
        string $turno,
        string $horaEntrada,
        string $horaSalida
    ): bool {
        $sql = 'UPDATE reportes_diarios
                SET aula_id = :aula_id, grupo = :grupo, asignatura = :asignatura, turno = :turno, hora_entrada = :entrada, hora_salida = :salida
                WHERE id = :id AND usuario_id = :uid';
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':aula_id' => $aulaId,
            ':grupo' => $grupo,
            ':asignatura' => $asignatura,
            ':turno' => $turno,
            ':entrada' => $horaEntrada,
            ':salida' => $horaSalida,
            ':id' => $reporteId,
            ':uid' => $usuarioId,
        ]);
    }

    // Obtiene los equipos asignados a un reporte diario específico
    public function obtenerEquipos(int $reporteId): array
    {
        $sql = 'SELECT pc, alumno FROM reporte_diario_pc WHERE reporte_diario_id = :reporte_id ORDER BY id ASC';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':reporte_id' => $reporteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
