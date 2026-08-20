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
});

