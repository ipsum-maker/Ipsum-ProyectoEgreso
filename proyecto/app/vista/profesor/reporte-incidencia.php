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
            <input type="hidden" name="csrf_token" value="<?= escapar(obtenerTokenCSRF()) ?>">

            <article class="report-form__group">
                <label class="report-form__label">Docente Responsable</label>
                <input class="report-form__input" type="text" value="<?= escapar((string) $usuario['nombre']) ?>" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
            </article>

            <label class="report-form__label" for="asignatura">Asignatura</label>
            <input class="report-form__input" type="text" name="asignatura" id="asignatura" placeholder="Ej: Full Stack, Redes, Base de Datos" required>

            <section class="report-form__group" style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                <div style="flex: 1;">
                    <label class="report-form__label" for="grupo">Grupo</label>
                    <input class="report-form__input" type="text" name="grupo" id="grupo" placeholder="Ej: 3°MC" required>
                </div>

                <div style="flex: 1;">
                    <label class="report-form__label" for="turno">Turno</label>
                    <select class="report-form__select" name="turno" id="turno" required>
                        <option value="matutino">Matutino</option>
                        <option value="vespertino">Vespertino</option>
                        <option value="nocturno">Nocturno</option>
                    </select>
                </div>
            </section>

            <article class="report-form__group">
                <label class="report-form__label" for="fecha-incidencia">Fecha de la Incidencia</label>
                <input class="report-form__input" type="date" name="fecha" id="fecha-incidencia" readonly>

                <label class="report-form__label" for="hora-reporte">Hora de la Apertura del Reporte</label>
                <input class="report-form__input" type="time" name="hora" id="hora-reporte" readonly>
            </article>

            <label class="report-form__label" for="tipoSolicitud">Tipo de solicitud</label>
            <select class="report-form__select" name="tipoSolicitud" id="tipoSolicitud" required>
                <option value="">Seleccione una opción</option>
                <option value="rotura-equipo">Rotura de equipo</option>
                <option value="falla-equipo">Falla de equipo</option>
                <option value="otro">Otro</option>
            </select>

            <label class="report-form__label" for="otroTipo">Especifique el tipo de incidencia</label>
            <input class="report-form__input" type="text" name="otroTipo" id="otroTipo" placeholder="Describa el tipo de incidencia">

            <label class="report-form__label" for="taller">Laboratorio / Taller</label>
            <select class="report-form__select" name="aula_id" id="taller" required>
                <option value="">Seleccione el laboratorio o taller</option>
                <?php foreach ($aulas as $aulaItem): ?>
                    <option value="<?= (int) $aulaItem['id'] ?>">
                        <?= escapar((string) $aulaItem['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="report-form__label" for="descripcion">Descripción Detallada</label>
            <textarea class="report-form__textarea" name="descripcion" id="descripcion" rows="5" placeholder="Describa con detalle la incidencia ocurrida, equipos afectados, circunstancias, etc."></textarea>

            <footer class="report-form__footer">
                <button class="report-form__button report-form__button--submit" type="submit">Enviar Reporte</button>
                <a class="report-form__button report-form__button--reset" href="index.php?ruta=panel-profesores" style="text-decoration: none; text-align: center; display: inline-block;">Cancelar</a>
            </footer>
        </form>

    </main>

</body>

</html>
