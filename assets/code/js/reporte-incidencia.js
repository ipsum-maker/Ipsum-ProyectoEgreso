document.addEventListener("DOMContentLoaded", function () {
    const STORAGE_KEY_REPORTES = "reportes";

    const formReporteIncidencia = document.getElementById("form-reporte-incidencia");

    const campoNombreProfesor = document.getElementById("nombre-profesor");
    const campoApellidoProfesor = document.getElementById("apellido-profesor");
    const campoAsignatura = document.getElementById("asignatura");
    const campoFechaIncidencia = document.getElementById("fecha-incidencia");
    const campoHoraReporte = document.getElementById("hora-reporte");
    const campoTipoSolicitud = document.getElementById("tipoSolicitud");
    const campoOtroTipo = document.getElementById("otroTipo");
    const campoTaller = document.getElementById("taller");
    const campoDescripcion = document.getElementById("descripcion");

    function obtenerFechaActual() {
        const fechaActual = new Date();

        const anio = fechaActual.getFullYear();
        const mes = String(fechaActual.getMonth() + 1).padStart(2, "0");
        const dia = String(fechaActual.getDate()).padStart(2, "0");

        return `${anio}-${mes}-${dia}`;
    }

    function obtenerHoraActual() {
        const fechaActual = new Date();

        const horas = String(fechaActual.getHours()).padStart(2, "0");
        const minutos = String(fechaActual.getMinutes()).padStart(2, "0");

        return `${horas}:${minutos}`;
    }

    function cargarFechaYHoraAutomatica() {
        campoFechaIncidencia.value = obtenerFechaActual();
        campoHoraReporte.value = obtenerHoraActual();

        campoFechaIncidencia.readOnly = true;
        campoHoraReporte.readOnly = true;
    }

    function obtenerReportesGuardados() {
        const reportesGuardados = localStorage.getItem(STORAGE_KEY_REPORTES);

        if (reportesGuardados) {
            return JSON.parse(reportesGuardados);
        }

        return [];
    }

    function guardarReporteIncidencia(reporteIncidencia) {
        const reportesGuardados = obtenerReportesGuardados();

        reportesGuardados.push(reporteIncidencia);

        localStorage.setItem(STORAGE_KEY_REPORTES, JSON.stringify(reportesGuardados));
    }

    cargarFechaYHoraAutomatica();

    formReporteIncidencia.addEventListener("submit", function (event) {
        event.preventDefault();

        const reporteIncidencia = {
            tipo: "reporte-incidencia",
            nombreProfesor: campoNombreProfesor.value,
            apellidoProfesor: campoApellidoProfesor.value,
            asignatura: campoAsignatura.value,
            fecha: campoFechaIncidencia.value,
            hora: campoHoraReporte.value,
            tipoSolicitud: campoTipoSolicitud.value,
            otroTipo: campoOtroTipo.value,
            taller: campoTaller.value,
            descripcion: campoDescripcion.value
        };

        guardarReporteIncidencia(reporteIncidencia);

        alert("✅ Reporte enviado correctamente");

        formReporteIncidencia.reset();

        cargarFechaYHoraAutomatica();
    });
});