<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reportes</title>
    <link rel="stylesheet" href="assets/css/panel-cordinacion.css">
    <script>
        window.IPSUM_USUARIO = <?= json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="assets/js/sesion-pestana.js"></script>
</head>

<body class="panel-cordinacion-page">

    <header class="panel-cordinacion-header" id="panel-cordinacion-header">
        <h1 class="panel-cordinacion-header__title">Panel de Asistente</h1>
        <p class="panel-cordinacion-header__email" id="usuario-email"><?= escapar(formatearCedula((string) $usuario['cedula'])) ?></p>
        <p class="panel-cordinacion-header__welcome">Bienvenido al sistema</p>

        <nav class="panel-cordinacion-nav" id="panel-cordinacion-nav">
            <a class="panel-cordinacion-nav__link" id="cerrar-sesion" href="index.php?ruta=cerrar-sesion">Cerrar sesión</a>
        </nav>
    </header>

    <main class="panel-cordinacion-main" id="panel-cordinacion-main">
        <!-- Diarios -->
        <section class="panel-cordinacion-section panel-cordinacion-section--daily" id="registros-diarios">
            <h2 class="panel-cordinacion-section__title">Ver Registros Diarios</h2>
            <h3 class="panel-cordinacion-section__subtitle">Consulta de Registros</h3>
            <p class="panel-cordinacion-section__description">Acceda a los registros diarios generados dentro del sistema</p>

                        <a class="panel-cordinacion-section__button panel-cordinacion-section__button--daily"
                id="boton-registros-diarios" href="index.php?ruta=cordinacion-diarios">
                Ver Registros Diarios
            </a>
        </section>

        <!-- Incidencias -->
        <section class="panel-cordinacion-section panel-cordinacion-section--incidents" id="registros-incidencias">
            <h2 class="panel-cordinacion-section__title">Ver Registros de Incidencias</h2>
            <h3 class="panel-cordinacion-section__subtitle">Consulta de Incidencias</h3>
            <p class="panel-cordinacion-section__description">Acceda a los registros de Incidencias reportadas en el sistema</p>

            <a class="panel-cordinacion-section__button panel-cordinacion-section__button--incidents"
                id="boton-registros-incidencias" href="index.php?ruta=cordinacion-incidencias">
                Ver Registros de Incidencias
            </a>

        </section>
    </main>

    <footer class="panel-cordinacion-footer" id="panel-cordinacion-footer">
        <p class="panel-cordinacion-footer__text">&copy; 2026 Sistema de Reportes</p>
    </footer>
<script src="assets/js/control-errores.js"></script>

    <script src="assets/js/panel-cordinacion.js"></script>
</body>

</html>
