<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador se ocupa únicamente de cerrar la sesión.
destruirSesion();
enviarCabecerasSinCache();
require __DIR__ . '/../vista/autenticacion/logout.php';
