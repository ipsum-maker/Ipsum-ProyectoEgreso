<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= escapar($csrfToken) ?>">
    <title>Solicitudes de laboratorio - Ipsum</title>
    <link rel="stylesheet" href="assets/css/funciones.css">
    <script src="assets/js/api.js"></script>
</head>
<body class="funciones-page">
    <header class="funciones-header">
        <a class="funciones-volver" href="<?= escapar(urlRuta($rutaPanel)) ?>">← Volver al Panel</a>
        <h1>Gestión de Solicitudes de Laboratorio y Salones</h1>
        <p>Revise las solicitudes de uso de espacios enviadas por los profesores y apruebe o rechace cada reserva.</p>
    </header>
    <main class="funciones-main">
        <?php if ($mensaje !== null): ?>
            <p class="funciones-aviso funciones-aviso--<?= escapar((string) $mensaje['tipo']) ?>" role="status"><?= escapar((string) $mensaje['texto']) ?></p>
        <?php endif; ?>

        <section aria-labelledby="pendientes-titulo">
            <h2 id="pendientes-titulo">Pendientes de revisión (<?= count($pendientes) ?>)</h2>
            <p class="funciones-ayuda">Al aprobar una solicitud se reserva el intervalo de tiempo seleccionado. El sistema verifica que no existan superposiciones horarias.</p>

            <?php if (empty($pendientes)): ?>
                <div class="funciones-card"><p>No hay solicitudes pendientes en este momento.</p></div>
            <?php endif; ?>

            <?php foreach ($pendientes as $solicitud): ?>
                <article class="funciones-card">
                    <div class="funciones-card__encabezado">
                        <h3><?= escapar((string) $solicitud['aula_nombre']) ?> (<?= escapar((string) $solicitud['aula_codigo']) ?>)</h3>
                        <span class="funciones-estado funciones-estado--pendiente">Pendiente</span>
                    </div>
                    <p><strong>Profesor:</strong> <?= escapar((string) $solicitud['profesor_nombre']) ?> (C.I. <?= escapar((string) $solicitud['profesor_cedula']) ?>)</p>
                    <p><strong>Fecha:</strong> <?= escapar((string) $solicitud['fecha']) ?> · <strong>Horario:</strong> <?= escapar(substr((string) $solicitud['hora_inicio'], 0, 5)) ?> a <?= escapar(substr((string) $solicitud['hora_fin'], 0, 5)) ?></p>
                    <p class="funciones-texto"><strong>Motivo:</strong> <?= escapar((string) $solicitud['motivo']) ?></p>

                    <form class="funciones-formulario funciones-decision" action="index.php?ruta=decidir-solicitud-laboratorio" method="post">
                        <input type="hidden" name="csrf_token" value="<?= escapar($csrfToken) ?>">
                        <input type="hidden" name="solicitud_id" value="<?= (int) $solicitud['id'] ?>">

                        <label for="decision-<?= (int) $solicitud['id'] ?>">Decisión:</label>
                        <select id="decision-<?= (int) $solicitud['id'] ?>" name="estado" required>
                            <option value="">Seleccione una acción...</option>
                            <option value="aprobada">Aprobar / Conceder uso</option>
                            <option value="rechazada">Rechazar solicitud</option>
                        </select>

                        <label for="respuesta-<?= (int) $solicitud['id'] ?>">Comentario / Respuesta al profesor (opcional):</label>
                        <textarea id="respuesta-<?= (int) $solicitud['id'] ?>" name="respuesta" rows="2" maxlength="2000" placeholder="Escriba un comentario o motivo..."></textarea>

                        <button class="funciones-boton" type="submit">Guardar decisión</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="funciones-card" aria-labelledby="historial-titulo">
            <h2 id="historial-titulo">Historial de decisiones</h2>
            <?php if (empty($historial)): ?>
                <p class="funciones-ayuda">Todavía no se han resuelto solicitudes.</p>
            <?php else: ?>
                <div class="funciones-tabla-contenedor">
                    <table class="funciones-tabla">
                        <thead>
                            <tr>
                                <th scope="col">Espacio</th>
                                <th scope="col">Profesor</th>
                                <th scope="col">Fecha y Horario</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Respuesta / Revisor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $solicitud): ?>
                                <tr>
                                    <td><?= escapar((string) $solicitud['aula_nombre']) ?> (<?= escapar((string) $solicitud['aula_codigo']) ?>)</td>
                                    <td><?= escapar((string) $solicitud['profesor_nombre']) ?></td>
                                    <td><?= escapar((string) $solicitud['fecha']) ?><br><?= escapar(substr((string) $solicitud['hora_inicio'], 0, 5)) ?> a <?= escapar(substr((string) $solicitud['hora_fin'], 0, 5)) ?></td>
                                    <td>
                                        <span class="funciones-estado funciones-estado--<?= escapar((string) $solicitud['estado']) ?>">
                                            <?= $solicitud['estado'] === 'aprobada' ? 'Aprobada' : 'Rechazada' ?>
                                        </span>
                                    </td>
                                    <td class="funciones-texto">
                                        <?= escapar((string) ($solicitud['respuesta'] ?? '-')) ?>
                                        <?php if (!empty($solicitud['revisor_nombre'])): ?>
                                            <br><small style="color: #64748b;">(Por: <?= escapar((string) $solicitud['revisor_nombre']) ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <footer class="funciones-footer"><p>&copy; 2026 Sistema de Reportes</p></footer>
</body>
</html>
