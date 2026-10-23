<?php

declare(strict_types=1);

require_once __DIR__ . '/../../modelo/ReporteIncidenciaModelo.php';
require_once __DIR__ . '/../../utilidades/sesion.php';

$usuario = exigirRol(['profesor']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(urlRuta('historial-reportes'));
}

$tokenRecibido = (string) ($_POST['csrf_token'] ?? '');
if (!verificarTokenCSRF($tokenRecibido)) {
    guardarMensaje('error', 'Solicitud no válida. Intente nuevamente.');
    redirigir(urlRuta('historial-reportes'));
}

$incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);
$aulaId = (int) ($_POST['aula_id'] ?? $_POST['taller'] ?? 0);
$asignatura = trim((string) ($_POST['asignatura'] ?? ''));
$grupo = trim((string) ($_POST['grupo'] ?? ''));
$turno = trim((string) ($_POST['turno'] ?? 'matutino'));
$tipoSolicitud = trim((string) ($_POST['tipoSolicitud'] ?? ''));
$otroTipo = trim((string) ($_POST['otroTipo'] ?? ''));
$descripcion = trim((string) ($_POST['descripcion'] ?? ''));

if ($incidenciaId <= 0 || $aulaId <= 0 || $asignatura === '' || $tipoSolicitud === '') {
    guardarMensaje('error', 'Complete los campos obligatorios.');
    redirigir(urlRuta('historial-reportes'));
}

try {
    $modelo = new ReporteIncidenciaModelo();
    $exito = $modelo->actualizar(
        $incidenciaId,
        (int) $usuario['id'],
        $aulaId,
        $asignatura,
        $grupo,
        $turno,
        $tipoSolicitud,
        $tipoSolicitud === 'otro' ? $otroTipo : null,
        $descripcion
    );

    if ($exito) {
        guardarMensaje('exito', 'Incidencia actualizada correctamente.');
    } else {
        guardarMensaje('error', 'No se pudo editar el reporte (puede que ya no esté en estado pendiente).');
    }
} catch (PDOException $error) {
    guardarMensaje('error', 'Error al actualizar la incidencia en la base de datos.');
}

redirigir(urlRuta('historial-reportes'));
