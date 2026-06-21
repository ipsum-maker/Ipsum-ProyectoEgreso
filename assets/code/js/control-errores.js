// Herramientas  para evitar que el sistema se rompa por errores comunes
const ControlErrores = {
    // Muestra errores al usuario y también los deja en consola para poder revisarlos
    mostrarError: function (mensaje, error) {
        alert(mensaje);

        if (error !== undefined) {
            console.error(mensaje, error);
        }
    },

    // Lee datos JSON desde localStorage sin romper la página si el dato está dañado
    leerJson: function (clave, valorPorDefecto) {
        try {
            const datosGuardados = localStorage.getItem(clave);

            if (datosGuardados === null || datosGuardados === "") {
                return valorPorDefecto;
            }

            return JSON.parse(datosGuardados);
        } catch (error) {
            console.error("Error al leer localStorage:", error);
            alert("Hubo un problema al leer información guardada. Se cargará la página sin esos datos.");
            return valorPorDefecto;
        }
    },

    // Guarda datos JSON en localStorage sin romper la página si ocurre un error
    guardarJson: function (clave, datos) {
        try {
            localStorage.setItem(clave, JSON.stringify(datos));
            return true;
        } catch (error) {
            console.error("Error al guardar en localStorage:", error);
            alert("No se pudo guardar la información. Revise el almacenamiento del navegador.");
            return false;
        }
    },

    // Evita errores cuando un botón, formulario o elemento no existe en el HTML
    agregarEventoSeguro: function (elemento, tipoEvento, funcion) {
        if (elemento === null) {
            console.error("No se pudo agregar el evento porque el elemento no existe.");
            return;
        }

        elemento.addEventListener(tipoEvento, funcion);
    },

    // Verifica que existan elementos importantes antes de usar value, textContent, etc.
    validarElementos: function (elementos) {
        for (let i = 0; i < elementos.length; i++) {
            if (elementos[i].elemento === null) {
                console.error("Falta el elemento:", elementos[i].nombre);
                alert("Error interno: falta un elemento importante en la página.");
                return false;
            }
        }

        return true;
    },

    // Abre un modal dialog de forma segura
    abrirModalSeguro: function (modal) {
        if (modal === null) {
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
        if (modal === null) {
            return;
        }

        if (typeof modal.close === "function") {
            modal.close();
        } else {
            modal.removeAttribute("open");
        }
    }
};