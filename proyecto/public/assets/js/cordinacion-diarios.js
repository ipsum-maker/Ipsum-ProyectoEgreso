document.addEventListener("DOMContentLoaded", function () {
    const buscador = document.getElementById("buscador-registros-diarios");
    const tabla = document.getElementById("tabla-registros-diarios");
    const modalDetalle = document.getElementById("modal-detalle-diario");
    const botonCerrarModal1 = document.getElementById("cerrar-modal-diario");
    const botonCerrarModal2 = document.getElementById("boton-cerrar-detalle-diario");

    // 1. Filtrado en tiempo real en la tabla mediante el buscador
    if (buscador && tabla) {
        buscador.addEventListener("input", function () {
            const termino = buscador.value.toLowerCase().trim();
            const filas = tabla.querySelectorAll("tr");

            filas.forEach(function (fila) {
                const textoFila = fila.textContent.toLowerCase();
                if (textoFila.includes(termino)) {
                    fila.style.display = "";
                } else {
                    fila.style.display = "none";
                }
            });
        });
    }

    // 2. Controladores para cerrar el modal de detalles
    if (botonCerrarModal1) {
        botonCerrarModal1.addEventListener("click", function () {
            ControlErrores.cerrarModalSeguro(modalDetalle);
        });
    }

    if (botonCerrarModal2) {
        botonCerrarModal2.addEventListener("click", function () {
            ControlErrores.cerrarModalSeguro(modalDetalle);
        });
    }
});

// Función global invocable desde botones de ver detalles en las filas renderizadas por PHP
function abrirDetalleDiario(idReporte) {
    const modalDetalle = document.getElementById("modal-detalle-diario");
    // Al hacer clic, se abre el modal que ya contiene los datos cargados desde PHP
    ControlErrores.abrirModalSeguro(modalDetalle);
}
