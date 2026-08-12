<?php

declare(strict_types=1);

require_once __DIR__ . '/../modelo/UsuarioModelo.php';
require_once __DIR__ . '/../utilidades/cedula.php';
require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador se ocupa únicamente de crear un usuario.
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

$nombre = trim((string) ($_POST['nombre'] ?? ''));
$cedulaIngresada = trim((string) ($_POST['cedula'] ?? ''));
$contrasena = (string) ($_POST['contrasena'] ?? '');
$rol = (string) ($_POST['rol'] ?? '');
$rolesPermitidos = ['administrador', 'coordinador', 'profesor'];

if ($nombre === '' || $contrasena === '' || !in_array($rol, $rolesPermitidos, true)) {
    guardarMensaje('error', 'Complete correctamente todos los datos del usuario.');
    redirigir(urlRuta('panel-administrador'));
}

if (!cedulaTieneFormatoValido($cedulaIngresada)) {
    guardarMensaje('error', 'La cédula debe contener 7 u 8 dígitos.');
    redirigir(urlRuta('panel-administrador'));
}

try {
    $modelo = new UsuarioModelo();
    $cedula = normalizarCedula($cedulaIngresada);

    if ($modelo->existeCedula($cedula)) {
        guardarMensaje('error', 'Ya existe un usuario con esa cédula.');
        redirigir(urlRuta('panel-administrador'));
    }

    $modelo->crear($nombre, $cedula, $contrasena, $rol);
    guardarMensaje('exito', 'Usuario creado correctamente.');
} catch (PDOException $error) {
    error_log('Error al crear usuario: ' . $error->getMessage());
    guardarMensaje('error', 'No se pudo guardar el usuario en la base de datos.');
}

redirigir(urlRuta('panel-administrador'));
