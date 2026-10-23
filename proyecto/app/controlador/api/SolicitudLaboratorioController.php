<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/Conexion.php';
require_once __DIR__ . '/../../modelo/SolicitudLaboratorioDAO.php';
require_once __DIR__ . '/../../vista/RespuestaJson.php';
require_once __DIR__ . '/../../utilidades/PeticionJson.php';

final class SolicitudLaboratorioController
{
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
                PeticionJson::verificarCsrf();
            }

            $dao = new SolicitudLaboratorioDAO(Conexion::obtener());

            match ($metodo) {
                'GET' => $this->consultar($dao, $usuario),
                'POST' => $this->crear($dao, $usuario),
                'PUT', 'PATCH' => $this->decidir($dao, $usuario),
            };
        } catch (ErrorPeticion $error) {
            RespuestaJson::error($error->getMessage(), $error->getCode());
        } catch (Throwable $error) {
            $codigo = (int) $error->getCode();
            error_log('API solicitudes laboratorio: ' . $error->getMessage());
            if ($codigo < 400 || $codigo > 499) {
                RespuestaJson::error('Error interno al procesar la solicitud. Intente nuevamente.', 500);
            }
            RespuestaJson::error($error->getMessage(), $codigo);
        }
    }

    private function consultar(SolicitudLaboratorioDAO $dao, array $usuario): never
    {
        // 1. Consulta de disponibilidad de un aula en una fecha
        if (isset($_GET['disponibilidad']) || (isset($_GET['accion']) && $_GET['accion'] === 'disponibilidad') || (isset($_GET['aula_id']) && isset($_GET['fecha']) && !isset($_GET['id']))) {
            $aulaId = PeticionJson::id($_GET['aula_id'] ?? null);
            $fecha = PeticionJson::fecha($_GET['fecha'] ?? null);
            $ocupados = $dao->disponibilidad($aulaId, $fecha);
            RespuestaJson::exito([
                'aula_id' => $aulaId,
                'fecha' => $fecha,
                'ocupados' => $ocupados,
            ]);
        }

        // 2. Consulta de solicitud por ID
        if (array_key_exists('id', $_GET)) {
            $id = PeticionJson::id($_GET['id']);
            $solicitud = $dao->obtenerPorId($id);
            if ($solicitud === null || ($usuario['rol'] === 'profesor' && (int) $solicitud['usuario_id'] !== (int) $usuario['id'])) {
                throw new ErrorPeticion('Solicitud no encontrada', 404);
            }
            RespuestaJson::exito($solicitud);
        }

        // 3. Listado de solicitudes
        $propietario = $usuario['rol'] === 'profesor' ? (int) $usuario['id'] : null;
        RespuestaJson::exito($dao->obtener($propietario));
    }

    private function crear(SolicitudLaboratorioDAO $dao, array $usuario): never
    {
        PeticionJson::exigirRol($usuario, ['profesor']);
        $entrada = PeticionJson::leer();
        PeticionJson::campos($entrada, ['aula_id', 'fecha', 'hora_inicio', 'hora_fin', 'motivo']);

        $aulaId = PeticionJson::id($entrada['aula_id'] ?? null);
        $fecha = PeticionJson::fecha($entrada['fecha'] ?? null);
        $horaInicio = PeticionJson::hora($entrada['hora_inicio'] ?? null, 'hora_inicio', true);
        $horaFin = PeticionJson::hora($entrada['hora_fin'] ?? null, 'hora_fin', true);
        $motivo = PeticionJson::texto($entrada['motivo'] ?? null, 'motivo', 2000, true);

        if ($horaInicio >= $horaFin) {
            throw new ErrorPeticion('La hora de fin debe ser posterior a la de inicio.', 422);
        }

        $solicitud = $dao->crear([
            'usuario_id' => (int) $usuario['id'],
            'aula_id' => $aulaId,
            'fecha' => $fecha,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'motivo' => $motivo,
        ]);

        RespuestaJson::exito([
            'solicitud' => $solicitud,
            'redirect' => 'index.php?ruta=mis-solicitudes-laboratorio'
        ], 201, 'Solicitud enviada correctamente');
    }

    private function decidir(SolicitudLaboratorioDAO $dao, array $usuario): never
    {
        PeticionJson::exigirRol($usuario, ['coordinador', 'administrador']);
        $entrada = PeticionJson::leer();
        PeticionJson::campos($entrada, ['id', 'solicitud_id', 'estado', 'respuesta']);

        $id = PeticionJson::id($entrada['id'] ?? ($entrada['solicitud_id'] ?? null));
        $estado = PeticionJson::enumerado($entrada['estado'] ?? '', 'estado', ['aprobada', 'rechazada']);
        $respuesta = PeticionJson::texto($entrada['respuesta'] ?? '', 'respuesta', 2000, false);

        $solicitud = $dao->decidir($id, (int) $usuario['id'], $estado, $respuesta !== '' ? $respuesta : null);

        $mensaje = $estado === 'aprobada'
            ? 'Solicitud aprobada. El espacio queda reservado para ese horario.'
            : 'Solicitud rechazada.';

        RespuestaJson::exito([
            'solicitud' => $solicitud,
            'redirect' => 'index.php?ruta=solicitudes-laboratorio'
        ], 200, $mensaje);
    }
}
