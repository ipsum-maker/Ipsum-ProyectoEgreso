<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador se ocupa únicamente de mostrar el reporte de incidencia.
$usuario = exigirRol(['profesor']);
require __DIR__ . '/../vista/profesor/reporte-incidencia.php';
