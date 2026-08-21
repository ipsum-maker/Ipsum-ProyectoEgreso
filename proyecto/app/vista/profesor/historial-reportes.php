<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/historial-reportes.css">
    <script src="assets/js/control-errores.js"></script>
    <script src="assets/js/historial-reportes.js"></script>
    <title>Sistema de Reportes - Ipsum</title>
</head>

<body>
    <header class="historial-header">
        <nav class="historial-header__nav">
            <a class="historial-header__link-back" id="btn-volver" href="index.php?ruta=panel-profesores">
                <span class="historial-header__icon-back">&larr;</span> Volver al Panel
            </a>
        </nav>
        <h1 class="historial-header__title" id="historial-title">Mis Reportes</h1>
        <p class="historial-header__subtitle">Historial completo de reportes diarios e incidencias</p>
    </header>

    <main class="historial-main">

        <!-- Barra de filtros con contadores calculados por PHP -->
        <section class="historial-filters" aria-label="Filtros de reportes">
            <button class="historial-filters__button historial-filters__button--active" id="btn-filtro-todos" type="button">
                Todos <span class="historial-filters__count" id="cantidad-todos">(<?= $totalTodos ?>)</span>
            </button>
            <button class="historial-filters__button" id="btn-filtro-diarios" type="button">
                Reportes Diarios <span class="historial-filters__count" id="cantidad-diarios">(<?= $totalDiarios ?>)</span>
            </button>
            <button class="historial-filters__button" id="btn-filtro-incidencias" type="button">
                Incidencias <span class="historial-filters__count" id="cantidad-incidencias">(<?= $totalIncidencias ?>)</span>
            </button>
        </section>

        <!-- Contenedor donde se imprimen los reportes de la Base de Datos -->
        <section class="historial-results" id="contenedor-reportes">
            <?php if ($totalTodos === 0): ?>
                <article class="historial-results__empty-state" id="mensaje-vacio">
                    <p class="historial-results__empty-text">No hay reportes registrados aún</p>
                </article>
            <?php else: ?>
                <!-- Reportes Diarios -->
                <?php foreach ($reportesDiarios as $diario): ?>
                    <article class="historial-card historial-card--diario" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                        <header style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="background: #e3f2fd; color: #1565c0; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">Reporte Diario</span>
                            <span style="color: #666; font-size: 0.85rem;"><?= escapar((string) $diario['fecha']) ?> - <?= escapar((string) $diario['hora']) ?></span>
                        </header>
                        <p style="margin: 0.5rem 0;"><strong>Aula / Taller:</strong> <?= escapar((string) $diario['aula']) ?></p>
                        <p style="margin: 0.5rem 0; color: #555;"><strong>Equipos asignados:</strong> <?= (int) ($diario['cantidad_equipos'] ?? 0) ?></p>
                    </article>
                <?php endforeach; ?>

                <!-- Reportes de Incidencias -->
                <?php foreach ($reportesIncidencias as $incidencia): ?>
                    <article class="historial-card historial-card--incidencia" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                        <header style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="background: #fff3e0; color: #e65100; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">Incidencia</span>
                            <span style="color: #666; font-size: 0.85rem;"><?= escapar((string) $incidencia['fecha']) ?> - <?= escapar((string) $incidencia['hora']) ?></span>
                        </header>
                        <p style="margin: 0.5rem 0;"><strong>Asignatura:</strong> <?= escapar((string) $incidencia['asignatura']) ?> | <strong>Taller:</strong> <?= escapar((string) $incidencia['taller']) ?></p>
                        <p style="margin: 0.5rem 0; color: #333;"><?= escapar((string) $incidencia['descripcion']) ?></p>
                        <p style="margin: 0.5rem 0;">
                            <strong>Estado:</strong> 
                            <span style="font-weight: bold; color: <?= $incidencia['estado'] === 'resuelto' ? '#2e7d32' : '#e65100' ?>; text-transform: capitalize;">
                                <?= escapar((string) $incidencia['estado']) ?>
                            </span>
                        </p>
                        <?php if (!empty($incidencia['solucion'])): ?>
                            <blockquote style="background: #f1f8e9; border-left: 4px solid #4caf50; margin: 0.5rem 0; padding: 0.5rem; border-radius: 0 4px 4px 0;">
                                <strong>Solución documentada por coordinación:</strong><br>
                                <?= escapar((string) $incidencia['solucion']) ?>
                            </blockquote>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <footer class="historial-footer">
        <p class="historial-footer__text">&copy; 2026 Sistema de Reportes</p>
    </footer>
</body>

</html>
