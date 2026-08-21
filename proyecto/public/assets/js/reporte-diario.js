document.addEventListener("DOMContentLoaded", function () {
    const botonAgregarPc = document.getElementById("agregar-pc");
    const listaPcs = document.getElementById("lista-pcs");
    const campoFecha = document.getElementById("fecha");
    const campoHora = document.getElementById("hora");

    // Autocompletar fecha y hora actual en el navegador
    if (campoFecha && !campoFecha.value) {
        const hoy = new Date();
        const yyyy = hoy.getFullYear();
        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
        const dd = String(hoy.getDate()).padStart(2, '0');
        campoFecha.value = `${yyyy}-${mm}-${dd}`;
    }

    if (campoHora && !campoHora.value) {
        const hoy = new Date();
        const hh = String(hoy.getHours()).padStart(2, '0');
        const mi = String(hoy.getMinutes()).padStart(2, '0');
        campoHora.value = `${hh}:${mi}`;
    }

    let contadorEquipo = 0;

    // Función para agregar una nueva fila de asignación (PC + Alumno)
    function agregarFilaEquipo() {
        if (!listaPcs) return;

        contadorEquipo++;
        const numeroPc = contadorEquipo;
        const nombrePc = `PC${numeroPc}`;

        const contenedorItem = document.createElement("article");
        contenedorItem.className = "assignment__item";
        contenedorItem.style.marginBottom = "1rem";
        contenedorItem.style.display = "flex";
        contenedorItem.style.gap = "0.75rem";
        contenedorItem.style.alignItems = "center";

        contenedorItem.innerHTML = `
            <input class="report-form__input" type="text" name="equipos[${numeroPc}][pc]" value="${nombrePc}" style="width: 90px; text-align: center; font-weight: bold;" required>
            <input class="report-form__input" type="text" name="equipos[${numeroPc}][alumno]" placeholder="Nombre del alumno" required style="flex: 1;">
            <button class="button button--secondary assignment__delete" type="button" title="Eliminar asignación" style="padding: 0.5rem 0.8rem;">×</button>
        `;

        // Evento para eliminar esta fila específica
        const botonEliminar = contenedorItem.querySelector(".assignment__delete");
        botonEliminar.addEventListener("click", function () {
            contenedorItem.remove();
        });

        listaPcs.appendChild(contenedorItem);
    }

    if (botonAgregarPc) {
        botonAgregarPc.addEventListener("click", agregarFilaEquipo);
    }
});
