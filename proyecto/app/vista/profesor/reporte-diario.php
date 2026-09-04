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
        <form class="report-form" id="form-reporte-diario" action="index.php?ruta=guardar-reporte-diario" method="post">
            <input type="hidden" name="csrf_token" value="<?= escapar(obtenerTokenCSRF()) ?>">

            <article class="report-form__group">
                <label class="report-form__label" for="fecha">Fecha</label>
                <input class="report-form__input" type="date" id="fecha" name="fecha" readonly>
            </article>

            <!-- Turno -->
            <article class="report-form__group">
                <label class="report-form__label">Turno</label>
                <div style="display: flex; gap: 1.5rem; margin-top: 0.25rem;">
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="radio" name="turno" value="matutino" checked> Matutino
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="radio" name="turno" value="vespertino"> Vespertino
                    </label>
                    <label style="display: flex; align-items: center; gap: 0.4rem; cursor: pointer;">
                        <input type="radio" name="turno" value="nocturno"> Nocturno
                    </label>
                </div>
            </article>

            <!-- Horarios Entrada y Salida -->
            <section class="report-form__row" style="display: flex; gap: 1rem;">
                <article class="report-form__group" style="flex: 1;">
                    <label class="report-form__label" for="hora_entrada">Hora de Entrada</label>
                    <input class="report-form__input" type="time" id="hora_entrada" name="hora_entrada" required>
                </article>

                <article class="report-form__group" style="flex: 1;">
                    <label class="report-form__label" for="hora_salida">Hora de Salida</label>
                    <input class="report-form__input" type="time" id="hora_salida" name="hora_salida" required>
                </article>
            </section>

            <!-- Grupo y Asignatura -->
            <section class="report-form__row" style="display: flex; gap: 1rem;">
                <article class="report-form__group" style="flex: 1;">
                    <label class="report-form__label" for="grupo">Grupo</label>
                    <input class="report-form__input" type="text" id="grupo" name="grupo" placeholder="Ej: 3°MC" required>
                </article>

                <article class="report-form__group" style="flex: 1;">
                    <label class="report-form__label" for="asignatura">Asignatura</label>
                    <input class="report-form__input" type="text" id="asignatura" name="asignatura" placeholder="Ej: Programación" required>
                </article>
            </section>
            
            <section class="report-form__row">
                <article class="report-form__group" style="flex: 1;">
                    <label class="report-form__label">Docente Responsable</label>
                    <input class="report-form__input" type="text" value="<?= escapar((string) $usuario['nombre']) ?>" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                </article>
            </section>

            <article class="report-form__group">
                <label class="report-form__label" for="aula">Aula / Taller / Laboratorio</label>
                <select class="report-form__select" id="aula" name="aula_id" required>
                    <option value="">Seleccione el aula o taller</option>
                    <?php foreach ($aulas as $aulaItem): ?>
                        <option value="<?= (int) $aulaItem['id'] ?>">
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
            </section>

            <footer class="report-form__actions">
                <button class="button button--primary" type="submit">Guardar Reporte</button>
                <a class="button button--secondary" href="index.php?ruta=panel-profesores" style="text-decoration: none; text-align: center; display: inline-block;">Cancelar</a>
            </footer>
        </form>



    </main>

</body>

</html>
