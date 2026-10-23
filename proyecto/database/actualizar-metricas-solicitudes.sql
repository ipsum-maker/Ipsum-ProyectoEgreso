

-- 1. Agregar columna codigo_equipo en reportes_incidencias si no existe
SET @ipsum_agregar_equipo = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reportes_incidencias' AND COLUMN_NAME = 'codigo_equipo') = 0,
    'ALTER TABLE reportes_incidencias ADD COLUMN codigo_equipo VARCHAR(50) DEFAULT NULL AFTER descripcion',
    'SELECT 1');
PREPARE ipsum_migracion FROM @ipsum_agregar_equipo;
EXECUTE ipsum_migracion;
DEALLOCATE PREPARE ipsum_migracion;

-- 2. Agregar índice idx_incidencia_equipo si no existe
SET @ipsum_indice_equipo = IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reportes_incidencias' AND INDEX_NAME = 'idx_incidencia_equipo') = 0,
    'ALTER TABLE reportes_incidencias ADD INDEX idx_incidencia_equipo (codigo_equipo)',
    'SELECT 1');
PREPARE ipsum_migracion FROM @ipsum_indice_equipo;
EXECUTE ipsum_migracion;
DEALLOCATE PREPARE ipsum_migracion;

-- 3. Crear tabla de solicitudes de laboratorio / salones si no existe
CREATE TABLE IF NOT EXISTS solicitudes_laboratorio (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    aula_id INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    motivo TEXT NOT NULL,
    estado ENUM('pendiente', 'aprobada', 'rechazada') NOT NULL DEFAULT 'pendiente',
    respuesta TEXT DEFAULT NULL,
    revisor_id INT UNSIGNED DEFAULT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    decidido_en TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_solicitud_laboratorio_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_solicitud_laboratorio_aula
        FOREIGN KEY (aula_id) REFERENCES aulas(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_solicitud_laboratorio_revisor
        FOREIGN KEY (revisor_id) REFERENCES usuarios(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_solicitud_disponibilidad (aula_id, fecha, estado, hora_inicio, hora_fin),
    INDEX idx_solicitud_usuario (usuario_id, creado_en)
) ENGINE=InnoDB;
