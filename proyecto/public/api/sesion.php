<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/utilidades/sesion.php';
require_once __DIR__ . '/../../app/vista/RespuestaJson.php';
require_once __DIR__ . '/../../app/modelo/UsuarioModelo.php';
require_once __DIR__ . '/../../app/utilidades/cedula.php';
require_once __DIR__ . '/../../app/utilidades/PeticionJson.php';

$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($metodo === 'GET') {
    $usuario = $_SESSION['usuario'] ?? null;
    RespuestaJson::exito([
        'usuario' => $usuario,
        'csrfToken' => obtenerTokenCSRF(),
    ]);
}

if ($metodo === 'POST') {
    $entrada = PeticionJson::leer();
    $cedula = normalizarCedula((string) ($entrada['cedula'] ?? ''));
    $contrasena = (string) ($entrada['contrasena'] ?? '');

    $modelo = new UsuarioModelo();
    $usuario = $modelo->buscarPorCedula($cedula);

    if ($usuario === null || !(bool) $usuario['activo'] || !password_verify($contrasena, (string) $usuario['contrasena_hash'])) {
        RespuestaJson::error('Cédula o contraseña incorrecta.', 401);
    }

    // Inicio de sesión exitoso
    session_regenerate_id(true);
    $_SESSION['usuario'] = [
        'id' => (int) $usuario['id'],
        'nombre' => (string) $usuario['nombre'],
        'cedula' => (string) $usuario['cedula'],
        'rol' => (string) $usuario['rol'],
    ];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    RespuestaJson::exito([
        'mensaje' => 'Sesión iniciada correctamente',
        'usuario' => $_SESSION['usuario'],
        'csrfToken' => $_SESSION['csrf_token'],
        'redirect' => urlRuta(rutaPanelSegunRol($usuario['rol'])),
    ]);
}

if ($metodo === 'DELETE') {
    destruirSesion();
    RespuestaJson::exito([
        'mensaje' => 'Sesión cerrada correctamente',
        'redirect' => urlRuta('login'),
    ]);
}

header('Allow: GET, POST, DELETE');
RespuestaJson::error('Método no permitido', 405);
