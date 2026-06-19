document.addEventListener("DOMContentLoaded", function () {
    // Claves usadas para leer y guardar datos en localStorage
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";
    const STORAGE_KEYS_REPORTES = ["reportes-sistema", "reportes"];

    // Elementos del encabezado
    const textoUsuarioEmail = document.getElementById("usuario-email");
    const botonCerrarSesion = document.getElementById("cerrar-sesion");

    // Contenedor principal del panel
    const panelCordinacionMain = document.getElementById("panel-cordinacion-main");

    // Tarjetas originales del panel
    const tarjetaRegistrosDiarios = document.getElementById("registros-diarios");
    const tarjetaRegistrosIncidencias = document.getElementById("registros-incidencias");

    // Botones de las tarjetas originales
    const botonRegistrosDiarios = document.getElementById("boton-registros-diarios");
    const botonRegistrosIncidencias = document.getElementById("boton-registros-incidencias");

    // Vistas internas agregadas dentro del mismo HTML
    const vistaRegistrosDiarios = document.getElementById("vista-registros-diarios");
    const vistaRegistrosIncidencias = document.getElementById("vista-registros-incidencias");

    // Botones para volver desde las vistas internas al menú principal
    const botonVolverDiarios = document.getElementById("boton-volver-diarios");
    const botonVolverIncidencias = document.getElementById("boton-volver-incidencias");

    // Elementos de la tabla de registros diarios
    const buscadorRegistrosDiarios = document.getElementById("buscador-registros-diarios");
    const tablaRegistrosDiarios = document.getElementById("tabla-registros-diarios");
    const cantidadRegistrosDiarios = document.getElementById("cantidad-registros-diarios");
    const mensajeRegistrosDiarios = document.getElementById("mensaje-registros-diarios");

    // Elementos de la tabla de incidencias
    const buscadorRegistrosIncidencias = document.getElementById("buscador-registros-incidencias");
    const filtroEstadoIncidencias = document.getElementById("filtro-estado-incidencias");
    const tablaRegistrosIncidencias = document.getElementById("tabla-registros-incidencias");
    const cantidadRegistrosIncidencias = document.getElementById("cantidad-registros-incidencias");
    const mensajeRegistrosIncidencias = document.getElementById("mensaje-registros-incidencias");

    // Modal de detalle diario
    const modalDetalleDiario = document.getElementById("modal-detalle-diario");
    const detalleDiarioContenido = document.getElementById("detalle-diario-contenido");
    const botonCerrarModalDiario = document.getElementById("cerrar-modal-diario");
    const botonCerrarDetalleDiario = document.getElementById("boton-cerrar-detalle-diario");

    // Modal de detalle de incidencia
    const modalDetalleIncidencia = document.getElementById("modal-detalle-incidencia");
    const detalleIncidenciaContenido = document.getElementById("detalle-incidencia-contenido");
    const botonCerrarModalIncidencia = document.getElementById("cerrar-modal-incidencia");
    const botonCerrarDetalleIncidencia = document.getElementById("boton-cerrar-detalle-incidencia");
    const botonMarcarProceso = document.getElementById("boton-marcar-proceso");
    const botonAbrirSolucion = document.getElementById("boton-abrir-solucion");

    // Modal para documentar solución
    const modalSolucionIncidencia = document.getElementById("modal-solucion-incidencia");
    const formSolucionIncidencia = document.getElementById("form-solucion-incidencia");
    const textoSolucionIncidencia = document.getElementById("texto-solucion-incidencia");
    const botonCerrarSolucion = document.getElementById("cerrar-modal-solucion");
    const botonCancelarSolucion = document.getElementById("boton-cancelar-solucion");

    // Guarda temporalmente la incidencia seleccionada
    let incidenciaSeleccionadaId = null;

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

    // Obtiene todos los reportes guardados
    function obtenerReportes() {
        for (let i = 0; i < STORAGE_KEYS_REPORTES.length; i++) {
            const reportesGuardados = localStorage.getItem(STORAGE_KEYS_REPORTES[i]);

            if (reportesGuardados !== null) {
                const reportes = JSON.parse(reportesGuardados);

                if (Array.isArray(reportes)) {
                    return reportes;
                }
            }
        }

        return [];
    }

    // Guarda todos los reportes en las dos claves para mantener compatibilidad
    function guardarReportes(reportes) {
        STORAGE_KEYS_REPORTES.forEach(function (clave) {
            localStorage.setItem(clave, JSON.stringify(reportes));
        });
    }

    // Cierra sesión y vuelve al login
    function cerrarSesion(evento) {
        evento.preventDefault();
        localStorage.removeItem(STORAGE_KEY_USUARIO_ACTIVO);
        window.location.href = "login.html";
    }

    // Muestra las tarjetas originales del panel y oculta las vistas internas
    function mostrarMenuPrincipal() {
        tarjetaRegistrosDiarios.style.display = "";
        tarjetaRegistrosIncidencias.style.display = "";
        panelCordinacionMain.classList.remove("panel-cordinacion-main--records");

        vistaRegistrosDiarios.classList.add("panel-cordinacion-records--hidden");
        vistaRegistrosIncidencias.classList.add("panel-cordinacion-records--hidden");
    }

    // Oculta las tarjetas originales del panel
    function ocultarMenuPrincipal() {
        tarjetaRegistrosDiarios.style.display = "none";
        tarjetaRegistrosIncidencias.style.display = "none";
        panelCordinacionMain.classList.add("panel-cordinacion-main--records");
    }

    // Muestra la tabla de registros diarios
    function mostrarVistaDiarios() {
        ocultarMenuPrincipal();
        vistaRegistrosIncidencias.classList.add("panel-cordinacion-records--hidden");
        vistaRegistrosDiarios.classList.remove("panel-cordinacion-records--hidden");
        mostrarRegistrosDiarios();
    }

    // Muestra la tabla de registros de incidencias
    function mostrarVistaIncidencias() {
        ocultarMenuPrincipal();
        vistaRegistrosDiarios.classList.add("panel-cordinacion-records--hidden");
        vistaRegistrosIncidencias.classList.remove("panel-cordinacion-records--hidden");
        mostrarRegistrosIncidencias();
    }

    // Normaliza texto para buscar sin importar mayúsculas o espacios
    function normalizarTexto(texto) {
        return String(texto || "").toLowerCase().trim();
    }

    // Obtiene un valor admitiendo distintos nombres posibles
    function obtenerValor(reporte, claves, valorPorDefecto) {
        for (let i = 0; i < claves.length; i++) {
            if (reporte[claves[i]] !== undefined && reporte[claves[i]] !== null) {
                return reporte[claves[i]];
            }
        }

        return valorPorDefecto;
    }

    // Crea una celda de tabla
    function crearCelda(texto) {
        const celda = document.createElement("td");
        celda.className = "panel-cordinacion-table__cell";
        celda.textContent = texto;

        return celda;
    }

    // Crea un botón de tabla
    function crearBotonTabla(texto, funcionClick) {
        const boton = document.createElement("button");
        boton.className = "panel-cordinacion-table__button";
        boton.type = "button";
        boton.textContent = texto;
        boton.addEventListener("click", funcionClick);

        return boton;
    }

    // Obtiene solamente reportes diarios
    function obtenerRegistrosDiarios() {
        const reportes = obtenerReportes();

        return reportes.filter(function (reporte) {
            return reporte.tipo === "diario";
        });
    }

    // Filtra reportes diarios por docente, aula o fecha
    function filtrarRegistrosDiarios(registros) {
        const textoBuscado = normalizarTexto(buscadorRegistrosDiarios.value);

        if (textoBuscado === "") {
            return registros;
        }

        return registros.filter(function (registro) {
            const nombre = obtenerValor(registro, ["nombreProfesor", "nombre"], "");
            const apellido = obtenerValor(registro, ["apellidoProfesor", "apellido"], "");
            const aula = obtenerValor(registro, ["aula", "taller"], "");
            const fecha = obtenerValor(registro, ["fecha"], "");
            const docente = `${nombre} ${apellido}`;

            return normalizarTexto(docente).includes(textoBuscado)
                || normalizarTexto(aula).includes(textoBuscado)
                || normalizarTexto(fecha).includes(textoBuscado);
        });
    }

    // Crea una fila para un registro diario
    function crearFilaDiario(registro) {
        const fila = document.createElement("tr");
        fila.className = "panel-cordinacion-table__row";

        const nombre = obtenerValor(registro, ["nombreProfesor", "nombre"], "");
        const apellido = obtenerValor(registro, ["apellidoProfesor", "apellido"], "");
        const aula = obtenerValor(registro, ["aula", "taller"], "");
        const fecha = obtenerValor(registro, ["fecha"], "");
        const asignaciones = obtenerValor(registro, ["asignaciones"], []);
        const docente = `${nombre} ${apellido}`;

        fila.appendChild(crearCelda(fecha));
        fila.appendChild(crearCelda(docente));
        fila.appendChild(crearCelda(aula));
        fila.appendChild(crearCelda(`${asignaciones.length} equipo(s)`));

        const celdaVer = document.createElement("td");
        celdaVer.className = "panel-cordinacion-table__cell";
        celdaVer.appendChild(crearBotonTabla("Ver", function () {
            abrirDetalleDiario(registro);
        }));

        fila.appendChild(celdaVer);

        return fila;
    }

    // Muestra los registros diarios en la tabla
    function mostrarRegistrosDiarios() {
        const registros = filtrarRegistrosDiarios(obtenerRegistrosDiarios());

        tablaRegistrosDiarios.innerHTML = "";
        cantidadRegistrosDiarios.textContent = `${registros.length} registro(s) encontrado(s)`;

        if (registros.length === 0) {
            mensajeRegistrosDiarios.style.display = "block";
            return;
        }

        mensajeRegistrosDiarios.style.display = "none";

        registros.forEach(function (registro) {
            tablaRegistrosDiarios.appendChild(crearFilaDiario(registro));
        });
    }

    // Crea un párrafo para mostrar detalles en los modales
    function crearParrafoDetalle(etiqueta, valor) {
        const parrafo = document.createElement("p");
        parrafo.textContent = `${etiqueta}: ${valor}`;

        return parrafo;
    }

    // Abre el modal de detalle diario
    function abrirDetalleDiario(registro) {
        detalleDiarioContenido.innerHTML = "";

        const nombre = obtenerValor(registro, ["nombreProfesor", "nombre"], "");
        const apellido = obtenerValor(registro, ["apellidoProfesor", "apellido"], "");
        const fecha = obtenerValor(registro, ["fecha"], "");
        const hora = obtenerValor(registro, ["hora"], "");
        const correo = obtenerValor(registro, ["profesorCorreo", "correo"], "");
        const aula = obtenerValor(registro, ["aula", "taller"], "");
        const asignaciones = obtenerValor(registro, ["asignaciones"], []);

        detalleDiarioContenido.appendChild(crearParrafoDetalle("Fecha", fecha));
        detalleDiarioContenido.appendChild(crearParrafoDetalle("Hora", hora));
        detalleDiarioContenido.appendChild(crearParrafoDetalle("Docente", `${nombre} ${apellido}`));
        detalleDiarioContenido.appendChild(crearParrafoDetalle("Correo", correo));
        detalleDiarioContenido.appendChild(crearParrafoDetalle("Aula / Taller", aula));

        const tituloEquipos = document.createElement("h3");
        tituloEquipos.textContent = "Equipos registrados";
        detalleDiarioContenido.appendChild(tituloEquipos);

        const listaEquipos = document.createElement("ul");

        asignaciones.forEach(function (asignacion) {
            const item = document.createElement("li");
            item.textContent = `${asignacion.pc}: ${asignacion.alumno}`;
            listaEquipos.appendChild(item);
        });

        detalleDiarioContenido.appendChild(listaEquipos);
        modalDetalleDiario.showModal();
    }

    // Cierra el modal de detalle diario
    function cerrarDetalleDiario() {
        modalDetalleDiario.close();
    }

    // Obtiene solamente reportes de incidencia
    function obtenerIncidencias() {
        const reportes = obtenerReportes();

        return reportes.filter(function (reporte) {
            return reporte.tipo === "incidencia";
        });
    }

    // Calcula prioridad según el tipo de solicitud
    function obtenerPrioridad(incidencia) {
        const tipoSolicitud = obtenerValor(incidencia, ["tipoSolicitud"], "");

        if (tipoSolicitud === "rotura-equipo") {
            return "alta";
        }

        if (tipoSolicitud === "falla-equipo") {
            return "media";
        }

        return "baja";
    }

    // Convierte prioridad interna a texto visible
    function formatearPrioridad(prioridad) {
        if (prioridad === "alta") {
            return "Alta";
        }

        if (prioridad === "media") {
            return "Media";
        }

        return "Baja";
    }

    // Convierte estado interno a texto visible
    function formatearEstado(estado) {
        if (estado === "reenviado") {
            return "Reenviado";
        }

        if (estado === "en-proceso") {
            return "En proceso";
        }

        if (estado === "resuelto") {
            return "Resuelto";
        }

        return "Pendiente";
    }

    // Convierte el tipo de solicitud a texto visible
    function formatearTipoSolicitud(incidencia) {
        const tipoSolicitud = obtenerValor(incidencia, ["tipoSolicitud"], "");
        const otroTipo = obtenerValor(incidencia, ["otroTipo"], "");

        if (tipoSolicitud === "rotura-equipo") {
            return "Rotura de equipo";
        }

        if (tipoSolicitud === "falla-equipo") {
            return "Falla de equipo";
        }

        if (otroTipo !== "") {
            return otroTipo;
        }

        return "Otro";
    }

    // Crea una etiqueta visual para estado o prioridad
    function crearBadge(texto, modificador) {
        const badge = document.createElement("span");
        badge.className = `panel-cordinacion-badge panel-cordinacion-badge--${modificador}`;
        badge.textContent = texto;

        return badge;
    }

    // Filtra incidencias por buscador y estado
    function filtrarIncidencias(incidencias) {
        const textoBuscado = normalizarTexto(buscadorRegistrosIncidencias.value);
        const estadoFiltrado = filtroEstadoIncidencias.value;

        return incidencias.filter(function (incidencia) {
            const nombre = obtenerValor(incidencia, ["nombreProfesor", "nombre"], "");
            const apellido = obtenerValor(incidencia, ["apellidoProfesor", "apellido"], "");
            const taller = obtenerValor(incidencia, ["taller", "aula"], "");
            const fecha = obtenerValor(incidencia, ["fecha"], "");
            const estado = obtenerValor(incidencia, ["estado"], "pendiente");
            const docente = `${nombre} ${apellido}`;
            const tipo = formatearTipoSolicitud(incidencia);

            const coincideTexto = textoBuscado === ""
                || normalizarTexto(docente).includes(textoBuscado)
                || normalizarTexto(tipo).includes(textoBuscado)
                || normalizarTexto(taller).includes(textoBuscado)
                || normalizarTexto(fecha).includes(textoBuscado);

            const coincideEstado = estadoFiltrado === "todos" || estado === estadoFiltrado;

            return coincideTexto && coincideEstado;
        });
    }

    // Ordena incidencias por prioridad y deja las resueltas al final
    function ordenarIncidencias(incidencias) {
        const valorPrioridad = {
            alta: 1,
            media: 2,
            baja: 3
        };

        return incidencias.sort(function (incidenciaA, incidenciaB) {
            const estadoA = obtenerValor(incidenciaA, ["estado"], "pendiente");
            const estadoB = obtenerValor(incidenciaB, ["estado"], "pendiente");

            if (estadoA === "resuelto" && estadoB !== "resuelto") {
                return 1;
            }

            if (estadoA !== "resuelto" && estadoB === "resuelto") {
                return -1;
            }

            return valorPrioridad[obtenerPrioridad(incidenciaA)] - valorPrioridad[obtenerPrioridad(incidenciaB)];
        });
    }

  // Crea el selector de estado dentro de la tabla de incidencias
function crearSelectorEstado(incidencia) {
    const estadoActual = obtenerValor(incidencia, ["estado"], "pendiente");

    const selector = document.createElement("select");
    selector.className = "panel-cordinacion-table__select";
    selector.value = estadoActual;

    const opcionPendiente = document.createElement("option");
    opcionPendiente.value = "pendiente";
    opcionPendiente.textContent = "Pendiente";

    const opcionEnProceso = document.createElement("option");
    opcionEnProceso.value = "en-proceso";
    opcionEnProceso.textContent = "En proceso";

    const opcionResuelto = document.createElement("option");
    opcionResuelto.value = "resuelto";
    opcionResuelto.textContent = "Resuelto...";

    selector.appendChild(opcionPendiente);
    selector.appendChild(opcionEnProceso);
    selector.appendChild(opcionResuelto);

    selector.addEventListener("change", function () {
        incidenciaSeleccionadaId = incidencia.id;

        if (selector.value === "resuelto") {
            selector.value = estadoActual;
            abrirModalSolucion();
            return;
        }

        cambiarEstadoIncidencia(selector.value);
        mostrarRegistrosIncidencias();
    });

    return selector;
}

// Crea una fila para una incidencia
function crearFilaIncidencia(incidencia) {
    const fila = document.createElement("tr");
    fila.className = "panel-cordinacion-table__row";

    const nombre = obtenerValor(incidencia, ["nombreProfesor", "nombre"], "");
    const apellido = obtenerValor(incidencia, ["apellidoProfesor", "apellido"], "");
    const fecha = obtenerValor(incidencia, ["fecha"], "");
    const taller = obtenerValor(incidencia, ["taller", "aula"], "");
    const prioridad = obtenerPrioridad(incidencia);
    const docente = `${nombre} ${apellido}`;

    const celdaPrioridad = document.createElement("td");
    celdaPrioridad.className = "panel-cordinacion-table__cell";
    celdaPrioridad.appendChild(crearBadge(formatearPrioridad(prioridad), prioridad));

    const celdaEstado = document.createElement("td");
    celdaEstado.className = "panel-cordinacion-table__cell";
    celdaEstado.appendChild(crearSelectorEstado(incidencia));

    const celdaVer = document.createElement("td");
    celdaVer.className = "panel-cordinacion-table__cell";
    celdaVer.appendChild(crearBotonTabla("Ver", function () {
        abrirDetalleIncidencia(incidencia);
    }));

    fila.appendChild(celdaPrioridad);
    fila.appendChild(crearCelda(fecha));
    fila.appendChild(crearCelda(docente));
    fila.appendChild(crearCelda(formatearTipoSolicitud(incidencia)));
    fila.appendChild(crearCelda(taller));
    fila.appendChild(celdaEstado);
    fila.appendChild(celdaVer);

    return fila;
}

    // Muestra las incidencias en la tabla
    function mostrarRegistrosIncidencias() {
        const incidencias = ordenarIncidencias(filtrarIncidencias(obtenerIncidencias()));

        tablaRegistrosIncidencias.innerHTML = "";
        cantidadRegistrosIncidencias.textContent = `${incidencias.length} incidencia(s) ordenadas por prioridad`;

        if (incidencias.length === 0) {
            mensajeRegistrosIncidencias.style.display = "block";
            return;
        }

        mensajeRegistrosIncidencias.style.display = "none";

        incidencias.forEach(function (incidencia) {
            tablaRegistrosIncidencias.appendChild(crearFilaIncidencia(incidencia));
        });
    }

    // Abre el modal de detalle de incidencia
    function abrirDetalleIncidencia(incidencia) {
        incidenciaSeleccionadaId = incidencia.id;
        detalleIncidenciaContenido.innerHTML = "";

        const nombre = obtenerValor(incidencia, ["nombreProfesor", "nombre"], "");
        const apellido = obtenerValor(incidencia, ["apellidoProfesor", "apellido"], "");
        const fecha = obtenerValor(incidencia, ["fecha"], "");
        const hora = obtenerValor(incidencia, ["hora"], "");
        const correo = obtenerValor(incidencia, ["profesorCorreo", "correo"], "");
        const asignatura = obtenerValor(incidencia, ["asignatura"], "");
        const taller = obtenerValor(incidencia, ["taller", "aula"], "");
        const estado = obtenerValor(incidencia, ["estado"], "pendiente");
        const descripcion = obtenerValor(incidencia, ["descripcion"], "");
        const solucion = obtenerValor(incidencia, ["solucion"], "");

        detalleIncidenciaContenido.appendChild(crearParrafoDetalle("Fecha", fecha));
        detalleIncidenciaContenido.appendChild(crearParrafoDetalle("Hora", hora));
        detalleIncidenciaContenido.appendChild(crearParrafoDetalle("Docente", `${nombre} ${apellido}`));
        detalleIncidenciaContenido.appendChild(crearParrafoDetalle("Correo", correo));
        detalleIncidenciaContenido.appendChild(crearParrafoDetalle("Asignatura", asignatura));
        detalleIncidenciaContenido.appendChild(crearParrafoDetalle("Tipo", formatearTipoSolicitud(incidencia)));
        detalleIncidenciaContenido.appendChild(crearParrafoDetalle("Taller / Aula", taller));
        detalleIncidenciaContenido.appendChild(crearParrafoDetalle("Estado", formatearEstado(estado)));
        detalleIncidenciaContenido.appendChild(crearParrafoDetalle("Descripción", descripcion));

        if (solucion !== "") {
            detalleIncidenciaContenido.appendChild(crearParrafoDetalle("Solución aplicada", solucion));
        }

        // Como el estado ahora se cambia desde la tabla, estos botones quedan ocultos
botonMarcarProceso.style.display = "none";
botonAbrirSolucion.style.display = "none";

        modalDetalleIncidencia.showModal();
    }

    // Cierra el modal de detalle de incidencia
    function cerrarDetalleIncidencia() {
        modalDetalleIncidencia.close();
    }

    // Cambia el estado de una incidencia
    function cambiarEstadoIncidencia(nuevoEstado, solucion) {
        const usuarioActivo = obtenerUsuarioActivo();
        const reportes = obtenerReportes();

        const reportesActualizados = reportes.map(function (reporte) {
            if (reporte.id === incidenciaSeleccionadaId) {
                reporte.estado = nuevoEstado;

                if (solucion !== undefined) {
                    reporte.solucion = solucion;
                    reporte.fechaResolucion = new Date().toISOString();
                    reporte.tecnico = usuarioActivo.correo;
                }
            }

            return reporte;
        });

        guardarReportes(reportesActualizados);
        mostrarRegistrosIncidencias();
    }

    // Marca una incidencia como en proceso
    function marcarEnProceso() {
        cambiarEstadoIncidencia("en-proceso");
        modalDetalleIncidencia.close();
        alert("La incidencia fue marcada como en proceso.");
    }

    // Abre el modal para documentar solución
    function abrirModalSolucion() {
        textoSolucionIncidencia.value = "";
        modalDetalleIncidencia.close();
        modalSolucionIncidencia.showModal();
    }

    // Cierra el modal de solución
    function cerrarModalSolucion() {
        modalSolucionIncidencia.close();
    }

    // Guarda la solución y marca la incidencia como resuelta
function guardarSolucion(evento) {
    evento.preventDefault();

    const solucionIngresada = textoSolucionIncidencia.value.trim();

    if (solucionIngresada === "") {
        alert("Debe escribir la solución aplicada.");
        return;
    }

    cambiarEstadoIncidencia("resuelto", solucionIngresada);
    modalSolucionIncidencia.close();
    mostrarRegistrosIncidencias();
    alert("Incidencia marcada como resuelta.");
}

    // Inicio del panel
    protegerPanelCoordinacion();
    mostrarMenuPrincipal();

    // Eventos principales
    botonCerrarSesion.addEventListener("click", cerrarSesion);
    botonRegistrosDiarios.addEventListener("click", mostrarVistaDiarios);
    botonRegistrosIncidencias.addEventListener("click", mostrarVistaIncidencias);
    botonVolverDiarios.addEventListener("click", mostrarMenuPrincipal);
    botonVolverIncidencias.addEventListener("click", mostrarMenuPrincipal);

    // Eventos de búsqueda y filtro
    buscadorRegistrosDiarios.addEventListener("input", mostrarRegistrosDiarios);
    buscadorRegistrosIncidencias.addEventListener("input", mostrarRegistrosIncidencias);
    filtroEstadoIncidencias.addEventListener("change", mostrarRegistrosIncidencias);

    // Eventos de modales diarios
    botonCerrarModalDiario.addEventListener("click", cerrarDetalleDiario);
    botonCerrarDetalleDiario.addEventListener("click", cerrarDetalleDiario);

    // Eventos de modales de incidencias
    botonCerrarModalIncidencia.addEventListener("click", cerrarDetalleIncidencia);
    botonCerrarDetalleIncidencia.addEventListener("click", cerrarDetalleIncidencia);
    botonMarcarProceso.addEventListener("click", marcarEnProceso);
    botonAbrirSolucion.addEventListener("click", abrirModalSolucion);

    // Eventos del modal de solución
    botonCerrarSolucion.addEventListener("click", cerrarModalSolucion);
    botonCancelarSolucion.addEventListener("click", cerrarModalSolucion);
    formSolucionIncidencia.addEventListener("submit", guardarSolucion);
});