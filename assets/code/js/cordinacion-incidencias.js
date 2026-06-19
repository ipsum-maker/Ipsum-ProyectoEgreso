document.addEventListener("DOMContentLoaded", function () {
    // Claves del almacenamiento local para credenciales y reportes
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";
    const STORAGE_KEYS_REPORTES = ["reportes-sistema", "reportes"];

    // Referencias principales de la interfaz
    const buscadorRegistrosIncidencias = document.getElementById("buscador-registros-incidencias");
    const filtroEstadoIncidencias = document.getElementById("filtro-estado-incidencias");
    const tablaRegistrosIncidencias = document.getElementById("tabla-registros-incidencias");
    const cantidadRegistrosIncidencias = document.getElementById("cantidad-registros-incidencias");
    const mensajeVacioIncidencias = document.getElementById("mensaje-vacio-incidencias");
    const tablaContenedor = document.getElementById("tabla-contenedor");

    // Diálogos modales y sus componentes
    const modalDetalleIncidencia = document.getElementById("modal-detalle-incidencia");
    const detalleIncidenciaContenido = document.getElementById("detalle-incidencia-contenido");
    const botonCerrarModalIncidencia = document.getElementById("cerrar-modal-incidencia");
    const botonCerrarDetalleIncidencia = document.getElementById("boton-cerrar-detalle-incidencia");

    const modalSolucionIncidencia = document.getElementById("modal-solucion-incidencia");
    const formSolucionIncidencia = document.getElementById("form-solucion-incidencia");
    const textoSolucionIncidencia = document.getElementById("texto-solucion-incidencia");
    const botonCerrarSolucion = document.getElementById("cerrar-modal-solucion");
    const botonCancelarSolucion = document.getElementById("boton-cancelar-solucion");

    // ID de la incidencia activa sobre la que se documenta la solución
    let incidenciaSeleccionadaId = null;

    // Obtiene los datos del usuario activo desde el almacenamiento local
    function obtenerUsuarioActivo() {
        const usuarioActivoGuardado = localStorage.getItem(STORAGE_KEY_USUARIO_ACTIVO);
        if (usuarioActivoGuardado === null) {
            return null;
        }
        return JSON.parse(usuarioActivoGuardado);
    }

    // Valida y restringe el acceso de usuarios no autorizados
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
    }

    // Lee los reportes almacenados buscando compatibilidad en las dos claves utilizadas
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

    // Persiste los reportes actualizados en todas las claves de almacenamiento de datos
    function guardarReportes(reportes) {
        STORAGE_KEYS_REPORTES.forEach(function (clave) {
            localStorage.setItem(clave, JSON.stringify(reportes));
        });
    }

    // Mapea los códigos del aula a nombres legibles
    function formatearAula(aulaKey) {
        const mapa = {
            "taller1": "Taller 1",
            "taller2": "Taller 2",
            "taller3": "Taller 3",
            "taller4": "Taller 4",
            "taller5": "Taller 5",
            "lab1": "Laboratorio 1",
            "lab2": "Laboratorio 2",
            "lab3": "Laboratorio 3"
        };
        return mapa[aulaKey] || aulaKey;
    }

    // Convierte el formato YYYY-MM-DD a DD/MM/YYYY
    function formatearFecha(fechaStr) {
        if (!fechaStr) return "";
        const partes = fechaStr.split("-");
        if (partes.length === 3) {
            return `${parseInt(partes[2], 10)}/${parseInt(partes[1], 10)}/${partes[0]}`;
        }
        return fechaStr;
    }

    // Limpia espacios y convierte texto a minúsculas para búsquedas exactas
    function normalizarTexto(texto) {
        return String(texto || "").toLowerCase().trim();
    }

    // Extrae campos del objeto admitiendo diversas variaciones en la clave
    function obtenerValor(reporte, claves, valorPorDefecto) {
        for (let i = 0; i < claves.length; i++) {
            if (reporte[claves[i]] !== undefined && reporte[claves[i]] !== null) {
                return reporte[claves[i]];
            }
        }
        return valorPorDefecto;
    }

    // Filtra y retorna únicamente los reportes de incidencia
    function obtenerIncidencias() {
        const reportes = obtenerReportes();
        return reportes.filter(function (reporte) {
            return reporte.tipo === "incidencia";
        });
    }

    // Asigna el nivel de prioridad basándose en el tipo de solicitud de incidencia
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

    // Formatea el nombre visual de la prioridad
    function formatearPrioridad(prioridad) {
        if (prioridad === "alta") return "Alta";
        if (prioridad === "media") return "Media";
        return "Baja";
    }

    // Formatea el nombre visual del estado
    function formatearEstado(estado) {
        if (estado === "reenviado") return "Reenviado";
        if (estado === "en-proceso") return "En proceso";
        if (estado === "resuelto") return "Resuelto";
        return "Pendiente";
    }

    // Retorna el nombre formateado o descripción del tipo de solicitud
    function formatearTipoSolicitud(incidencia) {
        const tipoSolicitud = obtenerValor(incidencia, ["tipoSolicitud"], "");
        const otroTipo = obtenerValor(incidencia, ["otroTipo"], "");

        if (tipoSolicitud === "rotura-equipo") return "Rotura de equipo";
        if (tipoSolicitud === "falla-equipo") return "Falla de equipo";
        if (otroTipo !== "") return otroTipo;
        return "Otro";
    }

    // Combina la búsqueda por texto y el filtro por estado
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
            const aulaFormateada = formatearAula(taller);
            const fechaFormateada = formatearFecha(fecha);

            const coincideTexto = textoBuscado === ""
                || normalizarTexto(docente).includes(textoBuscado)
                || normalizarTexto(tipo).includes(textoBuscado)
                || normalizarTexto(aulaFormateada).includes(textoBuscado)
                || normalizarTexto(fechaFormateada).includes(textoBuscado)
                || normalizarTexto(fecha).includes(textoBuscado);

            const coincideEstado = estadoFiltrado === "todos" || estado === estadoFiltrado;

            return coincideTexto && coincideEstado;
        });
    }

    // Ordena las incidencias por prioridad y envía las resueltas al final
    function ordenarIncidencias(incidencias) {
        const valorPrioridad = { alta: 1, media: 2, baja: 3 };

        return incidencias.sort(function (incidenciaA, incidenciaB) {
            const estadoA = obtenerValor(incidenciaA, ["estado"], "pendiente");
            const estadoB = obtenerValor(incidenciaB, ["estado"], "pendiente");

            if (estadoA === "resuelto" && estadoB !== "resuelto") return 1;
            if (estadoA !== "resuelto" && estadoB === "resuelto") return -1;

            return valorPrioridad[obtenerPrioridad(incidenciaA)] - valorPrioridad[obtenerPrioridad(incidenciaB)];
        });
    }

    // Actualiza el estado y opcionalmente añade los detalles de la solución en localStorage
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

    // Crea el selector de estado en la fila (dispara la documentación si es "Resuelto")
    function crearSelectorEstado(incidencia) {
        const estadoActual = obtenerValor(incidencia, ["estado"], "pendiente");

        const selector = document.createElement("select");
        selector.className = "cordinacion-table__select";

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

        selector.value = estadoActual;

        selector.addEventListener("change", function () {
            incidenciaSeleccionadaId = incidencia.id;
            if (selector.value === "resuelto") {
                selector.value = estadoActual;
                abrirModalSolucion();
                return;
            }
            cambiarEstadoIncidencia(selector.value);
            alert("Estado actualizado correctamente.");
        });

        return selector;
    }

    // Helper de celdas
    function crearCelda(contenido, esBold = false) {
        const celda = document.createElement("td");
        celda.className = "cordinacion-table__cell";
        if (esBold) {
            const span = document.createElement("span");
            span.style.fontWeight = "bold";
            span.textContent = contenido;
            celda.appendChild(span);
        } else {
            celda.textContent = contenido;
        }
        return celda;
    }

    // Crea la celda con el badge coloreado de prioridad
    function crearBadgePrioridad(prioridad) {
        const celda = document.createElement("td");
        celda.className = "cordinacion-table__cell";
        
        const badge = document.createElement("span");
        badge.className = `cordinacion-badge cordinacion-badge--${prioridad}`;
        badge.textContent = formatearPrioridad(prioridad);

        celda.appendChild(badge);
        return celda;
    }

    // Crea el botón con icono SVG para ver la incidencia
    function crearBotonVer(incidencia) {
        const celda = document.createElement("td");
        celda.className = "cordinacion-table__cell";
        
        const boton = document.createElement("button");
        boton.className = "cordinacion-table__button-ver";
        boton.type = "button";
        
        const svgIcon = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svgIcon.setAttribute("class", "cordinacion-table__btn-icon");
        svgIcon.setAttribute("viewBox", "0 0 24 24");
        svgIcon.setAttribute("fill", "none");
        svgIcon.setAttribute("stroke", "currentColor");
        svgIcon.setAttribute("stroke-width", "2");
        
        const path1 = document.createElementNS("http://www.w3.org/2000/svg", "path");
        path1.setAttribute("d", "M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z");
        
        const circle1 = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle1.setAttribute("cx", "12");
        circle1.setAttribute("cy", "12");
        circle1.setAttribute("r", "3");
        
        svgIcon.appendChild(path1);
        svgIcon.appendChild(circle1);
        
        const textSpan = document.createElement("span");
        textSpan.textContent = "Ver";
        
        boton.appendChild(svgIcon);
        boton.appendChild(textSpan);
        
        boton.addEventListener("click", function () {
            abrirDetalleIncidencia(incidencia);
        });

        celda.appendChild(boton);
        return celda;
    }

    // Dibuja la fila
    function crearFilaIncidencia(incidencia) {
        const fila = document.createElement("tr");
        fila.className = "cordinacion-table__row";

        const nombre = obtenerValor(incidencia, ["nombreProfesor", "nombre"], "");
        const apellido = obtenerValor(incidencia, ["apellidoProfesor", "apellido"], "");
        const fecha = obtenerValor(incidencia, ["fecha"], "");
        const taller = obtenerValor(incidencia, ["taller", "aula"], "");
        const prioridad = obtenerPrioridad(incidencia);
        const docente = `${nombre} ${apellido}`;

        fila.appendChild(crearBadgePrioridad(prioridad));
        fila.appendChild(crearCelda(formatearFecha(fecha)));
        fila.appendChild(crearCelda(docente, true));
        fila.appendChild(crearCelda(formatearTipoSolicitud(incidencia)));
        fila.appendChild(crearCelda(formatearAula(taller)));
        
        const celdaEstado = document.createElement("td");
        celdaEstado.className = "cordinacion-table__cell";
        celdaEstado.appendChild(crearSelectorEstado(incidencia));
        fila.appendChild(celdaEstado);

        fila.appendChild(crearBotonVer(incidencia));

        return fila;
    }

    // Controla la visibilidad y dibuja las incidencias filtradas y ordenadas
    function mostrarRegistrosIncidencias() {
        const incidencias = ordenarIncidencias(filtrarIncidencias(obtenerIncidencias()));
        tablaRegistrosIncidencias.innerHTML = "";
        cantidadRegistrosIncidencias.textContent = `${incidencias.length} incidencia(s) · ordenadas por prioridad`;

        if (incidencias.length === 0) {
            tablaContenedor.style.display = "none";
            mensajeVacioIncidencias.style.display = "block";
            return;
        }

        tablaContenedor.style.display = "block";
        mensajeVacioIncidencias.style.display = "none";

        incidencias.forEach(function (incidencia) {
            tablaRegistrosIncidencias.appendChild(crearFilaIncidencia(incidencia));
        });
    }

    // Helper para estructurar pares etiqueta-valor
    function crearElementoDetalle(label, valor) {
        const parrafo = document.createElement("p");
        parrafo.className = "cordinacion-modal__detail-item";
        
        const strong = document.createElement("strong");
        strong.textContent = `${label}: `;
        
        const span = document.createElement("span");
        span.textContent = valor;
        
        parrafo.appendChild(strong);
        parrafo.appendChild(span);
        return parrafo;
    }

    // Inyecta y abre el diálogo modal con el detalle y solución (si está resuelta) de la incidencia
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

        const gridInfo = document.createElement("section");
        gridInfo.className = "cordinacion-modal__info-grid";

        gridInfo.appendChild(crearElementoDetalle("Fecha", formatearFecha(fecha)));
        gridInfo.appendChild(crearElementoDetalle("Hora", hora));
        gridInfo.appendChild(crearElementoDetalle("Docente", `${nombre} ${apellido}`));
        gridInfo.appendChild(crearElementoDetalle("Correo", correo));
        gridInfo.appendChild(crearElementoDetalle("Asignatura", asignatura));
        gridInfo.appendChild(crearElementoDetalle("Tipo", formatearTipoSolicitud(incidencia)));
        gridInfo.appendChild(crearElementoDetalle("Taller / Aula", formatearAula(taller)));
        gridInfo.appendChild(crearElementoDetalle("Estado", formatearEstado(estado)));
        gridInfo.appendChild(crearElementoDetalle("Prioridad", formatearPrioridad(obtenerPrioridad(incidencia))));

        detalleIncidenciaContenido.appendChild(gridInfo);

        // Agrupa descripción y solución en contenedores semánticos tipo blockquote
        const textSection = document.createElement("section");
        textSection.className = "cordinacion-modal__text-section";

        const descDiv = document.createElement("p");
        descDiv.className = "cordinacion-modal__detail-item";
        descDiv.innerHTML = `<strong>Descripción:</strong><br><blockquote class="cordinacion-modal__blockquote cordinacion-modal__blockquote--desc">${descripcion}</blockquote>`;
        textSection.appendChild(descDiv);

        if (solucion !== "") {
            const solDiv = document.createElement("p");
            solDiv.className = "cordinacion-modal__detail-item";
            solDiv.innerHTML = `<strong>Solución aplicada:</strong><br><blockquote class="cordinacion-modal__blockquote cordinacion-modal__blockquote--sol">${solucion}</blockquote>`;
            textSection.appendChild(solDiv);
        }

        detalleIncidenciaContenido.appendChild(textSection);
        modalDetalleIncidencia.showModal();
    }

    function cerrarDetalleIncidencia() {
        modalDetalleIncidencia.close();
    }

    // Abre el modal para escribir la justificación de la solución
    function abrirModalSolucion() {
        textoSolucionIncidencia.value = "";
        modalSolucionIncidencia.showModal();
    }

    function cerrarModalSolucion() {
        modalSolucionIncidencia.close();
    }

    // Procesa el submit del modal de solución y marca como resuelta
    function guardarSolucion(evento) {
        evento.preventDefault();
        const solucionIngresada = textoSolucionIncidencia.value.trim();
        if (solucionIngresada === "") {
            alert("Debe escribir la solución aplicada.");
            return;
        }
        cambiarEstadoIncidencia("resuelto", solucionIngresada);
        modalSolucionIncidencia.close();
        alert("Incidencia marcada como resuelta.");
    }

    // Inicializaciones y vinculación de escuchas
    protegerPanelCoordinacion();
    mostrarRegistrosIncidencias();

    buscadorRegistrosIncidencias.addEventListener("input", mostrarRegistrosIncidencias);
    filtroEstadoIncidencias.addEventListener("change", mostrarRegistrosIncidencias);

    botonCerrarModalIncidencia.addEventListener("click", cerrarDetalleIncidencia);
    botonCerrarDetalleIncidencia.addEventListener("click", cerrarDetalleIncidencia);

    botonCerrarSolucion.addEventListener("click", cerrarModalSolucion);
    botonCancelarSolucion.addEventListener("click", cerrarModalSolucion);
    formSolucionIncidencia.addEventListener("submit", guardarSolucion);
});
