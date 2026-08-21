<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        window.IPSUM_USUARIO = <?= json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <title>Reporte de Incidencias</title>
    <link rel="stylesheet" href="assets/css/reporte-incidencia.css">
    <script src="assets/js/control-errores.js"></script>
    <script src="assets/js/reporte-incidencia.js"></script>
</head>

<body class="report-page">

    <!--Encabezado-->

    <header class="report-header">
        <a class="report-header__link" href="index.php?ruta=panel-profesores">← Volver al Panel</a>
        <h1 class="report-header__title">Reporte de Incidencias</h1>
    </header>

    <!--Contenido principal-->

    <main class="report-main">
        <form class="report-form" id="form-reporte-incidencia" action="index.php?ruta=guardar-reporte-incidencia" method="post">

            <article class="report-form__group">
                <label class="report-form__label" for="nombre-profesor">Nombre del Profesor</label>
                <input class="report-form__input" type="text" name="nombre" id="nombre-profesor" placeholder="Ej: Juan">

                <label class="report-form__label" for="apellido-profesor">Apellido del Profesor</label>
                <input class="report-form__input" type="text" name="apellido" id="apellido-profesor" placeholder="Ej: García">
            </article>

            <label class="report-form__label" for="asignatura">Asignatura</label>
            <input class="report-form__input" type="text" name="asignatura" id="asignatura" placeholder="Ej: Full Stack, Redes, Base de Datos">

            <article class="report-form__group">
                <!-- ERROR: el atributo for="fecha" no coincide con el id="fecha-incidencia". Para corregirlo debería ser for="fecha-incidencia" -->
                <label class="report-form__label" for="fecha-incidencia">Fecha de la Incidencia</label>
                <input class="report-form__input" type="date" name="fecha" id="fecha-incidencia" readonly>

                <!-- ERROR: el atributo for="hora" no coincide con el id="hora-reporte". Para corregirlo debería ser for="hora-reporte" -->
                <label class="report-form__label" for="hora-reporte">Hora de la Apertura del Reporte</label>
                <input class="report-form__input" type="time" name="hora" id="hora-reporte" readonly>
            </article>

            <!-- ERROR: según el reglamento, los nombres deberían usar kebab-case. tipoSolicitud debería ser tipo-solicitud -->
            <label class="report-form__label" for="tipoSolicitud">Tipo de solicitud</label>
            <select class="report-form__select" name="tipoSolicitud" id="tipoSolicitud">
                <option value="">Seleccione una opción</option>
                <option value="rotura-equipo">Rotura de equipo</option>
                <option value="falla-equipo">Falla de equipo</option>
                <option value="otro">Otro</option>
            </select>

            <!-- ERROR: según el reglamento, los nombres deberían usar kebab-case. otroTipo debería ser otro-tipo -->
            <label class="report-form__label" for="otroTipo">Especifique el tipo de incidencia</label>
            <input class="report-form__input" type="text" name="otroTipo" id="otroTipo" placeholder="Describa el tipo de incidencia">

            <label class="report-form__label" for="taller">Laboratorio / Taller</label>
            <select class="report-form__select" name="taller" id="taller" required>
                <option value="">Seleccione el laboratorio o taller</option>
                <?php foreach ($aulas as $aulaItem): ?>
                    <option value="<?= escapar((string) $aulaItem['codigo']) ?>">
                        <?= escapar((string) $aulaItem['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="report-form__label" for="descripcion">Descripción Detallada</label>
            <textarea class="report-form__textarea" name="descripcion" id="descripcion" rows="5" placeholder="Describa con detalle la incidencia ocurrida, equipos afectados, circunstancias, etc."></textarea>

            <footer class="report-form__footer">
                <button class="report-form__button report-form__button--submit" type="submit">Enviar Reporte</button>
                <button class="report-form__button report-form__button--reset" type="reset">Cancelar</button>
            </footer>

        </form>
    </main>

</body>

</html>
