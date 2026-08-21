<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';

// Cierra la sesión en el servidor y redirige al login
destruirSesion();
enviarCabecerasSinCache();
redirigir(urlRuta('login'));
