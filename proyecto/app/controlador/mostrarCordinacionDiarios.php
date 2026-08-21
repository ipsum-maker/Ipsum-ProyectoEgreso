<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../modelo/ReporteDiarioModelo.php';

$usuario = exigirRol(['coordinador', 'administrador']);

$modelo = new ReporteDiarioModelo();
$reportesDiarios = $modelo->obtenerTodos();

require __DIR__ . '/../vista/coordinacion/cordinacion-diarios.php';