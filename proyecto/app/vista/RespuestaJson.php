<?php
declare(strict_types=1);

final class RespuestaJson
{
    public static function exito(array $datos, int $codigo = 200, string $mensaje = 'Operación realizada correctamente'): never
    {
        self::enviar([
            'status' => 'success',
            'message' => $mensaje,
            'data' => $datos,
        ], $codigo);
    }

    public static function error(string $mensaje, int $codigo = 400): never
    {
        self::enviar([
            'status' => 'error',
            'message' => $mensaje,
            'data' => null,
        ], $codigo);
    }

    private static function enviar(array $contenido, int $codigo): never
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }
}
