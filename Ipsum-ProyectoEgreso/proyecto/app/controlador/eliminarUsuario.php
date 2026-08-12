<?php

declare(strict_types=1);

require_once __DIR__ . '/../modelo/UsuarioModelo.php';
require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador se ocupa únicamente de eliminar un usuario.
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

if (!is_int($usuarioId) || $usuarioId <= 0) {
    guardarMensaje('error', 'No se pudo identificar al usuario.');
    redirigir(urlRuta('panel-administrador'));
}

try {
    $modelo = new UsuarioModelo();

    if (!$modelo->eliminar($usuarioId)) {
        guardarMensaje('error', 'El usuario inicial no puede eliminarse.');
        redirigir(urlRuta('panel-administrador'));
    }

    guardarMensaje('exito', 'Usuario eliminado correctamente.');
} catch (PDOException $error) {
    error_log('Error al eliminar usuario: ' . $error->getMessage());
    guardarMensaje('error', 'No se pudo eliminar el usuario de la base de datos.');
}

redirigir(urlRuta('panel-administrador'));
