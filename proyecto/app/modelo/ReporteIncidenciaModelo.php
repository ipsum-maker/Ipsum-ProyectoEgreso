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
        string $descripcion,
        ?string $grupo = null,
        string $turno = 'matutino'
    ): bool {
        $sql = 'INSERT INTO reportes_incidencias 
                (usuario_id, nombre_profesor, apellido_profesor, asignatura, fecha, hora, tipo_solicitud, otro_tipo, taller, descripcion, grupo, turno) 
                VALUES (:usuario_id, :nombre, :apellido, :asignatura, :fecha, :hora, :tipo, :otro_tipo, :taller, :descripcion, :grupo, :turno)';

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
            ':grupo' => $grupo,
            ':turno' => $turno,
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

    // Obtiene una incidencia por su ID
    public function obtenerPorId(int $id): ?array
    {
        $sql = 'SELECT * FROM reportes_incidencias WHERE id = :id LIMIT 1';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res !== false ? $res : null;
    }

    // Cambia únicamente el estado (ej: a 'en_proceso')
    public function cambiarEstado(int $id, string $nuevoEstado): bool
    {
        $sql = 'UPDATE reportes_incidencias SET estado = :estado WHERE id = :id';
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([':estado' => $nuevoEstado, ':id' => $id]);
    }

    // Elimina una incidencia solo si pertenece al usuario y está pendiente
    public function eliminar(int $id, int $usuarioId): bool
    {
        $sql = "DELETE FROM reportes_incidencias WHERE id = :id AND usuario_id = :usuario_id AND estado = 'pendiente'";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([':id' => $id, ':usuario_id' => $usuarioId]);
    }

    // Actualiza una incidencia si está en estado 'pendiente'
    public function actualizar(
        int $id,
        int $usuarioId,
        string $nombreProfesor,
        string $apellidoProfesor,
        string $asignatura,
        ?string $grupo,
        string $turno,
        string $tipoSolicitud,
        ?string $otroTipo,
        string $taller,
        string $descripcion
    ): bool {
        $sql = "UPDATE reportes_incidencias 
                SET nombre_profesor = :nombre, apellido_profesor = :apellido, asignatura = :asignatura, 
                    grupo = :grupo, turno = :turno, tipo_solicitud = :tipo, otro_tipo = :otro_tipo, 
                    taller = :taller, descripcion = :descripcion 
                WHERE id = :id AND usuario_id = :usuario_id AND estado = 'pendiente'";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':usuario_id' => $usuarioId,
            ':nombre' => $nombreProfesor,
            ':apellido' => $apellidoProfesor,
            ':asignatura' => $asignatura,
            ':grupo' => $grupo,
            ':turno' => $turno,
            ':tipo' => $tipoSolicitud,
            ':otro_tipo' => $otroTipo,
            ':taller' => $taller,
            ':descripcion' => $descripcion,
        ]);
    }

    // Marca una incidencia con un nuevo estado y guarda la solución
    public function resolver(int $incidenciaId, string $solucion, string $estado = 'resuelto'): bool
    {
        $sql = "UPDATE reportes_incidencias SET estado = :estado, solucion = :solucion WHERE id = :id";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':estado' => $estado,
            ':solucion' => $solucion,
            ':id' => $incidenciaId,
        ]);
    }

    // Obtiene las incidencias resueltas (para el panel de administración)
    public function obtenerResueltas(): array
    {
        $sql = "SELECT * FROM reportes_incidencias WHERE estado = 'resuelto' ORDER BY fecha DESC, hora DESC";
        $stmt = $this->conexion->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
