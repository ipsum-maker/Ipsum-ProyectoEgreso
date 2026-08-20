// Herramientas de interfaz para evitar errores y gestionar modales
const ControlErrores = {
    // Muestra errores al usuario y en la consola del navegador
    mostrarError: function (mensaje, error) {
        alert(mensaje);
        if (error !== undefined) {
            console.error(mensaje, error);
        }
    },

    // Agrega eventos de escucha a elementos asegurándose de que existan
    agregarEventoSeguro: function (elemento, tipoEvento, funcion) {
        if (elemento === null || elemento === undefined) {
            console.error("No se pudo agregar el evento porque el elemento no existe.");
            return;
        }
        elemento.addEventListener(tipoEvento, funcion);
    },

    // Verifica la existencia de varios elementos antes de manipularlos
    validarElementos: function (elementos) {
        for (let i = 0; i < elementos.length; i++) {
            if (elementos[i].elemento === null || elementos[i].elemento === undefined) {
                console.error("Falta el elemento:", elementos[i].nombre);
                alert("Error interno: falta un elemento importante en la página.");
                return false;
            }
        }
        return true;
    },

    // Abre un modal dialog de forma segura
    abrirModalSeguro: function (modal) {
        if (modal === null || modal === undefined) {
            alert("No se pudo abrir la ventana porque no existe.");
            return;
        }

        if (typeof modal.showModal === "function") {
            modal.showModal();
        } else {
            modal.setAttribute("open", "");
        }
    },

    // Cierra un modal dialog de forma segura
    cerrarModalSeguro: function (modal) {
        if (modal === null || modal === undefined) {
            return;
        }

        if (typeof modal.close === "function") {
            modal.close();
        } else {
            modal.removeAttribute("open");
        }
    }
};
