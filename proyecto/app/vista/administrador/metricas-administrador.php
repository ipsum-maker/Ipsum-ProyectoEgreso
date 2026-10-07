<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métricas del Sistema - Ipsum</title>
    <link rel="stylesheet" href="assets/css/panel-administrador.css">
    <link rel="stylesheet" href="assets/css/metricas-administrador.css">
    <script>
        window.IPSUM_USUARIO = <?= json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="assets/js/sesion-pestana.js"></script>
</head>

<body class="admin-page">
    <header class="admin-header">
        <section class="admin-header__brand" id="encabezado-metricas">
            <h1 class="admin-header__title">Métricas y Estadísticas</h1>
            <p class="admin-header__subtitle">Rendimiento operativo, incidencias y ocupación de aulas</p>
        </section>

        <nav class="admin-header__nav" id="navegacion-metricas">
            <a class="admin-header__logout admin-header__back" href="index.php?ruta=panel-administrador">
                Volver al Panel
            </a>
        </nav>
    </header>

    <main class="admin-main" id="contenido-metricas">
        <!-- 1. Barra de Filtros Temporales -->
        <section class="metricas-filtros" id="filtros-temporales">
            <form action="index.php" method="get" class="metricas-filtros__form">
                <input type="hidden" name="ruta" value="metricas-administrador">

                <div class="metricas-filtros__botones">
                    <a href="index.php?ruta=metricas-administrador&periodo=hoy" 
                       class="metricas-filtros__btn <?= $periodo === 'hoy' ? 'metricas-filtros__btn--activo' : '' ?>">
                        Hoy
                    </a>
                    <a href="index.php?ruta=metricas-administrador&periodo=esta-semana" 
                       class="metricas-filtros__btn <?= $periodo === 'esta-semana' ? 'metricas-filtros__btn--activo' : '' ?>">
                        Esta Semana
                    </a>
                    <a href="index.php?ruta=metricas-administrador&periodo=ultimos-30-dias" 
                       class="metricas-filtros__btn <?= $periodo === 'ultimos-30-dias' ? 'metricas-filtros__btn--activo' : '' ?>">
                        Últimos 30 días
                    </a>
                </div>

                <div class="metricas-filtros__personalizado">
                    <input type="hidden" name="periodo" value="personalizado">
                    <label class="metricas-filtros__label" for="fecha_desde">Desde:</label>
                    <input class="metricas-filtros__date" type="date" id="fecha_desde" name="fecha_desde" value="<?= escapar($fechaDesde) ?>" required>

                    <label class="metricas-filtros__label" for="fecha_hasta">Hasta:</label>
                    <input class="metricas-filtros__date" type="date" id="fecha_hasta" name="fecha_hasta" value="<?= escapar($fechaHasta) ?>" required>

                    <button class="metricas-filtros__submit" type="submit">Filtrar</button>
                </div>
            </form>
        </section>

        <!-- 2. Tarjetas KPI Principales -->
        <section class="admin-stats metricas-kpis" id="kpis-superiores">
            <h2 class="admin-stats__title">Indicadores Principales</h2>

            <article class="admin-stats__card">
                <h3 class="admin-stats__name">Incidencias Activas</h3>
                <p class="admin-stats__number metricas-kpi--alerta"><?= (int) $kpis['activas'] ?></p>
            </article>

            <article class="admin-stats__card">
                <h3 class="admin-stats__name">Tasa de Resolución</h3>
                <p class="admin-stats__number metricas-kpi--exito"><?= (float) $kpis['tasa_resolucion'] ?>%</p>
            </article>

            <article class="admin-stats__card">
                <h3 class="admin-stats__name">Tiempo Promedio Solución</h3>
                <p class="admin-stats__number"><?= (float) $kpis['tiempo_promedio_horas'] ?> hs</p>
            </article>

            <article class="admin-stats__card">
                <h3 class="admin-stats__name">Reportes Diarios de Aula</h3>
                <p class="admin-stats__number"><?= (int) $kpis['total_diarios'] ?></p>
            </article>
        </section>

        <!-- 3. Gráfico de Barras Nativas: Incidencias por Aula / Taller / Lab -->
        <section class="admin-users metricas-card" id="incidencias-por-aula">
            <h2 class="admin-users__title">Incidencias por Taller y Laboratorio</h2>
            <p class="admin-users__description">
                Frecuencia de fallas reportadas por espacio en el rango seleccionado (del <?= escapar($fechaDesde) ?> al <?= escapar($fechaHasta) ?>).
            </p>

            <div class="metricas-barras">
                <?php if (empty($incidenciasPorAula)): ?>
                    <p class="admin-users__description">No hay aulas registradas en el sistema.</p>
                <?php else: ?>
                    <?php foreach ($incidenciasPorAula as $aula): 
                        $totalInc = (int) $aula['total_incidencias'];
                        $porcentaje = $maxIncidencias > 0 ? round(($totalInc / $maxIncidencias) * 100) : 0;
                    ?>
                        <div class="metricas-barras__fila">
                            <div class="metricas-barras__etiqueta">
                                <strong><?= escapar((string) $aula['nombre']) ?></strong>
                                <span class="metricas-barras__tipo"><?= escapar((string) $aula['tipo']) ?></span>
                            </div>

                            <div class="metricas-barras__pista">
                                <?php if ($totalInc > 0): ?>
                                    <div class="metricas-barras__relleno" style="width: <?= max(7, $porcentaje) ?>%;">
                                        <span class="metricas-barras__valor"><?= $totalInc ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="metricas-barras__relleno metricas-barras__relleno--cero" style="width: 25px;">
                                        <span class="metricas-barras__valor metricas-barras__valor--cero">0</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- 4. Cuadrícula Inferior: Distribución por Tipo + Tabla de Recientes -->
        <div class="metricas-grid-inferior">
            <!-- Tipos de Falla -->
            <section class="admin-users metricas-card" id="distribucion-fallas">
                <h2 class="admin-users__title">Distribución por Motivo</h2>
                <p class="admin-users__description">Desglose porcentual de motivos de incidencia.</p>

                <?php 
                    $totalTipos = array_sum($distribucionTipos);
                    $pctRotura = $totalTipos > 0 ? round(($distribucionTipos['rotura-equipo'] / $totalTipos) * 100) : 0;
                    $pctFalla = $totalTipos > 0 ? round(($distribucionTipos['falla-equipo'] / $totalTipos) * 100) : 0;
                    $pctOtro = $totalTipos > 0 ? round(($distribucionTipos['otro'] / $totalTipos) * 100) : 0;
                ?>

                <div class="metricas-distribucion">
                    <div class="metricas-distribucion__item">
                        <div class="metricas-distribucion__header">
                            <span>Rotura de Equipo</span>
                            <strong><?= $distribucionTipos['rotura-equipo'] ?> (<?= $pctRotura ?>%)</strong>
                        </div>
                        <div class="metricas-distribucion__barra">
                            <div class="metricas-distribucion__progreso metricas-color--rotura" style="width: <?= $pctRotura ?>%;"></div>
                        </div>
                    </div>

                    <div class="metricas-distribucion__item">
                        <div class="metricas-distribucion__header">
                            <span>Falla Técnica</span>
                            <strong><?= $distribucionTipos['falla-equipo'] ?> (<?= $pctFalla ?>%)</strong>
                        </div>
                        <div class="metricas-distribucion__barra">
                            <div class="metricas-distribucion__progreso metricas-color--falla" style="width: <?= $pctFalla ?>%;"></div>
                        </div>
                    </div>

                    <div class="metricas-distribucion__item">
                        <div class="metricas-distribucion__header">
                            <span>Otro Motivo</span>
                            <strong><?= $distribucionTipos['otro'] ?> (<?= $pctOtro ?>%)</strong>
                        </div>
                        <div class="metricas-distribucion__barra">
                            <div class="metricas-distribucion__progreso metricas-color--otro" style="width: <?= $pctOtro ?>%;"></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Tabla de Casos Recientes -->
            <section class="admin-users metricas-card" id="incidencias-recientes">
                <h2 class="admin-users__title">Incidencias Recientes</h2>
                <p class="admin-users__description">Últimos reportes registrados en el periodo.</p>

                <table class="admin-users__table">
                    <thead class="admin-users__thead">
                        <tr class="admin-users__row">
                            <th class="admin-users__header">Fecha</th>
                            <th class="admin-users__header">Aula</th>
                            <th class="admin-users__header">Motivo</th>
                            <th class="admin-users__header">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="admin-users__tbody">
                        <?php if (empty($incidenciasRecientes)): ?>
                            <tr class="admin-users__row">
                                <td colspan="4" class="admin-users__cell" style="text-align: center; color: #69707e; padding: 1.5rem;">
                                    No hay incidencias registradas en este periodo.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($incidenciasRecientes as $inc): ?>
                                <tr class="admin-users__row">
                                    <td class="admin-users__cell"><?= escapar((string) $inc['fecha']) ?></td>
                                    <td class="admin-users__cell"><?= escapar((string) $inc['aula_nombre']) ?></td>
                                    <td class="admin-users__cell">
                                        <?= escapar($inc['tipo_solicitud'] === 'otro' ? ($inc['otro_tipo'] ?? 'Otro') : (string) $inc['tipo_solicitud']) ?>
                                    </td>
                                    <td class="admin-users__cell">
                                        <span class="metricas-badge metricas-badge--<?= escapar((string) $inc['estado']) ?>">
                                            <?= escapar(ucwords(str_replace('_', ' ', (string) $inc['estado']))) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>

    <footer class="admin-footer">
        <p class="admin-footer__text">&copy; <?= date('Y') ?> Sistema de Reportes</p>
    </footer>
</body>

</html>
