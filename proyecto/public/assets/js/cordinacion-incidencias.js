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

// Función global para visualizar el formulario completo de la incidencia
function verDetalleIncidencia(boton) {
    const modalDetalle = document.getElementById("modal-detalle-incidencia");
    const contenedorBody = document.getElementById("detalle-incidencia-contenido");
    if (!modalDetalle || !contenedorBody || !boton) return;

    const profesor = boton.getAttribute("data-profesor") || "";
    const asignatura = boton.getAttribute("data-asignatura") || "";
    const grupo = boton.getAttribute("data-grupo") || "";
    const turno = boton.getAttribute("data-turno") || "";
    const fecha = boton.getAttribute("data-fecha") || "";
    const hora = boton.getAttribute("data-hora") || "";
    const tipo = boton.getAttribute("data-tipo") || "";
    const taller = boton.getAttribute("data-taller") || "";
    const descripcion = boton.getAttribute("data-descripcion") || "";
    const estado = boton.getAttribute("data-estado") || "";
    const solucion = boton.getAttribute("data-solucion") || "";

    let colorEstado = estado === "resuelto" ? "#2e7d32" : (estado === "en_proceso" ? "#1565c0" : "#e65100");

    let html = `<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 1rem;">`;
    html += `<div><strong>Docente:</strong> ${profesor}</div>`;
    html += `<div><strong>Fecha y Hora:</strong> ${fecha} - ${hora}</div>`;
    html += `<div><strong>Asignatura:</strong> ${asignatura}</div>`;
    html += `<div><strong>Grupo:</strong> ${grupo}</div>`;
    html += `<div><strong>Turno:</strong> <span style="text-transform: capitalize;">${turno}</span></div>`;
    html += `<div><strong>Laboratorio / Taller:</strong> ${taller}</div>`;
    html += `<div style="grid-column: span 2;"><strong>Tipo de Solicitud:</strong> ${tipo}</div>`;
    html += `<div style="grid-column: span 2;"><strong>Estado Actual:</strong> <span style="font-weight: bold; color: ${colorEstado}; text-transform: capitalize;">${estado.replace('_', ' ')}</span></div>`;
    html += `</div>`;

    html += `<div style="margin-bottom: 1rem;">`;
    html += `<h4 style="margin: 0 0 0.4rem; color: #1e293b;">Descripción Detallada del Problema:</h4>`;
    html += `<p style="margin: 0; background: #fff; border: 1px solid #e2e8f0; padding: 0.75rem; border-radius: 6px; color: #334155; white-space: pre-line;">${descripcion}</p>`;
    html += `</div>`;

    if (solucion) {
        html += `<div style="background: #f1f8e9; border-left: 4px solid #4caf50; padding: 0.75rem; border-radius: 0 6px 6px 0;">`;
        html += `<h4 style="margin: 0 0 0.4rem; color: #2e7d32;">Solución Documentada por Coordinación:</h4>`;
        html += `<p style="margin: 0; color: #1b5e20; white-space: pre-line;">${solucion}</p>`;
        html += `</div>`;
    }

    contenedorBody.innerHTML = html;
    ControlErrores.abrirModalSeguro(modalDetalle);
}
