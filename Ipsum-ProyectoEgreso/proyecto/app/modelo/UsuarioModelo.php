<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/Conexion.php';

final class UsuarioModelo
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::obtener();
    }

    public function buscarPorCedula(string $cedula): ?array
    {
        $consulta = $this->conexion->prepare(
            'SELECT id, nombre, cedula, contrasena_hash, rol, activo, es_inicial
             FROM usuarios
             WHERE cedula = :cedula
             LIMIT 1'
        );
        $consulta->execute(['cedula' => $cedula]);

        $usuario = $consulta->fetch();

        return is_array($usuario) ? $usuario : null;
    }

    public function obtenerTodos(): array
    {
        $consulta = $this->conexion->query(
            'SELECT id, nombre, cedula, rol, activo, es_inicial
             FROM usuarios
             ORDER BY es_inicial DESC, nombre ASC'
        );

        return $consulta->fetchAll();
    }

    public function contarPorRol(): array
    {
        $cantidades = [
            'profesor' => 0,
            'coordinador' => 0,
            'administrador' => 0,
        ];

        $consulta = $this->conexion->query(
            'SELECT rol, COUNT(*) AS cantidad
             FROM usuarios
             WHERE activo = 1
             GROUP BY rol'
        );

        foreach ($consulta->fetchAll() as $fila) {
            $rol = (string) $fila['rol'];

            if (array_key_exists($rol, $cantidades)) {
                $cantidades[$rol] = (int) $fila['cantidad'];
            }
        }

        return $cantidades;
    }

    public function existeCedula(string $cedula): bool
    {
        $consulta = $this->conexion->prepare(
            'SELECT COUNT(*) FROM usuarios WHERE cedula = :cedula'
        );
        $consulta->execute(['cedula' => $cedula]);

        return (int) $consulta->fetchColumn() > 0;
    }

    public function crear(string $nombre, string $cedula, string $contrasena, string $rol): void
    {
        $consulta = $this->conexion->prepare(
            'INSERT INTO usuarios (nombre, cedula, contrasena_hash, rol, activo, es_inicial)
             VALUES (:nombre, :cedula, :contrasena_hash, :rol, 1, 0)'
        );

        $consulta->execute([
            'nombre' => $nombre,
            'cedula' => $cedula,
            'contrasena_hash' => password_hash($contrasena, PASSWORD_DEFAULT),
            'rol' => $rol,
        ]);
    }

    public function actualizarRol(int $usuarioId, string $rol): bool
    {
        $consulta = $this->conexion->prepare(
            'UPDATE usuarios
             SET rol = :rol
             WHERE id = :id AND es_inicial = 0'
        );
        $consulta->execute([
            'id' => $usuarioId,
            'rol' => $rol,
        ]);

        return $consulta->rowCount() > 0;
    }

    public function eliminar(int $usuarioId): bool
    {
        $consulta = $this->conexion->prepare(
            'DELETE FROM usuarios
             WHERE id = :id AND es_inicial = 0'
        );
        $consulta->execute(['id' => $usuarioId]);

        return $consulta->rowCount() > 0;
    }
}
