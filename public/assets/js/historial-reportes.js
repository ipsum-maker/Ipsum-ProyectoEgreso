document.addEventListener("DOMContentLoaded", function () {
    // Claves usadas para leer y guardar información en localStorage
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";
    const STORAGE_KEYS_REPORTES = ["reportes-sistema", "reportes"];

    // Elementos principales del historial
    const contenedorReportes = document.getElementById("contenedor-reportes");
    const mensajeVacio = document.getElementById("mensaje-vacio");

    // Botones de filtro
    const botonFiltroTodos = document.getElementById("btn-filtro-todos");
    const botonFiltroDiarios = document.getElementById("btn-filtro-diarios");
    const botonFiltroIncidencias = document.getElementById("btn-filtro-incidencias");

    // Contadores de los filtros
    const cantidadTodos = document.getElementById("cantidad-todos");
    const cantidadDiarios = document.getElementById("cantidad-diarios");
    const cantidadIncidencias = document.getElementById("cantidad-incidencias");

    // Modal de edición de reporte diario
    const modalDiario = document.getElementById("modal-diario");
    const formEditarDiario = document.getElementById("form-editar-diario");
    const botonCerrarDiario = document.getElementById("btn-cerrar-diario");
    const botonCancelarDiario = document.getElementById("btn-cancelar-diario");
    const campoEditDiarioFecha = document.getElementById("edit-diario-fecha");
    const campoEditDiarioNombre = document.getElementById("edit-diario-nombre");
    const campoEditDiarioApellido = document.getElementById("edit-diario-apellido");
    const campoEditDiarioAula = document.getElementById("edit-diario-aula");
    const botonEditDiarioAgregarPc = document.getElementById("edit-diario-agregar-pc");
    const listaEditDiarioPcs = document.getElementById("edit-diario-lista-pcs");

    // Modal de edición de reporte de incidencia
    const modalIncidencia = document.getElementById("modal-incidencia");
    const formEditarIncidencia = document.getElementById("form-editar-incidencia");
    const botonCerrarIncidencia = document.getElementById("btn-cerrar-incidencia");
    const botonCancelarIncidencia = document.getElementById("btn-cancelar-incidencia");
    const campoEditIncidenciaNombre = document.getElementById("edit-incidencia-nombre");
    const campoEditIncidenciaApellido = document.getElementById("edit-incidencia-apellido");
    const campoEditIncidenciaAsignatura = document.getElementById("edit-incidencia-asignatura");
    const campoEditIncidenciaFecha = document.getElementById("edit-incidencia-fecha");
    const campoEditIncidenciaSolicitud = document.getElementById("edit-incidencia-solicitud");
    const campoEditIncidenciaTaller = document.getElementById("edit-incidencia-taller");
    const campoEditIncidenciaDescripcion = document.getElementById("edit-incidencia-descripcion");

    // Contenedor de alertas visuales
    const contenedorAlertas = document.getElementById("alertas-contenedor");

    // Variables de control
    let filtroActual = "todos";
    let reporteEditandoId = null;
    let contadorPcEdicion = 0;

    // Obtiene el usuario que inició sesión
    function obtenerUsuarioActivo() {
    return ControlErrores.leerJson(STORAGE_KEY_USUARIO_ACTIVO, null);
}

    // Protege el historial para que solo entren profesores
    function protegerHistorialProfesor() {
        const usuarioActivo = obtenerUsuarioActivo();

        if (usuarioActivo === null) {
            window.location.href = "login.html";
            return;
        }

        if (usuarioActivo.rol !== "profesor") {
            alert("Esta sección pertenece al historial del profesor.");
            window.location.href = "login.html";
        }
    }

    // Obtiene todos los reportes guardados
    function obtenerReportes() {
    for (let i = 0; i < STORAGE_KEYS_REPORTES.length; i++) {
        const reportes = ControlErrores.leerJson(STORAGE_KEYS_REPORTES[i], null);

        if (Array.isArray(reportes)) {
            return reportes;
        }
    }

    return [];
}

    // Guarda todos los reportes en las dos claves para mantener compatibilidad
    function guardarReportes(reportes) {
    STORAGE_KEYS_REPORTES.forEach(function (clave) {
        ControlErrores.guardarJson(clave, reportes);
    });
}

    // Obtiene solo los reportes del profesor activo
    function obtenerReportesDelProfesor() {
        const usuarioActivo = obtenerUsuarioActivo();
        const reportes = obtenerReportes();

        return reportes.filter(function (reporte) {
            return reporte.profesorCorreo === usuarioActivo.correo;
        });
    }

    // Muestra alertas visuales usando las clases originales del CSS
    function mostrarAlerta(mensaje, tipo) {
        if (contenedorAlertas === null) {
            alert(mensaje);
            return;
        }

        const alerta = document.createElement("article");
        alerta.className = `alerta-notificacion alerta-notificacion--${tipo}`;

        const textoAlerta = document.createElement("p");
        textoAlerta.className = "alerta-notificacion__mensaje";
        textoAlerta.textContent = mensaje;

        alerta.appendChild(textoAlerta);
        contenedorAlertas.appendChild(alerta);

        setTimeout(function () {
            alerta.remove();
        }, 2500);
    }

    // Quita el estado activo de todos los filtros
    function limpiarFiltrosActivos() {
        botonFiltroTodos.classList.remove("historial-filters__button--active");
        botonFiltroDiarios.classList.remove("historial-filters__button--active");
        botonFiltroIncidencias.classList.remove("historial-filters__button--active");
    }

    // Marca visualmente el filtro seleccionado
    function activarFiltro(boton) {
        limpiarFiltrosActivos();
        boton.classList.add("historial-filters__button--active");
    }

    // Actualiza las cantidades de los filtros
    function actualizarCantidades(reportes) {
        const diarios = reportes.filter(function (reporte) {
            return reporte.tipo === "diario";
        });

        const incidencias = reportes.filter(function (reporte) {
            return reporte.tipo === "incidencia";
        });

        cantidadTodos.textContent = `(${reportes.length})`;
        cantidadDiarios.textContent = `(${diarios.length})`;
        cantidadIncidencias.textContent = `(${incidencias.length})`;
    }

    // Filtra los reportes según el botón seleccionado
    function obtenerReportesFiltrados(reportes) {
        if (filtroActual === "diarios") {
            return reportes.filter(function (reporte) {
                return reporte.tipo === "diario";
            });
        }

        if (filtroActual === "incidencias") {
            return reportes.filter(function (reporte) {
                return reporte.tipo === "incidencia";
            });
        }

        return reportes;
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

    // Convierte estados internos a texto visible
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

    // Crea un badge usando las clases originales del CSS
    function crearBadge(texto, claseModificadora) {
        const badge = document.createElement("span");
        badge.className = `reporte-tarjeta__badge ${claseModificadora}`;
        badge.textContent = texto;

        return badge;
    }

    // Crea una tarjeta de reporte usando el diseño original del historial
    function crearTarjetaReporte(reporte) {
        const tarjeta = document.createElement("article");

        if (reporte.tipo === "diario") {
            tarjeta.className = "reporte-tarjeta reporte-tarjeta--diario";
        } else {
            tarjeta.className = "reporte-tarjeta reporte-tarjeta--incidencia";
        }

        const encabezado = document.createElement("header");
        encabezado.className = "reporte-tarjeta__header";

        const titulo = document.createElement("h2");
        titulo.className = "reporte-tarjeta__titulo";

        if (reporte.tipo === "diario") {
            titulo.textContent = "Reporte Diario";
        } else {
            titulo.textContent = "Reporte de Incidencia";
        }

        const contenedorBadges = document.createElement("section");
        contenedorBadges.className = "reporte-tarjeta__badges";

        if (reporte.tipo === "diario") {
            contenedorBadges.appendChild(crearBadge("Diario", "reporte-tarjeta__badge--diario"));
        } else {
            const estado = obtenerValor(reporte, ["estado"], "pendiente");

            contenedorBadges.appendChild(crearBadge("Incidencia", "reporte-tarjeta__badge--incidencia"));
            contenedorBadges.appendChild(crearBadge(formatearEstado(estado), "reporte-tarjeta__badge--estado"));
        }

        encabezado.appendChild(titulo);
        encabezado.appendChild(contenedorBadges);

        const detalles = document.createElement("section");
        detalles.className = "reporte-tarjeta__detalles";

        const fecha = document.createElement("p");
        fecha.className = "reporte-tarjeta__info reporte-tarjeta__info-fecha";
        fecha.textContent = `${obtenerValor(reporte, ["fecha"], "")} - ${obtenerValor(reporte, ["hora"], "")}`;

        const docente = document.createElement("p");
        docente.className = "reporte-tarjeta__info";
        docente.innerHTML = `<strong>Docente:</strong> ${obtenerValor(reporte, ["nombreProfesor", "nombre"], "")} ${obtenerValor(reporte, ["apellidoProfesor", "apellido"], "")}`;

        const detallePrincipal = document.createElement("p");
        detallePrincipal.className = "reporte-tarjeta__info";

        if (reporte.tipo === "diario") {
            detallePrincipal.innerHTML = `<strong>Aula / Taller:</strong> ${obtenerValor(reporte, ["aula", "taller"], "")}`;
        } else {
            detallePrincipal.innerHTML = `<strong>Asignatura:</strong> ${obtenerValor(reporte, ["asignatura"], "")} | <strong>Taller:</strong> ${obtenerValor(reporte, ["taller", "aula"], "")}`;
        }

        detalles.appendChild(fecha);
        detalles.appendChild(docente);
        detalles.appendChild(detallePrincipal);

        if (reporte.tipo === "diario") {
            const asignaciones = obtenerValor(reporte, ["asignaciones"], []);
            const detallePc = document.createElement("p");

            detallePc.className = "reporte-tarjeta__detalle-pc";
            detallePc.textContent = `Equipos registrados: ${asignaciones.length}`;
            detalles.appendChild(detallePc);
        }

        const solucionGuardada = obtenerValor(reporte, ["solucion"], "");

        if (solucionGuardada !== "") {
            const solucion = document.createElement("p");
            solucion.className = "reporte-tarjeta__info";
            solucion.innerHTML = `<strong>Solución:</strong> ${solucionGuardada}`;
            detalles.appendChild(solucion);
        }

        const acciones = document.createElement("footer");
        acciones.className = "reporte-tarjeta__acciones";

        const botonEditar = document.createElement("button");
        botonEditar.type = "button";
        botonEditar.textContent = "Editar";
        botonEditar.className = "reporte-tarjeta__boton reporte-tarjeta__boton--editar";

        botonEditar.addEventListener("click", function () {
            abrirModalEdicion(reporte.id);
        });

        const botonEliminar = document.createElement("button");
        botonEliminar.type = "button";
        botonEliminar.textContent = "Eliminar";
        botonEliminar.className = "reporte-tarjeta__boton reporte-tarjeta__boton--eliminar";

        botonEliminar.addEventListener("click", function () {
            eliminarReporte(reporte.id);
        });

        acciones.appendChild(botonEditar);
        acciones.appendChild(botonEliminar);

        tarjeta.appendChild(encabezado);
        tarjeta.appendChild(detalles);
        tarjeta.appendChild(acciones);

        return tarjeta;
    }

    // Muestra los reportes en pantalla
    function mostrarReportes() {
        const reportesProfesor = obtenerReportesDelProfesor();
        const reportesFiltrados = obtenerReportesFiltrados(reportesProfesor);

        actualizarCantidades(reportesProfesor);
        contenedorReportes.innerHTML = "";

        if (reportesFiltrados.length === 0) {
            mensajeVacio.style.display = "block";
            contenedorReportes.appendChild(mensajeVacio);
            return;
        }

        mensajeVacio.style.display = "none";

        reportesFiltrados.forEach(function (reporte) {
            contenedorReportes.appendChild(crearTarjetaReporte(reporte));
        });
    }

    // Elimina un reporte
    function eliminarReporte(reporteId) {
        const confirmar = confirm("¿Seguro que querés eliminar este reporte?");

        if (!confirmar) {
            return;
        }

        const reportes = obtenerReportes();

        const reportesActualizados = reportes.filter(function (reporte) {
            return reporte.id !== reporteId;
        });

        guardarReportes(reportesActualizados);
        mostrarReportes();
        mostrarAlerta("Reporte eliminado correctamente.", "error");
    }

    // Busca un reporte por su id
    function buscarReportePorId(reporteId) {
        const reportes = obtenerReportes();

        return reportes.find(function (reporte) {
            return reporte.id === reporteId;
        });
    }

    // Abre el modal correspondiente según el tipo de reporte
    function abrirModalEdicion(reporteId) {
        const reporte = buscarReportePorId(reporteId);

        if (reporte === undefined) {
            alert("No se encontró el reporte.");
            return;
        }

        reporteEditandoId = reporteId;

        if (reporte.tipo === "diario") {
            abrirModalDiario(reporte);
            return;
        }

        abrirModalIncidencia(reporte);
    }

    // Crea una fila editable de PC dentro del modal
    function crearCampoPcEdicion(asignacion) {
        contadorPcEdicion++;

        const tarjetaPc = document.createElement("section");
        tarjetaPc.className = "assignment__card";

        const etiquetaPc = document.createElement("label");
        etiquetaPc.className = "assignment__pc";
        etiquetaPc.textContent = `PC${contadorPcEdicion}`;

        const campoPc = document.createElement("input");
        campoPc.className = "assignment__input";
        campoPc.type = "text";
        campoPc.value = asignacion.pc;
        campoPc.readOnly = true;

        const etiquetaAlumno = document.createElement("label");
        etiquetaAlumno.className = "assignment__label";
        etiquetaAlumno.textContent = "Nombre del alumno";

        const campoAlumno = document.createElement("input");
        campoAlumno.className = "assignment__input assignment__input--student";
        campoAlumno.type = "text";
        campoAlumno.value = asignacion.alumno;
        campoAlumno.placeholder = "Nombre del alumno";

        const botonEliminar = document.createElement("button");
        botonEliminar.className = "assignment__delete";
        botonEliminar.type = "button";
        botonEliminar.textContent = "×";

        botonEliminar.addEventListener("click", function () {
            tarjetaPc.remove();
        });

        tarjetaPc.appendChild(etiquetaPc);
        tarjetaPc.appendChild(campoPc);
        tarjetaPc.appendChild(etiquetaAlumno);
        tarjetaPc.appendChild(campoAlumno);
        tarjetaPc.appendChild(botonEliminar);

        return tarjetaPc;
    }

    // Abre el modal de edición de reporte diario
    function abrirModalDiario(reporte) {
        contadorPcEdicion = 0;

        campoEditDiarioFecha.value = obtenerValor(reporte, ["fecha"], "");
        campoEditDiarioNombre.value = obtenerValor(reporte, ["nombreProfesor", "nombre"], "");
        campoEditDiarioApellido.value = obtenerValor(reporte, ["apellidoProfesor", "apellido"], "");
        campoEditDiarioAula.value = obtenerValor(reporte, ["aula", "taller"], "");

        listaEditDiarioPcs.innerHTML = "";

        const asignaciones = obtenerValor(reporte, ["asignaciones"], []);

        asignaciones.forEach(function (asignacion) {
            listaEditDiarioPcs.appendChild(crearCampoPcEdicion(asignacion));
        });

        ControlErrores.abrirModalSeguro(modalDiario);
    }

    // Abre el modal de edición de incidencia
    function abrirModalIncidencia(reporte) {
        campoEditIncidenciaNombre.value = obtenerValor(reporte, ["nombreProfesor", "nombre"], "");
        campoEditIncidenciaApellido.value = obtenerValor(reporte, ["apellidoProfesor", "apellido"], "");
        campoEditIncidenciaAsignatura.value = obtenerValor(reporte, ["asignatura"], "");
        campoEditIncidenciaFecha.value = obtenerValor(reporte, ["fecha"], "");
        campoEditIncidenciaSolicitud.value = obtenerValor(reporte, ["tipoSolicitud"], "");
        campoEditIncidenciaTaller.value = obtenerValor(reporte, ["taller", "aula"], "");
        campoEditIncidenciaDescripcion.value = obtenerValor(reporte, ["descripcion"], "");

        ControlErrores.abrirModalSeguro(modalIncidencia);
    }

    // Obtiene las asignaciones editadas del modal diario
    function obtenerAsignacionesEditadas() {
        const tarjetasPc = document.querySelectorAll("#edit-diario-lista-pcs .assignment__card");
        const asignaciones = [];

        tarjetasPc.forEach(function (tarjeta) {
            const inputs = tarjeta.querySelectorAll("input");

            asignaciones.push({
                pc: inputs[0].value.trim(),
                alumno: inputs[1].value.trim()
            });
        });

        return asignaciones;
    }

    // Guarda la edición de un reporte diario
    function guardarEdicionDiario(evento) {
        evento.preventDefault();

        const asignaciones = obtenerAsignacionesEditadas();

        if (asignaciones.length === 0) {
            alert("El reporte debe tener al menos una PC asignada.");
            return;
        }

        const reportes = obtenerReportes();

        const reportesActualizados = reportes.map(function (reporte) {
            if (reporte.id === reporteEditandoId) {
                reporte.estado = "reenviado";
                reporte.nombreProfesor = campoEditDiarioNombre.value.trim();
                reporte.apellidoProfesor = campoEditDiarioApellido.value.trim();
                reporte.aula = campoEditDiarioAula.value;
                reporte.asignaciones = asignaciones;
            }

            return reporte;
        });

        guardarReportes(reportesActualizados);
        ControlErrores.cerrarModalSeguro(modalDiario);
        mostrarReportes();
        mostrarAlerta("Reporte diario editado y reenviado.", "exito");
    }

    // Guarda la edición de una incidencia
    function guardarEdicionIncidencia(evento) {
        evento.preventDefault();

        const reportes = obtenerReportes();

        const reportesActualizados = reportes.map(function (reporte) {
            if (reporte.id === reporteEditandoId) {
                reporte.estado = "reenviado";
                reporte.nombreProfesor = campoEditIncidenciaNombre.value.trim();
                reporte.apellidoProfesor = campoEditIncidenciaApellido.value.trim();
                reporte.asignatura = campoEditIncidenciaAsignatura.value.trim();
                reporte.tipoSolicitud = campoEditIncidenciaSolicitud.value;
                reporte.taller = campoEditIncidenciaTaller.value;
                reporte.descripcion = campoEditIncidenciaDescripcion.value.trim();
            }

            return reporte;
        });

        guardarReportes(reportesActualizados);
        ControlErrores.cerrarModalSeguro(modalIncidencia);
        mostrarReportes();
        mostrarAlerta("Incidencia editada y reenviada.", "exito");
    }

    // Agrega una PC nueva en el modal de edición diario
    function agregarPcEnEdicion() {
        const nuevaAsignacion = {
            pc: `PC${contadorPcEdicion + 1}`,
            alumno: ""
        };

        listaEditDiarioPcs.appendChild(crearCampoPcEdicion(nuevaAsignacion));
    }

    // Inicio del historial
    protegerHistorialProfesor();
    mostrarReportes();

    // Eventos de filtros
    botonFiltroTodos.addEventListener("click", function () {
        filtroActual = "todos";
        activarFiltro(botonFiltroTodos);
        mostrarReportes();
    });

    botonFiltroDiarios.addEventListener("click", function () {
        filtroActual = "diarios";
        activarFiltro(botonFiltroDiarios);
        mostrarReportes();
    });

    botonFiltroIncidencias.addEventListener("click", function () {
        filtroActual = "incidencias";
        activarFiltro(botonFiltroIncidencias);
        mostrarReportes();
    });

    // Eventos de cierre de modales
    botonCerrarDiario.addEventListener("click", function () {
        modalDiario.close();
    });

    botonCancelarDiario.addEventListener("click", function () {
        modalDiario.close();
    });

    botonCerrarIncidencia.addEventListener("click", function () {
        modalIncidencia.close();
    });

    botonCancelarIncidencia.addEventListener("click", function () {
        modalIncidencia.close();
    });

    // Eventos de guardado
    botonEditDiarioAgregarPc.addEventListener("click", agregarPcEnEdicion);
    formEditarDiario.addEventListener("submit", guardarEdicionDiario);
    formEditarIncidencia.addEventListener("submit", guardarEdicionIncidencia);
});