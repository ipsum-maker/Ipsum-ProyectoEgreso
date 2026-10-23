<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/controlador/api/UsuarioController.php';

(new UsuarioController())->gestionar($_SERVER['REQUEST_METHOD']);
