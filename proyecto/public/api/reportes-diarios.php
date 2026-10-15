<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/controlador/ReporteDiarioController.php';

(new ReporteDiarioController())->gestionar($_SERVER['REQUEST_METHOD']);
