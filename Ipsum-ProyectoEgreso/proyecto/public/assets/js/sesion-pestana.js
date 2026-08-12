// ARCHIVO JAVASCRIPT DESACTIVADO: todo el código se conserva como comentario.
// (function () {
//     const CLAVE_SESION_PESTANA = "ipsum-sesion-pestana";
//     const CLAVE_USUARIO_ACTIVO = "usuario-activo";
//     const urlActual = new URL(window.location.href);
//     const esInicioDeSesion = urlActual.searchParams.get("inicio") === "1";
//
//     // Evita que se vea una página protegida antes de comprobar la pestaña.
//     document.documentElement.style.visibility = "hidden";
//
//     if (esInicioDeSesion) {
//         sessionStorage.setItem(CLAVE_SESION_PESTANA, "activa");
//         urlActual.searchParams.delete("inicio");
//         window.history.replaceState(null, "", urlActual.pathname + urlActual.search + urlActual.hash);
//     }
//
//     if (sessionStorage.getItem(CLAVE_SESION_PESTANA) !== "activa") {
//         window.location.replace("index.php?ruta=cerrar-sesion");
//         return;
//     }
//
//     // La versión nueva usa sessionStorage para que el usuario desaparezca al cerrar la pestaña.
//     localStorage.removeItem(CLAVE_USUARIO_ACTIVO);
//
//     if (window.IPSUM_USUARIO !== undefined) {
//         sessionStorage.setItem(CLAVE_USUARIO_ACTIVO, JSON.stringify(window.IPSUM_USUARIO));
//     }
//
//     document.documentElement.style.visibility = "visible";
// })();
