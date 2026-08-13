<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/cedula.php';
require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador se ocupa únicamente de mostrar el panel del profesor.
$usuario = exigirRol(['profesor']);
require __DIR__ . '/../vista/profesor/panel-profesores.php';
