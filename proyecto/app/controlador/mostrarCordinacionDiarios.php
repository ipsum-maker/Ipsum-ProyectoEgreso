<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../modelo/ReporteDiarioModelo.php';

$usuario = exigirRol(['coordinador', 'administrador']);

$modelo = new ReporteDiarioModelo();
$reportesDiarios = $modelo->obtenerTodos();

foreach ($reportesDiarios as &$rd) {
    $rd['equipos_detalle'] = $modelo->obtenerEquipos((int) $rd['id']);
}
unset($rd);

require __DIR__ . '/../vista/coordinacion/cordinacion-diarios.php';
