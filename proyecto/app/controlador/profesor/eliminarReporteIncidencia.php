<?php

declare(strict_types=1);

require_once __DIR__ . '/../../modelo/ReporteIncidenciaModelo.php';
require_once __DIR__ . '/../../utilidades/sesion.php';

$usuario = exigirRol(['profesor']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir(urlRuta('historial-reportes'));
}

$tokenRecibido = (string) ($_POST['csrf_token'] ?? '');
if (!verificarTokenCSRF($tokenRecibido)) {
    guardarMensaje('error', 'Solicitud no válida. Intente nuevamente.');
    redirigir(urlRuta('historial-reportes'));
}

$incidenciaId = (int) ($_POST['incidencia_id'] ?? 0);

if ($incidenciaId <= 0) {
    guardarMensaje('error', 'Incidencia no válida.');
    redirigir(urlRuta('historial-reportes'));
}

try {
    $modelo = new ReporteIncidenciaModelo();
    $incidencia = $modelo->obtenerPorId($incidenciaId);

    if ($incidencia === null || (int) $incidencia['usuario_id'] !== (int) $usuario['id']) {
        guardarMensaje('error', 'No tiene permisos para eliminar este reporte.');
        redirigir(urlRuta('historial-reportes'));
    }

    if ((string) $incidencia['estado'] !== 'pendiente') {
        guardarMensaje('error', 'No se puede eliminar un reporte que está en proceso o resuelto.');
        redirigir(urlRuta('historial-reportes'));
    }

    $modelo->eliminar($incidenciaId, (int) $usuario['id']);
    guardarMensaje('exito', 'Reporte de incidencia eliminado correctamente.');
} catch (PDOException $error) {
    guardarMensaje('error', 'Error al eliminar el reporte de la base de datos.');
}

redirigir(urlRuta('historial-reportes'));
