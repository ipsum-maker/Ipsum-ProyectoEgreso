<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= escapar($csrfToken) ?>">
    <title>Solicitar uso de laboratorio - Ipsum</title>
    <link rel="stylesheet" href="assets/css/funciones.css">
    <script src="assets/js/api.js"></script>
    <script src="assets/js/solicitudes-laboratorio.js" defer></script>
</head>
<body class="funciones-page">
    <header class="funciones-header">
        <a class="funciones-volver" href="index.php?ruta=panel-profesores">← Volver al Panel</a>
        <h1>Solicitar uso de salón / laboratorio</h1>
        <p>Solicite un salón, laboratorio o taller e indique la fecha y el horario de uso.</p>
    </header>
    <main class="funciones-main funciones-main--formulario">
        <?php if ($mensaje !== null): ?>
            <p class="funciones-aviso funciones-aviso--<?= escapar((string) $mensaje['tipo']) ?>" role="status"><?= escapar((string) $mensaje['texto']) ?></p>
        <?php endif; ?>
        <form class="funciones-card funciones-formulario" id="form-solicitud-laboratorio" action="index.php?ruta=guardar-solicitud-laboratorio" method="post">
            <input type="hidden" name="csrf_token" value="<?= escapar($csrfToken) ?>">
            <p class="funciones-ayuda">La solicitud queda pendiente hasta que coordinación la apruebe. Un horario ya concedido no puede solicitarse nuevamente.</p>
            
            <label for="solicitud-aula">Espacio (Salón / Laboratorio / Taller)</label>
            <select id="solicitud-aula" name="aula_id" required>
                <option value="">Seleccione un espacio</option>
                <?php foreach ($aulas as $aula): ?>
                    <option value="<?= (int) $aula['id'] ?>"><?= escapar((string) $aula['nombre']) ?> (<?= escapar((string) $aula['codigo']) ?>)</option>
                <?php endforeach; ?>
            </select>

            <label for="solicitud-fecha">Fecha de uso</label>
            <input id="solicitud-fecha" name="fecha" type="date" min="<?= escapar($fechaActual) ?>" value="<?= escapar($fechaActual) ?>" required>

            <div class="funciones-horario">
                <div>
                    <label for="solicitud-inicio">Hora de inicio</label>
                    <input id="solicitud-inicio" name="hora_inicio" type="time" required>
                </div>
                <div>
                    <label for="solicitud-fin">Hora de fin</label>
                    <input id="solicitud-fin" name="hora_fin" type="time" required>
                </div>
            </div>

            <section class="funciones-agenda" aria-labelledby="agenda-titulo">
                <h2 id="agenda-titulo">Horarios ya concedidos</h2>
                <p id="agenda-mensaje" class="funciones-ayuda" role="status" aria-live="polite">Seleccione un espacio y una fecha para consultar los horarios ocupados.</p>
                <ul id="agenda-ocupados"></ul>
                <noscript>Al enviar la solicitud, el sistema comprobará que el horario esté disponible.</noscript>
            </section>

            <label for="solicitud-motivo">Motivo de la solicitud</label>
            <textarea id="solicitud-motivo" name="motivo" rows="4" maxlength="2000" placeholder="Indique la actividad y necesidad del espacio..." required></textarea>

            <div class="funciones-acciones">
                <button class="funciones-boton" type="submit">Enviar solicitud</button>
                <a class="funciones-boton funciones-boton--secundario" href="index.php?ruta=mis-solicitudes-laboratorio">Mis solicitudes</a>
            </div>
        </form>
    </main>
    <footer class="funciones-footer"><p>&copy; 2026 Sistema de Reportes</p></footer>
</body>
</html>
