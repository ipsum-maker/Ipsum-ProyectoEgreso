<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';

// Este controlador se ocupa únicamente de mostrar los registros de incidencias.
$usuario = exigirRol(['coordinador', 'administrador']);
require __DIR__ . '/../vista/coordinacion/cordinacion-incidencias.php';
