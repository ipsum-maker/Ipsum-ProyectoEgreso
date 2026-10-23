<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/controlador/api/IncidenciaController.php';

(new IncidenciaController())->gestionar($_SERVER['REQUEST_METHOD']);
