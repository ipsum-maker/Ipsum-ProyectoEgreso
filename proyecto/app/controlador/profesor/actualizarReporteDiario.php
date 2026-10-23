<?php

declare(strict_types=1);

require_once __DIR__ . '/../../modelo/ReporteDiarioModelo.php';
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

$reporteId = (int) ($_POST['reporte_id'] ?? 0);
$aulaId = (int) ($_POST['aula_id'] ?? $_POST['aula'] ?? 0);
$grupo = trim((string) ($_POST['grupo'] ?? ''));
$asignatura = trim((string) ($_POST['asignatura'] ?? ''));
$turno = trim((string) ($_POST['turno'] ?? 'matutino'));
$horaEntrada = trim((string) ($_POST['hora_entrada'] ?? ''));
$horaSalida = trim((string) ($_POST['hora_salida'] ?? ''));

if ($reporteId <= 0 || $aulaId <= 0) {
    guardarMensaje('error', 'Complete los datos obligatorios.');
    redirigir(urlRuta('historial-reportes'));
}

try {
    $modelo = new ReporteDiarioModelo();
    
    // Verificar que pertenezca al usuario y no supere los 10 minutos
    $reporte = $modelo->obtenerPropio($reporteId, (int) $usuario['id']);

    if ($reporte === null) {
        guardarMensaje('error', 'No se encontró el reporte a editar.');
        redirigir(urlRuta('historial-reportes'));
    }

    $creadoTs = strtotime((string) $reporte['creado_en']);
    if ((time() - $creadoTs) > 600) {
        guardarMensaje('error', 'El tiempo límite de 10 minutos para editar este reporte ha expirado.');
        redirigir(urlRuta('historial-reportes'));
    }

    $modelo->actualizar(
        $reporteId,
        (int) $usuario['id'],
        $aulaId,
        $grupo,
        $asignatura,
        $turno,
        $horaEntrada,
        $horaSalida
    );

    guardarMensaje('exito', 'Reporte diario actualizado correctamente.');
} catch (PDOException $e) {
    guardarMensaje('error', 'Error al actualizar el reporte en la base de datos.');
}

redirigir(urlRuta('historial-reportes'));
