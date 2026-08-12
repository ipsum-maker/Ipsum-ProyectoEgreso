document.addEventListener("DOMContentLoaded", function () {
    // Claves usadas en localStorage
    const STORAGE_KEY_USUARIOS = "usuarios-sistema";
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";
    const STORAGE_KEYS_REPORTES = ["reportes-sistema", "reportes"];

    // Datos del administrador inicial
    const CORREO_ADMIN_INICIAL = "ElvisLaLeyendaDeVentas@gmail.com";

    // Elementos principales del panel administrador
    const tablaUsuarios = document.querySelector("#tabla-usuarios .admin-users__tbody");
    const botonNuevoUsuario = document.getElementById("boton-nuevo-usuario");
    const botonCerrarSesion = document.getElementById("cerrar-sesion");
    const numerosIndicadores = document.querySelectorAll(".admin-stats__number");

    // Elementos del modal para crear usuarios
    const modalNuevoUsuario = document.getElementById("modal-nuevo-usuario");
    const formNuevoUsuario = document.getElementById("form-nuevo-usuario");
    const botonCerrarModalUsuario = document.getElementById("cerrar-modal-usuario");
    const botonCancelarModalUsuario = document.getElementById("cancelar-modal-usuario");
    const campoNuevoNombre = document.getElementById("nuevo-usuario-nombre");
    const campoNuevoCorreo = document.getElementById("nuevo-usuario-correo");
    const campoNuevaContrasena = document.getElementById("nuevo-usuario-contrasena");
    const campoNuevoRol = document.getElementById("nuevo-usuario-rol");

    // Elementos de reportes resueltos
    const tablaReportesResueltos = document.querySelector("#tabla-reportes-resueltos .admin-users__tbody");
    const mensajeReportesResueltos = document.getElementById("mensaje-reportes-resueltos");
    const modalDetalleResuelto = document.getElementById("modal-detalle-resuelto");
    const detalleResueltoContenido = document.getElementById("detalle-resuelto-contenido");
    const botonCerrarModalResuelto = document.getElementById("cerrar-modal-resuelto");
    const botonCerrarResuelto = document.getElementById("boton-cerrar-resuelto");

    // Obtiene el usuario activo
    function obtenerUsuarioActivo() {
    return ControlErrores.leerJson(STORAGE_KEY_USUARIO_ACTIVO, null);
}

    // Protege el panel para que solo entre un administrador
    function protegerPanelAdministrador() {
        const usuarioActivo = obtenerUsuarioActivo();

        if (usuarioActivo === null) {
            window.location.href = "login.html";
            return;
        }

        if (usuarioActivo.rol !== "administrador") {
            alert("No tenés permisos para entrar al panel de administrador.");
            window.location.href = "login.html";
        }
    }

    // Obtiene usuarios guardados
   function obtenerUsuarios() {
    return ControlErrores.leerJson(STORAGE_KEY_USUARIOS, []);
}

    // Guarda usuarios
    function guardarUsuarios(usuarios) {
    return ControlErrores.guardarJson(STORAGE_KEY_USUARIOS, usuarios);
}

    // Obtiene reportes guardados
    function obtenerReportes() {
    for (let i = 0; i < STORAGE_KEYS_REPORTES.length; i++) {
        const reportes = ControlErrores.leerJson(STORAGE_KEYS_REPORTES[i], null);

        if (Array.isArray(reportes)) {
            return reportes;
        }
    }

    return [];
}

    // Crea el administrador inicial si no existe
    function crearAdministradorInicialSiFalta() {
        const usuarios = obtenerUsuarios();

        const existeAdministradorInicial = usuarios.some(function (usuario) {
            return usuario.correo.toLowerCase() === CORREO_ADMIN_INICIAL.toLowerCase();
        });

        if (existeAdministradorInicial) {
            return;
        }

        usuarios.push({
            id: Date.now(),
            nombre: "Elvis",
            correo: CORREO_ADMIN_INICIAL,
            contrasena: "123",
            rol: "administrador"
        });

        guardarUsuarios(usuarios);
    }

    // Convierte el rol interno en texto visible
    function formatearRol(rol) {
        if (rol === "administrador") {
            return "Administrador";
        }

        if (rol === "coordinador") {
            return "Coordinador";
        }

        return "Profesor";
    }

    // Actualiza los indicadores superiores
    function actualizarIndicadores(usuarios) {
        const cantidadProfesores = usuarios.filter(function (usuario) {
            return usuario.rol === "profesor";
        }).length;

        const cantidadCoordinadores = usuarios.filter(function (usuario) {
            return usuario.rol === "coordinador";
        }).length;

        const cantidadAdministradores = usuarios.filter(function (usuario) {
            return usuario.rol === "administrador";
        }).length;

        numerosIndicadores[0].textContent = cantidadProfesores;
        numerosIndicadores[1].textContent = cantidadCoordinadores;
        numerosIndicadores[2].textContent = cantidadAdministradores;
    }

    // Crea una celda de tabla
    function crearCelda(texto) {
        const celda = document.createElement("td");
        celda.className = "admin-users__cell";
        celda.textContent = texto;

        return celda;
    }

    // Crea el selector de rol en la tabla
    function crearCeldaRol(usuario) {
        const celda = document.createElement("td");
        celda.className = "admin-users__cell";

        const selectorRol = document.createElement("select");
        selectorRol.className = "admin-users__select";

        const roles = ["administrador", "coordinador", "profesor"];

        roles.forEach(function (rol) {
            const opcion = document.createElement("option");
            opcion.value = rol;
            opcion.textContent = formatearRol(rol);
            selectorRol.appendChild(opcion);
        });

        selectorRol.value = usuario.rol;

        if (usuario.correo === CORREO_ADMIN_INICIAL) {
            selectorRol.disabled = true;
        }

        selectorRol.addEventListener("change", function () {
            cambiarRolUsuario(usuario.id, selectorRol.value);
        });

        celda.appendChild(selectorRol);

        return celda;
    }

    // Crea la celda de acciones
    function crearCeldaAcciones(usuario) {
        const celda = document.createElement("td");
        celda.className = "admin-users__cell";

        if (usuario.correo === CORREO_ADMIN_INICIAL) {
            celda.textContent = "Usuario inicial";
            return celda;
        }

        const botonEliminar = document.createElement("button");
        botonEliminar.className = "admin-users__action-button";
        botonEliminar.type = "button";
        botonEliminar.textContent = "Eliminar";

        botonEliminar.addEventListener("click", function () {
            eliminarUsuario(usuario.id);
        });

        celda.appendChild(botonEliminar);

        return celda;
    }

    // Muestra usuarios en la tabla
    function mostrarUsuarios() {
        const usuarios = obtenerUsuarios();

        tablaUsuarios.innerHTML = "";

        usuarios.forEach(function (usuario) {
            const fila = document.createElement("tr");
            fila.className = "admin-users__row";

            const celdaNombre = crearCelda(usuario.nombre);
            celdaNombre.classList.add("admin-users__cell--name");

            fila.appendChild(celdaNombre);
            fila.appendChild(crearCelda(usuario.correo));
            fila.appendChild(crearCeldaRol(usuario));
            fila.appendChild(crearCeldaAcciones(usuario));

            tablaUsuarios.appendChild(fila);
        });

        actualizarIndicadores(usuarios);
    }

    // Abre el modal de nuevo usuario
    function abrirModalNuevoUsuario() {
        formNuevoUsuario.reset();
        ControlErrores.abrirModalSeguro(modalNuevoUsuario);
    }

    // Cierra el modal de nuevo usuario
    function cerrarModalNuevoUsuario() {
       ControlErrores.cerrarModalSeguro(modalNuevoUsuario);
    }

    // Valida que el nuevo usuario sea correcto
    function validarNuevoUsuario(nombre, correo, contrasena, rol) {
        if (nombre === "" || correo === "" || contrasena === "" || rol === "") {
            alert("Complete todos los campos.");
            return false;
        }

        const usuarios = obtenerUsuarios();

        const correoExiste = usuarios.some(function (usuario) {
            return usuario.correo.toLowerCase() === correo.toLowerCase();
        });

        if (correoExiste) {
            alert("Ya existe un usuario con ese correo.");
            return false;
        }

        return true;
    }

    // Crea un nuevo usuario desde el modal
    function crearUsuario(evento) {
        evento.preventDefault();

        const nombre = campoNuevoNombre.value.trim();
        const correo = campoNuevoCorreo.value.trim();
        const contrasena = campoNuevaContrasena.value.trim();
        const rol = campoNuevoRol.value;

        if (!validarNuevoUsuario(nombre, correo, contrasena, rol)) {
            return;
        }

        const usuarios = obtenerUsuarios();

        usuarios.push({
            id: Date.now(),
            nombre: nombre,
            correo: correo,
            contrasena: contrasena,
            rol: rol
        });

        guardarUsuarios(usuarios);
        cerrarModalNuevoUsuario();
        mostrarUsuarios();

        alert("Usuario creado correctamente.");
    }

    // Elimina un usuario
    function eliminarUsuario(usuarioId) {
        const confirmar = confirm("¿Seguro que querés eliminar este usuario?");

        if (!confirmar) {
            return;
        }

        const usuarios = obtenerUsuarios();

        const usuariosActualizados = usuarios.filter(function (usuario) {
            return usuario.id !== usuarioId;
        });

        guardarUsuarios(usuariosActualizados);
        mostrarUsuarios();
    }

    // Cambia el rol de un usuario
    function cambiarRolUsuario(usuarioId, nuevoRol) {
        const usuarios = obtenerUsuarios();

        const usuariosActualizados = usuarios.map(function (usuario) {
            if (usuario.id === usuarioId) {
                usuario.rol = nuevoRol;
            }

            return usuario;
        });

        guardarUsuarios(usuariosActualizados);
        mostrarUsuarios();
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

    // Convierte el tipo de solicitud en texto visible
    function formatearTipoSolicitud(reporte) {
        const tipoSolicitud = obtenerValor(reporte, ["tipoSolicitud"], "");
        const otroTipo = obtenerValor(reporte, ["otroTipo"], "");

        if (tipoSolicitud === "rotura-equipo") {
            return "Rotura de equipo";
        }

        if (tipoSolicitud === "falla-equipo") {
            return "Falla de equipo";
        }

        if (otroTipo !== "") {
            return otroTipo;
        }

        return "Otro";
    }

    // Obtiene únicamente incidencias resueltas
    function obtenerReportesResueltos() {
        const reportes = obtenerReportes();

        return reportes.filter(function (reporte) {
            return reporte.tipo === "incidencia" && reporte.estado === "resuelto";
        });
    }

    // Crea botón para ver detalle del reporte resuelto
    function crearBotonVerResuelto(reporte) {
        const boton = document.createElement("button");
        boton.className = "admin-users__action-button";
        boton.type = "button";
        boton.textContent = "Ver";

        boton.addEventListener("click", function () {
            abrirDetalleResuelto(reporte);
        });

        return boton;
    }

    // Muestra reportes resueltos
    function mostrarReportesResueltos() {
        const reportesResueltos = obtenerReportesResueltos();

        tablaReportesResueltos.innerHTML = "";

        if (reportesResueltos.length === 0) {
            mensajeReportesResueltos.style.display = "block";
            return;
        }

        mensajeReportesResueltos.style.display = "none";

        reportesResueltos.forEach(function (reporte) {
            const fila = document.createElement("tr");
            fila.className = "admin-users__row";

            const nombre = obtenerValor(reporte, ["nombreProfesor", "nombre"], "");
            const apellido = obtenerValor(reporte, ["apellidoProfesor", "apellido"], "");
            const fecha = obtenerValor(reporte, ["fecha"], "");
            const taller = obtenerValor(reporte, ["taller", "aula"], "");
            const tecnico = obtenerValor(reporte, ["tecnico"], "Coordinación");
            const docente = `${nombre} ${apellido}`;

            fila.appendChild(crearCelda(fecha));
            fila.appendChild(crearCelda(docente));
            fila.appendChild(crearCelda(formatearTipoSolicitud(reporte)));
            fila.appendChild(crearCelda(taller));
            fila.appendChild(crearCelda(tecnico));

            const celdaVer = document.createElement("td");
            celdaVer.className = "admin-users__cell";
            celdaVer.appendChild(crearBotonVerResuelto(reporte));

            fila.appendChild(celdaVer);
            tablaReportesResueltos.appendChild(fila);
        });
    }

    // Crea párrafos de detalle
    function crearParrafoDetalle(etiqueta, valor) {
        const parrafo = document.createElement("p");
        parrafo.textContent = `${etiqueta}: ${valor}`;

        return parrafo;
    }

    // Abre el modal con el detalle de un reporte resuelto
    function abrirDetalleResuelto(reporte) {
        detalleResueltoContenido.innerHTML = "";

        const nombre = obtenerValor(reporte, ["nombreProfesor", "nombre"], "");
        const apellido = obtenerValor(reporte, ["apellidoProfesor", "apellido"], "");
        const fecha = obtenerValor(reporte, ["fecha"], "");
        const taller = obtenerValor(reporte, ["taller", "aula"], "");
        const descripcion = obtenerValor(reporte, ["descripcion"], "");
        const solucion = obtenerValor(reporte, ["solucion"], "");
        const tecnico = obtenerValor(reporte, ["tecnico"], "Coordinación");
        const docente = `${nombre} ${apellido}`;

        detalleResueltoContenido.appendChild(crearParrafoDetalle("Fecha", fecha));
        detalleResueltoContenido.appendChild(crearParrafoDetalle("Docente", docente));
        detalleResueltoContenido.appendChild(crearParrafoDetalle("Tipo", formatearTipoSolicitud(reporte)));
        detalleResueltoContenido.appendChild(crearParrafoDetalle("Taller / Aula", taller));
        detalleResueltoContenido.appendChild(crearParrafoDetalle("Descripción", descripcion));
        detalleResueltoContenido.appendChild(crearParrafoDetalle("Solución aplicada", solucion));
        detalleResueltoContenido.appendChild(crearParrafoDetalle("Técnico", tecnico));

      ControlErrores.abrirModalSeguro(modalDetalleResuelto);
    }

    // Cierra el modal de detalle resuelto
    function cerrarDetalleResuelto() {
        ControlErrores.cerrarModalSeguro(modalNuevoUsuario);
    }

    // Cierra sesión
    function cerrarSesion(evento) {
        evento.preventDefault();
        localStorage.removeItem(STORAGE_KEY_USUARIO_ACTIVO);
        window.location.href = "login.html";
    }

    // Inicio del panel administrador
    crearAdministradorInicialSiFalta();
    protegerPanelAdministrador();
    mostrarUsuarios();
    mostrarReportesResueltos();

    // Eventos del modal de usuario
    botonNuevoUsuario.addEventListener("click", abrirModalNuevoUsuario);
    botonCerrarModalUsuario.addEventListener("click", cerrarModalNuevoUsuario);
    botonCancelarModalUsuario.addEventListener("click", cerrarModalNuevoUsuario);
    formNuevoUsuario.addEventListener("submit", crearUsuario);

    // Eventos del modal de reporte resuelto
    botonCerrarModalResuelto.addEventListener("click", cerrarDetalleResuelto);
    botonCerrarResuelto.addEventListener("click", cerrarDetalleResuelto);

    // Evento de cerrar sesión
    botonCerrarSesion.addEventListener("click", cerrarSesion);
});