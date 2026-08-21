document.addEventListener("DOMContentLoaded", function () {
    const btnTodos = document.getElementById("btn-filtro-todos");
    const btnDiarios = document.getElementById("btn-filtro-diarios");
    const btnIncidencias = document.getElementById("btn-filtro-incidencias");

    const tarjetasDiarios = document.querySelectorAll(".historial-card--diario");
    const tarjetasIncidencias = document.querySelectorAll(".historial-card--incidencia");

    function desmarcarFiltros() {
        if (btnTodos) btnTodos.classList.remove("historial-filters__button--active");
        if (btnDiarios) btnDiarios.classList.remove("historial-filters__button--active");
        if (btnIncidencias) btnIncidencias.classList.remove("historial-filters__button--active");
    }

    function mostrarTodos() {
        desmarcarFiltros();
        if (btnTodos) btnTodos.classList.add("historial-filters__button--active");
        tarjetasDiarios.forEach(el => el.style.display = "");
        tarjetasIncidencias.forEach(el => el.style.display = "");
    }

    function mostrarDiarios() {
        desmarcarFiltros();
        if (btnDiarios) btnDiarios.classList.add("historial-filters__button--active");
        tarjetasDiarios.forEach(el => el.style.display = "");
        tarjetasIncidencias.forEach(el => el.style.display = "none");
    }

    function mostrarIncidencias() {
        desmarcarFiltros();
        if (btnIncidencias) btnIncidencias.classList.add("historial-filters__button--active");
        tarjetasDiarios.forEach(el => el.style.display = "none");
        tarjetasIncidencias.forEach(el => el.style.display = "");
    }

    if (btnTodos) btnTodos.addEventListener("click", mostrarTodos);
    if (btnDiarios) btnDiarios.addEventListener("click", mostrarDiarios);
    if (btnIncidencias) btnIncidencias.addEventListener("click", mostrarIncidencias);
});
