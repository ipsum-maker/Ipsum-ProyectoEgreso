document.addEventListener("DOMContentLoaded", function () {
    // Claves del almacenamiento local para credenciales y reportes
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";
    const STORAGE_KEYS_REPORTES = ["reportes-sistema", "reportes"];

    // Referencias principales de la interfaz
    const buscadorRegistrosDiarios = document.getElementById("buscador-registros-diarios");
    const tablaRegistrosDiarios = document.getElementById("tabla-registros-diarios");
    const cantidadRegistrosDiarios = document.getElementById("cantidad-registros-diarios");
    const mensajeVacioDiarios = document.getElementById("mensaje-vacio-diarios");
    const tablaContenedor = document.getElementById("tabla-contenedor");

    // Diálogos modales y su contenido
    const modalDetalleDiario = document.getElementById("modal-detalle-diario");
    const detalleDiarioContenido = document.getElementById("detalle-diario-contenido");
    const botonCerrarModalDiario = document.getElementById("cerrar-modal-diario");
    const botonCerrarDetalleDiario = document.getElementById("boton-cerrar-detalle-diario");

    // Valida si el usuario activo tiene rol autorizado
   function obtenerUsuarioActivo() {
    return ControlErrores.leerJson(STORAGE_KEY_USUARIO_ACTIVO, null);
}

    // Bloquea el acceso a usuarios no autorizados (solo coordinadores o administradores)
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

    // Carga los reportes desde localStorage buscando en las claves compatibles
  function obtenerReportes() {
    for (let i = 0; i < STORAGE_KEYS_REPORTES.length; i++) {
        const reportes = ControlErrores.leerJson(STORAGE_KEYS_REPORTES[i], null);

        if (Array.isArray(reportes)) {
            return reportes;
        }
    }

    return [];
}

    // Mapea códigos técnicos del aula a texto legible
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

    // Convierte el formato de fecha nativo YYYY-MM-DD a DD/MM/YYYY
    function formatearFecha(fechaStr) {
        if (!fechaStr) return "";
        const partes = fechaStr.split("-");
        if (partes.length === 3) {
            return `${parseInt(partes[2], 10)}/${parseInt(partes[1], 10)}/${partes[0]}`;
        }
        return fechaStr;
    }

    // Limpia y normaliza texto para realizar búsquedas insensibles a mayúsculas
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

    // Recupera únicamente los reportes diarios
    function obtenerRegistrosDiarios() {
        const reportes = obtenerReportes();
        return reportes.filter(function (reporte) {
            return reporte.tipo === "diario";
        });
    }

    // Aplica el filtro de texto sobre los registros diarios cargados
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
            const aulaFormateada = formatearAula(aula);
            const fechaFormateada = formatearFecha(fecha);

            return normalizarTexto(docente).includes(textoBuscado)
                || normalizarTexto(aulaFormateada).includes(textoBuscado)
                || normalizarTexto(fechaFormateada).includes(textoBuscado)
                || normalizarTexto(fecha).includes(textoBuscado);
        });
    }

    // Crea celdas estructuradas para el cuerpo de la tabla
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

    // Genera el botón con icono SVG para examinar el detalle de la planilla
    function crearBotonVer(registro) {
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
            abrirDetalleDiario(registro);
        });

        celda.appendChild(boton);
        return celda;
    }

    // Construye la fila de la tabla a partir de los datos del reporte
    function crearFilaDiario(registro) {
        const fila = document.createElement("tr");
        fila.className = "cordinacion-table__row";

        const nombre = obtenerValor(registro, ["nombreProfesor", "nombre"], "");
        const apellido = obtenerValor(registro, ["apellidoProfesor", "apellido"], "");
        const aula = obtenerValor(registro, ["aula", "taller"], "");
        const fecha = obtenerValor(registro, ["fecha"], "");
        const asignaciones = obtenerValor(registro, ["asignaciones"], []);
        const docente = `${nombre} ${apellido}`;

        fila.appendChild(crearCelda(formatearFecha(fecha)));
        fila.appendChild(crearCelda(docente, true));
        fila.appendChild(crearCelda(formatearAula(aula)));
        fila.appendChild(crearCelda(`${asignaciones.length} equipo(s)`));
        fila.appendChild(crearBotonVer(registro));

        return fila;
    }

    // Dibuja las planillas cargadas, controlando el contador y la visibilidad del estado vacío
    function mostrarRegistrosDiarios() {
        const registros = filtrarRegistrosDiarios(obtenerRegistrosDiarios());
        tablaRegistrosDiarios.innerHTML = "";
        cantidadRegistrosDiarios.textContent = `${registros.length} registro(s) encontrado(s)`;

        if (registros.length === 0) {
            tablaContenedor.style.display = "none";
            mensajeVacioDiarios.style.display = "block";
            return;
        }

        tablaContenedor.style.display = "block";
        mensajeVacioDiarios.style.display = "none";

        registros.forEach(function (registro) {
            tablaRegistrosDiarios.appendChild(crearFilaDiario(registro));
        });
    }

    // Helper para estructurar los pares de etiqueta-valor en el modal semánticamente
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

    // Inyecta dinámicamente y abre el diálogo modal con el detalle completo del registro diario
    function abrirDetalleDiario(registro) {
        detalleDiarioContenido.innerHTML = "";

        const nombre = obtenerValor(registro, ["nombreProfesor", "nombre"], "");
        const apellido = obtenerValor(registro, ["apellidoProfesor", "apellido"], "");
        const fecha = obtenerValor(registro, ["fecha"], "");
        const hora = obtenerValor(registro, ["hora"], "");
        const correo = obtenerValor(registro, ["profesorCorreo", "correo"], "");
        const aula = obtenerValor(registro, ["aula", "taller"], "");
        const asignaciones = obtenerValor(registro, ["asignaciones"], []);

        const gridInfo = document.createElement("section");
        gridInfo.className = "cordinacion-modal__info-grid";

        gridInfo.appendChild(crearElementoDetalle("Fecha", formatearFecha(fecha)));
        gridInfo.appendChild(crearElementoDetalle("Hora", hora));
        gridInfo.appendChild(crearElementoDetalle("Docente", `${nombre} ${apellido}`));
        gridInfo.appendChild(crearElementoDetalle("Correo", correo));
        gridInfo.appendChild(crearElementoDetalle("Aula / Taller", formatearAula(aula)));

        detalleDiarioContenido.appendChild(gridInfo);

        // Agrupa las asignaciones de PCs en un listado semántico
        const sectionEquipos = document.createElement("section");
        sectionEquipos.className = "cordinacion-modal__equipos-section";

        const tituloEquipos = document.createElement("h3");
        tituloEquipos.className = "cordinacion-modal__equipos-title";
        tituloEquipos.textContent = "Equipos registrados";
        sectionEquipos.appendChild(tituloEquipos);

        if (asignaciones.length === 0) {
            const emptyP = document.createElement("p");
            emptyP.textContent = "No hay asignaciones de equipos registradas.";
            sectionEquipos.appendChild(emptyP);
        } else {
            const gridEquipos = document.createElement("ul");
            gridEquipos.className = "cordinacion-modal__equipos-grid";

            asignaciones.forEach(function (asignacion) {
                const item = document.createElement("li");
                item.className = "cordinacion-modal__equipo-card";
                
                const labelPc = document.createElement("span");
                labelPc.className = "cordinacion-modal__equipo-pc";
                labelPc.textContent = asignacion.pc;

                const labelAlumno = document.createElement("span");
                labelAlumno.className = "cordinacion-modal__equipo-alumno";
                labelAlumno.textContent = asignacion.alumno;

                item.appendChild(labelPc);
                item.appendChild(labelAlumno);
                gridEquipos.appendChild(item);
            });

            sectionEquipos.appendChild(gridEquipos);
        }

        detalleDiarioContenido.appendChild(sectionEquipos);
        ControlErrores.abrirModalSeguro(modalDetalleDiario);
    }

    // Cierra el diálogo modal
    function cerrarDetalleDiario() {
       ControlErrores.cerrarModalSeguro(modalDetalleDiario);
    }

    // Inicializaciones y vinculación de escuchas
    protegerPanelCoordinacion();
    mostrarRegistrosDiarios();

    buscadorRegistrosDiarios.addEventListener("input", mostrarRegistrosDiarios);
    botonCerrarModalDiario.addEventListener("click", cerrarDetalleDiario);
    botonCerrarDetalleDiario.addEventListener("click", cerrarDetalleDiario);
});
