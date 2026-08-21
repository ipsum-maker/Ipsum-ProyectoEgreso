<?php

declare(strict_types=1);

require_once __DIR__ . '/../modelo/UsuarioModelo.php';
require_once __DIR__ . '/../utilidades/cedula.php';
require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador se ocupa únicamente de iniciar una sesión.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(urlRuta('login'));
}

// Verificación de Token CSRF
$tokenRecibido = (string) ($_POST['csrf_token'] ?? '');
if (!verificarTokenCSRF($tokenRecibido)) {
    $_SESSION['error_login'] = 'Solicitud no válida. Intente nuevamente.';
    redirigir(urlRuta('login'));
}

$cedulaIngresada = trim((string) ($_POST['cedula'] ?? ''));
$contrasena = (string) ($_POST['contrasena'] ?? '');

if ($cedulaIngresada === '' || $contrasena === '') {
    $_SESSION['error_login'] = 'Complete la cédula y la contraseña.';
    redirigir(urlRuta('login'));
}

if (!cedulaTieneFormatoValido($cedulaIngresada)) {
    $_SESSION['error_login'] = 'Ingrese una cédula válida de 7 u 8 dígitos.';
    redirigir(urlRuta('login'));
}

try {
    $modelo = new UsuarioModelo();
    $usuario = $modelo->buscarPorCedula(normalizarCedula($cedulaIngresada));
} catch (PDOException $error) {
    error_log('Error de conexión al iniciar sesión: ' . $error->getMessage());
    $_SESSION['error_login'] = 'Error en la base de datos al validar credenciales.';
    redirigir(urlRuta('login'));
}

if (
    $usuario === null
    || (int) $usuario['activo'] !== 1
    || !password_verify($contrasena, (string) $usuario['contrasena_hash'])
) {
    $_SESSION['error_login'] = 'Cédula o contraseña incorrectas.';
    redirigir(urlRuta('login'));
}

session_regenerate_id(true);
$_SESSION['usuario'] = [
    'id' => (int) $usuario['id'],
    'nombre' => (string) $usuario['nombre'],
    'cedula' => (string) $usuario['cedula'],
    'rol' => (string) $usuario['rol'],
];

redirigir(urlRuta(rutaPanelSegunRol((string) $usuario['rol'])));
