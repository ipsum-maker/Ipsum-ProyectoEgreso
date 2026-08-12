<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador envía al usuario a su panel según el rol.
$usuario = $_SESSION['usuario'] ?? null;

if (!is_array($usuario)) {
    redirigir(urlRuta('login'));
}

redirigir(urlRuta(rutaPanelSegunRol((string) ($usuario['rol'] ?? ''))));
