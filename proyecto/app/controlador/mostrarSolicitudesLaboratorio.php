<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';
require_once __DIR__ . '/../modelo/SolicitudLaboratorioModelo.php';

$usuario = exigirRol(['coordinador', 'administrador']);
$csrfToken = obtenerTokenCSRF();
$mensaje = obtenerMensaje();
$rutaPanel = rutaPanelSegunRol($usuario['rol']);

$modelo = new SolicitudLaboratorioModelo();
$pendientes = $modelo->obtenerPendientes();
$historial = $modelo->obtenerHistorial();

require __DIR__ . '/../vista/coordinacion/solicitudes-laboratorio.php';
