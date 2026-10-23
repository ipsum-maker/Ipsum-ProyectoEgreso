<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/utilidades/sesion.php';
require_once __DIR__ . '/../../app/vista/RespuestaJson.php';
require_once __DIR__ . '/../../app/controlador/api/IncidenciaController.php';
require_once __DIR__ . '/../../app/controlador/api/ReporteDiarioController.php';
require_once __DIR__ . '/../../app/controlador/api/SolicitudLaboratorioController.php';
require_once __DIR__ . '/../../app/controlador/api/UsuarioController.php';
require_once __DIR__ . '/../../app/controlador/api/MetricasController.php';
require_once __DIR__ . '/../../app/modelo/AulaModelo.php';
require_once __DIR__ . '/../../app/utilidades/PeticionJson.php';

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
            '/usuarios' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            '/aulas' => ['GET'],
            '/incidencias' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            '/reportes-diarios' => ['GET', 'POST', 'PUT', 'PATCH'],
            '/solicitudes-laboratorio' => ['GET', 'POST', 'PUT', 'PATCH'],
            '/solicitudes-laboratorio/disponibilidad' => ['GET'],
            '/metricas' => ['GET'],
        ]
    ], 200, 'Catálogo de endpoints de la API');
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
    try {
        PeticionJson::usuario();
        if ($metodo !== 'GET') {
            header('Allow: GET');
            throw new ErrorPeticion('Método no permitido', 405);
        }
        $aulaModelo = new AulaModelo();
        RespuestaJson::exito($aulaModelo->obtenerTodasActivas());
    } catch (ErrorPeticion $error) {
        RespuestaJson::error($error->getMessage(), $error->getCode());
    } catch (Throwable $error) {
        error_log('API aulas: ' . $error->getMessage());
        RespuestaJson::error('Error interno al obtener las aulas. Intente nuevamente.', 500);
    }
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
if ($recurso === '/metricas') {
    (new MetricasController())->gestionar($metodo);
}

RespuestaJson::error('Recurso no encontrado: ' . $recurso, 404);
