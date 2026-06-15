document.addEventListener("DOMContentLoaded", function() {
  const botonAgregar = document.getElementById("agregar-pc");
  const contenedor = document.getElementById("lista-pcs");

  botonAgregar.addEventListener("click", function() {
    // número de PC: arranca en 2 porque ya existe PC1 en el HTML
    const pcIndex = contenedor.querySelectorAll(".assignment__card").length + 1;

    // crear bloque de PC con misma estructura que el inicial
    const bloque = document.createElement("section");
    bloque.classList.add("assignment__card");
    bloque.innerHTML = `
      <label class="assignment__pc" for="pc${pcIndex}">PC${pcIndex}</label>
      <input class="assignment__input" type="text" id="pc${pcIndex}" name="pc${pcIndex}" value="PC${pcIndex}">

      <label class="assignment__label" for="alumno${pcIndex}">Nombre del alumno</label>
      <input class="assignment__input assignment__input--student" type="text" id="alumno${pcIndex}" name="alumno${pcIndex}" placeholder="Nombre del alumno">

      <button class="assignment__delete" type="button" aria-label="Eliminar asignación">✖</button>
    `;

    // agregar al contenedor
    contenedor.appendChild(bloque);

    // funcionalidad para eliminar PC
    bloque.querySelector(".assignment__delete").addEventListener("click", function() {
      contenedor.removeChild(bloque);
    });
  });
});

document.addEventListener("DOMContentLoaded", function() {
  const form = document.getElementById("form-reporte-diario");
  const contenedor = document.getElementById("lista-pcs");

  form.addEventListener("submit", function(event) {
    event.preventDefault();

    // se obtienen los datos principales del reporte
    const fecha = document.getElementById("fecha").value;
    const hora = document.getElementById("hora").value;
    const nombre = document.getElementById("nombre").value;
    const apellido = document.getElementById("apellido").value;
    const aula = document.getElementById("aula").value;

    // se obtienen todas las PCs agregadas dinámicamente
    const pcs = [];
    contenedor.querySelectorAll(".assignment__card").forEach(card => {
      const pcNombre = card.querySelector(".pc-nombre").value;
      const alumnoNombre = card.querySelector(".pc-alumno").value;

      pcs.push({
        pc: pcNombre,
        alumno: alumnoNombre
      });
    });

    // se crea el objeto del reporte
    const reporte = {
      fecha,
      hora,
      nombre,
      apellido,
      aula,
      pcs
    };

    // se guarda en localStorage
    localStorage.setItem("reporteDiario", JSON.stringify(reporte));

    alert("✅ Reporte guardado correctamente");
  });
});
