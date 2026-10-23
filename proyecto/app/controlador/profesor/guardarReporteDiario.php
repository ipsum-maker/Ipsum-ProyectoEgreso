<?php

declare(strict_types=1);

require_once __DIR__ . '/../../modelo/ReporteDiarioModelo.php';
require_once __DIR__ . '/../../utilidades/sesion.php';

$usuario = exigirRol(['profesor']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(urlRuta('reporte-diario'));
}

$tokenRecibido = (string) ($_POST['csrf_token'] ?? '');
if (!verificarTokenCSRF($tokenRecibido)) {
    guardarMensaje('error', 'Solicitud no válida. Intente nuevamente.');
    redirigir(urlRuta('reporte-diario'));
}

$fecha = trim((string) ($_POST['fecha'] ?? date('Y-m-d')));
$horaEntrada = trim((string) ($_POST['hora_entrada'] ?? ''));
$horaSalida = trim((string) ($_POST['hora_salida'] ?? ''));
$hora = $horaEntrada !== '' ? $horaEntrada : date('H:i');
$turno = trim((string) ($_POST['turno'] ?? 'matutino'));
$grupo = trim((string) ($_POST['grupo'] ?? ''));
$asignatura = trim((string) ($_POST['asignatura'] ?? ''));
$aulaId = (int) ($_POST['aula_id'] ?? $_POST['aula'] ?? 0);
$equipos = $_POST['equipos'] ?? [];

if ($aulaId <= 0) {
    guardarMensaje('error', 'Seleccione un aula o taller válido.');
    redirigir(urlRuta('reporte-diario'));
}

try {
    $modelo = new ReporteDiarioModelo();
    $modelo->crear(
        (int) $usuario['id'],
        $aulaId,
        $fecha,
        $hora,
        (array) $equipos,
        $turno,
        $horaEntrada,
        $horaSalida,
        $grupo,
        $asignatura
    );
    guardarMensaje('exito', 'Reporte diario guardado correctamente.');
    redirigir(urlRuta('panel-profesores'));


} catch (PDOException $error) {
    guardarMensaje('error', 'Ocurrió un error al guardar el reporte en la base de datos.');
    redirigir(urlRuta('reporte-diario'));
}
