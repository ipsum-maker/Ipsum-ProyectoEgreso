document.addEventListener("DOMContentLoaded", function () {
    const campoFecha = document.getElementById("fecha-incidencia");
    const campoHora = document.getElementById("hora-reporte");
    const selectorTipo = document.getElementById("tipoSolicitud");
    const campoOtroTipo = document.getElementById("otroTipo");
    const etiquetaOtroTipo = document.querySelector('label[for="otroTipo"]');

    // Autocompletar fecha y hora actual
    if (campoFecha && !campoFecha.value) {
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        campoFecha.value = `${yyyy}-${mm}-${dd}`;
    }

    if (campoHora && !campoHora.value) {
        const hoy = new Date();
        const hh = String(hoy.getHours()).padStart(2, '0');
        const mi = String(hoy.getMinutes()).padStart(2, '0');
        campoHora.value = `${hh}:${mi}`;
    }

    // Mostrar u ocultar el campo "Especifique el tipo de incidencia" según selección
    function actualizarVisibilidadOtroTipo() {
        if (!selectorTipo || !campoOtroTipo) return;

        if (selectorTipo.value === "otro") {
            campoOtroTipo.style.display = "block";
            if (etiquetaOtroTipo) etiquetaOtroTipo.style.display = "block";
            campoOtroTipo.required = true;
        } else {
            campoOtroTipo.style.display = "none";
            if (etiquetaOtroTipo) etiquetaOtroTipo.style.display = "none";
            campoOtroTipo.required = false;
            campoOtroTipo.value = "";
        }
    }

    if (selectorTipo) {
        actualizarVisibilidadOtroTipo();
        selectorTipo.addEventListener("change", actualizarVisibilidadOtroTipo);
    }
});
