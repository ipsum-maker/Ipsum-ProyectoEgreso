document.addEventListener("DOMContentLoaded", function () {
    "use strict";
    const formulario = document.getElementById("form-solicitud-laboratorio");
    if (!formulario) return;
    const aula = document.getElementById("solicitud-aula");
    const fecha = document.getElementById("solicitud-fecha");
    const inicio = document.getElementById("solicitud-inicio");
    const fin = document.getElementById("solicitud-fin");
    const mensaje = document.getElementById("agenda-mensaje");
    const lista = document.getElementById("agenda-ocupados");
    let ocupados = [];
    let consultaActual = 0;

    function validarHorario() {
        inicio.setCustomValidity("");
        fin.setCustomValidity("");
        if (!inicio.value || !fin.value) return;
        if (fin.value <= inicio.value) {
            fin.setCustomValidity("La hora de fin debe ser posterior a la hora de inicio.");
            return;
        }
        const seSuperpone = ocupados.some(function (horario) {
            const hInicio = (horario.hora_inicio || "").slice(0, 5);
            const hFin = (horario.hora_fin || "").slice(0, 5);
            return inicio.value < hFin && fin.value > hInicio;
        });
        if (seSuperpone) {
            inicio.setCustomValidity("Ese horario coincide con un uso ya concedido. Elija otro intervalo.");
        }
    }

    async function consultarDisponibilidad() {
        const consulta = ++consultaActual;
        ocupados = [];
        lista.replaceChildren();
        validarHorario();
        if (!aula.value || !fecha.value) {
            mensaje.textContent = "Seleccione un espacio y una fecha para consultar los horarios ocupados.";
            return;
        }
        mensaje.textContent = "Consultando horarios concedidos…";
        try {
            const respuesta = await window.IpsumApi.solicitar("/solicitudes-laboratorio/disponibilidad", {
                query: { aula_id: aula.value, fecha: fecha.value }
            });
            if (consulta !== consultaActual) return;
            const resData = respuesta.data || respuesta.datos || {};
            ocupados = resData.ocupados || [];
            mensaje.textContent = ocupados.length ? "Estos intervalos ya están ocupados:" : "No hay horarios ocupados para este espacio y fecha. Su solicitud aún requerirá aprobación.";
            ocupados.forEach(function (horario) {
                const item = document.createElement("li");
                const hInicio = (horario.hora_inicio || "").slice(0, 5);
                const hFin = (horario.hora_fin || "").slice(0, 5);
                item.textContent = hInicio + " a " + hFin;
                lista.appendChild(item);
            });
            validarHorario();
        } catch (error) {
            if (consulta !== consultaActual) return;
            mensaje.textContent = error.message + " Al enviar, el sistema comprobará nuevamente la disponibilidad.";
        }
    }

    if (aula) aula.addEventListener("change", consultarDisponibilidad);
    if (fecha) fecha.addEventListener("change", consultarDisponibilidad);
    if (inicio) inicio.addEventListener("input", validarHorario);
    if (fin) fin.addEventListener("input", validarHorario);

    consultarDisponibilidad();
});
