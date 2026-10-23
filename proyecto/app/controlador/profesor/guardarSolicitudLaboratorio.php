<?php

declare(strict_types=1);

require_once __DIR__ . '/../../utilidades/sesion.php';
require_once __DIR__ . '/../../modelo/SolicitudLaboratorioModelo.php';

$usuario = exigirRol(['profesor']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(urlRuta('solicitar-laboratorio'));
}

$tokenRecibido = (string) ($_POST['csrf_token'] ?? '');
if (!verificarTokenCSRF($tokenRecibido)) {
    guardarMensaje('error', 'Solicitud no válida o sesión expirada. Intente nuevamente.');
    redirigir(urlRuta('solicitar-laboratorio'));
}

$aulaId = (int) ($_POST['aula_id'] ?? 0);
$fecha = trim((string) ($_POST['fecha'] ?? ''));
$horaInicio = trim((string) ($_POST['hora_inicio'] ?? ''));
$horaFin = trim((string) ($_POST['hora_fin'] ?? ''));
$motivo = trim((string) ($_POST['motivo'] ?? ''));

if ($aulaId <= 0 || $fecha === '' || $horaInicio === '' || $horaFin === '' || $motivo === '') {
    guardarMensaje('error', 'Complete todos los campos obligatorios para la solicitud.');
    redirigir(urlRuta('solicitar-laboratorio'));
}

try {
    $modelo = new SolicitudLaboratorioModelo();
    $modelo->crear([
        'usuario_id' => (int) $usuario['id'],
        'aula_id' => $aulaId,
        'fecha' => $fecha,
        'hora_inicio' => $horaInicio,
        'hora_fin' => $horaFin,
        'motivo' => $motivo,
    ]);

    guardarMensaje('exito', 'Solicitud enviada correctamente. El asistente revisará su pedido.');
    redirigir(urlRuta('mis-solicitudes-laboratorio'));
} catch (Throwable $error) {
    guardarMensaje('error', $error->getMessage());
    redirigir(urlRuta('solicitar-laboratorio'));
}
