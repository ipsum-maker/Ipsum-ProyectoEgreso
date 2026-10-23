<?php
declare(strict_types=1);

require_once __DIR__ . '/../../modelo/MetricasModelo.php';
require_once __DIR__ . '/../../vista/RespuestaJson.php';
require_once __DIR__ . '/../../utilidades/PeticionJson.php';

final class MetricasController
{
    private const PERIODOS = ['hoy', 'esta-semana', 'ultimos-30-dias', 'personalizado'];

    public function gestionar(string $metodo): void
    {
        try {
            $usuario = PeticionJson::usuario();
            PeticionJson::exigirRol($usuario, ['administrador']);

            if (strtoupper($metodo) !== 'GET') {
                header('Allow: GET');
                throw new ErrorPeticion('Método no permitido', 405);
            }

            [$periodo, $fechaDesde, $fechaHasta] = $this->rangoDeFechas();

            $modelo = new MetricasModelo();
            RespuestaJson::exito([
                'periodo' => $periodo,
                'desde' => $fechaDesde,
                'hasta' => $fechaHasta,
                'kpis' => $modelo->obtenerResumenKPIs($fechaDesde, $fechaHasta),
                'porAula' => $modelo->obtenerIncidenciasPorAula($fechaDesde, $fechaHasta),
                'distribucionTipos' => $modelo->obtenerDistribucionTipos($fechaDesde, $fechaHasta),
                'recientes' => $modelo->obtenerIncidenciasRecientes($fechaDesde, $fechaHasta, 5),
            ], 200, 'Métricas del período ' . $fechaDesde . ' al ' . $fechaHasta);
        } catch (ErrorPeticion $error) {
            RespuestaJson::error($error->getMessage(), $error->getCode());
        } catch (Throwable $error) {
            error_log('API metricas: ' . $error->getMessage());
            RespuestaJson::error('Error interno al calcular las métricas. Intente nuevamente.', 500);
        }
    }

    private function rangoDeFechas(): array
    {
        $periodo = PeticionJson::enumerado($_GET['periodo'] ?? 'ultimos-30-dias', 'periodo', self::PERIODOS);
        $hoy = date('Y-m-d');

        return match ($periodo) {
            'hoy' => [$periodo, $hoy, $hoy],
            'esta-semana' => [$periodo, date('Y-m-d', strtotime('monday this week')), $hoy],
            'ultimos-30-dias' => [$periodo, date('Y-m-d', strtotime('-30 days')), $hoy],
            'personalizado' => $this->rangoPersonalizado(),
        };
    }

    private function rangoPersonalizado(): array
    {
        $desde = PeticionJson::fecha($_GET['fecha_desde'] ?? null);
        $hasta = PeticionJson::fecha($_GET['fecha_hasta'] ?? null);
        if ($desde > $hasta) {
            throw new ErrorPeticion('fecha_desde no puede ser posterior a fecha_hasta', 422);
        }
        return ['personalizado', $desde, $hasta];
    }
}
