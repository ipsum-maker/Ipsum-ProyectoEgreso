<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../modelo/IncidenciaDAO.php';
require_once __DIR__ . '/../vista/RespuestaJson.php';
require_once __DIR__ . '/../utilidades/PeticionJson.php';

final class IncidenciaController
{
    private const ESTADOS = ['pendiente', 'en_proceso', 'resuelto'];
    private const TIPOS = ['rotura-equipo', 'falla-equipo', 'otro'];
    private const CAMPOS = ['asignatura', 'grupo', 'turno', 'tipo_solicitud', 'otro_tipo', 'descripcion', 'codigo_equipo', 'aula_id', 'taller', 'nombre_profesor', 'apellido_profesor'];

    public function gestionar(string $metodo): void
    {
        try {
            $usuario = PeticionJson::usuario();
            $metodo = strtoupper($metodo);

            if (!in_array($metodo, ['GET', 'POST', 'PATCH', 'DELETE'], true)) {
                header('Allow: GET, POST, PATCH, DELETE');
                throw new ErrorPeticion('Método no permitido', 405);
            }

            if ($metodo !== 'GET') {
                PeticionJson::verificarCsrf();
            }

            if (in_array($metodo, ['POST', 'DELETE'], true)) {
                PeticionJson::exigirRol($usuario, ['profesor']);
            }

            $dao = new IncidenciaDAO(Conexion::obtener());

            match ($metodo) {
                'GET' => $this->listar($dao, $usuario),
                'POST' => $this->alta($dao, $usuario),
                'PATCH' => $this->modificar($dao, $usuario),
                'DELETE' => $this->baja($dao, $usuario),
            };
        } catch (ErrorPeticion $error) {
            RespuestaJson::error($error->getMessage(), $error->getCode());
        } catch (Throwable $error) {
            error_log('API incidencias: ' . $error->getMessage());
            RespuestaJson::error('Error interno al procesar la incidencia: ' . $error->getMessage(), 500);
        }
    }

    private function listar(IncidenciaDAO $dao, array $usuario): never
    {
        $propietario = $usuario['rol'] === 'profesor' ? (int) $usuario['id'] : null;

        if (array_key_exists('id', $_GET)) {
            $incidencia = $dao->obtener(PeticionJson::id($_GET['id']), $propietario);
            if ($incidencia === null) {
                throw new ErrorPeticion('Incidencia no encontrada', 404);
            }
            RespuestaJson::exito($incidencia);
        }

        $estado = array_key_exists('estado', $_GET) ? PeticionJson::enumerado($_GET['estado'], 'estado', self::ESTADOS) : null;
        RespuestaJson::exito($dao->listar($propietario, $estado));
    }

    private function alta(IncidenciaDAO $dao, array $usuario): never
    {
        $entrada = PeticionJson::leer();
        PeticionJson::campos($entrada, array_merge(self::CAMPOS, ['fecha', 'hora']));
        $datos = $this->validarDatos($entrada, false);
        $datos['fecha'] = PeticionJson::fecha($entrada['fecha'] ?? date('Y-m-d'));
        $datos['hora'] = PeticionJson::hora($entrada['hora'] ?? date('H:i:s'), 'hora', true);

        if ($datos['tipo_solicitud'] === 'otro' && empty($datos['otro_tipo'])) {
            throw new ErrorPeticion('Especifique el otro tipo de incidencia', 422);
        }
        if ($datos['tipo_solicitud'] !== 'otro') {
            $datos['otro_tipo'] = null;
        }

        $id = $dao->crear((int) $usuario['id'], $datos);
        RespuestaJson::exito(['id' => $id, 'mensaje' => 'Reporte de incidencia creado correctamente'], 201);
    }

    private function modificar(IncidenciaDAO $dao, array $usuario): never
    {
        $entrada = PeticionJson::leer();
        $accion = PeticionJson::enumerado($entrada['accion'] ?? 'editar', 'accion', ['editar', 'marcar-en-proceso', 'resolver']);
        $id = PeticionJson::id($entrada['id'] ?? null);

        if ($accion === 'editar') {
            PeticionJson::exigirRol($usuario, ['profesor']);
            PeticionJson::campos($entrada, array_merge(['id', 'accion'], self::CAMPOS));
            $cambios = $this->validarDatos($entrada, true);
            if (!$cambios) {
                throw new ErrorPeticion('No se enviaron campos para modificar', 422);
            }

            $dao->transaccion(function () use ($dao, $id, $usuario, $cambios): void {
                $incidencia = $this->propiaPendiente($dao, $id, (int) $usuario['id']);
                $final = array_replace($incidencia, $cambios);
                if ($final['tipo_solicitud'] === 'otro' && trim((string) ($final['otro_tipo'] ?? '')) === '') {
                    throw new ErrorPeticion('Especifique el otro tipo de incidencia', 422);
                }
                if ($final['tipo_solicitud'] !== 'otro') {
                    $cambios['otro_tipo'] = null;
                }
                if (!$dao->modificar($id, (int) $usuario['id'], $cambios)) {
                    throw new RuntimeException('Falló la modificación de la incidencia');
                }
            });
        } else {
            PeticionJson::exigirRol($usuario, ['coordinador', 'administrador']);
            PeticionJson::campos($entrada, $accion === 'resolver' ? ['id', 'accion', 'estado', 'solucion'] : ['id', 'accion']);
            $estado = $accion === 'resolver' ? PeticionJson::enumerado($entrada['estado'] ?? 'resuelto', 'estado', self::ESTADOS) : 'en_proceso';
            $solucion = $accion === 'resolver' ? PeticionJson::textoLargo($entrada['solucion'] ?? '', 'solucion', true) : '';

            $dao->transaccion(function () use ($dao, $id, $accion, $estado, $solucion): void {
                $incidencia = $dao->obtener($id, null, true);
                if ($incidencia === null) {
                    throw new ErrorPeticion('Incidencia no encontrada', 404);
                }
                if ($accion === 'marcar-en-proceso') {
                    if ($incidencia['estado'] !== 'pendiente') {
                        throw new ErrorPeticion('Solo una incidencia pendiente puede pasar a proceso', 409);
                    }
                    if (!$dao->marcarEnProceso($id)) {
                        throw new RuntimeException('Falló el cambio de estado');
                    }
                } elseif (!$dao->resolver($id, $estado, $solucion)) {
                    throw new RuntimeException('Falló la resolución');
                }
            });
        }

        RespuestaJson::exito(['mensaje' => 'Incidencia actualizada correctamente']);
    }

    private function baja(IncidenciaDAO $dao, array $usuario): never
    {
        $entrada = PeticionJson::leer();
        PeticionJson::campos($entrada, ['id']);
        $id = PeticionJson::id($entrada['id'] ?? null);

        $dao->transaccion(function () use ($dao, $id, $usuario): void {
            $this->propiaPendiente($dao, $id, (int) $usuario['id']);
            if (!$dao->eliminar($id, (int) $usuario['id'])) {
                throw new RuntimeException('Falló la eliminación');
            }
        });

        RespuestaJson::exito(['mensaje' => 'Incidencia eliminada correctamente']);
    }

    private function propiaPendiente(IncidenciaDAO $dao, int $id, int $usuarioId): array
    {
        $incidencia = $dao->obtener($id, $usuarioId, true);
        if ($incidencia === null) {
            throw new ErrorPeticion('Incidencia propia no encontrada', 404);
        }
        if ($incidencia['estado'] !== 'pendiente') {
            throw new ErrorPeticion('Solo puede modificar o eliminar incidencias pendientes', 409);
        }
        return $incidencia;
    }

    private function validarDatos(array $entrada, bool $parcial): array
    {
        $datos = [];
        foreach (['asignatura' => 100, 'grupo' => 50, 'otro_tipo' => 150, 'codigo_equipo' => 50] as $campo => $maximo) {
            if ($parcial && !array_key_exists($campo, $entrada)) continue;
            $obligatorio = !in_array($campo, ['grupo', 'otro_tipo', 'codigo_equipo'], true);
            $datos[$campo] = PeticionJson::texto($entrada[$campo] ?? '', $campo, $maximo, $obligatorio);
        }

        if (array_key_exists('aula_id', $entrada)) {
            $datos['aula_id'] = PeticionJson::id($entrada['aula_id']);
        } elseif (array_key_exists('taller', $entrada)) {
            $datos['taller'] = is_numeric($entrada['taller']) ? (int) $entrada['taller'] : PeticionJson::texto($entrada['taller'], 'taller', 100, true);
        }

        if (!$parcial || array_key_exists('turno', $entrada)) {
            $datos['turno'] = PeticionJson::enumerado($entrada['turno'] ?? 'matutino', 'turno', ['matutino', 'vespertino', 'nocturno']);
        }
        if (!$parcial || array_key_exists('tipo_solicitud', $entrada)) {
            $datos['tipo_solicitud'] = PeticionJson::enumerado($entrada['tipo_solicitud'] ?? '', 'tipo_solicitud', self::TIPOS);
        }
        if (!$parcial || array_key_exists('descripcion', $entrada)) {
            $datos['descripcion'] = PeticionJson::textoLargo($entrada['descripcion'] ?? '', 'descripcion', false);
        }

        return $datos;
    }
}
