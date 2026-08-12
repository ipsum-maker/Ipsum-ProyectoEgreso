-- Base de datos para el proyecto Ipsum.
-- Importar este archivo desde phpMyAdmin antes de iniciar el sistema.

CREATE DATABASE IF NOT EXISTS ipsum_reportes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ipsum_reportes;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    cedula VARCHAR(8) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'coordinador', 'profesor') NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    es_inicial TINYINT(1) NOT NULL DEFAULT 0,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_usuarios_rol (rol),
    INDEX idx_usuarios_activo (activo)
) ENGINE=InnoDB;

-- Usuarios iniciales de prueba.
-- Las cédulas se guardan sin puntos ni guion.
-- Elvis: 1.234.567-8 / contraseña: 123
-- Pepita: 2.345.678-9 / contraseña: Coord123!
-- Pepito: 3.456.789-0 / contraseña: Profe123!
INSERT INTO usuarios (nombre, cedula, contrasena_hash, rol, activo, es_inicial)
VALUES
    ('Elvis', '12345678', '$2y$12$J.qWMrE4MVtYHlNGHQ/smO9irZNnm1WWf3T4sYF1ZM81reIFV2jRS', 'administrador', 1, 1),
    ('Pepita', '23456789', '$2y$12$RZ/L4v0cmBcGSBmEt2UFTeE0vyBdsgak7NTtfO0BsgBo5yWGFl.AO', 'coordinador', 1, 0),
    ('Pepito', '34567890', '$2y$12$fZ9lfVedv2UKqmrJBl9fIeYHIGg8BujyirURpui7KCixWrXkkOh/a', 'profesor', 1, 0)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    contrasena_hash = VALUES(contrasena_hash),
    rol = VALUES(rol),
    activo = VALUES(activo),
    es_inicial = VALUES(es_inicial);
