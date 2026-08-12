<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador se ocupa únicamente de mostrar el reporte diario.
$usuario = exigirRol(['profesor']);
require __DIR__ . '/../vista/profesor/reporte-diario.php';
