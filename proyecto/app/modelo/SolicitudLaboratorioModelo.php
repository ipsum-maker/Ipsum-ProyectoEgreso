<?php
declare(strict_types=1);

require_once __DIR__ . '/SolicitudLaboratorioDAO.php';

/**
 * Modelo para Solicitudes de Laboratorio / Salones.
 * Delega en SolicitudLaboratorioDAO manteniendo compatibilidad total.
 */
class SolicitudLaboratorioModelo
{
    private SolicitudLaboratorioDAO $dao;

    public function __construct(?PDO $conexion = null)
    {
        $this->dao = new SolicitudLaboratorioDAO($conexion);
    }

    public function obtener(?int $usuarioId = null): array
    {
        return $this->dao->obtener($usuarioId);
    }

    public function obtenerPorId(int $id): ?array
    {
        return $this->dao->obtenerPorId($id);
    }

    public function obtenerPendientes(): array
    {
        return $this->dao->obtenerPendientes();
    }

    public function obtenerHistorial(): array
    {
        return $this->dao->obtenerHistorial();
    }

    public function disponibilidad(int $aulaId, string $fecha): array
    {
        return $this->dao->disponibilidad($aulaId, $fecha);
    }

    public function crear(array $datos): array
    {
        return $this->dao->crear($datos);
    }

    public function decidir(int $id, int $revisorId, string $estado, ?string $respuesta): array
    {
        return $this->dao->decidir($id, $revisorId, $estado, $respuesta);
    }
}
