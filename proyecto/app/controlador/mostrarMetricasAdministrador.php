<?php

declare(strict_types=1);

require_once __DIR__ . '/../modelo/MetricasModelo.php';
require_once __DIR__ . '/../utilidades/sesion.php';

// 1. Control de acceso: exclusivo para rol administrador
$usuario = exigirRol(['administrador']);

// 2. Gestión de filtros temporales (HTTP GET)
$periodo = (string) ($_GET['periodo'] ?? 'ultimos-30-dias');
$hoy = date('Y-m-d');

$fechaDesde = $hoy;
$fechaHasta = $hoy;

switch ($periodo) {
    case 'hoy':
        $fechaDesde = $hoy;
        $fechaHasta = $hoy;
        break;

    case 'esta-semana':
        // Lunes de la semana en curso hasta hoy
        $fechaDesde = date('Y-m-d', strtotime('monday this week'));
        $fechaHasta = $hoy;
        break;

    case 'personalizado':
        $desdeInput = (string) ($_GET['fecha_desde'] ?? '');
        $hastaInput = (string) ($_GET['fecha_hasta'] ?? '');

        // Validación de formato YYYY-MM-DD
        $esValidaDesde = !empty($desdeInput) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $desdeInput);
        $esValidaHasta = !empty($hastaInput) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $hastaInput);

        $fechaDesde = $esValidaDesde ? $desdeInput : date('Y-m-d', strtotime('-30 days'));
        $fechaHasta = $esValidaHasta ? $hastaInput : $hoy;

        // Si la fecha desde es posterior a la fecha hasta, las invertimos para no romper la consulta
        if ($fechaDesde > $fechaHasta) {
            $temporal = $fechaDesde;
            $fechaDesde = $fechaHasta;
            $fechaHasta = $temporal;
        }
        break;

    case 'ultimos-30-dias':
    default:
        $periodo = 'ultimos-30-dias';
        $fechaDesde = date('Y-m-d', strtotime('-30 days'));
        $fechaHasta = $hoy;
        break;
}

// 3. Orquestación con el Modelo
$modelo = new MetricasModelo();
$kpis = $modelo->obtenerResumenKPIs($fechaDesde, $fechaHasta);
$incidenciasPorAula = $modelo->obtenerIncidenciasPorAula($fechaDesde, $fechaHasta);
$distribucionTipos = $modelo->obtenerDistribucionTipos($fechaDesde, $fechaHasta);
$incidenciasRecientes = $modelo->obtenerIncidenciasRecientes($fechaDesde, $fechaHasta, 5);

// 4. Preparación de datos para la presentación
$maxIncidencias = 1;
foreach ($incidenciasPorAula as $aula) {
    $total = (int) $aula['total_incidencias'];
    if ($total > $maxIncidencias) {
        $maxIncidencias = $total;
    }
}

// 5. Renderizado de la Vista
require __DIR__ . '/../vista/administrador/metricas-administrador.php';
