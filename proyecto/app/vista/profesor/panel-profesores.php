<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        window.IPSUM_USUARIO = <?= json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="assets/js/sesion-pestana.js"></script>
    <script src="assets/js/control-errores.js"></script>
    <script src="assets/js/panel-profesores.js"></script>
    <link rel="stylesheet" href="assets/css/panel-profesores.css">
    <title>Sistema de Reportes</title>
</head>

<body>

    <!-- Bienvenida -->
    <header>
        <h1>Panel de Profesor</h1>
        <nav>
            <p id="usuario-email"><?= escapar(formatearCedula((string) $usuario['cedula'])) ?></p>
            <a class="logout-button" id="logout-button" href="index.php?ruta=cerrar-sesion">Cerrar Sesión</a>
        </nav>

    </header>

    <main>

        <?php if (!empty($mensaje)): ?>
            <div class="mensaje-alerta mensaje-alerta--<?= escapar((string) $mensaje['tipo']) ?>" role="status">
                <?= escapar((string) $mensaje['texto']) ?>
            </div>
        <?php endif; ?>

        <section>
            <h2>Bienvenido</h2>
            <p>Seleccione el tipo de reporte que desea generar.</p>
        </section>

        <!-- Sección de Reportes diarios -->
        <section class="reporte-diario">
            <article>
                <h2>Reporte Diario</h2>
                <span>Formulario diario sin incidencias</span>
                <a href="index.php?ruta=reporte-diario"><button class="reportediario-button" id="reportediario-button"
                        type="button">Crear Reporte Diario</button></a>
            </article>
        </section>

        <!-- Sección de Reportes de Incidencias -->
        <section class="reporte-incidencia">
            <article>
                <h2>Reporte de Incidencia</h2>
                <span>Formulario de reporte por inconvenientes o solicitudes</span>
                <a href="index.php?ruta=reporte-incidencia"><button class="reporteincidencia-button"
                        id="reporteincidencia-button" type="button">Crear Reporte</button></a>
            </article>
        </section>

        <!-- Sección de Historial de Reportes -->
        <section class="historial-reportes">
            <article>
                <h2>Mis Reportes</h2>
                <p>Consulte el historial de sus reportes.</p>
                <a href="index.php?ruta=historial-reportes"><button class="historial-button" id="historial-button"
                        type="button">Ver Todos los
                        Reportes</button></a>
            </article>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 Sistema de Reportes</p>
    </footer>

</body>

</html>
