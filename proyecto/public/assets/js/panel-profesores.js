// ARCHIVO JAVASCRIPT DESACTIVADO: todo el código se conserva como comentario.
// document.addEventListener("DOMContentLoaded", function () {
//     const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";
//
//     const textoUsuarioEmail = document.getElementById("usuario-email");
//     const botonCerrarSesion = document.getElementById("logout-button");
//
//     function formatearCedula(cedula) {
//         const numeros = String(cedula || "").replace(/\D/g, "");
//
//         if (numeros.length === 8) {
//             return `${numeros.slice(0, 1)}.${numeros.slice(1, 4)}.${numeros.slice(4, 7)}-${numeros.slice(7)}`;
//         }
//
//         if (numeros.length === 7) {
//             return `${numeros.slice(0, 3)}.${numeros.slice(3, 6)}-${numeros.slice(6)}`;
//         }
//
//         return cedula;
//     }
//
//     function obtenerUsuarioActivo() {
//     return ControlErrores.leerJsonSesion(STORAGE_KEY_USUARIO_ACTIVO, null);
// }
//
//     function protegerPanelProfesor() {
//         const usuarioActivo = obtenerUsuarioActivo();
//
//         if (usuarioActivo === null) {
//             window.location.href = "index.php?ruta=login";
//             return;
//         }
//
//         if (usuarioActivo.rol !== "profesor") {
//             alert("No tenés permisos para entrar al panel de profesores.");
//             window.location.href = "index.php?ruta=login";
//             return;
//         }
//
//         if (textoUsuarioEmail !== null) {
//             textoUsuarioEmail.textContent = formatearCedula(usuarioActivo.cedula);
//         }
//     }
//
//     function cerrarSesion(evento) {
//         evento.preventDefault();
//         sessionStorage.removeItem(STORAGE_KEY_USUARIO_ACTIVO);
//         window.location.href = "index.php?ruta=cerrar-sesion";
//     }
//
//     protegerPanelProfesor();
//
//    ControlErrores.agregarEventoSeguro(botonCerrarSesion, "click", cerrarSesion);
// });
