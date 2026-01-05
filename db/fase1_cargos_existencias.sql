-- =====================================================
-- FASE 1: SISTEMA DE CARGOS DE EXISTENCIAS
-- Base de datos: dbuzz
-- Fecha: 2026-01-05
-- Descripción: Script para crear las tablas y objetos
--              necesarios para el sistema de cargos
--              masivos de existencias en BuzzDM
-- =====================================================
-- INSTRUCCIONES:
-- 1. Ejecutar este script en phpMyAdmin o HeidiSQL
-- 2. Asegúrate de estar conectado a la base 'dbuzz'
-- 3. Ejecutar todo el script de una vez
-- =====================================================

USE `dbuzz`;

-- =====================================================
-- 1. ELIMINAR OBJETOS EXISTENTES (si los hay)
-- =====================================================

-- Eliminar triggers primero
DROP TRIGGER IF EXISTS `trg_cargo_confirmado`;
DROP TRIGGER IF EXISTS `trg_cargo_anulado`;

-- Eliminar vistas
DROP VIEW IF EXISTS `v_cargos_resumen`;
DROP VIEW IF EXISTS `v_cargos_detalle_completo`;

-- Eliminar función
DROP FUNCTION IF EXISTS `fn_generar_codigo_cargo`;

-- Eliminar tablas (orden importante por FKs)
DROP TABLE IF EXISTS `detalle_cargo_existencias`;
DROP TABLE IF EXISTS `cargos_existencias`;

-- =====================================================
-- 2. TABLA PRINCIPAL: CARGOS DE EXISTENCIAS
-- =====================================================

CREATE TABLE `cargos_existencias` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `codigo` VARCHAR(20) NOT NULL COMMENT 'Formato: CGO-YYYY-NNNN',
    `fecha` DATE NOT NULL,
    `concepto` ENUM(
        'compra',
        'donacion',
        'ajuste_positivo',
        'devolucion_cliente',
        'inventario_inicial',
        'otro'
    ) NOT NULL DEFAULT 'compra',
    `concepto_otro` VARCHAR(100) DEFAULT NULL COMMENT 'Descripción si concepto=otro',
    `proveedor_id` INT DEFAULT NULL,
    `referencia_externa` VARCHAR(100) DEFAULT NULL COMMENT 'Nro. Factura, Guía, etc.',
    `notas` TEXT DEFAULT NULL,
    `total_productos` INT UNSIGNED DEFAULT 0 COMMENT 'Cantidad de líneas/productos distintos',
    `total_unidades` INT UNSIGNED DEFAULT 0 COMMENT 'Suma de todas las cantidades',
    `valor_total` DECIMAL(14,2) DEFAULT 0.00 COMMENT 'Suma de subtotales',
    `estado` ENUM('borrador', 'confirmado', 'anulado') NOT NULL DEFAULT 'borrador',
    `registrado_por` INT UNSIGNED NOT NULL COMMENT 'Usuario que creó el cargo',
    `confirmado_por` INT UNSIGNED DEFAULT NULL,
    `fecha_confirmacion` TIMESTAMP NULL DEFAULT NULL,
    `anulado_por` INT UNSIGNED DEFAULT NULL,
    `fecha_anulacion` TIMESTAMP NULL DEFAULT NULL,
    `motivo_anulacion` VARCHAR(255) DEFAULT NULL,
    `creado_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `actualizado_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_codigo` (`codigo`),
    KEY `idx_fecha` (`fecha`),
    KEY `idx_estado` (`estado`),
    KEY `idx_concepto` (`concepto`),
    KEY `fk_cargo_proveedor` (`proveedor_id`),
    KEY `fk_cargo_registrado` (`registrado_por`),
    CONSTRAINT `fk_cargo_proveedor` FOREIGN KEY (`proveedor_id`)
        REFERENCES `proveedores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_cargo_registrado` FOREIGN KEY (`registrado_por`)
        REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_cargo_confirmado` FOREIGN KEY (`confirmado_por`)
        REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `fk_cargo_anulado` FOREIGN KEY (`anulado_por`)
        REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Encabezado de cargos masivos de existencias';

-- =====================================================
-- 3. TABLA DE DETALLE (LÍNEAS DEL CARGO)
-- =====================================================

CREATE TABLE `detalle_cargo_existencias` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cargo_id` INT UNSIGNED NOT NULL,
    `producto_id` INT UNSIGNED NOT NULL,
    `cantidad` INT UNSIGNED NOT NULL DEFAULT 1,
    `costo_unitario` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `subtotal` DECIMAL(14,2) GENERATED ALWAYS AS (`cantidad` * `costo_unitario`) STORED,
    `notas` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_cargo` (`cargo_id`),
    KEY `idx_producto` (`producto_id`),
    UNIQUE KEY `uk_cargo_producto` (`cargo_id`, `producto_id`) COMMENT 'Un producto solo una vez por cargo',
    CONSTRAINT `fk_detalle_cargo` FOREIGN KEY (`cargo_id`)
        REFERENCES `cargos_existencias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`producto_id`)
        REFERENCES `productos` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Líneas de productos en cada cargo de existencias';

-- =====================================================
-- 4. MODIFICAR TABLA MOVIMIENTOS_INVENTARIO
-- =====================================================

-- Agregar columna para enlazar con cargos (si no existe)
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'dbuzz'
      AND TABLE_NAME = 'movimientos_inventario'
      AND COLUMN_NAME = 'cargo_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `movimientos_inventario`
     ADD COLUMN `cargo_id` INT UNSIGNED DEFAULT NULL AFTER `justificacion`,
     ADD KEY `fk_mov_cargo` (`cargo_id`),
     ADD CONSTRAINT `fk_mov_cargo` FOREIGN KEY (`cargo_id`)
         REFERENCES `cargos_existencias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT "Columna cargo_id ya existe en movimientos_inventario"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 5. AGREGAR CAMPOS DE COSTEO A PRODUCTOS
-- =====================================================

-- Agregar costo_promedio si no existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'dbuzz'
      AND TABLE_NAME = 'productos'
      AND COLUMN_NAME = 'costo_promedio'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `productos` ADD COLUMN `costo_promedio` DECIMAL(12,4) DEFAULT 0.0000 AFTER `precio`',
    'SELECT "Columna costo_promedio ya existe"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Agregar costo_ultimo si no existe
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'dbuzz'
      AND TABLE_NAME = 'productos'
      AND COLUMN_NAME = 'costo_ultimo'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE `productos` ADD COLUMN `costo_ultimo` DECIMAL(12,4) DEFAULT 0.0000 AFTER `costo_promedio`',
    'SELECT "Columna costo_ultimo ya existe"'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 6. FUNCIÓN PARA GENERAR CÓDIGO DE CARGO
-- =====================================================

DELIMITER //

CREATE FUNCTION `fn_generar_codigo_cargo`()
RETURNS VARCHAR(20)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_year CHAR(4);
    DECLARE v_next_num INT;
    DECLARE v_codigo VARCHAR(20);

    SET v_year = YEAR(CURDATE());

    SELECT COALESCE(MAX(
        CAST(SUBSTRING(codigo, 10) AS UNSIGNED)
    ), 0) + 1
    INTO v_next_num
    FROM cargos_existencias
    WHERE codigo LIKE CONCAT('CGO-', v_year, '-%');

    SET v_codigo = CONCAT('CGO-', v_year, '-', LPAD(v_next_num, 4, '0'));

    RETURN v_codigo;
END //

DELIMITER ;

-- =====================================================
-- 7. TRIGGER PARA ACTUALIZAR STOCK AL CONFIRMAR
-- =====================================================

DELIMITER //

CREATE TRIGGER `trg_cargo_confirmado`
AFTER UPDATE ON `cargos_existencias`
FOR EACH ROW
BEGIN
    -- Solo cuando cambia de 'borrador' a 'confirmado'
    IF NEW.estado = 'confirmado' AND OLD.estado = 'borrador' THEN

        -- Actualizar stock de cada producto en el cargo
        UPDATE productos p
        INNER JOIN detalle_cargo_existencias d ON p.id = d.producto_id
        SET
            p.stock = p.stock + d.cantidad,
            p.costo_ultimo = d.costo_unitario,
            p.actualizado_at = NOW()
        WHERE d.cargo_id = NEW.id;

        -- Registrar movimientos de inventario para cada producto
        INSERT INTO movimientos_inventario
            (producto_id, usuario_id, tipo_movimiento, cantidad,
             justificacion, stock_anterior, stock_actual, cargo_id, fecha)
        SELECT
            d.producto_id,
            NEW.confirmado_por,
            'entrada',
            d.cantidad,
            CONCAT('Cargo ', NEW.codigo, ' - ',
                   CASE NEW.concepto
                       WHEN 'compra' THEN 'Compra'
                       WHEN 'donacion' THEN 'Donación'
                       WHEN 'ajuste_positivo' THEN 'Ajuste Positivo'
                       WHEN 'devolucion_cliente' THEN 'Devolución Cliente'
                       WHEN 'inventario_inicial' THEN 'Inventario Inicial'
                       ELSE COALESCE(NEW.concepto_otro, 'Otro')
                   END),
            (SELECT stock - d.cantidad FROM productos WHERE id = d.producto_id),
            (SELECT stock FROM productos WHERE id = d.producto_id),
            NEW.id,
            NOW()
        FROM detalle_cargo_existencias d
        WHERE d.cargo_id = NEW.id;

    END IF;
END //

DELIMITER ;

-- =====================================================
-- 8. TRIGGER PARA REVERTIR STOCK AL ANULAR
-- =====================================================

DELIMITER //

CREATE TRIGGER `trg_cargo_anulado`
AFTER UPDATE ON `cargos_existencias`
FOR EACH ROW
BEGIN
    -- Solo cuando cambia de 'confirmado' a 'anulado'
    IF NEW.estado = 'anulado' AND OLD.estado = 'confirmado' THEN

        -- Revertir stock de cada producto
        UPDATE productos p
        INNER JOIN detalle_cargo_existencias d ON p.id = d.producto_id
        SET
            p.stock = GREATEST(0, p.stock - d.cantidad),
            p.actualizado_at = NOW()
        WHERE d.cargo_id = NEW.id;

        -- Registrar movimientos de inventario (reversión)
        INSERT INTO movimientos_inventario
            (producto_id, usuario_id, tipo_movimiento, cantidad,
             justificacion, stock_anterior, stock_actual, cargo_id, fecha)
        SELECT
            d.producto_id,
            NEW.anulado_por,
            'ajuste_negativo',
            d.cantidad,
            CONCAT('ANULACIÓN Cargo ', NEW.codigo,
                   CASE WHEN NEW.motivo_anulacion IS NOT NULL
                        THEN CONCAT(' - ', NEW.motivo_anulacion)
                        ELSE '' END),
            (SELECT stock + d.cantidad FROM productos WHERE id = d.producto_id),
            (SELECT stock FROM productos WHERE id = d.producto_id),
            NEW.id,
            NOW()
        FROM detalle_cargo_existencias d
        WHERE d.cargo_id = NEW.id;

    END IF;
END //

DELIMITER ;

-- =====================================================
-- 9. VISTA PARA CONSULTAR CARGOS CON RESUMEN
-- =====================================================

CREATE VIEW `v_cargos_resumen` AS
SELECT
    c.id,
    c.codigo,
    c.fecha,
    c.concepto,
    CASE c.concepto
        WHEN 'compra' THEN 'Compra'
        WHEN 'donacion' THEN 'Donación'
        WHEN 'ajuste_positivo' THEN 'Ajuste Positivo'
        WHEN 'devolucion_cliente' THEN 'Devolución Cliente'
        WHEN 'inventario_inicial' THEN 'Inventario Inicial'
        WHEN 'otro' THEN COALESCE(c.concepto_otro, 'Otro')
    END AS concepto_texto,
    p.nombre AS proveedor_nombre,
    c.referencia_externa,
    c.total_productos,
    c.total_unidades,
    c.valor_total,
    c.estado,
    CASE c.estado
        WHEN 'borrador' THEN 'Borrador'
        WHEN 'confirmado' THEN 'Confirmado'
        WHEN 'anulado' THEN 'Anulado'
    END AS estado_texto,
    u.nombre AS registrado_por_nombre,
    c.creado_at,
    uc.nombre AS confirmado_por_nombre,
    c.fecha_confirmacion,
    ua.nombre AS anulado_por_nombre,
    c.fecha_anulacion,
    c.motivo_anulacion
FROM cargos_existencias c
LEFT JOIN proveedores p ON c.proveedor_id = p.id
LEFT JOIN usuarios u ON c.registrado_por = u.id
LEFT JOIN usuarios uc ON c.confirmado_por = uc.id
LEFT JOIN usuarios ua ON c.anulado_por = ua.id
ORDER BY c.creado_at DESC;

-- =====================================================
-- 10. VISTA PARA DETALLE COMPLETO DE CARGOS
-- =====================================================

CREATE VIEW `v_cargos_detalle_completo` AS
SELECT
    c.id AS cargo_id,
    c.codigo AS cargo_codigo,
    c.fecha AS cargo_fecha,
    c.estado AS cargo_estado,
    d.id AS detalle_id,
    d.producto_id,
    pr.sku AS producto_sku,
    pr.nombre AS producto_nombre,
    d.cantidad,
    d.costo_unitario,
    d.subtotal,
    d.notas AS detalle_notas
FROM cargos_existencias c
INNER JOIN detalle_cargo_existencias d ON c.id = d.cargo_id
INNER JOIN productos pr ON d.producto_id = pr.id
ORDER BY c.id DESC, d.id ASC;

-- =====================================================
-- 11. VERIFICACIÓN DE INSTALACIÓN
-- =====================================================

-- Mostrar tablas creadas
SELECT 'TABLAS CREADAS:' AS info;
SELECT TABLE_NAME, TABLE_COMMENT
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'dbuzz'
  AND TABLE_NAME IN ('cargos_existencias', 'detalle_cargo_existencias');

-- Mostrar función creada
SELECT 'FUNCIÓN CREADA:' AS info;
SELECT ROUTINE_NAME, ROUTINE_TYPE
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = 'dbuzz'
  AND ROUTINE_NAME = 'fn_generar_codigo_cargo';

-- Mostrar triggers creados
SELECT 'TRIGGERS CREADOS:' AS info;
SELECT TRIGGER_NAME, EVENT_MANIPULATION, EVENT_OBJECT_TABLE
FROM INFORMATION_SCHEMA.TRIGGERS
WHERE TRIGGER_SCHEMA = 'dbuzz'
  AND TRIGGER_NAME IN ('trg_cargo_confirmado', 'trg_cargo_anulado');

-- Mostrar vistas creadas
SELECT 'VISTAS CREADAS:' AS info;
SELECT TABLE_NAME
FROM INFORMATION_SCHEMA.VIEWS
WHERE TABLE_SCHEMA = 'dbuzz'
  AND TABLE_NAME IN ('v_cargos_resumen', 'v_cargos_detalle_completo');

-- Probar función de generación de código
SELECT 'PRUEBA DE GENERACIÓN DE CÓDIGO:' AS info;
SELECT fn_generar_codigo_cargo() AS codigo_prueba;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
SELECT '✓ FASE 1 COMPLETADA EXITOSAMENTE' AS resultado;
