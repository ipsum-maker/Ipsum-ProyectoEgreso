<?php

declare(strict_types=1);

require_once __DIR__ . '/../utilidades/sesion.php';

// Uso de la función homogeneizada de utilidades
redireccionarSiAutenticado();

$errorLogin = (string) ($_SESSION['error_login'] ?? '');
unset($_SESSION['error_login']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reportes - Iniciar Sesión</title>
    <link rel="stylesheet" href="assets/css/ingreso.css">
</head>

<body>

    <main class="login-card">

        <header class="login-header">
            <h1 class="login-title">Bienvenido al sistema de reportes</h1>
            <span class="login-subtitle">Ingrese sus datos para ingresar</span>
        </header>

        <?php if ($errorLogin !== ''): ?>
            <p class="login-error" role="alert"><?= escapar((string) $errorLogin) ?></p>
        <?php endif; ?>

        <!-- Formulario apuntando a index.php?ruta=iniciar-sesion con Token CSRF -->
        <form class="login-form" id="login-form" action="index.php?ruta=iniciar-sesion" method="post">
            <input type="hidden" name="csrf_token" value="<?= escapar(obtenerTokenCSRF()) ?>">

            <label class="login-label" for="cedula">Cédula de identidad</label>
            <input class="login-input" type="text" id="cedula" name="cedula"
                inputmode="numeric" autocomplete="username" placeholder="1.234.567-8"
                pattern="[0-9.\-]{7,12}" maxlength="12" required>

            <label class="login-label" for="contrasena">Contraseña</label>
            <input class="login-input" type="password" id="contrasena" name="contrasena"
                autocomplete="current-password" required>

            <button class="login-button" type="submit">Iniciar Sesión</button>
        </form>

    </main>

</body>

</html>
