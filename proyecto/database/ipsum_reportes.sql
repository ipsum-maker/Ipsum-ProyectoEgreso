CREATE DATABASE IF NOT EXISTS ipsum_reportes
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ipsum_reportes;

-- 1. Tabla de Usuarios
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

-- Usuarios iniciales de prueba (contraseñas encriptadas)
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

-- Tabla de Aulas, Talleres y Laboratorios
CREATE TABLE IF NOT EXISTS aulas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('taller', 'laboratorio', 'aula') NOT NULL DEFAULT 'taller',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Carga inicial de aulas y laboratorios
INSERT INTO aulas (codigo, nombre, tipo) VALUES
    ('taller1', 'Taller 1', 'taller'),
    ('taller2', 'Taller 2', 'taller'),
    ('taller3', 'Taller 3', 'taller'),
    ('taller4', 'Taller 4', 'taller'),
    ('taller5', 'Taller 5', 'taller'),
    ('lab1', 'Laboratorio 1', 'laboratorio'),
    ('lab2', 'Laboratorio 2', 'laboratorio'),
    ('lab3', 'Laboratorio 3', 'laboratorio')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), tipo = VALUES(tipo);

-- 2. Tabla de Reportes Diarios
CREATE TABLE IF NOT EXISTS reportes_diarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    nombre_profesor VARCHAR(100) NOT NULL,
    apellido_profesor VARCHAR(100) NOT NULL,
    aula VARCHAR(50) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_reporte_diario_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    INDEX idx_reporte_diario_usuario (usuario_id),
    INDEX idx_reporte_diario_fecha (fecha)
) ENGINE=InnoDB;

-- 3. Tabla de Asignación de PCs para Reportes Diarios
CREATE TABLE IF NOT EXISTS reporte_diario_pc (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporte_diario_id INT UNSIGNED NOT NULL,
    pc VARCHAR(50) NOT NULL,
    alumno VARCHAR(150) NOT NULL,
    CONSTRAINT fk_pc_reporte_diario
        FOREIGN KEY (reporte_diario_id)
        REFERENCES reportes_diarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_pc_reporte_diario (reporte_diario_id)
) ENGINE=InnoDB;

-- 4. Tabla de Reportes de Incidencias
CREATE TABLE IF NOT EXISTS reportes_incidencias (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL,
    nombre_profesor VARCHAR(100) NOT NULL,
    apellido_profesor VARCHAR(100) NOT NULL,
    asignatura VARCHAR(100) NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    tipo_solicitud ENUM('rotura-equipo', 'falla-equipo', 'otro') NOT NULL,
    otro_tipo VARCHAR(150) DEFAULT NULL,
    taller VARCHAR(50) NOT NULL,
    descripcion TEXT DEFAULT NULL,
    estado ENUM('pendiente', 'en_proceso', 'resuelto') NOT NULL DEFAULT 'pendiente',
    solucion TEXT DEFAULT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_incidencia_usuarios
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    INDEX idx_incidencia_usuario (usuario_id),
    INDEX idx_incidencia_fecha (fecha),
    INDEX idx_incidencia_estado (estado)
) ENGINE=InnoDB;
