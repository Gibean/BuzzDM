-- =====================================================
-- Migration: Precios por Proveedor
-- Fecha: 2026-02-04
-- Descripción: Tabla para registrar historial de precios
--              de compra por producto y proveedor
-- =====================================================

-- Tabla principal de historial de precios
CREATE TABLE IF NOT EXISTS `precios_proveedor` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `producto_id` INT(20) UNSIGNED NOT NULL,
  `proveedor_id` INT NOT NULL,
  `precio_compra` DECIMAL(12,4) NOT NULL,
  `fecha_registro` DATE NOT NULL,
  `cargo_id` INT UNSIGNED DEFAULT NULL COMMENT 'Cargo donde se registró la compra',
  `es_ultimo` TINYINT(1) DEFAULT 1 COMMENT 'Indica si es el precio más reciente para este producto/proveedor',
  `notas` VARCHAR(255) DEFAULT NULL,
  `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  -- Índices
  KEY `idx_producto` (`producto_id`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_fecha` (`fecha_registro`),
  KEY `idx_es_ultimo` (`es_ultimo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Historial de precios de compra por producto y proveedor';

-- Add foreign keys only if they don't exist (wrapped in procedure for safety)
DELIMITER //
CREATE PROCEDURE add_precios_proveedor_constraints()
BEGIN
    -- Check and add fk_precio_producto
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'precios_proveedor'
        AND CONSTRAINT_NAME = 'fk_precio_producto'
    ) THEN
        ALTER TABLE `precios_proveedor`
            ADD CONSTRAINT `fk_precio_producto` FOREIGN KEY (`producto_id`)
            REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    END IF;

    -- Check and add fk_precio_proveedor
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'precios_proveedor'
        AND CONSTRAINT_NAME = 'fk_precio_proveedor'
    ) THEN
        ALTER TABLE `precios_proveedor`
            ADD CONSTRAINT `fk_precio_proveedor` FOREIGN KEY (`proveedor_id`)
            REFERENCES `proveedores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
    END IF;

    -- Check and add fk_precio_cargo
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'precios_proveedor'
        AND CONSTRAINT_NAME = 'fk_precio_cargo'
    ) THEN
        ALTER TABLE `precios_proveedor`
            ADD CONSTRAINT `fk_precio_cargo` FOREIGN KEY (`cargo_id`)
            REFERENCES `cargos_existencias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
    END IF;

    -- Check and add unique key
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'precios_proveedor'
        AND INDEX_NAME = 'uk_producto_proveedor_fecha'
    ) THEN
        ALTER TABLE `precios_proveedor`
            ADD UNIQUE KEY `uk_producto_proveedor_fecha` (`producto_id`, `proveedor_id`, `fecha_registro`);
    END IF;
END //
DELIMITER ;

-- Execute and drop the procedure
CALL add_precios_proveedor_constraints();
DROP PROCEDURE IF EXISTS add_precios_proveedor_constraints;

-- Verification
SELECT 'precios_proveedor table ready' AS status;
