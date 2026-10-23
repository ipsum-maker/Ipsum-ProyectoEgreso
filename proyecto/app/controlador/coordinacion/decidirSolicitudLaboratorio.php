<?php

declare(strict_types=1);

require_once __DIR__ . '/../../utilidades/sesion.php';
require_once __DIR__ . '/../../modelo/SolicitudLaboratorioModelo.php';

$usuario = exigirRol(['coordinador', 'administrador']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(urlRuta('solicitudes-laboratorio'));
}

$tokenRecibido = (string) ($_POST['csrf_token'] ?? '');
if (!verificarTokenCSRF($tokenRecibido)) {
    guardarMensaje('error', 'Solicitud no válida o sesión expirada.');
    redirigir(urlRuta('solicitudes-laboratorio'));
}

$solicitudId = (int) ($_POST['solicitud_id'] ?? 0);
$estado = trim((string) ($_POST['estado'] ?? ''));
$respuesta = trim((string) ($_POST['respuesta'] ?? ''));

if ($solicitudId <= 0 || !in_array($estado, ['aprobada', 'rechazada'], true)) {
    guardarMensaje('error', 'Debe seleccionar una decisión válida (Conceder o Rechazar).');
    redirigir(urlRuta('solicitudes-laboratorio'));
}

try {
    $modelo = new SolicitudLaboratorioModelo();
    $modelo->decidir($solicitudId, (int) $usuario['id'], $estado, $respuesta !== '' ? $respuesta : null);

    $msg = $estado === 'aprobada' ? 'Solicitud aprobada y espacio reservado correctamente.' : 'Solicitud rechazada.';
    guardarMensaje('exito', $msg);
    redirigir(urlRuta('solicitudes-laboratorio'));
} catch (Throwable $error) {
    guardarMensaje('error', $error->getMessage());
    redirigir(urlRuta('solicitudes-laboratorio'));
}
