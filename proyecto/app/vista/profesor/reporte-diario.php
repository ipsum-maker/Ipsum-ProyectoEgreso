<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        window.IPSUM_USUARIO = <?= json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <title>Reporte Diario</title>
    <link rel="stylesheet" href="assets/css/reporte-diario.css">
    <script src="assets/js/control-errores.js"></script>
    <script src="assets/js/reporte-diario.js"></script>
</head>

<body class="page">
    <header class="page-header">
        <nav class="page-header_nav">
            <a class="page-header__link" href="index.php?ruta=panel-profesores"> ← Volver al Panel</a>


    </header>
    <main class="report container">
        <section class="report__intro">
            <h1 class="report__title">Reporte Diario</h1>
            <p class="report__description">Complete la información del reporte diario de actividades</p>
        </section>
        <form class="report-form" id="form-reporte-diario" action="index.php? ruta=guardar-reporte-diario" method="post">
            <article class="report-form__group">
                <label class="report-form__label" for="fecha">Fecha</label>
                <input class="report-form__input" type="date" id="fecha" name="fecha" readonly>
            </article>

            <article class="report-form__group">
                <label class="report-form__label" for="hora">Hora</label>
                <input class="report-form__input" type="time" id="hora" name="hora" readonly>
            </article><section class="report-form__row">
    <article class="report-form__group">
        <label class="report-form__label" for="nombre-profesor">Nombre del Profesor</label>
        <input class="report-form__input" type="text" id="nombre-profesor" name="nombre-profesor" placeholder="Nombre">
    </article>

    <article class="report-form__group">
        <label class="report-form__label" for="apellido-profesor">Apellido del Profesor</label>
        <input class="report-form__input" type="text" id="apellido-profesor" name="apellido-profesor" placeholder="Apellido">
    </article>
</section>

<article class="report-form__group">
    <label class="report-form__label" for="aula">Aula / Taller / Laboratorio</label>
    <select class="report-form__select" id="aula" name="aula" required>
        <option value="">Seleccione el aula o taller</option>
        <?php foreach ($aulas as $aulaItem): ?>
            <option value="<?= escapar((string) $aulaItem['codigo']) ?>">
                <?= escapar((string) $aulaItem['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</article>

            <!-- Botón Agregar PC -->
            <header class="assignment__header">
                <h2 class="assignment__title">Asignación de Equipos</h2>
                <button class="button button--add" type="button" id="agregar-pc">+ Agregar PC</button>
            </header>

            <!-- Contenedor dinámico de PCs -->
            <section class="assignment__card" id="lista-pcs">
                <!--<label class="assignment__pc" for="pc1">PC1</label>
                <input class="assignment__input" type="text" id="pc1" name="pc1" value="PC1">

                <label class="assignment__label" for="alumno1">Nombre del alumno</label>
                <input class="assignment__input assignment__input--student" type="text" id="alumno1" name="alumno1"
                    placeholder="Nombre del alumno">

                <button class="assignment__delete" type="button" aria-label="Eliminar asignación">×</button>-->
            </section>

            <footer class="report-form__actions">
                <button class="button button--primary" type="submit">Guardar Reporte</button>
                <button class="button button--secondary" type="reset">Cancelar</button>
            </footer>
        </form>


    </main>

</body>

</html>
