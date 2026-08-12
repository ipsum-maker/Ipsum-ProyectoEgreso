document.addEventListener("DOMContentLoaded", function () {
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";

    const textoUsuarioEmail = document.getElementById("usuario-email");
    const botonCerrarSesion = document.getElementById("logout-button");

    function obtenerUsuarioActivo() {
    return ControlErrores.leerJson(STORAGE_KEY_USUARIO_ACTIVO, null);
}

    function protegerPanelProfesor() {
        const usuarioActivo = obtenerUsuarioActivo();

        if (usuarioActivo === null) {
            window.location.href = "login.html";
            return;
        }

        if (usuarioActivo.rol !== "profesor") {
            alert("No tenés permisos para entrar al panel de profesores.");
            window.location.href = "login.html";
            return;
        }

        if (textoUsuarioEmail !== null) {
            textoUsuarioEmail.textContent = usuarioActivo.correo;
        }
    }

    function cerrarSesion(evento) {
        evento.preventDefault();
        localStorage.removeItem(STORAGE_KEY_USUARIO_ACTIVO);
        window.location.href = "login.html";
    }

    protegerPanelProfesor();

   ControlErrores.agregarEventoSeguro(botonCerrarSesion, "click", cerrarSesion);
});