// ARCHIVO JAVASCRIPT DESACTIVADO: todo el código se conserva como comentario.
// document.addEventListener("DOMContentLoaded", function () {
//     // Clave usada para leer al usuario activo de la pestaña
//     const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";
//
//     // Elementos del encabezado
//     const textoUsuarioEmail = document.getElementById("usuario-email");
//     const botonCerrarSesion = document.getElementById("cerrar-sesion");
//
//     // Botones de las tarjetas principales
//     const botonRegistrosDiarios = document.getElementById("boton-registros-diarios");
//     const botonRegistrosIncidencias = document.getElementById("boton-registros-incidencias");
//
//     // Obtiene el usuario activo desde sessionStorage
//    function obtenerUsuarioActivo() {
//     return ControlErrores.leerJsonSesion(STORAGE_KEY_USUARIO_ACTIVO, null);
// }
//     // Protege el panel para que solo entren coordinadores o administradores
//     function protegerPanelCoordinacion() {
//         const usuarioActivo = obtenerUsuarioActivo();
//
//         if (usuarioActivo === null) {
//             window.location.href = "index.php?ruta=login";
//             return;
//         }
//
//         if (usuarioActivo.rol !== "coordinador" && usuarioActivo.rol !== "administrador") {
//             alert("No tenés permisos para entrar al panel de coordinación.");
//             window.location.href = "index.php?ruta=login";
//             return;
//         }
//
//         if (textoUsuarioEmail !== null) {
//     textoUsuarioEmail.textContent = formatearCedula(usuarioActivo.cedula);
// }
//     }
//
//     // Cierra sesión y vuelve al login
//     function cerrarSesion(evento) {
//         evento.preventDefault();
//         sessionStorage.removeItem(STORAGE_KEY_USUARIO_ACTIVO);
//         window.location.href = "index.php?ruta=cerrar-sesion";
//     }
//
//     // Redirecciones a las páginas independientes de registros
//     function irARegistrosDiarios() {
//         window.location.href = "index.php?ruta=cordinacion-diarios";
//     }
//
//     function irARegistrosIncidencias() {
//         window.location.href = "index.php?ruta=cordinacion-incidencias";
//     }
//
//     // Inicio del panel
//     protegerPanelCoordinacion();
//
//     // Eventos principales
//    ControlErrores.agregarEventoSeguro(botonCerrarSesion, "click", cerrarSesion);
// ControlErrores.agregarEventoSeguro(botonRegistrosDiarios, "click", irARegistrosDiarios);
// ControlErrores.agregarEventoSeguro(botonRegistrosIncidencias, "click", irARegistrosIncidencias);
// });
