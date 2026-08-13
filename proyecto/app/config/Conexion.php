<?php

declare(strict_types=1);

/**
 * Crea y comparte la conexión PDO con MySQL.
 * Esta clase pertenece a la configuración de acceso a datos.
 */
final class Conexion
{
    private static ?PDO $conexion = null;

    public static function obtener(): PDO
    {
        if (self::$conexion instanceof PDO) {
            return self::$conexion;
        }

        // Valores habituales de XAMPP. Pueden cambiarse aquí si fuera necesario.
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $puerto = getenv('DB_PORT') ?: '3306';
        $baseDeDatos = getenv('DB_NAME') ?: 'ipsum_reportes';
        $usuario = getenv('DB_USER') ?: 'root';
        $contrasena = getenv('DB_PASSWORD') ?: '';

        $dsn = "mysql:host={$host};port={$puerto};dbname={$baseDeDatos};charset=utf8mb4";

        self::$conexion = new PDO($dsn, $usuario, $contrasena, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$conexion;
    }
}
