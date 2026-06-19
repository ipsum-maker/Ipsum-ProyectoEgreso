document.addEventListener("DOMContentLoaded", function () {
    // Claves usadas para leer y guardar datos en localStorage
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";

    // Elementos del encabezado
    const textoUsuarioEmail = document.getElementById("usuario-email");
    const botonCerrarSesion = document.getElementById("cerrar-sesion");

    // Botones de las tarjetas principales
    const botonRegistrosDiarios = document.getElementById("boton-registros-diarios");
    const botonRegistrosIncidencias = document.getElementById("boton-registros-incidencias");

    // Obtiene el usuario activo desde localStorage
    function obtenerUsuarioActivo() {
        const usuarioActivoGuardado = localStorage.getItem(STORAGE_KEY_USUARIO_ACTIVO);

        if (usuarioActivoGuardado === null) {
            return null;
        }

        return JSON.parse(usuarioActivoGuardado);
    }

    // Protege el panel para que solo entren coordinadores o administradores
    function protegerPanelCoordinacion() {
        const usuarioActivo = obtenerUsuarioActivo();

        if (usuarioActivo === null) {
            window.location.href = "login.html";
            return;
        }

        if (usuarioActivo.rol !== "coordinador" && usuarioActivo.rol !== "administrador") {
            alert("No tenés permisos para entrar al panel de coordinación.");
            window.location.href = "login.html";
            return;
        }

        textoUsuarioEmail.textContent = usuarioActivo.correo;
    }

    // Cierra sesión y vuelve al login
    function cerrarSesion(evento) {
        evento.preventDefault();
        localStorage.removeItem(STORAGE_KEY_USUARIO_ACTIVO);
        window.location.href = "login.html";
    }

    // Redirecciones a las páginas independientes de registros
    function irARegistrosDiarios() {
        window.location.href = "cordinacion-diarios.html";
    }

    function irARegistrosIncidencias() {
        window.location.href = "cordinacion-incidencias.html";
    }

    // Inicio del panel
    protegerPanelCoordinacion();

    // Eventos principales
    botonCerrarSesion.addEventListener("click", cerrarSesion);
    botonRegistrosDiarios.addEventListener("click", irARegistrosDiarios);
    botonRegistrosIncidencias.addEventListener("click", irARegistrosIncidencias);
});