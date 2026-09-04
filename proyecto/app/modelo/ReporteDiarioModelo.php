<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

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

        // Obtiene los equipos asignados a un reporte diario específico
    public function obtenerEquipos(int $reporteId): array
    {
        $sql = 'SELECT pc, alumno FROM reporte_diario_pc WHERE reporte_diario_id = :reporte_id ORDER BY id ASC';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':reporte_id' => $reporteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
