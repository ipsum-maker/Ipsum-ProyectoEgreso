<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../modelo/ReporteDiarioDAO.php';
require_once __DIR__ . '/../../vista/RespuestaJson.php';
require_once __DIR__ . '/../../utilidades/PeticionJson.php';

final class ReporteDiarioController
{
    private const EDITABLES = ['aula', 'aula_id', 'grupo', 'asignatura', 'turno', 'hora_entrada', 'hora_salida'];

    public function gestionar(string $metodo): void
    {
        try {
            $usuario = PeticionJson::usuario();
            $metodo = strtoupper($metodo);

            if (!in_array($metodo, ['GET', 'POST', 'PUT', 'PATCH'], true)) {
                header('Allow: GET, POST, PUT, PATCH');
                throw new ErrorPeticion('Método no permitido', 405);
            }

            if ($metodo !== 'GET') {
                PeticionJson::exigirRol($usuario, ['profesor']);
                PeticionJson::verificarCsrf();
            }

            $dao = new ReporteDiarioDAO(Conexion::obtener());

            match ($metodo) {
                'GET' => $this->listar($dao, $usuario['rol'] === 'profesor' ? (int) $usuario['id'] : null),
                'POST' => $this->alta($dao, (int) $usuario['id']),
                'PUT', 'PATCH' => $this->modificar($dao, (int) $usuario['id']),
            };
        } catch (ErrorPeticion $error) {
            RespuestaJson::error($error->getMessage(), $error->getCode());
        } catch (Throwable $error) {
            error_log('API reportes diarios: ' . $error->getMessage());
            RespuestaJson::error('Error interno al procesar el reporte diario. Intente nuevamente.', 500);
        }
    }

    private function listar(ReporteDiarioDAO $dao, ?int $usuarioId): never
    {
        if (array_key_exists('id', $_GET)) {
            $reporte = $dao->obtener(PeticionJson::id($_GET['id']), $usuarioId);
            if ($reporte === null) {
                throw new ErrorPeticion('Reporte diario no encontrado', 404);
            }
            RespuestaJson::exito($reporte);
        }

        RespuestaJson::exito($dao->listar($usuarioId));
    }

    private function alta(ReporteDiarioDAO $dao, int $usuarioId): never
    {
        $entrada = PeticionJson::leer();
        PeticionJson::campos($entrada, array_merge(self::EDITABLES, ['fecha', 'hora', 'nombre_profesor', 'apellido_profesor', 'equipos']));
        $datos = $this->validarCampos($entrada, false);

        $datos['fecha'] = PeticionJson::fecha($entrada['fecha'] ?? date('Y-m-d'));
        if (isset($entrada['hora'])) {
            $datos['hora'] = PeticionJson::hora($entrada['hora'], 'hora', false);
        }

        $equipos = $entrada['equipos'] ?? [];
        if (!is_array($equipos)) {
            throw new ErrorPeticion('El formato de los equipos es inválido', 422);
        }

        $datos['equipos'] = [];
        foreach ($equipos as $equipo) {
            if (!is_array($equipo)) {
                throw new ErrorPeticion('Equipo incorrecto', 422);
            }
            PeticionJson::campos($equipo, ['pc', 'alumno']);
            $datos['equipos'][] = [
                'pc' => PeticionJson::texto($equipo['pc'] ?? '', 'pc', 50),
                'alumno' => PeticionJson::texto($equipo['alumno'] ?? '', 'alumno', 150),
            ];
        }

        $id = $dao->crear($usuarioId, $datos);
        RespuestaJson::exito(['id' => $id], 201, 'Reporte diario creado correctamente');
    }

    private function modificar(ReporteDiarioDAO $dao, int $usuarioId): never
    {
        $entrada = PeticionJson::leer();
        PeticionJson::campos($entrada, array_merge(['id'], self::EDITABLES));
        $id = PeticionJson::id($entrada['id'] ?? null);
        $cambios = $this->validarCampos($entrada, true);

        if (empty($cambios)) {
            throw new ErrorPeticion('No se enviaron campos para modificar', 422);
        }

        $resultado = $dao->modificar($id, $usuarioId, $cambios);
        if ($resultado === 'no_encontrado') {
            throw new ErrorPeticion('Reporte propio no encontrado', 404);
        }
        if ($resultado === 'vencido') {
            throw new ErrorPeticion('El plazo de 10 minutos para editar ha expirado', 409);
        }

        RespuestaJson::exito(['id' => $id], 200, 'Reporte diario actualizado correctamente');
    }

    private function validarCampos(array $entrada, bool $parcial): array
    {
        $salida = [];

        if (array_key_exists('aula_id', $entrada)) {
            $salida['aula_id'] = PeticionJson::id($entrada['aula_id']);
        } elseif (array_key_exists('aula', $entrada)) {
            $salida['aula'] = is_numeric($entrada['aula']) ? (int) $entrada['aula'] : PeticionJson::texto($entrada['aula'], 'aula', 100, !$parcial);
        } elseif (!$parcial) {
            throw new ErrorPeticion('Debe indicar el aula o salón', 422);
        }

        foreach (['grupo' => 50, 'asignatura' => 100] as $campo => $maximo) {
            if ($parcial && !array_key_exists($campo, $entrada)) continue;
            $valor = PeticionJson::texto($entrada[$campo] ?? '', $campo, $maximo, false);
            $salida[$campo] = $valor === '' ? null : $valor;
        }

        if (!$parcial || array_key_exists('turno', $entrada)) {
            $salida['turno'] = PeticionJson::enumerado($entrada['turno'] ?? 'matutino', 'turno', ['matutino', 'vespertino', 'nocturno']);
        }

        foreach (['hora_entrada', 'hora_salida'] as $campo) {
            if ($parcial && !array_key_exists($campo, $entrada)) continue;
            $salida[$campo] = PeticionJson::hora($entrada[$campo] ?? null, $campo, false);
        }

        return $salida;
    }
}
