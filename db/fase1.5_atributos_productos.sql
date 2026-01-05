-- =====================================================
-- FASE 1.5: ATRIBUTOS DETALLADOS DE PRODUCTOS
-- Base de datos: dbuzz
-- Fecha: 2026-01-05
-- Descripción: Agregar campos de categorización detallada
--              a la tabla productos para mejor control
-- =====================================================
-- INSTRUCCIONES:
-- 1. Ejecutar este script en phpMyAdmin o HeidiSQL
-- 2. Asegúrate de estar conectado a la base 'dbuzz'
-- 3. Ejecutar después de fase1_cargos_existencias.sql
-- =====================================================

USE `dbuzz`;

-- =====================================================
-- 1. AGREGAR NUEVOS CAMPOS A LA TABLA PRODUCTOS
-- =====================================================

-- Modelo del producto (ej: "G2 Pro", "Clásico", etc.)
ALTER TABLE `productos`
    ADD COLUMN IF NOT EXISTS `modelo` VARCHAR(100) DEFAULT NULL
    COMMENT 'Modelo o línea del producto'
    AFTER `proveedor_id`;

-- Color del producto
ALTER TABLE `productos`
    ADD COLUMN IF NOT EXISTS `color` VARCHAR(50) DEFAULT NULL
    COMMENT 'Color principal del producto'
    AFTER `modelo`;

-- Tamaño/Talla (ej: "S", "M", "L", "XL", "A4", "Grande")
ALTER TABLE `productos`
    ADD COLUMN IF NOT EXISTS `tamanio` VARCHAR(50) DEFAULT NULL
    COMMENT 'Tamaño, talla o formato'
    AFTER `color`;

-- Medidas físicas (ej: "21x27 cm", "15x10x5 cm")
ALTER TABLE `productos`
    ADD COLUMN IF NOT EXISTS `medidas` VARCHAR(100) DEFAULT NULL
    COMMENT 'Dimensiones físicas del producto'
    AFTER `tamanio`;

-- Peso en kilogramos (para envíos)
ALTER TABLE `productos`
    ADD COLUMN IF NOT EXISTS `peso_kg` DECIMAL(10,3) DEFAULT NULL
    COMMENT 'Peso en kilogramos'
    AFTER `medidas`;

-- Material principal (ej: "Plástico", "Madera", "Metal")
ALTER TABLE `productos`
    ADD COLUMN IF NOT EXISTS `material` VARCHAR(100) DEFAULT NULL
    COMMENT 'Material principal del producto'
    AFTER `peso_kg`;

-- Unidad de medida para venta
ALTER TABLE `productos`
    ADD COLUMN IF NOT EXISTS `unidad_venta` ENUM('UND', 'KG', 'LT', 'MT', 'M2', 'PACK', 'CAJA', 'PAR')
    DEFAULT 'UND'
    COMMENT 'Unidad de medida para venta'
    AFTER `material`;

-- Cantidad por presentación (si es pack/caja)
ALTER TABLE `productos`
    ADD COLUMN IF NOT EXISTS `cantidad_presentacion` INT UNSIGNED DEFAULT 1
    COMMENT 'Cantidad de unidades por presentación (si es pack/caja)'
    AFTER `unidad_venta`;

-- =====================================================
-- 2. CREAR TABLA DE COLORES PREDEFINIDOS (Opcional)
-- =====================================================

DROP TABLE IF EXISTS `colores`;
CREATE TABLE IF NOT EXISTS `colores` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(50) NOT NULL,
    `codigo_hex` CHAR(7) DEFAULT NULL COMMENT 'Código hex del color (#RRGGBB)',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_color_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Colores predefinidos para productos';

-- Insertar colores comunes
INSERT INTO `colores` (`nombre`, `codigo_hex`) VALUES
    ('Negro', '#000000'),
    ('Blanco', '#FFFFFF'),
    ('Rojo', '#FF0000'),
    ('Azul', '#0000FF'),
    ('Verde', '#00FF00'),
    ('Amarillo', '#FFFF00'),
    ('Naranja', '#FFA500'),
    ('Rosa', '#FFC0CB'),
    ('Morado', '#800080'),
    ('Gris', '#808080'),
    ('Marrón', '#8B4513'),
    ('Beige', '#F5F5DC'),
    ('Transparente', NULL),
    ('Multicolor', NULL)
ON DUPLICATE KEY UPDATE codigo_hex = VALUES(codigo_hex);

-- =====================================================
-- 3. CREAR TABLA DE MATERIALES PREDEFINIDOS (Opcional)
-- =====================================================

DROP TABLE IF EXISTS `materiales`;
CREATE TABLE IF NOT EXISTS `materiales` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_material_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Materiales predefinidos para productos';

-- Insertar materiales comunes
INSERT INTO `materiales` (`nombre`) VALUES
    ('Plástico'),
    ('Madera'),
    ('Metal'),
    ('Vidrio'),
    ('Papel'),
    ('Cartón'),
    ('Tela'),
    ('Cuero'),
    ('Goma'),
    ('Cerámica'),
    ('Aluminio'),
    ('Acero'),
    ('PVC'),
    ('Silicona')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- =====================================================
-- 4. ACTUALIZAR VISTA DE INVENTARIO
-- =====================================================

DROP VIEW IF EXISTS `v_inventario`;
CREATE VIEW `v_inventario` AS
SELECT
    p.id,
    p.sku,
    p.nombre,
    p.descripcion,
    p.precio,
    p.costo_promedio,
    p.costo_ultimo,
    p.stock,
    p.min_stock,
    p.estado,
    c.nombre AS categoria_nombre,
    m.nombre AS marca_nombre,
    pr.nombre AS proveedor_nombre,
    p.modelo,
    p.color,
    p.tamanio,
    p.medidas,
    p.peso_kg,
    p.material,
    p.unidad_venta,
    p.cantidad_presentacion,
    p.creado_at,
    p.actualizado_at,
    p.deleted_at
FROM productos p
LEFT JOIN categorias c ON p.categoria = c.id
LEFT JOIN marcas m ON p.marca_id = m.id
LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
WHERE p.deleted_at IS NULL;

-- =====================================================
-- 5. VERIFICACIÓN DE INSTALACIÓN
-- =====================================================

-- Mostrar nuevas columnas
SELECT 'NUEVAS COLUMNAS EN PRODUCTOS:' AS info;
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'dbuzz'
  AND TABLE_NAME = 'productos'
  AND COLUMN_NAME IN ('modelo', 'color', 'tamanio', 'medidas', 'peso_kg', 'material', 'unidad_venta', 'cantidad_presentacion');

-- Mostrar tablas auxiliares creadas
SELECT 'TABLAS AUXILIARES CREADAS:' AS info;
SELECT TABLE_NAME, TABLE_COMMENT
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = 'dbuzz'
  AND TABLE_NAME IN ('colores', 'materiales');

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
SELECT '✓ FASE 1.5 COMPLETADA EXITOSAMENTE' AS resultado;
