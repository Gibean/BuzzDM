-- =====================================================
-- FASE 1.7: TIPOS DE PRODUCTO, VARIANTES Y KITS
-- Ejecutar en: dbuzz
-- Fecha: 2026-01-05
-- =====================================================
-- Decisiones de diseño:
-- 1. Servicios con precio fijo
-- 2. Variantes agrupadas en UI
-- 3. Kits anidados permitidos
-- =====================================================

-- 1. Agregar campos de tipo de producto
ALTER TABLE productos
    ADD COLUMN IF NOT EXISTS tipo_producto ENUM('fisico', 'servicio', 'kit')
        NOT NULL DEFAULT 'fisico' AFTER estado,
    ADD COLUMN IF NOT EXISTS maneja_stock BOOLEAN DEFAULT TRUE AFTER tipo_producto,
    ADD COLUMN IF NOT EXISTS producto_padre_id INT UNSIGNED DEFAULT NULL
        COMMENT 'Para variantes: ID del producto padre' AFTER maneja_stock,
    ADD COLUMN IF NOT EXISTS es_kit_virtual BOOLEAN DEFAULT FALSE
        COMMENT 'TRUE=descuenta componentes, FALSE=stock propio' AFTER producto_padre_id,
    ADD COLUMN IF NOT EXISTS variante_atributo VARCHAR(100) DEFAULT NULL
        COMMENT 'Ej: Talla M, Color Rojo' AFTER es_kit_virtual;

-- 2. Agregar índice para producto padre (variantes)
SET @idx_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'productos'
    AND INDEX_NAME = 'idx_producto_padre'
);
SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE productos ADD INDEX idx_producto_padre (producto_padre_id)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Agregar foreign key para variantes si no existe
SET @fk_exists = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'productos'
    AND CONSTRAINT_NAME = 'fk_producto_padre'
);
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE productos ADD CONSTRAINT fk_producto_padre
     FOREIGN KEY (producto_padre_id) REFERENCES productos(id)
     ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Crear tabla de componentes de kits
CREATE TABLE IF NOT EXISTS producto_componentes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kit_id INT UNSIGNED NOT NULL COMMENT 'Producto tipo kit',
    componente_id INT UNSIGNED NOT NULL COMMENT 'Producto/kit componente',
    cantidad DECIMAL(10,3) NOT NULL DEFAULT 1 COMMENT 'Cantidad del componente por kit',
    orden INT DEFAULT 0 COMMENT 'Orden de visualización',
    creado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_kit_componente (kit_id, componente_id),
    KEY idx_kit (kit_id),
    KEY idx_componente (componente_id),
    CONSTRAINT fk_pc_kit FOREIGN KEY (kit_id)
        REFERENCES productos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pc_componente FOREIGN KEY (componente_id)
        REFERENCES productos(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Componentes de productos tipo kit';

-- 5. Función para calcular stock disponible de kit virtual
DROP FUNCTION IF EXISTS fn_stock_kit_virtual;
DELIMITER //
CREATE FUNCTION fn_stock_kit_virtual(p_kit_id INT UNSIGNED)
RETURNS INT
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_min_stock INT DEFAULT NULL;
    DECLARE v_comp_stock INT;
    DECLARE v_comp_cantidad DECIMAL(10,3);
    DECLARE v_comp_es_kit BOOLEAN;
    DECLARE v_comp_id INT;
    DECLARE v_done INT DEFAULT 0;

    DECLARE cur_componentes CURSOR FOR
        SELECT pc.componente_id, pc.cantidad, p.tipo_producto = 'kit' AND p.es_kit_virtual
        FROM producto_componentes pc
        INNER JOIN productos p ON p.id = pc.componente_id
        WHERE pc.kit_id = p_kit_id;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET v_done = 1;

    OPEN cur_componentes;

    read_loop: LOOP
        FETCH cur_componentes INTO v_comp_id, v_comp_cantidad, v_comp_es_kit;
        IF v_done THEN
            LEAVE read_loop;
        END IF;

        -- Si el componente es un kit virtual, calcular recursivamente
        IF v_comp_es_kit THEN
            SET v_comp_stock = fn_stock_kit_virtual(v_comp_id);
        ELSE
            SELECT stock INTO v_comp_stock FROM productos WHERE id = v_comp_id;
        END IF;

        -- Calcular cuántos kits se pueden armar con este componente
        IF v_comp_cantidad > 0 THEN
            SET v_comp_stock = FLOOR(v_comp_stock / v_comp_cantidad);
        END IF;

        -- El mínimo determina cuántos kits se pueden armar
        IF v_min_stock IS NULL OR v_comp_stock < v_min_stock THEN
            SET v_min_stock = v_comp_stock;
        END IF;
    END LOOP;

    CLOSE cur_componentes;

    RETURN COALESCE(v_min_stock, 0);
END //
DELIMITER ;

-- 6. Vista de productos con info de variantes y kits
DROP VIEW IF EXISTS v_productos_completo;
CREATE VIEW v_productos_completo AS
SELECT
    p.id,
    p.sku,
    p.nombre,
    p.descripcion,
    p.precio,
    p.tipo_producto,
    p.maneja_stock,
    p.es_kit_virtual,
    p.variante_atributo,
    p.producto_padre_id,
    pp.nombre AS producto_padre_nombre,
    pp.sku AS producto_padre_sku,
    CASE
        WHEN p.tipo_producto = 'kit' AND p.es_kit_virtual THEN fn_stock_kit_virtual(p.id)
        WHEN p.tipo_producto = 'servicio' THEN NULL
        ELSE p.stock
    END AS stock_efectivo,
    p.stock AS stock_fisico,
    p.min_stock,
    p.estado,
    p.marca_id,
    m.nombre AS marca_nombre,
    p.categoria,
    c.nombre AS categoria_nombre,
    (SELECT COUNT(*) FROM productos v WHERE v.producto_padre_id = p.id) AS total_variantes,
    (SELECT COUNT(*) FROM producto_componentes pc WHERE pc.kit_id = p.id) AS total_componentes,
    p.creado_at,
    p.deleted_at
FROM productos p
LEFT JOIN productos pp ON pp.id = p.producto_padre_id
LEFT JOIN marcas m ON m.id = p.marca_id
LEFT JOIN categorias c ON c.id = p.categoria;

-- 7. Vista de componentes de kit con detalles
DROP VIEW IF EXISTS v_kit_componentes;
CREATE VIEW v_kit_componentes AS
SELECT
    pc.id,
    pc.kit_id,
    pk.sku AS kit_sku,
    pk.nombre AS kit_nombre,
    pc.componente_id,
    p.sku AS componente_sku,
    p.nombre AS componente_nombre,
    p.tipo_producto AS componente_tipo,
    pc.cantidad,
    p.precio AS componente_precio,
    CASE
        WHEN p.tipo_producto = 'kit' AND p.es_kit_virtual THEN fn_stock_kit_virtual(p.id)
        WHEN p.tipo_producto = 'servicio' THEN 999999
        ELSE p.stock
    END AS componente_stock,
    pc.cantidad * p.precio AS subtotal,
    pc.orden
FROM producto_componentes pc
INNER JOIN productos p ON p.id = pc.componente_id
INNER JOIN productos pk ON pk.id = pc.kit_id
ORDER BY pc.kit_id, pc.orden, pc.id;

-- 8. Trigger para validar tipo al insertar componente
DROP TRIGGER IF EXISTS trg_validar_componente_kit;
DELIMITER //
CREATE TRIGGER trg_validar_componente_kit
BEFORE INSERT ON producto_componentes
FOR EACH ROW
BEGIN
    DECLARE v_tipo VARCHAR(20);

    -- Verificar que el kit sea realmente un kit
    SELECT tipo_producto INTO v_tipo FROM productos WHERE id = NEW.kit_id;
    IF v_tipo != 'kit' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El producto padre debe ser de tipo kit';
    END IF;

    -- Evitar que un kit sea componente de sí mismo
    IF NEW.kit_id = NEW.componente_id THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Un kit no puede ser componente de sí mismo';
    END IF;
END //
DELIMITER ;

-- 9. Trigger para auto-ajustar maneja_stock según tipo
DROP TRIGGER IF EXISTS trg_auto_maneja_stock;
DELIMITER //
CREATE TRIGGER trg_auto_maneja_stock
BEFORE INSERT ON productos
FOR EACH ROW
BEGIN
    IF NEW.tipo_producto = 'servicio' THEN
        SET NEW.maneja_stock = FALSE;
        SET NEW.stock = 0;
    ELSEIF NEW.tipo_producto = 'kit' AND NEW.es_kit_virtual = TRUE THEN
        SET NEW.maneja_stock = FALSE;
        -- Stock se calcula dinámicamente
    END IF;
END //
DELIMITER ;

DROP TRIGGER IF EXISTS trg_auto_maneja_stock_update;
DELIMITER //
CREATE TRIGGER trg_auto_maneja_stock_update
BEFORE UPDATE ON productos
FOR EACH ROW
BEGIN
    IF NEW.tipo_producto = 'servicio' THEN
        SET NEW.maneja_stock = FALSE;
        SET NEW.stock = 0;
    ELSEIF NEW.tipo_producto = 'kit' AND NEW.es_kit_virtual = TRUE THEN
        SET NEW.maneja_stock = FALSE;
    END IF;
END //
DELIMITER ;

-- 10. Actualizar productos existentes (todos son físicos por defecto)
UPDATE productos SET tipo_producto = 'fisico', maneja_stock = TRUE
WHERE tipo_producto IS NULL OR tipo_producto = '';

-- =====================================================
-- FIN FASE 1.7
-- =====================================================
