(function () {
    "use strict";

    // El script vive en public/assets/js: esta base funciona tanto en htdocs como en VirtualHost
    const basePublica = new URL("../../", document.currentScript.src);
    let csrfToken = "";

    function escapar(valor) {
        return String(valor == null ? "" : valor).replace(/[&<>"']/g, function (caracter) {
            return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[caracter];
        });
    }

    function tokenDePagina(formulario) {
        const campo = formulario && formulario.querySelector('[name="csrf_token"]');
        const meta = document.querySelector('meta[name="csrf-token"]');
        return csrfToken || (campo && campo.value) || (meta && meta.content) || "";
    }

    async function solicitar(recurso, opciones) {
        opciones = opciones || {};
        const metodo = (opciones.method || "GET").toUpperCase();
        const url = new URL("api/index.php", basePublica);
        url.searchParams.set("r", recurso);

        if (opciones.query) {
            Object.entries(opciones.query).forEach(function (entrada) {
                if (entrada[0] !== "r") {
                    url.searchParams.set(entrada[0], String(entrada[1]));
                }
            });
        }

        const cabeceras = { Accept: "application/json" };
        if (metodo !== "GET") {
            let token = opciones.csrfToken || tokenDePagina();
            if (!token) {
                try {
                    const sesion = await solicitar("/sesion");
                    token = sesion.data && sesion.data.csrfToken ? sesion.data.csrfToken : "";
                } catch (e) {}
            }
            if (token) {
                cabeceras["X-CSRF-Token"] = token;
            }
            cabeceras["Content-Type"] = "application/json";
        }

        let respuesta;
        try {
            respuesta = await fetch(url.href, {
                method: metodo,
                credentials: "same-origin",
                headers: cabeceras,
                body: metodo === "GET" ? undefined : JSON.stringify(opciones.body === undefined ? {} : opciones.body)
            });
        } catch (error) {
            throw new Error("No se pudo conectar con el servidor. Intente nuevamente.");
        }

        let datos;
        try {
            datos = await respuesta.json();
        } catch (error) {
            throw new Error("El servidor no devolvió una respuesta JSON válida.");
        }

        const esExitoso = datos.status === "success";
        if (!respuesta.ok || !esExitoso) {
            const error = new Error(datos.message || "No se pudo completar la solicitud.");
            error.codigo = respuesta.status;
            error.status = respuesta.status;
            throw error;
        }

        if (datos.data && datos.data.csrfToken) {
            csrfToken = datos.data.csrfToken;
        }

        return datos;
    }

    window.IpsumApi = {
        solicitar: solicitar,
        escapar: escapar,
        tokenDePagina: tokenDePagina,
        basePublica: basePublica
    };
})();
