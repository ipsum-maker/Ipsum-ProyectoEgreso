document.addEventListener("DOMContentLoaded", function () {
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";

    const textoUsuarioEmail = document.getElementById("usuario-email");
    const botonCerrarSesion = document.getElementById("logout-button");

    function obtenerUsuarioActivo() {
        const usuarioActivo = localStorage.getItem(STORAGE_KEY_USUARIO_ACTIVO);

        if (usuarioActivo === null) {
            return null;
        }

        return JSON.parse(usuarioActivo);
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

    if (botonCerrarSesion !== null) {
        botonCerrarSesion.addEventListener("click", cerrarSesion);
    }
});