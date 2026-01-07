-- =====================================================
-- FASE 2: SISTEMA MULTI-ALMACÉN
-- Ejecutar en: dbuzz
-- Fecha: 2026-01-05
-- =====================================================

-- 1. Tabla de Almacenes
CREATE TABLE IF NOT EXISTS `almacenes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(20) NOT NULL COMMENT 'Ej: ALM-001, BOD-CEN, TDA-01',
    `nombre` VARCHAR(100) NOT NULL,
    `direccion` VARCHAR(255) DEFAULT NULL,
    `telefono` VARCHAR(20) DEFAULT NULL,
    `tipo` ENUM('almacen', 'bodega', 'tienda', 'deposito', 'otro') NOT NULL DEFAULT 'almacen',
    `es_principal` BOOLEAN DEFAULT FALSE COMMENT 'Almacén principal/default',
    `activo` BOOLEAN DEFAULT TRUE,
    `responsable_id` INT UNSIGNED DEFAULT NULL COMMENT 'Usuario responsable',
    `notas` TEXT DEFAULT NULL,
    `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `actualizado_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_codigo` (`codigo`),
    KEY `idx_activo` (`activo`),
    KEY `idx_tipo` (`tipo`),
    KEY `fk_almacen_responsable` (`responsable_id`),
    CONSTRAINT `fk_almacen_responsable` FOREIGN KEY (`responsable_id`)
        REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Almacenes/Bodegas/Tiendas del sistema';

-- 2. Tabla de Ubicaciones dentro de cada almacén
CREATE TABLE IF NOT EXISTS `ubicaciones` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `almacen_id` INT UNSIGNED NOT NULL,
    `codigo` VARCHAR(30) NOT NULL COMMENT 'Ej: A-01-E1-N2 (Pasillo-Estante-Nivel)',
    `nombre` VARCHAR(100) DEFAULT NULL,
    `pasillo` VARCHAR(10) DEFAULT NULL,
    `estante` VARCHAR(10) DEFAULT NULL,
    `nivel` VARCHAR(10) DEFAULT NULL,
    `capacidad_maxima` INT DEFAULT NULL COMMENT 'Unidades máximas',
    `activo` BOOLEAN DEFAULT TRUE,
    `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_almacen_codigo` (`almacen_id`, `codigo`),
    KEY `idx_activo` (`activo`),
    CONSTRAINT `fk_ubicacion_almacen` FOREIGN KEY (`almacen_id`)
        REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Ubicaciones físicas dentro de cada almacén';

-- 3. Tabla de Stock por Ubicación
CREATE TABLE IF NOT EXISTS `stock_ubicacion` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `producto_id` INT UNSIGNED NOT NULL,
    `almacen_id` INT UNSIGNED NOT NULL,
    `ubicacion_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL = En tránsito/sin ubicar',
    `cantidad` INT NOT NULL DEFAULT 0,
    `stock_minimo` INT DEFAULT 0 COMMENT 'Stock mínimo para esta ubicación',
    `stock_maximo` INT DEFAULT NULL COMMENT 'Capacidad máxima',
    `actualizado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_producto_ubicacion` (`producto_id`, `almacen_id`, `ubicacion_id`),
    KEY `idx_almacen` (`almacen_id`),
    KEY `idx_producto` (`producto_id`),
    CONSTRAINT `fk_stock_producto` FOREIGN KEY (`producto_id`)
        REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_stock_almacen` FOREIGN KEY (`almacen_id`)
        REFERENCES `almacenes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_stock_ubicacion` FOREIGN KEY (`ubicacion_id`)
        REFERENCES `ubicaciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stock de productos por ubicación';

-- 4. Agregar campo almacen_id a cargos_existencias (si no existe)
-- Verificar primero si la columna existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cargos_existencias'
    AND COLUMN_NAME = 'almacen_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `cargos_existencias` ADD COLUMN `almacen_id` INT UNSIGNED DEFAULT NULL AFTER `proveedor_id`',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar foreign key si no existe
SET @fk_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'cargos_existencias'
    AND CONSTRAINT_NAME = 'fk_cargo_almacen'
);

SET @sql = IF(@fk_exists = 0 AND @column_exists = 0,
    'ALTER TABLE `cargos_existencias` ADD CONSTRAINT `fk_cargo_almacen` FOREIGN KEY (`almacen_id`) REFERENCES `almacenes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Agregar campos almacen_id y ubicacion_id a movimientos_inventario (si no existen)
SET @almacen_col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'movimientos_inventario'
    AND COLUMN_NAME = 'almacen_id'
);

SET @sql = IF(@almacen_col_exists = 0,
    'ALTER TABLE `movimientos_inventario` ADD COLUMN `almacen_id` INT UNSIGNED DEFAULT NULL AFTER `cargo_id`',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @ubicacion_col_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'movimientos_inventario'
    AND COLUMN_NAME = 'ubicacion_id'
);

SET @sql = IF(@ubicacion_col_exists = 0,
    'ALTER TABLE `movimientos_inventario` ADD COLUMN `ubicacion_id` INT UNSIGNED DEFAULT NULL AFTER `almacen_id`',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Vista de stock consolidado por almacén
DROP VIEW IF EXISTS `v_stock_por_almacen`;
CREATE VIEW `v_stock_por_almacen` AS
SELECT
    a.id AS almacen_id,
    a.codigo AS almacen_codigo,
    a.nombre AS almacen_nombre,
    p.id AS producto_id,
    p.sku,
    p.nombre AS producto_nombre,
    COALESCE(SUM(su.cantidad), 0) AS stock_almacen,
    COUNT(DISTINCT su.ubicacion_id) AS num_ubicaciones
FROM almacenes a
CROSS JOIN productos p
LEFT JOIN stock_ubicacion su ON su.almacen_id = a.id AND su.producto_id = p.id
WHERE a.activo = 1 AND p.deleted_at IS NULL
GROUP BY a.id, p.id
ORDER BY a.nombre, p.nombre;

-- 7. Vista de stock detallado por ubicación
DROP VIEW IF EXISTS `v_stock_detallado`;
CREATE VIEW `v_stock_detallado` AS
SELECT
    su.id,
    a.id AS almacen_id,
    a.codigo AS almacen_codigo,
    a.nombre AS almacen_nombre,
    u.id AS ubicacion_id,
    u.codigo AS ubicacion_codigo,
    CONCAT(COALESCE(u.pasillo, ''), '-', COALESCE(u.estante, ''), '-', COALESCE(u.nivel, '')) AS ubicacion_full,
    p.id AS producto_id,
    p.sku,
    p.nombre AS producto_nombre,
    su.cantidad,
    su.stock_minimo,
    su.stock_maximo,
    CASE
        WHEN su.cantidad <= su.stock_minimo THEN 'bajo'
        WHEN su.stock_maximo IS NOT NULL AND su.cantidad >= su.stock_maximo THEN 'lleno'
        ELSE 'normal'
    END AS estado_stock
FROM stock_ubicacion su
INNER JOIN almacenes a ON a.id = su.almacen_id
INNER JOIN productos p ON p.id = su.producto_id
LEFT JOIN ubicaciones u ON u.id = su.ubicacion_id
WHERE a.activo = 1;

-- 8. Insertar almacén principal por defecto
INSERT INTO `almacenes` (`codigo`, `nombre`, `tipo`, `es_principal`, `activo`)
VALUES ('ALM-PRINCIPAL', 'Almacén Principal', 'almacen', TRUE, TRUE)
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`);

-- 9. Migrar stock existente al almacén principal
-- Esto crea registros en stock_ubicacion para productos que ya tienen stock
INSERT INTO `stock_ubicacion` (`producto_id`, `almacen_id`, `cantidad`)
SELECT
    p.id,
    (SELECT id FROM almacenes WHERE es_principal = TRUE LIMIT 1),
    p.stock
FROM productos p
WHERE p.deleted_at IS NULL AND p.stock > 0
ON DUPLICATE KEY UPDATE cantidad = VALUES(cantidad);

-- =====================================================
-- FIN FASE 2
-- =====================================================
