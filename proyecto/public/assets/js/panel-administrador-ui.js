document.addEventListener("DOMContentLoaded", function () {
    const botonNuevoUsuario = document.getElementById("boton-nuevo-usuario");
    const modalNuevoUsuario = document.getElementById("modal-nuevo-usuario");
    const formNuevoUsuario = document.getElementById("form-nuevo-usuario");
    const botonCerrarModalUsuario = document.getElementById("cerrar-modal-usuario");
    const botonCancelarModalUsuario = document.getElementById("cancelar-modal-usuario");

    function abrirModalNuevoUsuario() {
        if (formNuevoUsuario) {
            formNuevoUsuario.reset();
        }
        if (modalNuevoUsuario) {
            if (typeof modalNuevoUsuario.showModal === "function") {
                modalNuevoUsuario.showModal();
            } else {
                modalNuevoUsuario.setAttribute("open", "");
            }
        }
    }

    function cerrarModalNuevoUsuario() {
        if (modalNuevoUsuario) {
            if (typeof modalNuevoUsuario.close === "function") {
                modalNuevoUsuario.close();
            } else {
                modalNuevoUsuario.removeAttribute("open");
            }
        }
    }

    if (botonNuevoUsuario) {
        botonNuevoUsuario.addEventListener("click", abrirModalNuevoUsuario);
    }
    if (botonCerrarModalUsuario) {
        botonCerrarModalUsuario.addEventListener("click", cerrarModalNuevoUsuario);
    }
    if (botonCancelarModalUsuario) {
        botonCancelarModalUsuario.addEventListener("click", cerrarModalNuevoUsuario);
    }

    const modalResuelto = document.getElementById("modal-detalle-resuelto");
    const btnCerrarResuelto = document.getElementById("cerrar-modal-resuelto");
    const btnBotonCerrarResuelto = document.getElementById("boton-cerrar-resuelto");

    function cerrarModalResuelto() {
        if (modalResuelto) {
            if (typeof modalResuelto.close === "function") {
                modalResuelto.close();
            } else {
                modalResuelto.removeAttribute("open");
            }
        }
    }

    if (btnCerrarResuelto) btnCerrarResuelto.addEventListener("click", cerrarModalResuelto);
    if (btnBotonCerrarResuelto) btnBotonCerrarResuelto.addEventListener("click", cerrarModalResuelto);
});

// Función global invocable al presionar Ver Solución en la tabla del Administrador
function verSolucionResuelta(boton) {
    const modalResuelto = document.getElementById("modal-detalle-resuelto");
    const contenedorBody = document.getElementById("detalle-resuelto-contenido");
    if (!modalResuelto || !contenedorBody || !boton) return;

    const profesor = boton.getAttribute("data-profesor") || "";
    const tipo = boton.getAttribute("data-tipo") || "";
    const taller = boton.getAttribute("data-taller") || "";
    const fecha = boton.getAttribute("data-fecha") || "";
    const descripcion = boton.getAttribute("data-descripcion") || "";
    const solucion = boton.getAttribute("data-solucion") || "";

    let html = `<p style="margin: 0 0 0.5rem;"><strong>Docente:</strong> ${profesor}</p>`;
    html += `<p style="margin: 0 0 0.5rem;"><strong>Fecha y Hora:</strong> ${fecha}</p>`;
    html += `<p style="margin: 0 0 0.5rem;"><strong>Tipo:</strong> ${tipo} | <strong>Taller:</strong> ${taller}</p>`;
    if (descripcion) {
        html += `<p style="margin: 0 0 1rem; color: #555;"><strong>Problema reportado:</strong> ${descripcion}</p>`;
    }
    html += `<div style="background: #f1f8e9; border-left: 4px solid #4caf50; padding: 0.75rem; border-radius: 0 4px 4px 0; margin-top: 0.5rem;">`;
    html += `<h4 style="margin: 0 0 0.4rem; color: #2e7d32;">Solución Documentada por Coordinación:</h4>`;
    html += `<p style="margin: 0; color: #1b5e20;">${solucion}</p>`;
    html += `</div>`;

    contenedorBody.innerHTML = html;

    if (typeof modalResuelto.showModal === "function") {
        modalResuelto.showModal();
    } else {
        modalResuelto.setAttribute("open", "");
    }
}

