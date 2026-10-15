<?php
declare(strict_types=1);

require_once __DIR__ . '/sesion.php';

final class ErrorPeticion extends RuntimeException {}

final class PeticionJson
{
    public static function usuario(): array
    {
        $usuario = $_SESSION['usuario'] ?? null;
        if (!is_array($usuario) || !isset($usuario['id']) || (int) $usuario['id'] <= 0) {
            throw new ErrorPeticion('Sesión no iniciada', 401);
        }
        self::exigirRol($usuario, ['profesor', 'coordinador', 'administrador']);
        return $usuario;
    }

    public static function exigirRol(array $usuario, array $roles): void
    {
        if (!in_array($usuario['rol'] ?? '', $roles, true)) {
            throw new ErrorPeticion('No tiene permisos para esta operación', 403);
        }
    }

    public static function verificarCsrf(?string $tokenEnviado = null): void
    {
        $token = $tokenEnviado ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_SERVER['HTTP_X_XSRF_TOKEN'] ?? ''));
        if (!is_string($token) || $token === '' || !verificarTokenCSRF($token)) {
            throw new ErrorPeticion('Token CSRF incorrecto. Recargue la página e intente nuevamente.', 403);
        }
    }

    public static function leer(): array
    {
        $tipo = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
        if ($tipo !== 'application/json') {
            throw new ErrorPeticion('Use Content-Type: application/json', 415);
        }
        $json = file_get_contents('php://input', false, null, 0, 1048577);
        if ($json === false) {
            throw new ErrorPeticion('No se pudo leer la solicitud', 400);
        }
        if (strlen($json) > 1048576) {
            throw new ErrorPeticion('La solicitud supera 1 MiB', 413);
        }
        return self::decodificar($json);
    }

    public static function decodificar(string $json): array
    {
        try {
            $objeto = json_decode($json, false, 64, JSON_THROW_ON_ERROR);
            if (!$objeto instanceof stdClass) {
                throw new ErrorPeticion('El cuerpo debe ser un objeto JSON', 400);
            }
            return json_decode($json, true, 64, JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            throw new ErrorPeticion('JSON inválido: ' . $error->getMessage(), 400);
        }
    }

    public static function campos(array $datos, array $permitidos): void
    {
        $clavesRecibidas = array_keys($datos);
        // Permitir opcionalmente csrf_token en el cuerpo JSON si el cliente lo envió
        $diferencia = array_diff($clavesRecibidas, array_merge($permitidos, ['csrf_token', '_token']));
        if (!empty($diferencia)) {
            throw new ErrorPeticion('Se enviaron campos no admitidos para esta operación: ' . implode(', ', $diferencia), 422);
        }
    }

    public static function id(mixed $valor): int
    {
        if ((!is_int($valor) && !is_string($valor)) || !preg_match('/^[1-9][0-9]*$/D', (string) $valor)) {
            throw new ErrorPeticion('ID incorrecto', 422);
        }
        $id = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 4294967295]]);
        if ($id === false) {
            throw new ErrorPeticion('ID fuera de rango', 422);
        }
        return (int) $id;
    }

    public static function texto(mixed $valor, string $campo, int $maximo, bool $obligatorio = true): string
    {
        if ($valor === null && !$obligatorio) {
            return '';
        }
        if (!is_string($valor)) {
            throw new ErrorPeticion('Debe enviar texto en ' . $campo, 422);
        }
        $valor = trim($valor);
        $longitud = function_exists('mb_strlen') ? mb_strlen($valor, 'UTF-8') : strlen($valor);
        if (($obligatorio && $valor === '') || $longitud > $maximo || str_contains($valor, "\0")) {
            throw new ErrorPeticion('Campo vacío, inválido o demasiado largo: ' . $campo, 422);
        }
        return $valor;
    }

    public static function enumerado(mixed $valor, string $campo, array $permitidos): string
    {
        if (!is_string($valor) || !in_array($valor, $permitidos, true)) {
            throw new ErrorPeticion('Valor incorrecto en ' . $campo . '. Permitidos: ' . implode(', ', $permitidos), 422);
        }
        return $valor;
    }

    public static function fecha(mixed $valor): string
    {
        if (!is_string($valor) || !preg_match('/^([1-9][0-9]{3})-([0-9]{2})-([0-9]{2})$/D', $valor, $partes)
            || !checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            throw new ErrorPeticion('Fecha incorrecta: use AAAA-MM-DD', 422);
        }
        return $valor;
    }

    public static function hora(mixed $valor, string $campo, bool $obligatorio = false): ?string
    {
        if (!$obligatorio && ($valor === null || $valor === '')) {
            return null;
        }
        if (!is_string($valor) || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9](?::[0-5][0-9])?$/D', $valor)) {
            throw new ErrorPeticion('Hora incorrecta en ' . $campo . '. Use HH:MM o HH:MM:SS', 422);
        }
        return strlen($valor) === 5 ? $valor . ':00' : $valor;
    }

    public static function textoLargo(mixed $valor, string $campo, bool $obligatorio): string
    {
        $valor = self::texto($valor, $campo, 65535, $obligatorio);
        if (strlen($valor) > 65535) {
            throw new ErrorPeticion('Texto demasiado largo en ' . $campo, 422);
        }
        return $valor;
    }
}
