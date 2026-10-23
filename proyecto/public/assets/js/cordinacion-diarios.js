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
function abrirDetalleDiario(boton) {
    const modalDetalle = document.getElementById("modal-detalle-diario");
    const contenedorBody = document.getElementById("detalle-diario-contenido");
    if (!modalDetalle || !contenedorBody || !boton) return;

    const docente = ControlErrores.escaparHtml(boton.getAttribute("data-docente") || "");
    const aula = ControlErrores.escaparHtml(boton.getAttribute("data-aula") || "");
    const fecha = ControlErrores.escaparHtml(boton.getAttribute("data-fecha") || "");
    const turno = ControlErrores.escaparHtml(boton.getAttribute("data-turno") || "");
    const entrada = ControlErrores.escaparHtml(boton.getAttribute("data-entrada") || "");
    const salida = ControlErrores.escaparHtml(boton.getAttribute("data-salida") || "");
    const grupo = ControlErrores.escaparHtml(boton.getAttribute("data-grupo") || "");
    const asignatura = ControlErrores.escaparHtml(boton.getAttribute("data-asignatura") || "");
    const equiposJson = boton.getAttribute("data-equipos") || "[]";

    let equipos = [];
    try { equipos = JSON.parse(equiposJson); } catch (e) { }

    let html = `<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; background: #f8fafc; padding: 1rem; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 1rem;">`;
    html += `<div><strong>Docente:</strong> ${docente}</div>`;
    html += `<div><strong>Fecha:</strong> ${fecha}</div>`;
    html += `<div><strong>Turno:</strong> <span style="text-transform: capitalize;">${turno}</span></div>`;
    html += `<div><strong>Horario:</strong> ${entrada} hs a ${salida} hs</div>`;
    html += `<div><strong>Grupo:</strong> ${grupo}</div>`;
    html += `<div><strong>Asignatura:</strong> ${asignatura}</div>`;
    html += `<div style="grid-column: span 2;"><strong>Aula / Taller / Laboratorio:</strong> ${aula}</div>`;
    html += `</div>`;

    html += `<h4 style="margin: 1rem 0 0.5rem; font-size: 1rem; color: #1e293b;">Asignación de Equipos y Alumnos</h4>`;

    if (equipos.length === 0) {
        html += `<p style="color: #64748b; font-style: italic;">No se registraron equipos en este reporte.</p>`;
    } else {
        html += `<table style="width: 100%; border-collapse: collapse; margin-top: 0.5rem; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #f1f5f9; text-align: left;">
                            <th style="padding: 8px; border: 1px solid #cbd5e1;">Equipo (PC)</th>
                            <th style="padding: 8px; border: 1px solid #cbd5e1;">Alumno Asignado</th>
                        </tr>
                    </thead>
                    <tbody>`;
        equipos.forEach(eq => {
            html += `<tr>
                        <td style="padding: 8px; border: 1px solid #cbd5e1; font-weight: bold; color: #0f172a;">${ControlErrores.escaparHtml(eq.pc)}</td>
                        <td style="padding: 8px; border: 1px solid #cbd5e1;">${ControlErrores.escaparHtml(eq.alumno)}</td>
                     </tr>`;
        });
        html += `</tbody></table>`;
    }

    contenedorBody.innerHTML = html;
    ControlErrores.abrirModalSeguro(modalDetalle);
}

