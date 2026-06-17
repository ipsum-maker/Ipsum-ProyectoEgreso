document.addEventListener("DOMContentLoaded", function () {
    // Clave donde se guardan todos los reportes
    const STORAGE_KEY_REPORTES = "reportes";

    // Elementos principales del HTML
    const formReporteDiario = document.getElementById("form-reporte-diario");
    const botonAgregarPc = document.getElementById("agregar-pc");
    const listaPcs = document.getElementById("lista-pcs");
    const campoFecha = document.getElementById("fecha");
    const campoHora = document.getElementById("hora");
    const botonCancelar = document.querySelector(".button--secondary");

    // Contador para crear PC1, PC2, PC3, etc.
    let contadorPc = 0;

    // Obtiene la fecha actual en formato correcto para input type="date"
    function obtenerFechaActual() {
        const fechaActual = new Date();

        const anio = fechaActual.getFullYear();
        const mes = String(fechaActual.getMonth() + 1).padStart(2, "0");
        const dia = String(fechaActual.getDate()).padStart(2, "0");

        return `${anio}-${mes}-${dia}`;
    }

    // Obtiene la hora actual en formato correcto para input type="time"
    function obtenerHoraActual() {
        const fechaActual = new Date();

        const horas = String(fechaActual.getHours()).padStart(2, "0");
        const minutos = String(fechaActual.getMinutes()).padStart(2, "0");

        return `${horas}:${minutos}`;
    }

    // Rellena automáticamente fecha y hora
    function cargarFechaYHoraAutomatica() {
        campoFecha.value = obtenerFechaActual();
        campoHora.value = obtenerHoraActual();

        campoFecha.readOnly = true;
        campoHora.readOnly = true;
    }

    // Agrega una nueva PC al reporte
    function agregarPc() {
        contadorPc++;

        const bloquePc = document.createElement("section");

        // Se conserva la misma clase de diseño
        bloquePc.classList.add("assignment__card");

        // El input de PC NO tiene readonly, así podés modificar PC1, PC2, etc.
        bloquePc.innerHTML = `
            <label class="assignment__pc" for="pc${contadorPc}">PC${contadorPc}</label>

            <input class="assignment__input pc-nombre"
                   type="text"
                   id="pc${contadorPc}"
                   name="pc${contadorPc}"
                   value="PC${contadorPc}">

            <label class="assignment__label" for="alumno${contadorPc}">Nombre del alumno</label>

            <input class="assignment__input pc-alumno"
                   type="text"
                   id="alumno${contadorPc}"
                   name="alumno${contadorPc}"
                   placeholder="Nombre del alumno">

            <button class="assignment__delete" type="button" aria-label="Eliminar asignación">✖</button>
        `;

        listaPcs.appendChild(bloquePc);

        const botonEliminar = bloquePc.querySelector(".assignment__delete");

        botonEliminar.addEventListener("click", function () {
            listaPcs.removeChild(bloquePc);
        });
    }

    // Obtiene todas las PCs agregadas
    function obtenerPcsAsignadas() {
        const pcs = [];

        listaPcs.querySelectorAll(".assignment__card").forEach(function (bloquePc) {
            const campoPc = bloquePc.querySelector(".pc-nombre");
            const campoAlumno = bloquePc.querySelector(".pc-alumno");

            pcs.push({
                pc: campoPc.value,
                alumno: campoAlumno.value
            });
        });

        return pcs;
    }

    // Obtiene reportes ya guardados
    function obtenerReportesGuardados() {
        const reportesGuardados = localStorage.getItem(STORAGE_KEY_REPORTES);

        if (reportesGuardados) {
            return JSON.parse(reportesGuardados);
        }

        return [];
    }

    // Guarda el reporte diario junto con los demás reportes
    function guardarReporteDiario(reporteDiario) {
        const reportesGuardados = obtenerReportesGuardados();

        reportesGuardados.push(reporteDiario);

        localStorage.setItem(STORAGE_KEY_REPORTES, JSON.stringify(reportesGuardados));
    }

    // Carga fecha y hora apenas abre la página
    cargarFechaYHoraAutomatica();

    // Botón Agregar PC
    botonAgregarPc.addEventListener("click", function () {
        agregarPc();
    });

    // Botón Cancelar
    botonCancelar.addEventListener("click", function (event) {
        event.preventDefault();
        window.location.href = "panel-profesores.html";
    });

    formReporteDiario.addEventListener("submit", function (event) {
    event.preventDefault();

    // Se obtienen las PCs agregadas
    const pcsAsignadas = obtenerPcsAsignadas();

    // Si no hay ninguna PC agregada, no se permite guardar
    if (pcsAsignadas.length === 0) {
        alert("⚠️ Informe quien esta asignado a la PC antes de enviar el reporte");
        return;
    }

    const reporteDiario = {
        tipo: "reporte-diario",
        fecha: campoFecha.value,
        hora: campoHora.value,
        pcs: pcsAsignadas
    };

    guardarReporteDiario(reporteDiario);
        alert("✅ Reporte diario guardado correctamente");

        formReporteDiario.reset();
        listaPcs.innerHTML = "";
        contadorPc = 0;

        cargarFechaYHoraAutomatica();
    });
});