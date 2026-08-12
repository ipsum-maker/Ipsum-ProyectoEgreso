document.addEventListener("DOMContentLoaded", function () {
    const STORAGE_KEY_USUARIOS = "usuarios-sistema";
    const STORAGE_KEY_USUARIO_ACTIVO = "usuario-activo";

    const formLogin = document.getElementById("login-form");
    const campoCorreo = document.getElementById("correo");
    const campoContrasena = document.getElementById("contrasena");

    function obtenerUsuarios() {
    return ControlErrores.leerJson(STORAGE_KEY_USUARIOS, []);
}
function guardarUsuarios(usuarios) {
    return ControlErrores.guardarJson(STORAGE_KEY_USUARIOS, usuarios);
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

        ControlErrores.guardarJson(STORAGE_KEY_USUARIO_ACTIVO, usuarioActivo);
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

   ControlErrores.agregarEventoSeguro(formLogin, "submit", iniciarSesion);
});