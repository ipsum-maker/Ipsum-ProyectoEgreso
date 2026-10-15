<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= escapar($csrfToken) ?>">
    <title>Mis solicitudes de laboratorio - Ipsum</title>
    <link rel="stylesheet" href="assets/css/funciones.css">
    <script src="assets/js/api.js"></script>
</head>
<body class="funciones-page">
    <header class="funciones-header">
        <a class="funciones-volver" href="index.php?ruta=panel-profesores">← Volver al Panel</a>
        <h1>Mis solicitudes de laboratorio / salón</h1>
        <p>Consulte el estado de aprobación de sus solicitudes de uso de espacios.</p>
    </header>
    <main class="funciones-main">
        <?php if ($mensaje !== null): ?>
            <p class="funciones-aviso funciones-aviso--<?= escapar((string) $mensaje['tipo']) ?>" role="status"><?= escapar((string) $mensaje['texto']) ?></p>
        <?php endif; ?>

        <div class="funciones-acciones">
            <a class="funciones-boton" href="index.php?ruta=solicitar-laboratorio">Nueva solicitud</a>
        </div>

        <?php if (empty($solicitudes)): ?>
            <section class="funciones-card"><p>Todavía no registró solicitudes de uso de espacios.</p></section>
        <?php endif; ?>

        <?php foreach ($solicitudes as $solicitud): ?>
            <article class="funciones-card">
                <div class="funciones-card__encabezado">
                    <h2><?= escapar((string) $solicitud['aula_nombre']) ?> (<?= escapar((string) $solicitud['aula_codigo']) ?>)</h2>
                    <span class="funciones-estado funciones-estado--<?= escapar((string) $solicitud['estado']) ?>">
                        <?= $solicitud['estado'] === 'aprobada' ? 'Aprobada / Concedida' : ($solicitud['estado'] === 'rechazada' ? 'Rechazada' : 'Pendiente') ?>
                    </span>
                </div>
                <p><strong>Fecha:</strong> <?= escapar((string) $solicitud['fecha']) ?> · <strong>Horario:</strong> <?= escapar(substr((string) $solicitud['hora_inicio'], 0, 5)) ?> a <?= escapar(substr((string) $solicitud['hora_fin'], 0, 5)) ?></p>
                <p class="funciones-texto"><strong>Motivo:</strong> <?= escapar((string) $solicitud['motivo']) ?></p>

                <?php if ($solicitud['estado'] === 'aprobada'): ?>
                    <p class="funciones-aviso funciones-aviso--exito">Su solicitud para usar <?= escapar((string) $solicitud['aula_nombre']) ?> el <?= escapar((string) $solicitud['fecha']) ?> de <?= escapar(substr((string) $solicitud['hora_inicio'], 0, 5)) ?> a <?= escapar(substr((string) $solicitud['hora_fin'], 0, 5)) ?> fue <strong>aprobada</strong>.</p>
                <?php elseif ($solicitud['estado'] === 'rechazada'): ?>
                    <p class="funciones-aviso funciones-aviso--error">Esta solicitud fue <strong>rechazada</strong> por coordinación.</p>
                <?php else: ?>
                    <p class="funciones-ayuda">Su solicitud está pendiente de revisión por el equipo de coordinación.</p>
                <?php endif; ?>

                <?php if (!empty($solicitud['respuesta'])): ?>
                    <p class="funciones-texto"><strong>Respuesta de coordinación:</strong> <?= escapar((string) $solicitud['respuesta']) ?></p>
                <?php endif; ?>
                <?php if (!empty($solicitud['revisor_nombre'])): ?>
                    <p class="funciones-ayuda"><small>Revisado por: <?= escapar((string) $solicitud['revisor_nombre']) ?></small></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </main>
    <footer class="funciones-footer"><p>&copy; 2026 Sistema de Reportes</p></footer>
</body>
</html>
