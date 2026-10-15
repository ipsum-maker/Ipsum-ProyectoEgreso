<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/controlador/SolicitudLaboratorioController.php';

(new SolicitudLaboratorioController())->gestionar($_SERVER['REQUEST_METHOD']);
