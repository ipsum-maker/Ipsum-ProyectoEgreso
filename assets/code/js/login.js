document.addEventListener("DOMContentLoaded", function () {
    const STORAGE_KEY_USUARIOS = "usuarios-sistema";
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";

    const formLogin = document.getElementById("login-form");
    const campoCorreo = document.getElementById("correo");
    const campoContrasena = document.getElementById("contrasena");

    function obtenerUsuarios() {
        const usuariosGuardados = localStorage.getItem(STORAGE_KEY_USUARIOS);

        if (usuariosGuardados === null) {
            return [];
        }

        return JSON.parse(usuariosGuardados);
    }

    function guardarUsuarios(usuarios) {
        localStorage.setItem(STORAGE_KEY_USUARIOS, JSON.stringify(usuarios));
    }

    function crearAdministradorInicial() {
        const usuarios = obtenerUsuarios();

        if (usuarios.length > 0) {
            return;
        }

        const administradorInicial = {
            id: Date.now(),
            nombre: "Elvis",
            correo: "ElvisLaLeyendaDeVentas@gmail.com",
            contrasena: "123",
            rol: "administrador"
        };

        guardarUsuarios([administradorInicial]);
    }

    function guardarUsuarioActivo(usuario) {
        const usuarioActivo = {
            id: usuario.id,
            nombre: usuario.nombre,
            correo: usuario.correo,
            rol: usuario.rol
        };

        localStorage.setItem(STORAGE_KEY_USUARIO_ACTIVO, JSON.stringify(usuarioActivo));
    }

    function redirigirSegunRol(rol) {
        if (rol === "administrador") {
            window.location.href = "panel-administrador.html";
            return;
        }

        if (rol === "coordinador") {
            window.location.href = "panel-cordinacion.html";
            return;
        }

        if (rol === "profesor") {
            window.location.href = "panel-profesores.html";
            return;
        }

        alert("El rol del usuario no es válido.");
    }

    function iniciarSesion(evento) {
        evento.preventDefault();

        const correoIngresado = campoCorreo.value.trim();
        const contrasenaIngresada = campoContrasena.value.trim();

        const usuarios = obtenerUsuarios();

        const usuarioEncontrado = usuarios.find(function (usuario) {
            return usuario.correo.toLowerCase() === correoIngresado.toLowerCase()
                && usuario.contrasena === contrasenaIngresada;
        });

        if (usuarioEncontrado === undefined) {
            alert("Correo o contraseña incorrectos.");
            return;
        }

        guardarUsuarioActivo(usuarioEncontrado);
        redirigirSegunRol(usuarioEncontrado.rol);
    }

    crearAdministradorInicial();

    if (formLogin !== null) {
        formLogin.addEventListener("submit", iniciarSesion);
    }
});