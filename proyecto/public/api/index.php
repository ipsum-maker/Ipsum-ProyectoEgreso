<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/utilidades/sesion.php';
require_once __DIR__ . '/../../app/vista/RespuestaJson.php';
require_once __DIR__ . '/../../app/controlador/IncidenciaController.php';
require_once __DIR__ . '/../../app/controlador/ReporteDiarioController.php';
require_once __DIR__ . '/../../app/controlador/SolicitudLaboratorioController.php';
require_once __DIR__ . '/../../app/controlador/UsuarioController.php';
require_once __DIR__ . '/../../app/modelo/AulaModelo.php';
require_once __DIR__ . '/../../app/modelo/MetricasModelo.php';

$metodo = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$recurso = trim((string) ($_GET['r'] ?? ''));

// Si no viene parámetro 'r', intentar extraer de PATH_INFO o REQUEST_URI
if ($recurso === '') {
    $pathInfo = $_SERVER['PATH_INFO'] ?? '';
    if ($pathInfo !== '') {
        $recurso = $pathInfo;
    }
}

// Normalizar barra inicial
if ($recurso !== '' && !str_starts_with($recurso, '/')) {
    $recurso = '/' . $recurso;
}

// 1. Raíz de la API con catálogo de endpoints
if ($recurso === '' || $recurso === '/') {
    RespuestaJson::exito([
        'api' => 'Ipsum REST API',
        'version' => '1.0',
        'endpoints' => [
            '/sesion' => ['GET', 'POST', 'DELETE'],
            '/usuarios' => ['GET', 'POST', 'PATCH', 'DELETE'],
            '/aulas' => ['GET'],
            '/incidencias' => ['GET', 'POST', 'PATCH', 'DELETE'],
            '/reportes-diarios' => ['GET', 'POST', 'PATCH'],
            '/solicitudes-laboratorio' => ['GET', 'POST', 'PATCH'],
            '/solicitudes-laboratorio/disponibilidad' => ['GET'],
            '/metricas/equipos' => ['GET'],
        ]
    ]);
}

// 2. Sesión
if ($recurso === '/sesion') {
    require __DIR__ . '/sesion.php';
    exit;
}

// 3. Usuarios
if (str_starts_with($recurso, '/usuarios')) {
    $partes = explode('/', trim($recurso, '/'));
    if (isset($partes[1]) && is_numeric($partes[1])) {
        $_GET['id'] = $partes[1];
    }
    (new UsuarioController())->gestionar($metodo);
    exit;
}

// 4. Catálogo de Aulas
if ($recurso === '/aulas') {
    $aulaModelo = new AulaModelo();
    RespuestaJson::exito($aulaModelo->obtenerTodasActivas());
}

// 5. Disponibilidad de espacios
if (str_starts_with($recurso, '/solicitudes-laboratorio/disponibilidad') || str_starts_with($recurso, '/solicitudes/disponibilidad')) {
    $_GET['disponibilidad'] = '1';
    (new SolicitudLaboratorioController())->gestionar('GET');
}

// 6. Solicitudes de laboratorio / salones
if (str_starts_with($recurso, '/solicitudes-laboratorio') || str_starts_with($recurso, '/solicitudes')) {
    $partes = explode('/', trim($recurso, '/'));
    if (isset($partes[1]) && is_numeric($partes[1])) {
        $_GET['id'] = $partes[1];
    }
    (new SolicitudLaboratorioController())->gestionar($metodo);
}

// 7. Incidencias
if (str_starts_with($recurso, '/incidencias')) {
    $partes = explode('/', trim($recurso, '/'));
    if (isset($partes[1]) && is_numeric($partes[1])) {
        $_GET['id'] = $partes[1];
    }
    (new IncidenciaController())->gestionar($metodo);
}

// 8. Reportes diarios
if (str_starts_with($recurso, '/reportes-diarios') || str_starts_with($recurso, '/diarios')) {
    $partes = explode('/', trim($recurso, '/'));
    if (isset($partes[1]) && is_numeric($partes[1])) {
        $_GET['id'] = $partes[1];
    }
    (new ReporteDiarioController())->gestionar($metodo);
}

// 9. Métricas para administración
if (str_starts_with($recurso, '/metricas')) {
    $usuario = PeticionJson::usuario();
    PeticionJson::exigirRol($usuario, ['administrador']);
    $metricasModelo = new MetricasModelo();
    RespuestaJson::exito([
        'estadisticas' => $metricasModelo->obtenerEstadisticasGenerales(),
        'rendimiento' => $metricasModelo->obtenerRendimientoResolucion(),
        'porTaller' => $metricasModelo->obtenerIncidenciasPorTaller(),
        'porDocente' => $metricasModelo->obtenerIncidenciasPorDocente(),
    ]);
}

RespuestaJson::error('Recurso no encontrado: ' . $recurso, 404);
