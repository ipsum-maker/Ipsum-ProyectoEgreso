<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../modelo/ReporteIncidenciaModelo.php';

$usuario = exigirRol(['coordinador', 'administrador']);

$modelo = new ReporteIncidenciaModelo();
$incidencias = $modelo->obtenerTodas();
$mensaje = obtenerMensaje();

require __DIR__ . '/../vista/coordinacion/cordinacion-incidencias.php';
