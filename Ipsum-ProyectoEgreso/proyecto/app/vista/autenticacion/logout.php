<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrando sesión</title>
</head>
<body>
    <script>
        sessionStorage.removeItem("usuario-activo");
        sessionStorage.removeItem("ipsum-sesion-pestana");
        localStorage.removeItem("usuario-activo");
        window.location.replace("index.php?ruta=login");
    </script>
    <p><a href="index.php?ruta=login">Volver al inicio de sesión</a></p>
</body>
</html>
