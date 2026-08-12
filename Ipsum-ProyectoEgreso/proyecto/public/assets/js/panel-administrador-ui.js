// ARCHIVO JAVASCRIPT DESACTIVADO: todo el código se conserva como comentario.
// document.addEventListener("DOMContentLoaded", function () {
//     const STORAGE_KEYS_REPORTES = ["reportes-sistema", "reportes"];
//
//     const botonNuevoUsuario = document.getElementById("boton-nuevo-usuario");
//     const modalNuevoUsuario = document.getElementById("modal-nuevo-usuario");
//     const formNuevoUsuario = document.getElementById("form-nuevo-usuario");
//     const botonCerrarModalUsuario = document.getElementById("cerrar-modal-usuario");
//     const botonCancelarModalUsuario = document.getElementById("cancelar-modal-usuario");
//
//     const tablaReportesResueltos = document.querySelector("#tabla-reportes-resueltos .admin-users__tbody");
//     const mensajeReportesResueltos = document.getElementById("mensaje-reportes-resueltos");
//     const modalDetalleResuelto = document.getElementById("modal-detalle-resuelto");
//     const detalleResueltoContenido = document.getElementById("detalle-resuelto-contenido");
//     const botonCerrarModalResuelto = document.getElementById("cerrar-modal-resuelto");
//     const botonCerrarResuelto = document.getElementById("boton-cerrar-resuelto");
//
//     function abrirModalNuevoUsuario() {
//         formNuevoUsuario.reset();
//         ControlErrores.abrirModalSeguro(modalNuevoUsuario);
//     }
//
//     function cerrarModalNuevoUsuario() {
//         ControlErrores.cerrarModalSeguro(modalNuevoUsuario);
//     }
//
//     function obtenerReportes() {
//         for (let i = 0; i < STORAGE_KEYS_REPORTES.length; i++) {
//             const reportes = ControlErrores.leerJson(STORAGE_KEYS_REPORTES[i], null);
//
//             if (Array.isArray(reportes)) {
//                 return reportes;
//             }
//         }
//
//         return [];
//     }
//
//     function obtenerValor(reporte, claves, valorPorDefecto) {
//         for (let i = 0; i < claves.length; i++) {
//             if (reporte[claves[i]] !== undefined && reporte[claves[i]] !== null) {
//                 return reporte[claves[i]];
//             }
//         }
//
//         return valorPorDefecto;
//     }
//
//     function formatearTipoSolicitud(reporte) {
//         const tipoSolicitud = obtenerValor(reporte, ["tipoSolicitud"], "");
//         const otroTipo = obtenerValor(reporte, ["otroTipo"], "");
//
//         if (tipoSolicitud === "rotura-equipo") {
//             return "Rotura de equipo";
//         }
//
//         if (tipoSolicitud === "falla-equipo") {
//             return "Falla de equipo";
//         }
//
//         return otroTipo !== "" ? otroTipo : "Otro";
//     }
//
//     function crearCelda(texto) {
//         const celda = document.createElement("td");
//         celda.className = "admin-users__cell";
//         celda.textContent = texto;
//         return celda;
//     }
//
//     function crearParrafoDetalle(etiqueta, valor) {
//         const parrafo = document.createElement("p");
//         parrafo.textContent = `${etiqueta}: ${valor}`;
//         return parrafo;
//     }
//
//     function cerrarDetalleResuelto() {
//         ControlErrores.cerrarModalSeguro(modalDetalleResuelto);
//     }
//
//     function abrirDetalleResuelto(reporte) {
//         detalleResueltoContenido.innerHTML = "";
//
//         const nombre = obtenerValor(reporte, ["nombreProfesor", "nombre"], "");
//         const apellido = obtenerValor(reporte, ["apellidoProfesor", "apellido"], "");
//         const fecha = obtenerValor(reporte, ["fecha"], "");
//         const taller = obtenerValor(reporte, ["taller", "aula"], "");
//         const descripcion = obtenerValor(reporte, ["descripcion"], "");
//         const solucion = obtenerValor(reporte, ["solucion"], "");
//         const tecnico = obtenerValor(reporte, ["tecnico"], "Coordinación");
//
//         detalleResueltoContenido.appendChild(crearParrafoDetalle("Fecha", fecha));
//         detalleResueltoContenido.appendChild(crearParrafoDetalle("Docente", `${nombre} ${apellido}`));
//         detalleResueltoContenido.appendChild(crearParrafoDetalle("Tipo", formatearTipoSolicitud(reporte)));
//         detalleResueltoContenido.appendChild(crearParrafoDetalle("Taller / Aula", taller));
//         detalleResueltoContenido.appendChild(crearParrafoDetalle("Descripción", descripcion));
//         detalleResueltoContenido.appendChild(crearParrafoDetalle("Solución aplicada", solucion));
//         detalleResueltoContenido.appendChild(crearParrafoDetalle("Técnico", tecnico));
//
//         ControlErrores.abrirModalSeguro(modalDetalleResuelto);
//     }
//
//     function mostrarReportesResueltos() {
//         const reportesResueltos = obtenerReportes().filter(function (reporte) {
//             return reporte.tipo === "incidencia" && reporte.estado === "resuelto";
//         });
//
//         tablaReportesResueltos.innerHTML = "";
//
//         if (reportesResueltos.length === 0) {
//             mensajeReportesResueltos.style.display = "block";
//             return;
//         }
//
//         mensajeReportesResueltos.style.display = "none";
//
//         reportesResueltos.forEach(function (reporte) {
//             const fila = document.createElement("tr");
//             fila.className = "admin-users__row";
//
//             const nombre = obtenerValor(reporte, ["nombreProfesor", "nombre"], "");
//             const apellido = obtenerValor(reporte, ["apellidoProfesor", "apellido"], "");
//             const fecha = obtenerValor(reporte, ["fecha"], "");
//             const taller = obtenerValor(reporte, ["taller", "aula"], "");
//             const tecnico = obtenerValor(reporte, ["tecnico"], "Coordinación");
//
//             fila.appendChild(crearCelda(fecha));
//             fila.appendChild(crearCelda(`${nombre} ${apellido}`));
//             fila.appendChild(crearCelda(formatearTipoSolicitud(reporte)));
//             fila.appendChild(crearCelda(taller));
//             fila.appendChild(crearCelda(tecnico));
//
//             const celdaVer = document.createElement("td");
//             celdaVer.className = "admin-users__cell";
//             const botonVer = document.createElement("button");
//             botonVer.className = "admin-users__action-button";
//             botonVer.type = "button";
//             botonVer.textContent = "Ver";
//             botonVer.addEventListener("click", function () {
//                 abrirDetalleResuelto(reporte);
//             });
//             celdaVer.appendChild(botonVer);
//             fila.appendChild(celdaVer);
//
//             tablaReportesResueltos.appendChild(fila);
//         });
//     }
//
//     ControlErrores.agregarEventoSeguro(botonNuevoUsuario, "click", abrirModalNuevoUsuario);
//     ControlErrores.agregarEventoSeguro(botonCerrarModalUsuario, "click", cerrarModalNuevoUsuario);
//     ControlErrores.agregarEventoSeguro(botonCancelarModalUsuario, "click", cerrarModalNuevoUsuario);
//     ControlErrores.agregarEventoSeguro(botonCerrarModalResuelto, "click", cerrarDetalleResuelto);
//     ControlErrores.agregarEventoSeguro(botonCerrarResuelto, "click", cerrarDetalleResuelto);
//
//     mostrarReportesResueltos();
// });
