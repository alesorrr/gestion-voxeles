-- ============================================================
--  Gestión Voxeles - Esquema de base de datos
--  Sistema de gestión para emprendimiento de impresión 3D
--  Motor: MySQL 8.x / MariaDB 10.x
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
--  Base de datos
-- ------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `gestion_voxeles`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `gestion_voxeles`;

-- ------------------------------------------------------------
--  Tabla: usuarios
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`        VARCHAR(120) NOT NULL,
    `usuario`       VARCHAR(60)  NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `rol`           ENUM('admin','operador') NOT NULL DEFAULT 'admin',
    `activo`        TINYINT(1)   NOT NULL DEFAULT 1,
    `creado_en`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_usuarios_usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabla: clientes
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `clientes`;
CREATE TABLE `clientes` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`     VARCHAR(150) NOT NULL,
    `email`      VARCHAR(150) DEFAULT NULL,
    `telefono`   VARCHAR(40)  DEFAULT NULL,
    `empresa`    VARCHAR(150) DEFAULT NULL,
    `notas`      TEXT         DEFAULT NULL,
    `creado_en`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_clientes_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabla: estados_orden (columnas del Kanban)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `estados_orden`;
CREATE TABLE `estados_orden` (
    `id`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`  VARCHAR(80)  NOT NULL,
    `slug`    VARCHAR(80)  NOT NULL,
    `color`   VARCHAR(20)  NOT NULL DEFAULT '#6c757d',
    `orden`   INT UNSIGNED NOT NULL DEFAULT 0,
    `es_final` TINYINT(1)  NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_estados_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabla: ordenes_trabajo (MOP)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `ordenes_trabajo`;
CREATE TABLE `ordenes_trabajo` (
    `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cliente_id`         INT UNSIGNED NOT NULL,
    `estado_id`          INT UNSIGNED NOT NULL,
    `nombre_proyecto`    VARCHAR(180) NOT NULL,
    `archivo_3d`         VARCHAR(255) DEFAULT NULL,
    `material`           ENUM('PLA','PETG','ASA','TPU','Resina','Nylon','Otro') NOT NULL DEFAULT 'PLA',
    `color`              VARCHAR(60)  DEFAULT NULL,
    `peso_estimado_g`    DECIMAL(10,2) NOT NULL DEFAULT 0,
    `tiempo_estimado_hs` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `infill_porcentaje`  TINYINT UNSIGNED NOT NULL DEFAULT 20,
    `costo_material`     DECIMAL(12,2) NOT NULL DEFAULT 0,
    `precio_final`       DECIMAL(12,2) NOT NULL DEFAULT 0,
    `pagado`             TINYINT(1)   NOT NULL DEFAULT 0,
    `fecha_pago`         DATE         DEFAULT NULL,
    `notas`              TEXT         DEFAULT NULL,
    `creado_en`          TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ordenes_cliente` (`cliente_id`),
    KEY `idx_ordenes_estado`  (`estado_id`),
    KEY `idx_ordenes_pagado`  (`pagado`),
    CONSTRAINT `fk_ordenes_cliente` FOREIGN KEY (`cliente_id`)
        REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ordenes_estado` FOREIGN KEY (`estado_id`)
        REFERENCES `estados_orden` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabla: gastos (egresos manuales)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `gastos`;
CREATE TABLE `gastos` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `categoria`   ENUM('Materiales','Repuestos','Electricidad','Herramientas','Marketing','Envios','Impuestos','Otro') NOT NULL DEFAULT 'Otro',
    `descripcion` VARCHAR(200) NOT NULL,
    `monto`       DECIMAL(12,2) NOT NULL DEFAULT 0,
    `fecha`       DATE         NOT NULL,
    `creado_en`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_gastos_fecha` (`fecha`),
    KEY `idx_gastos_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  Tabla: ingresos (registro de ingresos, ligado a órdenes)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `ingresos`;
CREATE TABLE `ingresos` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `orden_id`    INT UNSIGNED DEFAULT NULL,
    `descripcion` VARCHAR(200) NOT NULL,
    `monto`       DECIMAL(12,2) NOT NULL DEFAULT 0,
    `fecha`       DATE         NOT NULL,
    `creado_en`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ingresos_fecha` (`fecha`),
    KEY `idx_ingresos_orden` (`orden_id`),
    CONSTRAINT `fk_ingresos_orden` FOREIGN KEY (`orden_id`)
        REFERENCES `ordenes_trabajo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
--  Datos iniciales
-- ============================================================

-- Estados del Kanban (en orden de flujo)
INSERT INTO `estados_orden` (`nombre`, `slug`, `color`, `orden`, `es_final`) VALUES
    ('Pendiente / Presupuestado',   'pendiente',    '#6c757d', 1, 0),
    ('En Cola de Impresión',        'en-cola',      '#0d6efd', 2, 0),
    ('Imprimiendo',                 'imprimiendo',  '#fd7e14', 3, 0),
    ('Post-procesamiento / Acabado','postproceso',  '#6f42c1', 4, 0),
    ('Listo para Entrega / Enviado','listo',        '#20c997', 5, 0),
    ('Completado / Pagado',         'completado',   '#198754', 6, 1);

-- Usuario administrador
-- Usuario: admin | Contraseña: admin123
-- (hash generado con password_hash('admin123', PASSWORD_DEFAULT))
INSERT INTO `usuarios` (`nombre`, `usuario`, `password_hash`, `rol`, `activo`) VALUES
    ('Administrador', 'admin', '$2y$10$Ro0IoOrBCNX092UXUDoIluVM2Iu6bOgU2mic145NQ3D.PEL870lPC', 'admin', 1);

-- Cliente de ejemplo
INSERT INTO `clientes` (`nombre`, `email`, `telefono`, `empresa`, `notas`) VALUES
    ('Cliente de Ejemplo', 'ejemplo@correo.com', '099123456', NULL, 'Cliente cargado automáticamente para pruebas.');

SET FOREIGN_KEY_CHECKS = 1;
