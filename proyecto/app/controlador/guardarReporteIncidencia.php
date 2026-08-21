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

$nombreProfesor = trim((string) ($_POST['nombre'] ?? ''));
$apellidoProfesor = trim((string) ($_POST['apellido'] ?? ''));
$asignatura = trim((string) ($_POST['asignatura'] ?? ''));
$fecha = trim((string) ($_POST['fecha'] ?? ''));
$hora = trim((string) ($_POST['hora'] ?? ''));
$tipoSolicitud = trim((string) ($_POST['tipoSolicitud'] ?? ''));
$otroTipo = trim((string) ($_POST['otroTipo'] ?? ''));
$taller = trim((string) ($_POST['taller'] ?? ''));
$descripcion = trim((string) ($_POST['descripcion'] ?? ''));

if ($nombreProfesor === '' || $apellidoProfesor === '' || $asignatura === '' || $fecha === '' || $tipoSolicitud === '' || $taller === '') {
    guardarMensaje('error', 'Complete todos los campos obligatorios.');
    redirigir(urlRuta('reporte-incidencia'));
}

try {
    $modelo = new ReporteIncidenciaModelo();
    $modelo->crear(
        (int) $usuario['id'],
        $nombreProfesor,
        $apellidoProfesor,
        $asignatura,
        $fecha,
        $hora,
        $tipoSolicitud,
        $tipoSolicitud === 'otro' ? $otroTipo : null,
        $taller,
        $descripcion
    );

    guardarMensaje('exito', 'Reporte de incidencia enviado correctamente.');
    redirigir(urlRuta('historial-reportes'));
} catch (PDOException $error) {
    guardarMensaje('error', 'Error al guardar la incidencia en la base de datos.');
    redirigir(urlRuta('reporte-incidencia'));
}
