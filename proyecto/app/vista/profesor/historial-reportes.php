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

        <?php if ($mensaje !== null): ?>
            <p class="historial-mensaje historial-mensaje--<?= escapar((string) $mensaje['tipo']) ?>" role="status">
                <?= escapar((string) $mensaje['texto']) ?>
            </p>
        <?php endif; ?>

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
                    <?php 
                        $creadoTimestamp = !empty($diario['creado_en']) ? strtotime((string) $diario['creado_en']) : time();
                        $minutosTranscurridos = (int) floor((time() - $creadoTimestamp) / 60);
                        $esEditableDiario = $minutosTranscurridos <= 10;
                    ?>
                    <article class="historial-card historial-card--diario" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="background: #e3f2fd; color: #1565c0; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">Reporte Diario</span>
                            <span style="color: #666; font-size: 0.85rem;"><?= escapar((string) $diario['fecha']) ?> | Turno: <?= ucfirst(escapar((string) ($diario['turno'] ?? 'matutino'))) ?></span>
                        </header>
                        <p style="margin: 0.3rem 0;"><strong>Aula / Taller:</strong> <?= escapar((string) $diario['aula']) ?> | <strong>Grupo:</strong> <?= escapar((string) ($diario['grupo'] ?? 'N/A')) ?> | <strong>Asignatura:</strong> <?= escapar((string) ($diario['asignatura'] ?? 'N/A')) ?></p>
                        <p style="margin: 0.3rem 0; color: #444;"><strong>Horario:</strong> <?= escapar((string) ($diario['hora_entrada'] ?? $diario['hora'])) ?> hs a <?= escapar((string) ($diario['hora_salida'] ?? 'N/A')) ?> hs</p>
                        <p style="margin: 0.3rem 0; color: #555;"><strong>Equipos asignados:</strong> <?= (int) ($diario['cantidad_equipos'] ?? 0) ?></p>

                        <footer style="margin-top: 0.75rem; pt: 0.5rem; border-top: 1px dashed #eee; display: flex; justify-content: space-between; align-items: center;">
                            <?php if ($esEditableDiario): ?>
                                <span style="font-size: 0.8rem; color: #2e7d32; background: #e8f5e9; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                    Editable (creado hace <?= $minutosTranscurridos ?> min, máx. 10 min)
                                </span>
                                <button type="button" onclick="document.getElementById('modal-editar-diario-<?= (int) $diario['id'] ?>').showModal()" style="background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.85rem; font-weight: bold; cursor: pointer;">
                                    Editar
                                </button>

                                <dialog id="modal-editar-diario-<?= (int) $diario['id'] ?>" style="border: none; border-radius: 8px; padding: 1.5rem; max-width: 500px; width: 90%; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                    <form action="index.php?ruta=actualizar-reporte-diario" method="post" style="display: flex; flex-direction: column; gap: 1rem;">
                                        <input type="hidden" name="csrf_token" value="<?= escapar(obtenerTokenCSRF()) ?>">
                                        <input type="hidden" name="reporte_id" value="<?= (int) $diario['id'] ?>">

                                        <h3 style="margin: 0; color: #1565c0;">Editar Reporte Diario</h3>

                                        <div>
                                            <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Turno</label>
                                            <select name="turno" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                                                <option value="matutino" <?= ($diario['turno'] ?? '') === 'matutino' ? 'selected' : '' ?>>Matutino</option>
                                                <option value="vespertino" <?= ($diario['turno'] ?? '') === 'vespertino' ? 'selected' : '' ?>>Vespertino</option>
                                                <option value="nocturno" <?= ($diario['turno'] ?? '') === 'nocturno' ? 'selected' : '' ?>>Nocturno</option>
                                            </select>
                                        </div>

                                        <div style="display: flex; gap: 0.5rem;">
                                            <div style="flex: 1;">
                                                <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Hora Entrada</label>
                                                <input type="time" name="hora_entrada" value="<?= escapar((string) ($diario['hora_entrada'] ?? '')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                                            </div>
                                            <div style="flex: 1;">
                                                <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Hora Salida</label>
                                                <input type="time" name="hora_salida" value="<?= escapar((string) ($diario['hora_salida'] ?? '')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                                            </div>
                                        </div>

                                        <div style="display: flex; gap: 0.5rem;">
                                            <div style="flex: 1;">
                                                <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Grupo</label>
                                                <input type="text" name="grupo" value="<?= escapar((string) ($diario['grupo'] ?? '')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                                            </div>
                                            <div style="flex: 1;">
                                                <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Asignatura</label>
                                                <input type="text" name="asignatura" value="<?= escapar((string) ($diario['asignatura'] ?? '')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                                            </div>
                                        </div>

                                        <div>
                                            <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Aula / Taller</label>
                                            <select name="aula_id" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                                                <?php foreach ($aulas as $aItem): ?>
                                                    <option value="<?= (int) $aItem['id'] ?>" <?= (int) ($diario['aula_id'] ?? 0) === (int) $aItem['id'] ? 'selected' : '' ?>>
                                                        <?= escapar((string) $aItem['nombre']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem;">
                                            <button type="button" onclick="document.getElementById('modal-editar-diario-<?= (int) $diario['id'] ?>').close()" style="padding: 0.5rem 1rem; border: 1px solid #ccc; background: #eee; border-radius: 4px; cursor: pointer;">Cancelar</button>
                                            <button type="submit" style="padding: 0.5rem 1rem; border: none; background: #1565c0; color: #fff; border-radius: 4px; font-weight: bold; cursor: pointer;">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </dialog>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; color: #757575; background: #f5f5f5; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                    Edición cerrada (superó los 10 min)
                                </span>
                            <?php endif; ?>
                        </footer>
                    </article>
                <?php endforeach; ?>

                <!-- Reportes de Incidencias -->
                <?php foreach ($reportesIncidencias as $incidencia): ?>
                    <article class="historial-card historial-card--incidencia" style="background: #fff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                        <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="background: #fff3e0; color: #e65100; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: bold; font-size: 0.85rem;">Incidencia</span>
                            <span style="color: #666; font-size: 0.85rem;"><?= escapar((string) $incidencia['fecha']) ?> - <?= escapar((string) $incidencia['hora']) ?> | Turno: <?= ucfirst(escapar((string) ($incidencia['turno'] ?? 'matutino'))) ?></span>
                        </header>
                        <p style="margin: 0.3rem 0;"><strong>Asignatura:</strong> <?= escapar((string) $incidencia['asignatura']) ?> | <strong>Grupo:</strong> <?= escapar((string) ($incidencia['grupo'] ?? 'N/A')) ?> | <strong>Taller:</strong> <?= escapar((string) $incidencia['taller']) ?></p>
                        <p style="margin: 0.3rem 0; color: #333;"><?= escapar((string) $incidencia['descripcion']) ?></p>
                        <p style="margin: 0.3rem 0;">
                            <strong>Estado:</strong> 
                            <span style="font-weight: bold; color: <?= $incidencia['estado'] === 'resuelto' ? '#2e7d32' : ($incidencia['estado'] === 'en_proceso' ? '#1565c0' : '#e65100') ?>; text-transform: capitalize;">
                                <?= escapar(str_replace('_', ' ', $incidencia['estado'])) ?>
                            </span>
                        </p>
                        <?php if (!empty($incidencia['solucion'])): ?>
                            <blockquote style="background: #f1f8e9; border-left: 4px solid #4caf50; margin: 0.5rem 0; padding: 0.5rem; border-radius: 0 4px 4px 0;">
                                <strong>Solución documentada por coordinación:</strong><br>
                                <?= escapar((string) $incidencia['solucion']) ?>
                            </blockquote>
                        <?php endif; ?>

                        <footer style="margin-top: 0.75rem; pt: 0.5rem; border-top: 1px dashed #eee; display: flex; justify-content: space-between; align-items: center;">
                            <?php if ($incidencia['estado'] === 'pendiente'): ?>
                                <span style="font-size: 0.8rem; color: #e65100; background: #fff3e0; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                    Pendiente de atención por coordinación
                                </span>
                                <div style="display: flex; gap: 0.4rem; align-items: center;">
                                    <button type="button" onclick="document.getElementById('modal-editar-incidencia-<?= (int) $incidencia['id'] ?>').showModal()" style="background: #e3f2fd; color: #1565c0; border: 1px solid #90caf9; padding: 0.3rem 0.7rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer;">
                                        Editar
                                    </button>

                                    <dialog id="modal-editar-incidencia-<?= (int) $incidencia['id'] ?>" style="border: none; border-radius: 8px; padding: 1.5rem; max-width: 500px; width: 90%; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: left;">
                                        <form action="index.php?ruta=actualizar-reporte-incidencia" method="post" style="display: flex; flex-direction: column; gap: 0.8rem;">
                                            <input type="hidden" name="csrf_token" value="<?= escapar(obtenerTokenCSRF()) ?>">
                                            <input type="hidden" name="incidencia_id" value="<?= (int) $incidencia['id'] ?>">

                                            <h3 style="margin: 0; color: #e65100;">Editar Incidencia Pendiente</h3>

                                            <div style="display: flex; gap: 0.5rem;">
                                                <div style="flex: 1;">
                                                    <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Asignatura</label>
                                                    <input type="text" name="asignatura" value="<?= escapar((string) $incidencia['asignatura']) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                                                </div>
                                                <div style="flex: 1;">
                                                    <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Grupo</label>
                                                    <input type="text" name="grupo" value="<?= escapar((string) ($incidencia['grupo'] ?? '')) ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                                                </div>
                                            </div>

                                            <div style="display: flex; gap: 0.5rem;">
                                                <div style="flex: 1;">
                                                    <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Turno</label>
                                                    <select name="turno" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                                                        <option value="matutino" <?= ($incidencia['turno'] ?? '') === 'matutino' ? 'selected' : '' ?>>Matutino</option>
                                                        <option value="vespertino" <?= ($incidencia['turno'] ?? '') === 'vespertino' ? 'selected' : '' ?>>Vespertino</option>
                                                        <option value="nocturno" <?= ($incidencia['turno'] ?? '') === 'nocturno' ? 'selected' : '' ?>>Nocturno</option>
                                                    </select>
                                                </div>
                                                <div style="flex: 1;">
                                                    <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Taller / Lab</label>
                                                    <select name="aula_id" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required>
                                                        <?php foreach ($aulas as $aItem): ?>
                                                            <option value="<?= (int) $aItem['id'] ?>" <?= (int) ($incidencia['aula_id'] ?? 0) === (int) $aItem['id'] ? 'selected' : '' ?>>
                                                                <?= escapar((string) $aItem['nombre']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div>
                                                <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Tipo Solicitud</label>
                                                <select name="tipoSolicitud" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                                                    <option value="rotura-equipo" <?= $incidencia['tipo_solicitud'] === 'rotura-equipo' ? 'selected' : '' ?>>Rotura de equipo</option>
                                                    <option value="falla-equipo" <?= $incidencia['tipo_solicitud'] === 'falla-equipo' ? 'selected' : '' ?>>Falla de equipo</option>
                                                    <option value="otro" <?= $incidencia['tipo_solicitud'] === 'otro' ? 'selected' : '' ?>>Otro</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label style="display: block; font-weight: bold; margin-bottom: 0.3rem;">Descripción</label>
                                                <textarea name="descripcion" rows="3" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" required><?= escapar((string) $incidencia['descripcion']) ?></textarea>
                                            </div>

                                            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.5rem;">
                                                <button type="button" onclick="document.getElementById('modal-editar-incidencia-<?= (int) $incidencia['id'] ?>').close()" style="padding: 0.5rem 1rem; border: 1px solid #ccc; background: #eee; border-radius: 4px; cursor: pointer;">Cancelar</button>
                                                <button type="submit" style="padding: 0.5rem 1rem; border: none; background: #e65100; color: #fff; border-radius: 4px; font-weight: bold; cursor: pointer;">Guardar Cambios</button>
                                            </div>
                                        </form>
                                    </dialog>

                                    <form action="index.php?ruta=eliminar-reporte-incidencia" method="post" onsubmit="return confirm('¿Está seguro de eliminar esta incidencia pendiente?');" style="margin: 0;">
                                        <input type="hidden" name="csrf_token" value="<?= escapar(obtenerTokenCSRF()) ?>">
                                        <input type="hidden" name="incidencia_id" value="<?= (int) $incidencia['id'] ?>">
                                        <button type="submit" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.3rem 0.7rem; border-radius: 4px; font-size: 0.8rem; cursor: pointer;">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            <?php elseif ($incidencia['estado'] === 'en_proceso'): ?>
                                <span style="font-size: 0.8rem; color: #1565c0; background: #e3f2fd; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                    En proceso - No se puede modificar ni eliminar
                                </span>
                            <?php else: ?>
                                <span style="font-size: 0.8rem; color: #2e7d32; background: #e8f5e9; padding: 0.2rem 0.5rem; border-radius: 4px;">
                                    Incidencia resuelta
                                </span>
                            <?php endif; ?>
                        </footer>
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
