<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        window.IPSUM_USUARIO = <?= json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="assets/js/sesion-pestana.js"></script>
    <title>Registros de Incidencias - Ipsum</title>
    <link rel="stylesheet" href="assets/css/cordinacion-incidencias.css">
    <script src="assets/js/control-errores.js"></script>
    <script src="assets/js/cordinacion-incidencias.js"></script>
</head>

<body class="cordinacion-page">

    <!-- Encabezado principal de la página con barra de navegación superior -->
    <header class="cordinacion-header">
        <nav class="cordinacion-header__nav">
            <a href="index.php?ruta=panel-cordinacion" class="cordinacion-header__back" id="btn-volver-panel">
                <svg class="cordinacion-header__back-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Volver al Panel
            </a>
        </nav>
    </header>

    <!-- Contenido Principal: Agrupa la barra de filtros y la tabla de resultados -->
    <main class="cordinacion-main">

        <!-- Barra de Título y Filtros combinados -->
        <section class="cordinacion-top-bar">
            <header class="cordinacion-title-group">
                <span class="cordinacion-icon-wrapper cordinacion-icon-wrapper--orange">
                    <!-- Icono SVG del triángulo de advertencia -->
                    <svg class="cordinacion-title-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path
                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z">
                        </path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </span>
                <hgroup>
                    <h1 class="cordinacion-title" id="titulo-pagina">Registros de Incidencias</h1>
                    <p class="cordinacion-subtitle" id="cantidad-registros-incidencias">0 incidencia(s) · ordenadas por
                        prioridad</p>
                </hgroup>
            </header>

            <!-- Sección de filtrado: buscador y selector de estado -->
            <section class="cordinacion-filters-group">
                <!-- Buscador de texto -->
                <search class="cordinacion-search-wrapper">
                    <svg class="cordinacion-search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input class="cordinacion-search-input" id="buscador-registros-incidencias" type="search"
                        placeholder="Buscar...">
                </search>

                <!-- Selector de Filtro de Estado -->
                <select class="cordinacion-filter-select" id="filtro-estado-incidencias">
                    <option value="todos">Todos</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="en-proceso">En proceso</option>
                    <option value="resuelto">Resueltos</option>
                </select>
            </section>
        </section>

        <!-- Tarjeta Contenedora Principal: Muestra las incidencias -->
        <section class="cordinacion-card">
            <header class="cordinacion-card__header">
                <p class="cordinacion-card__description">Para marcar una incidencia como resuelta debe documentar la
                    solución aplicada. Las resueltas se muestran al final.</p>
            </header>
            <section class="cordinacion-table-wrapper" id="tabla-contenedor" style="<?= empty($incidencias) ? 'display: none;' : '' ?>">
                <table class="cordinacion-table">
                    <thead class="cordinacion-table__head">
                        <tr class="cordinacion-table__row">
                            <th class="cordinacion-table__header">Prioridad</th>
                            <th class="cordinacion-table__header">Fecha</th>
                            <th class="cordinacion-table__header">Docente</th>
                            <th class="cordinacion-table__header">Tipo</th>
                            <th class="cordinacion-table__header">Taller / Aula</th>
                            <th class="cordinacion-table__header">Estado</th>
                            <th class="cordinacion-table__header">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="cordinacion-table__body" id="tabla-registros-incidencias">
                        <?php foreach ($incidencias as $incidencia): ?>
                            <?php
                                $esAlta = $incidencia['tipo_solicitud'] === 'rotura-equipo';
                                $prioridad = $esAlta ? 'alta' : 'media';
                                $estado = (string) $incidencia['estado'];
                            ?>
                            <tr class="cordinacion-table__row estado-<?= escapar($estado) ?>">
                                <td class="cordinacion-table__cell">
                                    <span class="cordinacion-badge cordinacion-badge--<?= $prioridad ?>">
                                        <?= $esAlta ? 'Alta' : 'Media' ?>
                                    </span>
                                </td>
                                <td class="cordinacion-table__cell"><?= escapar((string) $incidencia['fecha']) ?></td>
                                <td class="cordinacion-table__cell" style="font-weight: bold;">
                                    <?= escapar((string) $incidencia['nombre_profesor'] . ' ' . (string) $incidencia['apellido_profesor']) ?>
                                </td>
                                <td class="cordinacion-table__cell">
                                    <?= escapar((string) ($incidencia['tipo_solicitud'] === 'otro' ? $incidencia['otro_tipo'] : $incidencia['tipo_solicitud'])) ?>
                                </td>
                                <td class="cordinacion-table__cell"><?= escapar((string) $incidencia['taller']) ?></td>
                                <td class="cordinacion-table__cell">
                                    <span style="font-weight: bold; text-transform: capitalize; color: <?= $estado === 'resuelto' ? '#2e7d32' : ($estado === 'en_proceso' ? '#1565c0' : '#e65100') ?>;">
                                        <?= escapar(str_replace('_', ' ', $estado)) ?>
                                    </span>
                                </td>
                                <td class="cordinacion-table__cell" style="display: flex; gap: 0.4rem; align-items: center;">
                                    <button class="cordinacion-table__button-ver" type="button" 
                                        data-profesor="<?= escapar((string) $incidencia['nombre_profesor'] . ' ' . (string) $incidencia['apellido_profesor']) ?>"
                                        data-asignatura="<?= escapar((string) $incidencia['asignatura']) ?>"
                                        data-grupo="<?= escapar((string) ($incidencia['grupo'] ?? 'N/A')) ?>"
                                        data-turno="<?= escapar((string) ($incidencia['turno'] ?? 'matutino')) ?>"
                                        data-fecha="<?= escapar((string) $incidencia['fecha']) ?>"
                                        data-hora="<?= escapar((string) $incidencia['hora']) ?>"
                                        data-tipo="<?= escapar((string) ($incidencia['tipo_solicitud'] === 'otro' ? $incidencia['otro_tipo'] : $incidencia['tipo_solicitud'])) ?>"
                                        data-taller="<?= escapar((string) $incidencia['taller']) ?>"
                                        data-descripcion="<?= escapar((string) ($incidencia['descripcion'] ?? '')) ?>"
                                        data-estado="<?= escapar($estado) ?>"
                                        data-solucion="<?= escapar((string) ($incidencia['solucion'] ?? '')) ?>"
                                        onclick="verDetalleIncidencia(this)" style="background: #f1f5f9; color: #334155; border-color: #cbd5e1;">
                                        Ver
                                    </button>

                                    <?php if ($estado === 'pendiente'): ?>
                                        <form action="index.php?ruta=marcar-incidencia-proceso" method="post" style="margin: 0; display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= escapar(obtenerTokenCSRF()) ?>">
                                            <input type="hidden" name="incidencia_id" value="<?= (int) $incidencia['id'] ?>">
                                            <button class="cordinacion-table__button-ver" type="submit" style="background: #e3f2fd; color: #1565c0; border-color: #90caf9;">
                                                En proceso
                                            </button>
                                        </form>
                                        <button class="cordinacion-table__button-ver" type="button" onclick="abrirModalSolucionIncidencia(<?= (int) $incidencia['id'] ?>)">
                                            Resolver
                                        </button>
                                    <?php elseif ($estado === 'en_proceso'): ?>
                                        <button class="cordinacion-table__button-ver" type="button" onclick="abrirModalSolucionIncidencia(<?= (int) $incidencia['id'] ?>)">
                                            Resolver
                                        </button>
                                    <?php else: ?>
                                        <span style="color: #2e7d32; font-weight: bold; font-size: 0.875rem;">Resuelto</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <p class="cordinacion-card__empty" id="mensaje-vacio-incidencias" style="<?= empty($incidencias) ? 'display: block;' : 'display: none;' ?>">
                No hay incidencias registradas aún
            </p>

            
    </main>

    <!-- Modal: Detalle de Incidencia seleccionada -->
    <dialog class="cordinacion-modal" id="modal-detalle-incidencia">
        <header class="cordinacion-modal__header">
            <h2 class="cordinacion-modal__title">Detalle de Incidencia</h2>
            <button class="cordinacion-modal__close" id="cerrar-modal-incidencia" type="button"
                aria-label="Cerrar detalle">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </header>

        <!-- Información e historial inyectado por JavaScript -->
        <section class="cordinacion-modal__body" id="detalle-incidencia-contenido">
            <!-- Inyectado dinámicamente -->
        </section>

        <footer class="cordinacion-modal__footer">
            <button class="cordinacion-modal__button cordinacion-modal__button--secondary"
                id="boton-cerrar-detalle-incidencia" type="button">
                Cerrar
            </button>
        </footer>
    </dialog>

        <!-- Modal: Documentar Solución Aplicada -->
    <dialog class="cordinacion-modal" id="modal-solucion-incidencia">
        <form id="form-solucion-incidencia" action="index.php?ruta=resolver-incidencia" method="post">
            <input type="hidden" name="csrf_token" value="<?= escapar(obtenerTokenCSRF()) ?>">
            <input type="hidden" name="incidencia_id" id="solucion-incidencia-id" value="">

            <header class="cordinacion-modal__header">
                <h2 class="cordinacion-modal__title">Documentar Solución Aplicada</h2>
                <button class="cordinacion-modal__close" id="cerrar-modal-solucion" type="button" aria-label="Cerrar solución">&times;</button>
            </header>

                        <section class="cordinacion-modal__body">
                <fieldset class="cordinacion-modal__form-group" style="border: none; padding: 0; margin-bottom: 1rem;">
                    <label class="cordinacion-modal__label" for="estado-solucion-incidencia" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                        Cambiar Estado
                    </label>
                    <select class="cordinacion-filter-select" id="estado-solucion-incidencia" name="estado" style="width: 100%; padding: 0.8rem; border: 1px solid #d9dee7; border-radius: 0.5rem;">
                        <option value="resuelto">Resuelto</option>
                        <option value="en_proceso">En proceso</option>
                        <option value="pendiente">Pendiente</option>
                    </select>
                </fieldset>

                <fieldset class="cordinacion-modal__form-group" style="border: none; padding: 0;">
                    <label class="cordinacion-modal__label" for="texto-solucion-incidencia" style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                        Descripción de la solución / actualización
                    </label>
                    <textarea class="cordinacion-modal__textarea" id="texto-solucion-incidencia" name="solucion" rows="5"
                        placeholder="Describa la solución o avance..." required></textarea>
                </fieldset>
            </section>


            <footer class="cordinacion-modal__footer cordinacion-modal__footer--row">
                <button class="cordinacion-modal__button cordinacion-modal__button--secondary"
                    id="boton-cancelar-solucion" type="button">
                    Cancelar
                </button>
                <button class="cordinacion-modal__button cordinacion-modal__button--success" type="submit">
                    Confirmar y marcar resuelto
                </button>
            </footer>
        </form>
    </dialog>


    <!-- Pie de página general del sitio -->
    <footer class="cordinacion-footer">
        <p class="cordinacion-footer__text">&copy; 2026 Sistema de Reportes</p>
    </footer>


</body>

</html>
