<?php

declare(strict_types=1);

require_once __DIR__ . '/../../modelo/ReporteIncidenciaModelo.php';
require_once __DIR__ . '/../../utilidades/sesion.php';

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
$solucion = trim((string) ($_POST['solucion'] ?? ''));
$estado = trim((string) ($_POST['estado'] ?? 'resuelto'));

if ($incidenciaId <= 0 || $solucion === '' || !in_array($estado, ['pendiente', 'en_proceso', 'resuelto'], true)) {
    guardarMensaje('error', 'Debe ingresar una descripción válida de la solución.');
    redirigir(urlRuta('cordinacion-incidencias'));
}

try {
    $modelo = new ReporteIncidenciaModelo();
    $modelo->resolver($incidenciaId, $solucion, $estado);
    guardarMensaje('exito', 'El estado de la incidencia ha sido actualizado.');
    redirigir(urlRuta('cordinacion-incidencias'));
} catch (PDOException $error) {
    guardarMensaje('error', 'Error al actualizar el estado de la incidencia en la base de datos.');
    redirigir(urlRuta('cordinacion-incidencias'));
}

