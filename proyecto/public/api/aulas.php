<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/modelo/AulaModelo.php';
require_once __DIR__ . '/../../app/vista/RespuestaJson.php';
require_once __DIR__ . '/../../app/utilidades/PeticionJson.php';

try {
    $usuario = PeticionJson::usuario();
    $aulaModelo = new AulaModelo();
    RespuestaJson::exito($aulaModelo->obtenerTodasActivas());
} catch (ErrorPeticion $error) {
    RespuestaJson::error($error->getMessage(), $error->getCode());
} catch (Throwable $error) {
    error_log('API aulas: ' . $error->getMessage());
    RespuestaJson::error('Error interno al obtener las aulas. Intente nuevamente.', 500);
}
