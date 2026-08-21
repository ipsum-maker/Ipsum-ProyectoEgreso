<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../modelo/AulaModelo.php';

$usuario = exigirRol(['profesor']);

$aulaModelo = new AulaModelo();
$aulas = $aulaModelo->obtenerTodasActivas();

require __DIR__ . '/../vista/profesor/reporte-diario.php';
