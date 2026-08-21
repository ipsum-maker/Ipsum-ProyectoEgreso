<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/conexion.php';

class AulaModelo
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::obtener();
    }

    // Obtiene todas las aulas/talleres/laboratorios activos ordenados
    public function obtenerTodasActivas(): array
    {
        $sql = 'SELECT * FROM aulas WHERE activo = 1 ORDER BY tipo ASC, nombre ASC';
        $stmt = $this->conexion->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
