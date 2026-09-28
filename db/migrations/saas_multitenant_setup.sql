-- ============================================================================
-- MIGRACIÓN SAAS DEMO MULTI-TENANT
-- BuzzDM - Control de prueba de 15 días, fingerprinting y aislamiento de datos
-- ============================================================================

-- 1. Ampliar y adaptar la tabla empresa para soporte SaaS Multi-tenant
ALTER TABLE `empresa` 
    MODIFY COLUMN `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    ADD COLUMN `admin_email` VARCHAR(150) NULL AFTER `email`,
    ADD COLUMN `device_fingerprint` VARCHAR(64) NULL AFTER `admin_email`,
    ADD COLUMN `trial_ends_at` TIMESTAMP NULL AFTER `device_fingerprint`,
    ADD COLUMN `is_demo` TINYINT(1) NOT NULL DEFAULT 1 AFTER `trial_ends_at`,
    ADD COLUMN `status` ENUM('active', 'expired', 'suspended') NOT NULL DEFAULT 'active' AFTER `is_demo`,
    ADD UNIQUE KEY `uk_empresa_fingerprint` (`device_fingerprint`),
    ADD KEY `idx_empresa_status_trial` (`status`, `trial_ends_at`);

-- Actualizar empresa demo inicial por defecto si existe
UPDATE `empresa` 
SET `admin_email` = 'admin@demo.com',
    `trial_ends_at` = DATE_ADD(NOW(), INTERVAL 15 DAY),
    `is_demo` = 1,
    `status` = 'active'
WHERE `id` = 1;

-- 2. Agregar empresa_id a tabla usuarios
ALTER TABLE `usuarios`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD KEY `idx_usuarios_empresa` (`empresa_id`),
    ADD CONSTRAINT `fk_usuarios_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 3. Agregar empresa_id a tabla productos
ALTER TABLE `productos`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD KEY `idx_productos_empresa` (`empresa_id`),
    ADD CONSTRAINT `fk_productos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 4. Agregar empresa_id a tabla categorias
ALTER TABLE `categorias`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD KEY `idx_categorias_empresa` (`empresa_id`),
    ADD CONSTRAINT `fk_categorias_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 5. Agregar empresa_id a tabla marcas
ALTER TABLE `marcas`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD KEY `idx_marcas_empresa` (`empresa_id`),
    ADD CONSTRAINT `fk_marcas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 6. Agregar empresa_id a tabla proveedores
ALTER TABLE `proveedores`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD KEY `idx_proveedores_empresa` (`empresa_id`),
    ADD CONSTRAINT `fk_proveedores_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 7. Agregar empresa_id a tabla clientes
ALTER TABLE `clientes`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD KEY `idx_clientes_empresa` (`empresa_id`),
    ADD CONSTRAINT `fk_clientes_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 8. Agregar empresa_id a tabla almacenes
ALTER TABLE `almacenes`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD KEY `idx_almacenes_empresa` (`empresa_id`),
    ADD CONSTRAINT `fk_almacenes_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 9. Agregar empresa_id a tabla cargos_existencias
ALTER TABLE `cargos_existencias`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD KEY `idx_cargos_empresa` (`empresa_id`),
    ADD CONSTRAINT `fk_cargos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 10. Agregar empresa_id a tabla pedidos
ALTER TABLE `pedidos`
    ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD KEY `idx_pedidos_empresa` (`empresa_id`),
    ADD CONSTRAINT `fk_pedidos_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- 11. Agregar empresa_id a tabla transferencias_almacen (si existe)
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'transferencias_almacen');
SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE `transferencias_almacen` ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`, ADD KEY `idx_transf_empresa` (`empresa_id`), ADD CONSTRAINT `fk_transf_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;', 
    'SELECT "Tabla transferencias_almacen no requiere alter"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 12. Agregar empresa_id a tabla requisiciones (si existe)
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'requisiciones');
SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE `requisiciones` ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`, ADD KEY `idx_req_empresa` (`empresa_id`), ADD CONSTRAINT `fk_req_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;', 
    'SELECT "Tabla requisiciones no requiere alter"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 13. Agregar empresa_id a tabla notas
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'notas');
SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE `notas` ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`, ADD KEY `idx_notas_empresa` (`empresa_id`), ADD CONSTRAINT `fk_notas_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;', 
    'SELECT "Tabla notas no requiere alter"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 14. Agregar empresa_id a tabla importaciones (si existe)
SET @table_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'importaciones');
SET @sql = IF(@table_exists > 0, 
    'ALTER TABLE `importaciones` ADD COLUMN `empresa_id` INT(10) UNSIGNED NOT NULL DEFAULT 1 AFTER `id`, ADD KEY `idx_import_empresa` (`empresa_id`), ADD CONSTRAINT `fk_import_empresa` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;', 
    'SELECT "Tabla importaciones no requiere alter"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
