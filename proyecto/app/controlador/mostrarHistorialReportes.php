<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../modelo/ReporteDiarioModelo.php';
require_once __DIR__ . '/../modelo/ReporteIncidenciaModelo.php';

$usuario = exigirRol(['profesor']);
$usuarioId = (int) $usuario['id'];

$diarioModelo = new ReporteDiarioModelo();
$incidenciaModelo = new ReporteIncidenciaModelo();

$reportesDiarios = $diarioModelo->obtenerPorUsuario($usuarioId);
$reportesIncidencias = $incidenciaModelo->obtenerPorUsuario($usuarioId);

$totalTodos = count($reportesDiarios) + count($reportesIncidencias);
$totalDiarios = count($reportesDiarios);
$totalIncidencias = count($reportesIncidencias);

require __DIR__ . '/../vista/profesor/historial-reportes.php';
