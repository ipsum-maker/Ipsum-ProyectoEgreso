<?php
declare(strict_types=1);

require_once __DIR__ . '/../modelo/UsuarioModelo.php';
require_once __DIR__ . '/../vista/RespuestaJson.php';
require_once __DIR__ . '/../utilidades/PeticionJson.php';
require_once __DIR__ . '/../utilidades/cedula.php';

final class UsuarioController
{
    public function gestionar(string $metodo): void
    {
        try {
            $usuario = PeticionJson::usuario();
            PeticionJson::exigirRol($usuario, ['administrador']);
            $metodo = strtoupper($metodo);

            if (!in_array($metodo, ['GET', 'POST', 'PATCH', 'DELETE'], true)) {
                header('Allow: GET, POST, PATCH, DELETE');
                throw new ErrorPeticion('Método no permitido', 405);
            }

            if ($metodo !== 'GET') {
                PeticionJson::verificarCsrf();
            }

            $modelo = new UsuarioModelo();

            match ($metodo) {
                'GET' => $this->listar($modelo),
                'POST' => $this->crear($modelo),
                'PATCH' => $this->actualizarRol($modelo),
                'DELETE' => $this->eliminar($modelo, (int) $usuario['id']),
            };
        } catch (ErrorPeticion $error) {
            RespuestaJson::error($error->getMessage(), $error->getCode());
        } catch (Throwable $error) {
            error_log('API usuarios: ' . $error->getMessage());
            RespuestaJson::error('Error al procesar la operación de usuario: ' . $error->getMessage(), 500);
        }
    }

    private function listar(UsuarioModelo $modelo): never
    {
        if (array_key_exists('id', $_GET)) {
            $id = PeticionJson::id($_GET['id']);
            $todos = $modelo->obtenerTodos();
            foreach ($todos as $u) {
                if ((int) $u['id'] === $id) {
                    RespuestaJson::exito($u);
                }
            }
            throw new ErrorPeticion('Usuario no encontrado', 404);
        }

        RespuestaJson::exito($modelo->obtenerTodos());
    }

    private function crear(UsuarioModelo $modelo): never
    {
        $entrada = PeticionJson::leer();
        PeticionJson::campos($entrada, ['nombre', 'cedula', 'contrasena', 'rol']);

        $nombre = PeticionJson::texto($entrada['nombre'] ?? '', 'nombre', 100);
        $cedula = PeticionJson::texto($entrada['cedula'] ?? '', 'cedula', 20);
        $cedulaLimpia = normalizarCedula($cedula);

        if (!validarFormatoCedula($cedulaLimpia)) {
            throw new ErrorPeticion('La cédula ingresada no tiene un formato válido', 422);
        }
        if ($modelo->existeCedula($cedulaLimpia)) {
            throw new ErrorPeticion('Ya existe un usuario registrado con esa cédula', 409);
        }

        $contrasena = (string) ($entrada['contrasena'] ?? '');
        if (strlen($contrasena) < 3) {
            throw new ErrorPeticion('La contraseña debe tener al menos 3 caracteres', 422);
        }

        $rol = PeticionJson::enumerado($entrada['rol'] ?? '', 'rol', ['administrador', 'coordinador', 'profesor']);

        $modelo->crear($nombre, $cedulaLimpia, $contrasena, $rol);
        RespuestaJson::exito(['mensaje' => 'Usuario creado correctamente'], 201);
    }

    private function actualizarRol(UsuarioModelo $modelo): never
    {
        $entrada = PeticionJson::leer();
        PeticionJson::campos($entrada, ['id', 'usuario_id', 'rol']);

        $id = PeticionJson::id($entrada['id'] ?? ($entrada['usuario_id'] ?? null));
        $rol = PeticionJson::enumerado($entrada['rol'] ?? '', 'rol', ['administrador', 'coordinador', 'profesor']);

        if (!$modelo->actualizarRol($id, $rol)) {
            throw new ErrorPeticion('No se pudo actualizar el rol. El usuario no existe o es un usuario inicial protegido.', 422);
        }

        RespuestaJson::exito(['mensaje' => 'Rol actualizado correctamente']);
    }

    private function eliminar(UsuarioModelo $modelo, int $adminActualId): never
    {
        $entrada = PeticionJson::leer();
        PeticionJson::campos($entrada, ['id', 'usuario_id']);

        $id = PeticionJson::id($entrada['id'] ?? ($entrada['usuario_id'] ?? null));

        if ($id === $adminActualId) {
            throw new ErrorPeticion('No puede eliminar su propia cuenta de administrador.', 403);
        }

        if (!$modelo->eliminar($id)) {
            throw new ErrorPeticion('No se pudo eliminar el usuario. Es posible que sea un usuario inicial o posea reportes asociados.', 422);
        }

        RespuestaJson::exito(['mensaje' => 'Usuario eliminado correctamente']);
    }
}
