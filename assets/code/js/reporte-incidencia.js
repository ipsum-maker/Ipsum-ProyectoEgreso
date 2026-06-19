document.addEventListener("DOMContentLoaded", function () {
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";
    const STORAGE_KEY_REPORTES = "reportes-sistema";

    const formReporteIncidencia = document.getElementById("form-reporte-incidencia");
    const campoNombre = document.getElementById("nombre-profesor");
    const campoApellido = document.getElementById("apellido-profesor");
    const campoAsignatura = document.getElementById("asignatura");
    const campoFecha = document.getElementById("fecha-incidencia");
    const campoHora = document.getElementById("hora-reporte");
    const campoTipoSolicitud = document.getElementById("tipoSolicitud");
    const campoOtroTipo = document.getElementById("otroTipo");
    const campoTaller = document.getElementById("taller");
    const campoDescripcion = document.getElementById("descripcion");
    const botonCancelar = document.querySelector(".report-form__button--reset");

    function obtenerUsuarioActivo() {
        const usuarioActivo = localStorage.getItem(STORAGE_KEY_USUARIO_ACTIVO);

        if (usuarioActivo === null) {
            return null;
        }

        return JSON.parse(usuarioActivo);
    }

    function protegerFormulario() {
        const usuarioActivo = obtenerUsuarioActivo();

        if (usuarioActivo === null) {
            window.location.href = "login.html";
            return;
        }

        if (usuarioActivo.rol !== "profesor") {
            alert("Solo los profesores pueden crear reportes de incidencia.");
            window.location.href = "login.html";
        }
    }

    function obtenerReportes() {
        const reportesGuardados = localStorage.getItem(STORAGE_KEY_REPORTES);

        if (reportesGuardados === null) {
            return [];
        }

        return JSON.parse(reportesGuardados);
    }

    function guardarReportes(reportes) {
        localStorage.setItem(STORAGE_KEY_REPORTES, JSON.stringify(reportes));
    }

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

    function cargarFechaYHora() {
        if (campoFecha !== null) {
            campoFecha.value = obtenerFechaActual();
        }

        if (campoHora !== null) {
            campoHora.value = obtenerHoraActual();
        }
    }

    function validarFormulario() {
        if (campoNombre.value.trim() === "") {
            alert("Ingrese el nombre del profesor.");
            return false;
        }

        if (campoApellido.value.trim() === "") {
            alert("Ingrese el apellido del profesor.");
            return false;
        }

        if (campoAsignatura.value.trim() === "") {
            alert("Ingrese la asignatura.");
            return false;
        }

        if (campoTipoSolicitud.value.trim() === "") {
            alert("Seleccione el tipo de solicitud.");
            return false;
        }

        if (campoTipoSolicitud.value === "otro" && campoOtroTipo.value.trim() === "") {
            alert("Especifique el tipo de incidencia.");
            return false;
        }

        if (campoTaller.value.trim() === "") {
            alert("Seleccione el laboratorio o taller.");
            return false;
        }

        if (campoDescripcion.value.trim() === "") {
            alert("Ingrese una descripción detallada.");
            return false;
        }

        return true;
    }

    function guardarReporteIncidencia(evento) {
        evento.preventDefault();

        const usuarioActivo = obtenerUsuarioActivo();

        if (!validarFormulario()) {
            return;
        }

        const nuevoReporte = {
            id: Date.now(),
            tipo: "incidencia",
            estado: "pendiente",
            profesorCorreo: usuarioActivo.correo,
            profesorNombreUsuario: usuarioActivo.nombre,
            nombreProfesor: campoNombre.value.trim(),
            apellidoProfesor: campoApellido.value.trim(),
            asignatura: campoAsignatura.value.trim(),
            fecha: campoFecha.value,
            hora: campoHora.value,
            tipoSolicitud: campoTipoSolicitud.value,
            otroTipo: campoOtroTipo.value.trim(),
            taller: campoTaller.value,
            descripcion: campoDescripcion.value.trim(),
            fechaCreacion: new Date().toISOString(),
            solucion: ""
        };

        const reportes = obtenerReportes();
        reportes.push(nuevoReporte);
        guardarReportes(reportes);

        alert("Reporte de incidencia enviado correctamente.");
        window.location.href = "historial-reportes.html";
    }

    function cancelarFormulario(evento) {
        evento.preventDefault();
        window.location.href = "panel-profesores.html";
    }

    protegerFormulario();
    cargarFechaYHora();

    if (formReporteIncidencia !== null) {
        formReporteIncidencia.addEventListener("submit", guardarReporteIncidencia);
    }

    if (botonCancelar !== null) {
        botonCancelar.addEventListener("click", cancelarFormulario);
    }
});