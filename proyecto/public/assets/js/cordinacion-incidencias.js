document.addEventListener("DOMContentLoaded", function () {
    const buscador = document.getElementById("buscador-registros-incidencias");
    const filtroEstado = document.getElementById("filtro-estado-incidencias");
    const tabla = document.getElementById("tabla-registros-incidencias");

    const modalDetalle = document.getElementById("modal-detalle-incidencia");
    const botonCerrarDetalle1 = document.getElementById("cerrar-modal-incidencia");
    const botonCerrarDetalle2 = document.getElementById("boton-cerrar-detalle-incidencia");

    const modalSolucion = document.getElementById("modal-solucion-incidencia");
    const botonCerrarSolucion = document.getElementById("cerrar-modal-solucion");
    const botonCancelarSolucion = document.getElementById("boton-cancelar-solucion");

    // 1. Filtrado combinado (por término de búsqueda y selector de estado)
    function aplicarFiltros() {
        if (!tabla) return;

        const termino = buscador ? buscador.value.toLowerCase().trim() : "";
        const estadoSeleccionado = filtroEstado ? filtroEstado.value.toLowerCase() : "todos";
        const filas = tabla.querySelectorAll("tr");

        filas.forEach(function (fila) {
            const textoFila = fila.textContent.toLowerCase();
            const coincideTexto = textoFila.includes(termino);
            const coincideEstado = (estadoSeleccionado === "todos") || fila.classList.contains(`estado-${estadoSeleccionado}`);

            if (coincideTexto && coincideEstado) {
                fila.style.display = "";
            } else {
                fila.style.display = "none";
            }
        });
    }

    if (buscador) buscador.addEventListener("input", aplicarFiltros);
    if (filtroEstado) filtroEstado.addEventListener("change", aplicarFiltros);

    // 2. Eventos para cerrar modal de detalle
    if (botonCerrarDetalle1) {
        botonCerrarDetalle1.addEventListener("click", function () {
            ControlErrores.cerrarModalSeguro(modalDetalle);
        });
    }
    if (botonCerrarDetalle2) {
        botonCerrarDetalle2.addEventListener("click", function () {
            ControlErrores.cerrarModalSeguro(modalDetalle);
        });
    }

    // 3. Eventos para cerrar modal de solución
    if (botonCerrarSolucion) {
        botonCerrarSolucion.addEventListener("click", function () {
            ControlErrores.cerrarModalSeguro(modalSolucion);
        });
    }
    if (botonCancelarSolucion) {
        botonCancelarSolucion.addEventListener("click", function () {
            ControlErrores.cerrarModalSeguro(modalSolucion);
        });
    }
});

// Función global para abrir el modal de documentar solución
function abrirModalSolucionIncidencia(idIncidencia) {
    const modalSolucion = document.getElementById("modal-solucion-incidencia");
    const inputId = document.getElementById("solucion-incidencia-id");
    if (inputId) {
        inputId.value = idIncidencia;
    }
    ControlErrores.abrirModalSeguro(modalSolucion);
}
