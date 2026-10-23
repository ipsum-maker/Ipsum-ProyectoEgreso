<?php

declare(strict_types=1);

require_once __DIR__ . '/../../utilidades/sesion.php';
require_once __DIR__ . '/../../modelo/AulaModelo.php';

$usuario = exigirRol(['profesor']);
$csrfToken = obtenerTokenCSRF();
$mensaje = obtenerMensaje();

$aulaModelo = new AulaModelo();
$aulas = $aulaModelo->obtenerTodasActivas();
$fechaActual = date('Y-m-d');

require __DIR__ . '/../../vista/profesor/solicitar-laboratorio.php';
