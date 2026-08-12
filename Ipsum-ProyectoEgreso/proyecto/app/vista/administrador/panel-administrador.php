<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reportes</title>
    <link rel="stylesheet" href="assets/css/panel-administrador.css">
    <script>
        window.IPSUM_USUARIO = <?= json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="assets/js/sesion-pestana.js"></script>
</head>

<body class="admin-page">
    <header class="admin-header">
        <section class="admin-header__brand" id="encabezado-administrador">
            <h1 class="admin-header__title">Panel de Administrador</h1>
            <p class="admin-header__subtitle">Gestión de usuarios y permisos</p>
        </section>

        <nav class="admin-header__nav" id="navegacion-administrador">
            <a class="admin-header__logout" id="cerrar-sesion" href="index.php?ruta=cerrar-sesion">Cerrar Sesión</a>
        </nav>
    </header>

    <main class="admin-main" id="contenido-principal">
        <?php if ($mensaje !== null): ?>
            <p class="admin-message admin-message--<?= escapar((string) $mensaje['tipo']) ?>" role="status">
                <?= escapar((string) $mensaje['texto']) ?>
            </p>
        <?php endif; ?>

        <?php if ($errorBaseDeDatos !== ''): ?>
            <p class="admin-message admin-message--error" role="alert"><?= escapar($errorBaseDeDatos) ?></p>
        <?php endif; ?>

        <section class="admin-stats" id="indicadores">
            <h2 class="admin-stats__title">Indicadores</h2>

            <article class="admin-stats__card">
                <h3 class="admin-stats__name">Profesores</h3>
                <p class="admin-stats__number"><?= $cantidades['profesor'] ?></p>
            </article>

            <article class="admin-stats__card">
                <h3 class="admin-stats__name">Coordinadores</h3>
                <p class="admin-stats__number"><?= $cantidades['coordinador'] ?></p>
            </article>

            <article class="admin-stats__card">
                <h3 class="admin-stats__name">Administradores</h3>
                <p class="admin-stats__number"><?= $cantidades['administrador'] ?></p>
            </article>
        </section>

        <section class="admin-users" id="usuarios-del-sistema">
            <h2 class="admin-users__title">Usuarios del Sistema</h2>
            <p class="admin-users__description">Gestión de usuarios y roles dentro del sistema.</p>

            <button class="admin-users__button" id="boton-nuevo-usuario" type="button">Nuevo Usuario</button>

            <table class="admin-users__table" id="tabla-usuarios">
                <thead class="admin-users__thead">
                    <tr class="admin-users__row">
                        <th class="admin-users__header">Nombre</th>
                        <th class="admin-users__header">Cédula</th>
                        <th class="admin-users__header">Rol/Módulo</th>
                        <th class="admin-users__header">Acciones</th>
                    </tr>
                </thead>

                <tbody class="admin-users__tbody">
                    <?php foreach ($usuarios as $usuarioListado): ?>
                        <tr class="admin-users__row">
                            <td class="admin-users__cell admin-users__cell--name">
                                <?= escapar((string) $usuarioListado['nombre']) ?>
                            </td>
                            <td class="admin-users__cell">
                                <?= escapar(formatearCedula((string) $usuarioListado['cedula'])) ?>
                            </td>
                            <td class="admin-users__cell">
                                <?php if ((int) $usuarioListado['es_inicial'] === 1): ?>
                                    <?= escapar(nombreVisibleRol((string) $usuarioListado['rol'])) ?>
                                <?php else: ?>
                                    <form action="index.php?ruta=cambiar-rol-usuario" method="post">
                                        <input type="hidden" name="csrf_token" value="<?= escapar((string) $_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="usuario_id" value="<?= (int) $usuarioListado['id'] ?>">
                                        <select class="admin-users__select" name="rol" onchange="this.form.submit()">
                                            <?php foreach (['administrador', 'coordinador', 'profesor'] as $rol): ?>
                                                <option value="<?= escapar($rol) ?>"
                                                    <?= $usuarioListado['rol'] === $rol ? 'selected' : '' ?>>
                                                    <?= escapar(nombreVisibleRol($rol)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td class="admin-users__cell">
                                <?php if ((int) $usuarioListado['es_inicial'] === 1): ?>
                                    Usuario inicial
                                <?php else: ?>
                                    <form action="index.php?ruta=eliminar-usuario" method="post"
                                        onsubmit="return confirm('¿Seguro que querés eliminar este usuario?')">
                                        <input type="hidden" name="csrf_token" value="<?= escapar((string) $_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="usuario_id" value="<?= (int) $usuarioListado['id'] ?>">
                                        <button class="admin-users__action-button" type="submit">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="admin-users admin-users--resolved" id="historial-reportes-resueltos">
            <h2 class="admin-users__title">Historial de Reportes Resueltos</h2>
            <p class="admin-users__description">Incidencias documentadas y marcadas como resueltas.</p>

            <table class="admin-users__table" id="tabla-reportes-resueltos">
                <thead class="admin-users__thead">
                    <tr class="admin-users__row">
                        <th class="admin-users__header">Fecha</th>
                        <th class="admin-users__header">Docente</th>
                        <th class="admin-users__header">Tipo</th>
                        <th class="admin-users__header">Taller / Aula</th>
                        <th class="admin-users__header">Técnico</th>
                        <th class="admin-users__header">Ver</th>
                    </tr>
                </thead>

                <tbody class="admin-users__tbody">
                </tbody>
            </table>

            <p class="admin-users__description" id="mensaje-reportes-resueltos">
                No hay reportes resueltos todavía.
            </p>
        </section>
    </main>

    <dialog class="admin-modal" id="modal-nuevo-usuario">
        <form class="admin-modal__form" id="form-nuevo-usuario"
            action="index.php?ruta=crear-usuario" method="post">
            <input type="hidden" name="csrf_token" value="<?= escapar((string) $_SESSION['csrf_token']) ?>">

            <header class="admin-modal__header">
                <h2 class="admin-modal__title">Crear Nuevo Usuario</h2>
                <button class="admin-modal__close" id="cerrar-modal-usuario" type="button"
                    aria-label="Cerrar modal">×</button>
            </header>

            <p class="admin-modal__description">Complete los datos para crear un usuario nuevo.</p>

            <label class="admin-modal__label" for="nuevo-usuario-nombre">Nombre completo</label>
            <input class="admin-modal__input" type="text" id="nuevo-usuario-nombre" name="nombre" required>

            <label class="admin-modal__label" for="nuevo-usuario-cedula">Cédula</label>
            <input class="admin-modal__input" type="text" id="nuevo-usuario-cedula" name="cedula"
                inputmode="numeric" placeholder="1.234.567-8" pattern="[0-9.\-]{7,12}" maxlength="12" required>

            <label class="admin-modal__label" for="nuevo-usuario-contrasena">Contraseña</label>
            <input class="admin-modal__input" type="password" id="nuevo-usuario-contrasena"
                name="contrasena" required>

            <label class="admin-modal__label" for="nuevo-usuario-rol">Rol / Módulo</label>
            <select class="admin-modal__select" id="nuevo-usuario-rol" name="rol" required>
                <option value="profesor">Profesor</option>
                <option value="coordinador">Coordinador</option>
                <option value="administrador">Administrador</option>
            </select>

            <footer class="admin-modal__actions">
                <button class="admin-modal__button admin-modal__button--primary" type="submit">Crear Usuario</button>
                <button class="admin-modal__button admin-modal__button--secondary" id="cancelar-modal-usuario"
                    type="button">Cancelar</button>
            </footer>
        </form>
    </dialog>

    <dialog class="admin-modal" id="modal-detalle-resuelto">
        <article class="admin-modal__form">
            <header class="admin-modal__header">
                <h2 class="admin-modal__title">Detalle del Reporte Resuelto</h2>
                <button class="admin-modal__close" id="cerrar-modal-resuelto" type="button"
                    aria-label="Cerrar detalle">×</button>
            </header>

            <section class="admin-modal__detail" id="detalle-resuelto-contenido">
            </section>

            <footer class="admin-modal__actions">
                <button class="admin-modal__button admin-modal__button--secondary" id="boton-cerrar-resuelto"
                    type="button">Cerrar</button>
            </footer>
        </article>
    </dialog>

    <footer class="admin-footer" id="pie-de-pagina">
        <p class="admin-footer__text">&copy; 2026 Sistema de Reportes</p>
    </footer>

    <script src="assets/js/control-errores.js"></script>
    <script src="assets/js/panel-administrador-ui.js"></script>
</body>

</html>
