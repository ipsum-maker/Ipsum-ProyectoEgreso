<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/utilidades/sesion.php';

$ruta = (string) ($_GET['ruta'] ?? 'inicio');

// Cada ruta carga un controlador que representa una sola funcionalidad.
switch ($ruta) {
    case 'inicio':
        require __DIR__ . '/../app/controlador/irAlInicio.php';
        break;
    case 'login':
        require __DIR__ . '/../app/vista/login.php';
        break;
    case 'iniciar-sesion':
        require __DIR__ . '/../app/controlador/iniciarSesion.php';
        break;
    case 'cerrar-sesion':
        require __DIR__ . '/../app/controlador/cerrarSesion.php';
        break;
    case 'panel-administrador':
        require __DIR__ . '/../app/controlador/mostrarPanelAdministrador.php';
        break;
    case 'crear-usuario':
        require __DIR__ . '/../app/controlador/crearUsuario.php';
        break;
    case 'cambiar-rol-usuario':
        require __DIR__ . '/../app/controlador/cambiarRolUsuario.php';
        break;
    case 'eliminar-usuario':
        require __DIR__ . '/../app/controlador/eliminarUsuario.php';
        break;
    case 'panel-profesores':
        require __DIR__ . '/../app/controlador/mostrarPanelProfesores.php';
        break;
    case 'reporte-diario':
        require __DIR__ . '/../app/controlador/mostrarReporteDiario.php';
        break;
    case 'reporte-incidencia':
        require __DIR__ . '/../app/controlador/mostrarReporteIncidencia.php';
        break;
    case 'historial-reportes':
        require __DIR__ . '/../app/controlador/mostrarHistorialReportes.php';
        break;
    case 'panel-cordinacion':
        require __DIR__ . '/../app/controlador/mostrarPanelCordinacion.php';
        break;
    case 'cordinacion-diarios':
        require __DIR__ . '/../app/controlador/mostrarCordinacionDiarios.php';
        break;
    case 'guardar-reporte-diario':
        require __DIR__ . '/../app/controlador/guardarReporteDiario.php';
        break;
    case 'guardar-reporte-incidencia':
        require __DIR__ . '/../app/controlador/guardarReporteIncidencia.php';
        break;
    case 'cordinacion-incidencias':
        require __DIR__ . '/../app/controlador/mostrarCordinacionIncidencias.php';
        break;
    case 'resolver-incidencia':
        require __DIR__ . '/../app/controlador/resolverIncidencia.php';
        break;
    case 'marcar-incidencia-proceso':
        require __DIR__ . '/../app/controlador/marcarIncidenciaProceso.php';
        break;
    case 'eliminar-reporte-incidencia':
        require __DIR__ . '/../app/controlador/eliminarReporteIncidencia.php';
        break;
    case 'actualizar-reporte-diario':
        require __DIR__ . '/../app/controlador/actualizarReporteDiario.php';
        break;
    case 'actualizar-reporte-incidencia':
        require __DIR__ . '/../app/controlador/actualizarReporteIncidencia.php';
        break;
    case 'metricas-administrador':
        require __DIR__ . '/../app/controlador/mostrarMetricasAdministrador.php';
        break;
    default:

        http_response_code(404);
        echo '<h1>Página no encontrada</h1>';
        echo '<p><a href="index.php">Volver al sistema</a></p>';
        break;
}
