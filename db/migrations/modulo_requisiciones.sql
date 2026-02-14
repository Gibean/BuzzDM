-- ==========================================
-- MÓDULO DE REQUISICIONES DE COMPRA
-- BuzzDM v1.3.12
-- Fecha: 2026-02-07
-- ==========================================

-- ==========================================
-- 1. TABLA PRINCIPAL: REQUISICIONES
-- ==========================================
CREATE TABLE IF NOT EXISTS `requisiciones` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(20) NOT NULL COMMENT 'Formato: REQ-YYYY-NNNN',
    `fecha_solicitud` DATE NOT NULL,
    `estado` ENUM('pendiente', 'aprobada', 'rechazada', 'procesada') NOT NULL DEFAULT 'pendiente',
    `prioridad` ENUM('baja', 'media', 'alta', 'urgente') NOT NULL DEFAULT 'media',
    `notas` TEXT DEFAULT NULL,
    `usuario_id` INT(10) UNSIGNED NOT NULL COMMENT 'Solicitante',
    `aprobado_por` INT(10) UNSIGNED DEFAULT NULL,
    `fecha_aprobacion` TIMESTAMP NULL DEFAULT NULL,
    `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `actualizado_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_codigo` (`codigo`),
    KEY `idx_estado` (`estado`),
    KEY `idx_fecha` (`fecha_solicitud`),
    KEY `idx_prioridad` (`prioridad`),
    CONSTRAINT `fk_req_usuario` FOREIGN KEY (`usuario_id`)
        REFERENCES `usuarios`(`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_req_aprobador` FOREIGN KEY (`aprobado_por`)
        REFERENCES `usuarios`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Encabezado de requisiciones de compra';

-- ==========================================
-- 2. TABLA DETALLE: LINEAS DE REQUISICION
-- ==========================================
CREATE TABLE IF NOT EXISTS `detalle_requisicion` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `requisicion_id` INT(10) UNSIGNED NOT NULL,
    `producto_id` INT(20) UNSIGNED NOT NULL,
    `cantidad_solicitada` INT(10) UNSIGNED NOT NULL DEFAULT 1,
    `proveedor_sugerido_id` INT(11) DEFAULT NULL COMMENT 'IA: mejor precio histórico',
    `precio_sugerido` DECIMAL(12,4) DEFAULT NULL COMMENT 'Último precio conocido',
    `proveedor_seleccionado_id` INT(11) DEFAULT NULL COMMENT 'Override manual usuario',
    `notas` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_req_producto` (`requisicion_id`, `producto_id`),
    KEY `idx_producto` (`producto_id`),
    KEY `idx_prov_sugerido` (`proveedor_sugerido_id`),
    KEY `idx_prov_seleccionado` (`proveedor_seleccionado_id`),
    CONSTRAINT `fk_detreq_requisicion` FOREIGN KEY (`requisicion_id`)
        REFERENCES `requisiciones`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_detreq_producto` FOREIGN KEY (`producto_id`)
        REFERENCES `productos`(`id`) ON UPDATE CASCADE,
    CONSTRAINT `fk_detreq_prov_sugerido` FOREIGN KEY (`proveedor_sugerido_id`)
        REFERENCES `proveedores`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_detreq_prov_seleccionado` FOREIGN KEY (`proveedor_seleccionado_id`)
        REFERENCES `proveedores`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Detalle de productos en requisición';

-- ==========================================
-- 3. FUNCIÓN: GENERAR CÓDIGO REQUISICIÓN
-- ==========================================
DROP FUNCTION IF EXISTS `fn_generar_codigo_requisicion`;
DELIMITER //
CREATE FUNCTION `fn_generar_codigo_requisicion`()
RETURNS VARCHAR(20) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
READS SQL DATA DETERMINISTIC
BEGIN
    DECLARE v_year CHAR(4);
    DECLARE v_next_num INT;

    SET v_year = YEAR(CURDATE());

    SELECT COALESCE(MAX(CAST(SUBSTRING(codigo, 10) AS UNSIGNED)), 0) + 1
    INTO v_next_num
    FROM requisiciones
    WHERE codigo LIKE CONCAT('REQ-', v_year, '-%');

    RETURN CONCAT('REQ-', v_year, '-', LPAD(v_next_num, 4, '0'));
END//
DELIMITER ;

-- ==========================================
-- 4. VISTA: PRODUCTOS CON STOCK BAJO (SOLO ACTIVOS)
-- Para sugerir productos a agregar a requisición
-- ==========================================
DROP VIEW IF EXISTS `vw_productos_stock_bajo_activos`;
CREATE VIEW `vw_productos_stock_bajo_activos` AS
SELECT
    p.id,
    p.sku,
    p.nombre,
    p.stock,
    p.min_stock,
    p.costo_ultimo,
    p.proveedor_id AS proveedor_default_id,
    prov.nombre AS proveedor_default_nombre,
    CASE
        WHEN p.stock = 0 THEN 'agotado'
        WHEN p.stock <= p.min_stock THEN 'bajo'
        ELSE 'normal'
    END AS nivel_stock,
    (p.min_stock - p.stock) AS cantidad_faltante
FROM productos p
LEFT JOIN proveedores prov ON prov.id = p.proveedor_id
WHERE p.estado != 'inactivo'  -- ✅ SOLO PRODUCTOS ACTIVOS (usando estado enum)
  AND p.stock <= p.min_stock
ORDER BY
    CASE WHEN p.stock = 0 THEN 0 ELSE 1 END,  -- Agotados primero
    (p.min_stock - p.stock) DESC;              -- Luego por déficit

-- ==========================================
-- 5. PERMISOS DE ROL
-- ==========================================
-- Agregar permisos solo si no existen
INSERT IGNORE INTO permisos_rol (rol, pagina) VALUES
('admin', 'requisiciones.php'),
('admin', 'api_requisiciones.php'),
('supervisor', 'requisiciones.php'),
('supervisor', 'api_requisiciones.php'),
('operador', 'requisiciones.php'),
('operador', 'api_requisiciones.php');

-- ==========================================
-- VERIFICACIÓN
-- ==========================================
SELECT 'Módulo Requisiciones instalado correctamente' AS status;
SELECT COUNT(*) AS productos_stock_bajo FROM vw_productos_stock_bajo_activos;
