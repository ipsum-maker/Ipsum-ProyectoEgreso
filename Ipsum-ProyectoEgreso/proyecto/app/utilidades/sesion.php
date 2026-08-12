<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('IPSUMSESSID');
    session_start([
        // La cookie se elimina al cerrar el navegador.
        'cookie_lifetime' => 0,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'use_cookies' => true,
        'use_only_cookies' => true,
        'use_strict_mode' => true,
    ]);
}

function redirigir(string $ruta): never
{
    header('Location: ' . $ruta);
    exit;
}

function urlRuta(string $ruta, array $parametros = []): string
{
    if ($ruta === 'login') {
        return 'login.php';
    }

    $consulta = array_merge(['ruta' => $ruta], $parametros);
    return 'index.php?' . http_build_query($consulta);
}

function rutaPanelSegunRol(string $rol): string
{
    return match ($rol) {
        'administrador' => 'panel-administrador',
        'coordinador' => 'panel-cordinacion',
        'profesor' => 'panel-profesores',
        default => 'login',
    };
}

function enviarCabecerasSinCache(): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

function exigirRol(array $rolesPermitidos): array
{
    enviarCabecerasSinCache();
    $usuario = $_SESSION['usuario'] ?? null;

    if (!is_array($usuario)) {
        redirigir(urlRuta('login'));
    }

    $rol = (string) ($usuario['rol'] ?? '');
    if (!in_array($rol, $rolesPermitidos, true)) {
        redirigir(urlRuta(rutaPanelSegunRol($rol), ['inicio' => 1]));
    }

    return $usuario;
}

function escapar(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function guardarMensaje(string $tipo, string $mensaje): void
{
    $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $mensaje];
}

function obtenerMensaje(): ?array
{
    $mensaje = $_SESSION['mensaje'] ?? null;
    unset($_SESSION['mensaje']);
    return is_array($mensaje) ? $mensaje : null;
}

function destruirSesion(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $parametros = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $parametros['path'],
            'domain' => $parametros['domain'],
            'secure' => $parametros['secure'],
            'httponly' => $parametros['httponly'],
            'samesite' => $parametros['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}
