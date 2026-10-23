<?php

declare(strict_types=1);

require_once __DIR__ . '/../../modelo/UsuarioModelo.php';
require_once __DIR__ . '/../../utilidades/sesion.php';

// Este controlador se ocupa únicamente de cambiar el rol de un usuario.
exigirRol(['administrador']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(urlRuta('panel-administrador'));
}

$tokenRecibido = (string) ($_POST['csrf_token'] ?? '');
$tokenSesion = (string) ($_SESSION['csrf_token'] ?? '');

if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
    guardarMensaje('error', 'La solicitud no es válida. Recargue la página.');
    redirigir(urlRuta('panel-administrador'));
}

$usuarioId = filter_input(INPUT_POST, 'usuario_id', FILTER_VALIDATE_INT);
$rol = (string) ($_POST['rol'] ?? '');
$rolesPermitidos = ['administrador', 'coordinador', 'profesor'];

if (!is_int($usuarioId) || $usuarioId <= 0 || !in_array($rol, $rolesPermitidos, true)) {
    guardarMensaje('error', 'No se pudo cambiar el rol.');
    redirigir(urlRuta('panel-administrador'));
}

try {
    $modelo = new UsuarioModelo();

    if (!$modelo->actualizarRol($usuarioId, $rol)) {
        guardarMensaje('error', 'El usuario inicial no puede cambiar de rol.');
        redirigir(urlRuta('panel-administrador'));
    }

    guardarMensaje('exito', 'Rol actualizado correctamente.');
} catch (PDOException $error) {
    error_log('Error al cambiar el rol: ' . $error->getMessage());
    guardarMensaje('error', 'No se pudo guardar el cambio de rol en la base de datos.');
}

redirigir(urlRuta('panel-administrador'));
