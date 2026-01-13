-- ============================================================================
-- BuzzDM - Database Cleanup Migration
-- Version: 1.0
-- Date: 2026-01-06
-- Description: Cleans duplicate tables, adds indexes, creates permission table
-- ============================================================================

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- 1. DROP UNUSED TABLES
-- ============================================================================

-- password_recovery is unused (only password_reset_tokens is used in PHP code)
DROP TABLE IF EXISTS `password_recovery`;

-- ============================================================================
-- 2. ADD MISSING FOREIGN KEY TO logs_login
-- ============================================================================

-- First check if FK already exists and drop it if so
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'logs_login'
    AND CONSTRAINT_NAME = 'FK_logs_login_usuarios'
);

-- Only add if it doesn't exist
-- Note: We need to use a procedure or just try to add and ignore error
ALTER TABLE `logs_login`
ADD CONSTRAINT `FK_logs_login_usuarios`
FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`)
ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================================================
-- 3. ADD MISSING INDEXES FOR PERFORMANCE
-- ============================================================================

-- productos.estado - frequently filtered
CREATE INDEX IF NOT EXISTS `idx_estado` ON `productos` (`estado`);

-- pedidos.estado_pago - used in dashboard queries
CREATE INDEX IF NOT EXISTS `idx_estado_pago` ON `pedidos` (`estado_pago`);

-- movimientos_inventario.tipo_movimiento - filtered in reports
CREATE INDEX IF NOT EXISTS `idx_tipo_movimiento` ON `movimientos_inventario` (`tipo_movimiento`);

-- detalle_pedido composite index for JOINs
CREATE INDEX IF NOT EXISTS `idx_pedido_producto` ON `detalle_pedido` (`pedido_id`, `producto_id`);

-- ============================================================================
-- 4. CLEAN DUPLICATE stock_ubicacion ENTRIES
-- ============================================================================

-- Delete duplicates keeping the lowest ID for each product/almacen/ubicacion combo
DELETE su1 FROM `stock_ubicacion` su1
INNER JOIN `stock_ubicacion` su2
    ON su1.producto_id = su2.producto_id
    AND su1.almacen_id = su2.almacen_id
    AND (su1.ubicacion_id = su2.ubicacion_id OR (su1.ubicacion_id IS NULL AND su2.ubicacion_id IS NULL))
WHERE su1.id > su2.id;

-- ============================================================================
-- 5. CREATE PERMISSIONS TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `permisos_rol` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rol` ENUM('admin','supervisor','operador') NOT NULL,
    `pagina` VARCHAR(100) NOT NULL COMMENT 'Nombre del archivo PHP permitido',
    `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_rol_pagina` (`rol`, `pagina`),
    INDEX `idx_rol` (`rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Permisos de acceso por rol a páginas del sistema';

-- ============================================================================
-- 6. POPULATE PERMISSIONS FROM EXISTING HARDCODED ARRAY
-- ============================================================================

-- Admin permissions (complete access)
INSERT IGNORE INTO `permisos_rol` (`rol`, `pagina`) VALUES
('admin', 'dashboard.php'),
('admin', 'pedidos.php'),
('admin', 'envios.php'),
('admin', 'inventario.php'),
('admin', 'importacion.php'),
('admin', 'admin.php'),
('admin', 'about.php'),
('admin', 'manual.php'),
('admin', 'login.php'),
('admin', 'logout.php'),
('admin', 'api_sales_chart.php'),
('admin', 'create_admin.php'),
('admin', 'get_import_history.php'),
('admin', 'product_actions.php'),
('admin', 'users.php'),
('admin', 'sesion.php'),
('admin', 'db.php'),
('admin', 'sidebar.php'),
('admin', 'reports.php'),
('admin', 'pedidos_actions.php'),
('admin', 'search_api.php'),
('admin', 'clientes.php'),
('admin', 'clientes_actions.php'),
('admin', 'api_sales_by_status_chart.php'),
('admin', 'envios_actions.php'),
('admin', 'company_settings.php'),
('admin', 'company_settings_actions.php'),
('admin', 'backup.php'),
('admin', 'backups.php'),
('admin', 'backup_status.php'),
('admin', 'logs.php'),
('admin', 'process_import.php'),
('admin', 'settings_actions.php'),
('admin', 'cargos_existencias.php'),
('admin', 'almacenes.php'),
('admin', 'cargos_actions.php'),
('admin', 'productos_search.php'),
('admin', 'catalogos_actions.php'),
('admin', 'kit_actions.php'),
('admin', 'almacenes_actions.php'),
('admin', 'ubicaciones_actions.php'),
('admin', 'proveedores.php'),
('admin', 'proveedores_actions.php');

-- Supervisor permissions
INSERT IGNORE INTO `permisos_rol` (`rol`, `pagina`) VALUES
('supervisor', 'dashboard.php'),
('supervisor', 'pedidos.php'),
('supervisor', 'envios.php'),
('supervisor', 'inventario.php'),
('supervisor', 'importacion.php'),
('supervisor', 'about.php'),
('supervisor', 'manual.php'),
('supervisor', 'login.php'),
('supervisor', 'logout.php'),
('supervisor', 'api_sales_chart.php'),
('supervisor', 'get_import_history.php'),
('supervisor', 'product_actions.php'),
('supervisor', 'users.php'),
('supervisor', 'sesion.php'),
('supervisor', 'db.php'),
('supervisor', 'sidebar.php'),
('supervisor', 'reports.php'),
('supervisor', 'pedidos_actions.php'),
('supervisor', 'search_api.php'),
('supervisor', 'clientes.php'),
('supervisor', 'clientes_actions.php'),
('supervisor', 'api_sales_by_status_chart.php'),
('supervisor', 'envios_actions.php'),
('supervisor', 'process_import.php'),
('supervisor', 'cargos_existencias.php'),
('supervisor', 'almacenes.php'),
('supervisor', 'cargos_actions.php'),
('supervisor', 'productos_search.php'),
('supervisor', 'catalogos_actions.php'),
('supervisor', 'kit_actions.php'),
('supervisor', 'almacenes_actions.php'),
('supervisor', 'ubicaciones_actions.php'),
('supervisor', 'proveedores.php'),
('supervisor', 'proveedores_actions.php');

-- Operador permissions (limited access)
INSERT IGNORE INTO `permisos_rol` (`rol`, `pagina`) VALUES
('operador', 'dashboard.php'),
('operador', 'pedidos.php'),
('operador', 'envios.php'),
('operador', 'about.php'),
('operador', 'manual.php'),
('operador', 'login.php'),
('operador', 'logout.php'),
('operador', 'api_sales_chart.php'),
('operador', 'sesion.php'),
('operador', 'db.php'),
('operador', 'sidebar.php'),
('operador', 'pedidos_actions.php'),
('operador', 'search_api.php'),
('operador', 'api_sales_by_status_chart.php'),
('operador', 'envios_actions.php'),
('operador', 'product_actions.php');

-- ============================================================================
-- 7. CREATE EXCHANGE RATE TABLE
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tasas_cambio` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tasa` DECIMAL(18,4) NOT NULL COMMENT 'Valor de 1 USD en moneda local (VES)',
    `fuente` VARCHAR(50) DEFAULT 'manual' COMMENT 'Fuente: manual, bcv, paralelo, etc.',
    `fecha` DATE NOT NULL COMMENT 'Fecha para la cual aplica esta tasa',
    `creado_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `usuario_id` INT UNSIGNED NULL COMMENT 'Usuario que registró la tasa',
    UNIQUE KEY `uk_fecha` (`fecha`),
    INDEX `idx_fecha` (`fecha`),
    CONSTRAINT `FK_tasa_usuario` FOREIGN KEY (`usuario_id`)
        REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tasa de cambio diaria USD para referencia de precios';

-- ============================================================================
-- 8. CREATE VIEW FOR TODAY'S EXCHANGE RATE
-- ============================================================================

DROP VIEW IF EXISTS `v_tasa_hoy`;
CREATE VIEW `v_tasa_hoy` AS
SELECT
    COALESCE(
        (SELECT tasa FROM tasas_cambio WHERE fecha = CURDATE() LIMIT 1),
        (SELECT tasa FROM tasas_cambio WHERE fecha < CURDATE() ORDER BY fecha DESC LIMIT 1),
        1
    ) AS tasa_actual,
    COALESCE(
        (SELECT fecha FROM tasas_cambio WHERE fecha = CURDATE() LIMIT 1),
        (SELECT fecha FROM tasas_cambio WHERE fecha < CURDATE() ORDER BY fecha DESC LIMIT 1),
        CURDATE()
    ) AS fecha_tasa,
    COALESCE(
        (SELECT fuente FROM tasas_cambio WHERE fecha = CURDATE() LIMIT 1),
        (SELECT fuente FROM tasas_cambio WHERE fecha < CURDATE() ORDER BY fecha DESC LIMIT 1),
        'no_configurada'
    ) AS fuente;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- VERIFICATION QUERIES (run manually to verify)
-- ============================================================================
-- SELECT 'password_recovery table dropped' AS status WHERE NOT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'password_recovery' AND table_schema = DATABASE());
-- SELECT 'permisos_rol created' AS status, COUNT(*) AS total_permisos FROM permisos_rol;
-- SELECT 'tasas_cambio created' AS status FROM information_schema.tables WHERE table_name = 'tasas_cambio' AND table_schema = DATABASE();
-- SELECT * FROM v_tasa_hoy;
