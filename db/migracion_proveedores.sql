-- =====================================================
-- MIGRACIÓN: Actualizar tabla PROVEEDORES
-- Ejecutar en: dbuzz
-- Fecha: 2026-01-05
-- =====================================================

-- Agregar columna RIF si no existe
SET @rif_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'proveedores'
    AND COLUMN_NAME = 'rif'
);

SET @sql = IF(@rif_exists = 0,
    'ALTER TABLE `proveedores` ADD COLUMN `rif` VARCHAR(50) DEFAULT NULL AFTER `nombre`',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columna NOTAS si no existe
SET @notas_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'proveedores'
    AND COLUMN_NAME = 'notas'
);

SET @sql = IF(@notas_exists = 0,
    'ALTER TABLE `proveedores` ADD COLUMN `notas` TEXT DEFAULT NULL AFTER `direccion`',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columna ACTUALIZADO_AT si no existe
SET @actualizado_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'proveedores'
    AND COLUMN_NAME = 'actualizado_at'
);

SET @sql = IF(@actualizado_exists = 0,
    'ALTER TABLE `proveedores` ADD COLUMN `actualizado_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `creado_at`',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar columna DELETED_AT si no existe (para soft delete)
SET @deleted_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'proveedores'
    AND COLUMN_NAME = 'deleted_at'
);

SET @sql = IF(@deleted_exists = 0,
    'ALTER TABLE `proveedores` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `actualizado_at`',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Crear índice para soft delete si no existe
SET @idx_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'proveedores'
    AND INDEX_NAME = 'idx_deleted_at'
);

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE `proveedores` ADD INDEX `idx_deleted_at` (`deleted_at`)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- VERIFICACIÓN
-- =====================================================

SELECT
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'proveedores'
ORDER BY ORDINAL_POSITION;

-- =====================================================
-- FIN MIGRACIÓN PROVEEDORES
-- =====================================================
