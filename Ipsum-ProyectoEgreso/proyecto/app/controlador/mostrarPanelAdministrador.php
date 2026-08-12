<?php

declare(strict_types=1);

require_once __DIR__ . '/../modelo/UsuarioModelo.php';
require_once __DIR__ . '/../utilidades/cedula.php';
require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador se ocupa únicamente de preparar y mostrar el panel administrador.
$usuario = exigirRol(['administrador']);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje = obtenerMensaje();
$usuarios = [];
$cantidades = ['profesor' => 0, 'coordinador' => 0, 'administrador' => 0];
$errorBaseDeDatos = '';

try {
    $modelo = new UsuarioModelo();
    $usuarios = $modelo->obtenerTodos();
    $cantidades = $modelo->contarPorRol();
} catch (PDOException $error) {
    error_log('Error al cargar el panel: ' . $error->getMessage());
    $errorBaseDeDatos = 'No se pudo conectar con la base de datos. Verifique MySQL y el archivo SQL.';
}

require __DIR__ . '/../vista/administrador/panel-administrador.php';
