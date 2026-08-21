<?php

declare(strict_types=1);

require_once __DIR__ . '/../modelo/ReporteIncidenciaModelo.php';
require_once __DIR__ . '/../utilidades/sesion.php';

$usuario = exigirRol(['coordinador', 'administrador']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(urlRuta('cordinacion-incidencias'));
}

$tokenRecibido = (string) ($_POST['csrf_token'] ?? '');
if (!verificarTokenCSRF($tokenRecibido)) {
    guardarMensaje('error', 'Solicitud no válida. Intente nuevamente.');
    redirigir(urlRuta('cordinacion-incidencias'));
}

$incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);

if ($incidenciaId <= 0) {
    guardarMensaje('error', 'Incidencia no válida.');
    redirigir(urlRuta('cordinacion-incidencias'));
}

try {
    $modelo = new ReporteIncidenciaModelo();
    $modelo->cambiarEstado($incidenciaId, 'en_proceso');
    guardarMensaje('exito', 'La incidencia se ha marcado como "En proceso".');
} catch (PDOException $error) {
    guardarMensaje('error', 'Error al actualizar el estado en la base de datos.');
}

redirigir(urlRuta('cordinacion-incidencias'));
