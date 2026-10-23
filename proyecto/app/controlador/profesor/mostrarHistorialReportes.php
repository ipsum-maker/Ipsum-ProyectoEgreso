<?php

declare(strict_types=1);

require_once __DIR__ . '/../../utilidades/sesion.php';
require_once __DIR__ . '/../../modelo/ReporteDiarioModelo.php';
require_once __DIR__ . '/../../modelo/ReporteIncidenciaModelo.php';
require_once __DIR__ . '/../../modelo/AulaModelo.php';

$usuario = exigirRol(['profesor']);
$usuarioId = (int) $usuario['id'];
$mensaje = obtenerMensaje();

$diarioModelo = new ReporteDiarioModelo();
$incidenciaModelo = new ReporteIncidenciaModelo();
$aulaModelo = new AulaModelo();

$reportesDiarios = $diarioModelo->obtenerPorUsuario($usuarioId);
$reportesIncidencias = $incidenciaModelo->obtenerPorUsuario($usuarioId);
$aulas = $aulaModelo->obtenerTodasActivas();

$totalTodos = count($reportesDiarios) + count($reportesIncidencias);
$totalDiarios = count($reportesDiarios);
$totalIncidencias = count($reportesIncidencias);

require __DIR__ . '/../../vista/profesor/historial-reportes.php';
