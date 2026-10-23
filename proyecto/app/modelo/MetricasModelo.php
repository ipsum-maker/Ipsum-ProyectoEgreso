<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Conexion.php';

class MetricasModelo
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::obtener();
    }

    /**
     * Obtiene el resumen de los 4 indicadores KPI superiores.
     * Todo el cálculo y agregación reside en el Modelo según la arquitectura MVC.
     */
    public function obtenerResumenKPIs(string $fechaDesde, string $fechaHasta): array
    {
        // 1. Incidencias activas (pendiente o en proceso) en el periodo
        $sqlActivas = 'SELECT COUNT(*) FROM reportes_incidencias 
                       WHERE estado IN ("pendiente", "en_proceso") 
                       AND fecha BETWEEN :desde AND :hasta';
        $stmtActivas = $this->conexion->prepare($sqlActivas);
        $stmtActivas->execute([':desde' => $fechaDesde, ':hasta' => $fechaHasta]);
        $incidenciasActivas = (int) $stmtActivas->fetchColumn();

        // 2. Conteo de total y resueltas para calcular la tasa de resolución
        $sqlTotales = 'SELECT 
                        COUNT(*) AS total,
                        SUM(CASE WHEN estado = "resuelto" THEN 1 ELSE 0 END) AS resueltas
                       FROM reportes_incidencias
                       WHERE fecha BETWEEN :desde AND :hasta';
        $stmtTotales = $this->conexion->prepare($sqlTotales);
        $stmtTotales->execute([':desde' => $fechaDesde, ':hasta' => $fechaHasta]);
        $totales = $stmtTotales->fetch(PDO::FETCH_ASSOC) ?: [];

        $totalIncidencias = (int) ($totales['total'] ?? 0);
        $totalResueltas = (int) ($totales['resueltas'] ?? 0);
        $tasaResolucion = $totalIncidencias > 0 
            ? round(($totalResueltas / $totalIncidencias) * 100, 1) 
            : 100.0;

        // 3. Tiempo promedio de resolución (en horas) para incidencias resueltas
        $sqlTiempo = 'SELECT AVG(TIMESTAMPDIFF(HOUR, creado_en, actualizado_en)) AS promedio_horas
                      FROM reportes_incidencias
                      WHERE estado = "resuelto" AND fecha BETWEEN :desde AND :hasta';
        $stmtTiempo = $this->conexion->prepare($sqlTiempo);
        $stmtTiempo->execute([':desde' => $fechaDesde, ':hasta' => $fechaHasta]);
        $horas = $stmtTiempo->fetchColumn();
        $promedioHoras = $horas !== null ? round((float) $horas, 1) : 0.0;

        // 4. Conteo de reportes diarios de uso de aula en el periodo
        $sqlDiarios = 'SELECT COUNT(*) FROM reportes_diarios WHERE fecha BETWEEN :desde AND :hasta';
        $stmtDiarios = $this->conexion->prepare($sqlDiarios);
        $stmtDiarios->execute([':desde' => $fechaDesde, ':hasta' => $fechaHasta]);
        $totalDiarios = (int) $stmtDiarios->fetchColumn();

        return [
            'activas' => $incidenciasActivas,
            'tasa_resolucion' => $tasaResolucion,
            'tiempo_promedio_horas' => $promedioHoras,
            'total_diarios' => $totalDiarios,
            'total_incidencias' => $totalIncidencias,
            'total_resueltas' => $totalResueltas,
        ];
    }

    /**
     * Obtiene el acumulado de incidencias por cada aula, taller o laboratorio.
     */
    public function obtenerIncidenciasPorAula(string $fechaDesde, string $fechaHasta): array
    {
        $sql = 'SELECT a.id, a.nombre, a.tipo, COUNT(i.id) AS total_incidencias
                FROM aulas a
                LEFT JOIN reportes_incidencias i 
                    ON a.id = i.aula_id AND i.fecha BETWEEN :desde AND :hasta
                WHERE a.activo = 1
                GROUP BY a.id, a.nombre, a.tipo
                ORDER BY total_incidencias DESC, a.nombre ASC';

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':desde' => $fechaDesde, ':hasta' => $fechaHasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el conteo agrupado por tipo de falla (rotura, falla, otro).
     */
    public function obtenerDistribucionTipos(string $fechaDesde, string $fechaHasta): array
    {
        $sql = 'SELECT tipo_solicitud, COUNT(*) AS cantidad
                FROM reportes_incidencias
                WHERE fecha BETWEEN :desde AND :hasta
                GROUP BY tipo_solicitud';

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':desde' => $fechaDesde, ':hasta' => $fechaHasta]);
        $resultados = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'rotura-equipo' => (int) ($resultados['rotura-equipo'] ?? 0),
            'falla-equipo'  => (int) ($resultados['falla-equipo'] ?? 0),
            'otro'          => (int) ($resultados['otro'] ?? 0),
        ];
    }

    /**
     * Obtiene las incidencias más recientes registradas en el periodo.
     */
    public function obtenerIncidenciasRecientes(string $fechaDesde, string $fechaHasta, int $limite = 5): array
    {
        $sql = 'SELECT i.id, i.fecha, i.hora, i.tipo_solicitud, i.otro_tipo, i.estado, i.descripcion,
                       a.nombre AS aula_nombre, u.nombre AS docente_nombre
                FROM reportes_incidencias i
                INNER JOIN aulas a ON i.aula_id = a.id
                INNER JOIN usuarios u ON i.usuario_id = u.id
                WHERE i.fecha BETWEEN :desde AND :hasta
                ORDER BY i.fecha DESC, i.hora DESC
                LIMIT ' . (int) $limite;

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':desde' => $fechaDesde, ':hasta' => $fechaHasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
