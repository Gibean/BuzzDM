-- =====================================================
-- MIGRACIÓN: Asignar stock de productos al almacén principal
-- Ejecutar después de tener almacenes y productos creados
--
-- !! SUPERADA — NO EJECUTAR EN INSTALACIONES MULTI-TENANT !!
-- Este script opera sobre TODOS los productos y warehouses del sistema sin
-- filtrar por empresa_id, lo que viola el aislamiento de tenants. El
-- backfill equivalente con filtrado por tenant vive ahora en
-- db/migrations/saas_integrity_002.sql (paso 4).
-- Se conserva sólo como referencia histórica del modelo monolítico.
-- =====================================================

-- 1. Verificar el almacén principal
SELECT id, codigo, nombre, es_principal FROM almacenes WHERE es_principal = 1;

-- 2. Insertar/Actualizar stock_ubicacion para todos los productos
-- Usamos el almacén principal como ubicación predeterminada

-- Variables para el almacén principal
SET @almacen_principal = (SELECT id FROM almacenes WHERE es_principal = 1 LIMIT 1);

-- Si no hay almacén principal, usar el primero disponible
SET @almacen_principal = COALESCE(@almacen_principal, (SELECT id FROM almacenes WHERE activo = 1 ORDER BY id LIMIT 1));

-- Mostrar qué almacén usaremos
SELECT CONCAT('Asignando stock al almacén ID: ', @almacen_principal) AS info;

-- 3. Insertar stock_ubicacion para productos que no lo tengan
-- Esto crea registros en stock_ubicacion con el stock actual del producto
INSERT INTO stock_ubicacion (producto_id, almacen_id, ubicacion_id, cantidad)
SELECT
    p.id AS producto_id,
    @almacen_principal AS almacen_id,
    NULL AS ubicacion_id,
    p.stock AS cantidad
FROM productos p
WHERE p.stock > 0
  AND p.deleted_at IS NULL
  AND NOT EXISTS (
    SELECT 1 FROM stock_ubicacion su
    WHERE su.producto_id = p.id
      AND su.almacen_id = @almacen_principal
  );

-- 4. Actualizar stock_ubicacion existentes que tengan cantidad diferente
UPDATE stock_ubicacion su
INNER JOIN productos p ON su.producto_id = p.id
SET su.cantidad = p.stock
WHERE su.almacen_id = @almacen_principal
  AND su.cantidad != p.stock;

-- 5. Verificar resultado
SELECT
    p.sku,
    p.nombre,
    p.stock AS stock_global,
    COALESCE(su.cantidad, 0) AS stock_en_almacen,
    a.nombre AS almacen
FROM productos p
LEFT JOIN stock_ubicacion su ON p.id = su.producto_id AND su.almacen_id = @almacen_principal
LEFT JOIN almacenes a ON su.almacen_id = a.id
WHERE p.deleted_at IS NULL
ORDER BY p.sku;

-- 6. Resumen
SELECT
    COUNT(*) AS productos_con_stock_asignado,
    SUM(su.cantidad) AS total_unidades
FROM stock_ubicacion su
WHERE su.almacen_id = @almacen_principal;
