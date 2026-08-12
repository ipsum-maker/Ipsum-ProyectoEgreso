document.addEventListener("DOMContentLoaded", function () {
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";
    const STORAGE_KEY_REPORTES = "reportes-sistema";

    const formReporteDiario = document.getElementById("form-reporte-diario");
    const botonAgregarPc = document.getElementById("agregar-pc");
    const listaPcs = document.getElementById("lista-pcs");
    const campoFecha = document.getElementById("fecha");
    const campoHora = document.getElementById("hora");
    const campoNombre = document.getElementById("nombre-profesor");
    const campoApellido = document.getElementById("apellido-profesor");
    const campoAula = document.getElementById("aula");
    const botonCancelar = document.querySelector(".button--secondary");

    let contadorPc = 0;

 function obtenerUsuarioActivo() {
    return ControlErrores.leerJson(STORAGE_KEY_USUARIO_ACTIVO, null);
}

    function protegerFormulario() {
        const usuarioActivo = obtenerUsuarioActivo();

        if (usuarioActivo === null) {
            window.location.href = "login.html";
            return;
        }

        if (usuarioActivo.rol !== "profesor") {
            alert("Solo los profesores pueden crear reportes diarios.");
            window.location.href = "login.html";
        }
    }

   function obtenerReportes() {
    return ControlErrores.leerJson(STORAGE_KEY_REPORTES, []);
}

   function guardarReportes(reportes) {
    return ControlErrores.guardarJson(STORAGE_KEY_REPORTES, reportes);
}

    function obtenerFechaActual() {
        const fechaActual = new Date();

        const anio = fechaActual.getFullYear();
        const mes = String(fechaActual.getMonth() + 1).padStart(2, "0");
        const dia = String(fechaActual.getDate()).padStart(2, "0");

        return `${anio}-${mes}-${dia}`;
    }

    function obtenerHoraActual() {
        const fechaActual = new Date();

        const horas = String(fechaActual.getHours()).padStart(2, "0");
        const minutos = String(fechaActual.getMinutes()).padStart(2, "0");

        return `${horas}:${minutos}`;
    }

    function cargarFechaYHora() {
        if (campoFecha !== null) {
            campoFecha.value = obtenerFechaActual();
        }

        if (campoHora !== null) {
            campoHora.value = obtenerHoraActual();
        }
    }

    function crearCampoPc(numeroPc) {
        const tarjetaPc = document.createElement("section");
        tarjetaPc.className = "assignment__card";
        tarjetaPc.dataset.numeroPc = numeroPc;

        const etiquetaPc = document.createElement("label");
        etiquetaPc.className = "assignment__pc";
        etiquetaPc.setAttribute("for", `pc-${numeroPc}`);
        etiquetaPc.textContent = `PC${numeroPc}`;

        const campoPc = document.createElement("input");
        campoPc.className = "assignment__input";
        campoPc.type = "text";
        campoPc.id = `pc-${numeroPc}`;
        campoPc.name = `pc-${numeroPc}`;
        campoPc.value = `PC${numeroPc}`;
        campoPc.readOnly = true;

        const etiquetaAlumno = document.createElement("label");
        etiquetaAlumno.className = "assignment__label";
        etiquetaAlumno.setAttribute("for", `alumno-${numeroPc}`);
        etiquetaAlumno.textContent = "Nombre del alumno";

        const campoAlumno = document.createElement("input");
        campoAlumno.className = "assignment__input assignment__input--student";
        campoAlumno.type = "text";
        campoAlumno.id = `alumno-${numeroPc}`;
        campoAlumno.name = `alumno-${numeroPc}`;
        campoAlumno.placeholder = "Nombre del alumno";

        const botonEliminar = document.createElement("button");
        botonEliminar.className = "assignment__delete";
        botonEliminar.type = "button";
        botonEliminar.setAttribute("aria-label", "Eliminar asignación");
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

    function agregarPc() {
        contadorPc++;
        const nuevaPc = crearCampoPc(contadorPc);

        if (listaPcs !== null) {
            listaPcs.appendChild(nuevaPc);
        }
    }

    function obtenerAsignacionesPc() {
        const tarjetasPc = document.querySelectorAll("#lista-pcs .assignment__card");
        const asignaciones = [];

        tarjetasPc.forEach(function (tarjeta) {
            const campoPc = tarjeta.querySelector("input[name^='pc-']");
            const campoAlumno = tarjeta.querySelector("input[name^='alumno-']");

            if (campoPc !== null && campoAlumno !== null) {
                asignaciones.push({
                    pc: campoPc.value.trim(),
                    alumno: campoAlumno.value.trim()
                });
            }
        });

        return asignaciones;
    }

    function validarFormulario(asignaciones) {
        const elementosCorrectos = ControlErrores.validarElementos([
    { nombre: "campoNombre", elemento: campoNombre },
    { nombre: "campoApellido", elemento: campoApellido },
    { nombre: "campoAula", elemento: campoAula },
    { nombre: "campoFecha", elemento: campoFecha },
    { nombre: "campoHora", elemento: campoHora }
]);

if (!elementosCorrectos) {
    return false;
}
        if (campoNombre.value.trim() === "") {
            alert("Ingrese el nombre del profesor.");
            return false;
        }

        if (campoApellido.value.trim() === "") {
            alert("Ingrese el apellido del profesor.");
            return false;
        }

        if (campoAula.value.trim() === "") {
            alert("Seleccione un aula, taller o laboratorio.");
            return false;
        }

        if (asignaciones.length === 0) {
            alert("Agregue al menos una PC.");
            return false;
        }

        const hayAlumnoVacio = asignaciones.some(function (asignacion) {
            return asignacion.alumno === "";
        });

        if (hayAlumnoVacio) {
            alert("Complete el nombre del alumno en todas las PCs agregadas.");
            return false;
        }

        return true;
    }

    function guardarReporteDiario(evento) {
        evento.preventDefault();

        const usuarioActivo = obtenerUsuarioActivo();
        const asignaciones = obtenerAsignacionesPc();

        if (!validarFormulario(asignaciones)) {
            return;
        }

        const nuevoReporte = {
            id: Date.now(),
            tipo: "diario",
            estado: "pendiente",
            profesorCorreo: usuarioActivo.correo,
            profesorNombreUsuario: usuarioActivo.nombre,
            fecha: campoFecha.value,
            hora: campoHora.value,
            nombreProfesor: campoNombre.value.trim(),
            apellidoProfesor: campoApellido.value.trim(),
            aula: campoAula.value,
            asignaciones: asignaciones,
            fechaCreacion: new Date().toISOString(),
            solucion: ""
        };

        const reportes = obtenerReportes();
        reportes.push(nuevoReporte);
        guardarReportes(reportes);

        alert("Reporte diario guardado correctamente.");
        window.location.href = "historial-reportes.html";
    }

    function cancelarFormulario(evento) {
        evento.preventDefault();
        window.location.href = "panel-profesores.html";
    }

    protegerFormulario();
    cargarFechaYHora();
    
ControlErrores.agregarEventoSeguro(botonAgregarPc, "click", agregarPc);
ControlErrores.agregarEventoSeguro(formReporteDiario, "submit", guardarReporteDiario);
ControlErrores.agregarEventoSeguro(botonCancelar, "click", cancelarFormulario);
});