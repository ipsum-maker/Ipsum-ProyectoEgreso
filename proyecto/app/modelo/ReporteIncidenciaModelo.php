<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class ReporteIncidenciaModelo
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::obtener();
    }

    public function crear(
        int $usuarioId,
        string $nombreProfesor,
        string $apellidoProfesor,
        string $asignatura,
        string $fecha,
        string $hora,
        string $tipoSolicitud,
        ?string $otroTipo,
        string $taller,
        string $descripcion
    ): bool {
        $sql = 'INSERT INTO reportes_incidencias 
                (usuario_id, nombre_profesor, apellido_profesor, asignatura, fecha, hora, tipo_solicitud, otro_tipo, taller, descripcion) 
                VALUES (:usuario_id, :nombre, :apellido, :asignatura, :fecha, :hora, :tipo, :otro_tipo, :taller, :descripcion)';

        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':nombre' => $nombreProfesor,
            ':apellido' => $apellidoProfesor,
            ':asignatura' => $asignatura,
            ':fecha' => $fecha,
            ':hora' => $hora,
            ':tipo' => $tipoSolicitud,
            ':otro_tipo' => $otroTipo,
            ':taller' => $taller,
            ':descripcion' => $descripcion,
        ]);
    }

    // Obtiene todas las incidencias (para coordinación)
    public function obtenerTodas(): array
    {
        $sql = 'SELECT * FROM reportes_incidencias ORDER BY fecha DESC, hora DESC';
        $stmt = $this->conexion->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtiene las incidencias de un profesor específico
    public function obtenerPorUsuario(int $usuarioId): array
    {
        $sql = 'SELECT * FROM reportes_incidencias WHERE usuario_id = :usuario_id ORDER BY fecha DESC, hora DESC';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Marca una incidencia como resuelta y guarda la solución
    public function resolver(int $incidenciaId, string $solucion): bool
    {
        $sql = "UPDATE reportes_incidencias SET estado = 'resuelto', solucion = :solucion WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':solucion' => $solucion,
            ':id' => $incidenciaId,
        ]);
    }
}
