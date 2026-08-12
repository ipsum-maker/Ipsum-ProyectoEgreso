<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/cedula.php';
require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador se ocupa únicamente de mostrar el panel de coordinación.
$usuario = exigirRol(['coordinador']);
require __DIR__ . '/../vista/coordinacion/panel-cordinacion.php';
