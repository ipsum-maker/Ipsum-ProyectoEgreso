<?php

declare(strict_types=1);

require_once __DIR__ . '/../modelo/ReporteIncidenciaModelo.php';
require_once __DIR__ . '/../utilidades/sesion.php';

$usuario = exigirRol(['profesor']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(urlRuta('reporte-incidencia'));
}

$tokenRecibido = (string) ($_POST['csrf_token'] ?? '');
if (!verificarTokenCSRF($tokenRecibido)) {
    guardarMensaje('error', 'Solicitud no válida. Intente nuevamente.');
    redirigir(urlRuta('reporte-incidencia'));
}

$asignatura = trim((string) ($_POST['asignatura'] ?? ''));
$grupo = trim((string) ($_POST['grupo'] ?? ''));
$turno = trim((string) ($_POST['turno'] ?? 'matutino'));
$fecha = trim((string) ($_POST['fecha'] ?? date('Y-m-d')));
$hora = trim((string) ($_POST['hora'] ?? date('H:i')));
$tipoSolicitud = trim((string) ($_POST['tipoSolicitud'] ?? ''));
$otroTipo = trim((string) ($_POST['otroTipo'] ?? ''));
$aulaId = (int) ($_POST['aula_id'] ?? $_POST['taller'] ?? 0);
$descripcion = trim((string) ($_POST['descripcion'] ?? ''));
$codigoEquipo = trim((string) ($_POST['codigo_equipo'] ?? ''));

if ($aulaId <= 0 || $asignatura === '' || $tipoSolicitud === '') {
    guardarMensaje('error', 'Complete todos los campos obligatorios.');
    redirigir(urlRuta('reporte-incidencia'));
}

try {
    $modelo = new ReporteIncidenciaModelo();
    $modelo->crear(
        (int) $usuario['id'],
        $aulaId,
        $asignatura,
        $fecha,
        $hora,
        $tipoSolicitud,
        $tipoSolicitud === 'otro' ? $otroTipo : null,
        $descripcion,
        $grupo,
        $turno,
        $codigoEquipo !== '' ? $codigoEquipo : null
    );

    guardarMensaje('exito', 'Reporte de incidencia enviado correctamente.');
    redirigir(urlRuta('panel-profesores'));

} catch (PDOException $error) {
    guardarMensaje('error', 'Error al guardar la incidencia en la base de datos.');
    redirigir(urlRuta('reporte-incidencia'));
}
