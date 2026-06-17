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

    // Se busca el botón cancelar usando la clase que ya existe en tu HTML
    const botonCancelar = document.querySelector(".report-form__button--reset");

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

    function formularioEstaCompleto() {
        if (campoNombreProfesor.value.trim() === "") {
            return false;
        }

        if (campoApellidoProfesor.value.trim() === "") {
            return false;
        }

        if (campoAsignatura.value.trim() === "") {
            return false;
        }

        if (campoFechaIncidencia.value.trim() === "") {
            return false;
        }

        if (campoHoraReporte.value.trim() === "") {
            return false;
        }

        if (campoTipoSolicitud.value.trim() === "") {
            return false;
        }

        if (campoTaller.value.trim() === "") {
            return false;
        }

        if (campoDescripcion.value.trim() === "") {
            return false;
        }

        // El campo "otroTipo" solo es obligatorio si se eligió "Otro"
        if (campoTipoSolicitud.value === "otro" && campoOtroTipo.value.trim() === "") {
            return false;
        }

        return true;
    }

    cargarFechaYHoraAutomatica();

    // Botón Cancelar: evita el reset y vuelve al panel
    botonCancelar.addEventListener("click", function (event) {
        event.preventDefault();
        window.location.href = "panel-profesores.html";
    });

    formReporteIncidencia.addEventListener("submit", function (event) {
        event.preventDefault();

        if (!formularioEstaCompleto()) {
            alert("⚠️ Debe rellenar el formulario antes de enviar el reporte");
            return;
        }

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