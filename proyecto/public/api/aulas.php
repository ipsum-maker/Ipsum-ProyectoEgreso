<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/modelo/AulaModelo.php';
require_once __DIR__ . '/../../app/vista/RespuestaJson.php';
require_once __DIR__ . '/../../app/utilidades/PeticionJson.php';

$usuario = PeticionJson::usuario();
$aulaModelo = new AulaModelo();
RespuestaJson::exito($aulaModelo->obtenerTodasActivas());
