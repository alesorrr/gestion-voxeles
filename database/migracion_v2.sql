-- ============================================================
--  Gestión Voxeles - Migración v2
--  Aplica los cambios nuevos SOBRE una base de datos que ya
--  tiene datos (NO borra nada). Importar desde phpMyAdmin con
--  la base de datos ya seleccionada.
--
--  Novedades:
--   - Rol "ventas" (Usuario Ventas) en usuarios
--   - Nuevos campos en ordenes_trabajo (fechas, cantidad, etc.)
--   - Materiales ABS y Flex
--   - Tabla presupuestos (calculadora de costos)
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
--  usuarios: nuevo rol "ventas"
-- ------------------------------------------------------------
ALTER TABLE `usuarios`
    MODIFY `rol` ENUM('admin','operador','ventas') NOT NULL DEFAULT 'admin';

-- ------------------------------------------------------------
--  ordenes_trabajo: nuevos materiales y campos del MOP
-- ------------------------------------------------------------
ALTER TABLE `ordenes_trabajo`
    MODIFY `material` ENUM('PLA','PETG','ASA','ABS','TPU','Flex','Resina','Nylon','Otro') NOT NULL DEFAULT 'PLA';

ALTER TABLE `ordenes_trabajo`
    ADD COLUMN `altura_capa`     DECIMAL(4,2) NOT NULL DEFAULT 0.20 AFTER `infill_porcentaje`,
    ADD COLUMN `cantidad_piezas` INT UNSIGNED NOT NULL DEFAULT 1   AFTER `altura_capa`,
    ADD COLUMN `metodo_contacto` VARCHAR(120) DEFAULT NULL          AFTER `cantidad_piezas`,
    ADD COLUMN `fecha_estimada`  DATE         DEFAULT NULL          AFTER `metodo_contacto`,
    ADD COLUMN `fecha_limite`    DATE         DEFAULT NULL          AFTER `fecha_estimada`;

-- ------------------------------------------------------------
--  Tabla nueva: presupuestos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `presupuestos` (
    `id`                   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre_pieza`         VARCHAR(180) NOT NULL,
    `cliente_id`           INT UNSIGNED DEFAULT NULL,
    `material`             ENUM('PLA','PETG','ASA','ABS','TPU','Flex','Resina','Nylon','Otro') NOT NULL DEFAULT 'PLA',
    `costo_kg`             DECIMAL(12,2) NOT NULL DEFAULT 0,
    `peso_g`               DECIMAL(10,2) NOT NULL DEFAULT 0,
    `tiempo_impresion_hs`  DECIMAL(10,2) NOT NULL DEFAULT 0,
    `tiempo_mano_obra_min` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `costo_mano_obra_hora` DECIMAL(12,2) NOT NULL DEFAULT 0,
    `costo_maquina_hora`   DECIMAL(12,2) NOT NULL DEFAULT 0,
    `potencia_w`           DECIMAL(10,2) NOT NULL DEFAULT 0,
    `precio_kwh`           DECIMAL(10,4) NOT NULL DEFAULT 0,
    `costo_hardware`       DECIMAL(12,2) NOT NULL DEFAULT 0,
    `costo_embalaje`       DECIMAL(12,2) NOT NULL DEFAULT 0,
    `cantidad`             INT UNSIGNED NOT NULL DEFAULT 1,
    `margen_porcentaje`    DECIMAL(6,2) NOT NULL DEFAULT 40,
    `iva_porcentaje`       DECIMAL(6,2) NOT NULL DEFAULT 0,
    `costo_material`       DECIMAL(12,2) NOT NULL DEFAULT 0,
    `costo_electricidad`   DECIMAL(12,2) NOT NULL DEFAULT 0,
    `costo_maquina`        DECIMAL(12,2) NOT NULL DEFAULT 0,
    `costo_mano_obra`      DECIMAL(12,2) NOT NULL DEFAULT 0,
    `costo_total`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    `precio_final`         DECIMAL(12,2) NOT NULL DEFAULT 0,
    `notas`                TEXT         DEFAULT NULL,
    `orden_id`             INT UNSIGNED DEFAULT NULL,
    `creado_en`            TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `actualizado_en`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_presupuestos_cliente` (`cliente_id`),
    KEY `idx_presupuestos_orden` (`orden_id`),
    CONSTRAINT `fk_presupuestos_cliente` FOREIGN KEY (`cliente_id`)
        REFERENCES `clientes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_presupuestos_orden` FOREIGN KEY (`orden_id`)
        REFERENCES `ordenes_trabajo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
