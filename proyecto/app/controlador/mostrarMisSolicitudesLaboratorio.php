<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../modelo/SolicitudLaboratorioModelo.php';

$usuario = exigirRol(['profesor']);
$csrfToken = obtenerTokenCSRF();
$mensaje = obtenerMensaje();

$modelo = new SolicitudLaboratorioModelo();
$solicitudes = $modelo->obtener((int) $usuario['id']);

require __DIR__ . '/../vista/profesor/mis-solicitudes-laboratorio.php';
