<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/utilidades/sesion.php';

$ruta = (string) ($_GET['ruta'] ?? 'inicio');

// Cada ruta carga un controlador que representa una sola funcionalidad.
switch ($ruta) {
    case 'inicio':
        require __DIR__ . '/../app/controlador/autenticacion/irAlInicio.php';
        break;
    case 'login':
        require __DIR__ . '/../app/vista/login.php';
        break;
    case 'iniciar-sesion':
        require __DIR__ . '/../app/controlador/autenticacion/iniciarSesion.php';
        break;
    case 'cerrar-sesion':
        require __DIR__ . '/../app/controlador/autenticacion/cerrarSesion.php';
        break;
    case 'panel-administrador':
        require __DIR__ . '/../app/controlador/administrador/mostrarPanelAdministrador.php';
        break;
    case 'crear-usuario':
        require __DIR__ . '/../app/controlador/administrador/crearUsuario.php';
        break;
    case 'cambiar-rol-usuario':
        require __DIR__ . '/../app/controlador/administrador/cambiarRolUsuario.php';
        break;
    case 'eliminar-usuario':
        require __DIR__ . '/../app/controlador/administrador/eliminarUsuario.php';
        break;
    case 'panel-profesores':
        require __DIR__ . '/../app/controlador/profesor/mostrarPanelProfesores.php';
        break;
    case 'reporte-diario':
        require __DIR__ . '/../app/controlador/profesor/mostrarReporteDiario.php';
        break;
    case 'reporte-incidencia':
        require __DIR__ . '/../app/controlador/profesor/mostrarReporteIncidencia.php';
        break;
    case 'historial-reportes':
        require __DIR__ . '/../app/controlador/profesor/mostrarHistorialReportes.php';
        break;
    case 'panel-cordinacion':
        require __DIR__ . '/../app/controlador/coordinacion/mostrarPanelCordinacion.php';
        break;
    case 'cordinacion-diarios':
        require __DIR__ . '/../app/controlador/coordinacion/mostrarCordinacionDiarios.php';
        break;
    case 'guardar-reporte-diario':
        require __DIR__ . '/../app/controlador/profesor/guardarReporteDiario.php';
        break;
    case 'guardar-reporte-incidencia':
        require __DIR__ . '/../app/controlador/profesor/guardarReporteIncidencia.php';
        break;
    case 'cordinacion-incidencias':
        require __DIR__ . '/../app/controlador/coordinacion/mostrarCordinacionIncidencias.php';
        break;
    case 'resolver-incidencia':
        require __DIR__ . '/../app/controlador/coordinacion/resolverIncidencia.php';
        break;
    case 'marcar-incidencia-proceso':
        require __DIR__ . '/../app/controlador/coordinacion/marcarIncidenciaProceso.php';
        break;
    case 'eliminar-reporte-incidencia':
        require __DIR__ . '/../app/controlador/profesor/eliminarReporteIncidencia.php';
        break;
    case 'actualizar-reporte-diario':
        require __DIR__ . '/../app/controlador/profesor/actualizarReporteDiario.php';
        break;
    case 'actualizar-reporte-incidencia':
        require __DIR__ . '/../app/controlador/profesor/actualizarReporteIncidencia.php';
        break;
    case 'metricas-administrador':
        require __DIR__ . '/../app/controlador/administrador/mostrarMetricasAdministrador.php';
        break;
    case 'solicitar-laboratorio':
    case 'solicitar-salon':
        require __DIR__ . '/../app/controlador/profesor/mostrarSolicitarLaboratorio.php';
        break;
    case 'guardar-solicitud-laboratorio':
        require __DIR__ . '/../app/controlador/profesor/guardarSolicitudLaboratorio.php';
        break;
    case 'mis-solicitudes-laboratorio':
    case 'mis-solicitudes':
        require __DIR__ . '/../app/controlador/profesor/mostrarMisSolicitudesLaboratorio.php';
        break;
    case 'solicitudes-laboratorio':
    case 'solicitudes-salones':
        require __DIR__ . '/../app/controlador/coordinacion/mostrarSolicitudesLaboratorio.php';
        break;
    case 'decidir-solicitud-laboratorio':
        require __DIR__ . '/../app/controlador/coordinacion/decidirSolicitudLaboratorio.php';
        break;
    default:

        http_response_code(404);
        echo '<h1>Página no encontrada</h1>';
        echo '<p><a href="index.php">Volver al sistema</a></p>';
        break;
}
