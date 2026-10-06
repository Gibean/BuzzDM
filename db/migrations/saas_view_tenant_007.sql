-- =====================================================
-- MIGRACIÓN 007: empresa_id en las vistas que usa la aplicación
-- =====================================================
--
-- El problema que PDFs y consultas de information_schema no ven: una vista no
-- tiene columnas propias, expone las de sus tablas base. Si la vista no proyecta
-- empresa_id, no hay forma de filtrarla desde el SQL que la consulta, por muy
-- bien escrito que esté el WHERE de la aplicación.
--
-- Las 18 vistas de la base se crearon antes del multi-tenant. Auditoría de uso
-- en el código PHP (ver db/tools/_verify_multitenant.php, sección 8):
--
--   VISTA                             empresa_id  USO
--   v_low_stock                       NO          api_stats.php, check_alerts.php
--   v_op_import                       NO          reports.php
--   vw_productos_stock_bajo_activos   NO          check_schema.php, RequisicionRepository
--   v_tasa_hoy                        NO          db.php
--
-- v_low_stock y vw_productos_stock_bajo_activos proyectan productos, que es el
-- catálogo de cada cliente: nombres, SKU, precios y costes de otra empresa son
-- datos del cliente, no metadatos. v_op_import proyecta la cabecera de las
-- importaciones con el nombre del usuario que las lanzó.
--
-- Decisión sobre v_tasa_hoy: NO se toca. Se apoya en tasas_cambio, que no tiene
-- empresa_id porque una tasa de mercado no pertenece a un tenant: es el mismo
-- número para todos. El endpoint que la usa sólo devuelve la cotización, sin
-- datos de cliente, así que no es una fuga. Documentado aquí para que la ausencia
-- de empresa_id se lea como decisión y no como olvido.
--
-- Por qué se recrean las vistas en vez de añadirles una columna: MySQL no tiene
-- ALTER VIEW ... ADD COLUMN. CREATE OR REPLACE VIEW es la única vía, y hay que
-- reescribir la definición completa.
--
-- Idempotente: CREATE OR REPLACE VIEW sobre una definición idéntica no cambia
-- nada. MariaDB hace auto-commit del DDL; el respaldo previo es la vuelta atrás.

-- ------------------------------------------------------------------
-- v_low_stock: productos bajo mínimo.
-- Se añade empresa_id para que el consumidor pueda filtrar. El ORDER BY se
-- conserva porque algunas pantallas lo usan tal cual.
-- ------------------------------------------------------------------
CREATE OR REPLACE VIEW `v_low_stock` AS
    SELECT
        p.empresa_id      AS empresa_id,
        p.id              AS id,
        p.sku             AS sku,
        p.nombre          AS nombre,
        p.descripcion     AS descripcion,
        p.precio          AS precio,
        p.stock           AS stock,
        p.min_stock       AS min_stock,
        p.estado          AS estado,
        p.creado_at       AS creado_at,
        p.actualizado_at  AS actualizado_at,
        (p.stock <= p.min_stock) AS bajo_minimo
    FROM productos p
    WHERE p.stock <= p.min_stock
      AND p.estado <> 'agotado'
    ORDER BY p.stock;

-- ------------------------------------------------------------------
-- vw_productos_stock_bajo_activos: bajo stock con proveedor por defecto.
-- El LEFT JOIN a proveedores se acota a la misma empresa. Antes, un producto
-- del tenant A unido por id con un proveedor del tenant B pintaba el nombre
-- de ese proveedor en el panel del tenant A.
-- ------------------------------------------------------------------
CREATE OR REPLACE VIEW `vw_productos_stock_bajo_activos` AS
    SELECT
        p.empresa_id                  AS empresa_id,
        p.id                          AS id,
        p.sku                         AS sku,
        p.nombre                      AS nombre,
        p.stock                       AS stock,
        p.min_stock                   AS min_stock,
        p.costo_ultimo                AS costo_ultimo,
        p.proveedor_id                AS proveedor_default_id,
        prov.nombre                   AS proveedor_default_nombre,
        CASE
            WHEN p.stock = 0         THEN 'agotado'
            WHEN p.stock <= p.min_stock THEN 'bajo'
            ELSE 'normal'
        END                           AS nivel_stock,
        (p.min_stock - p.stock)       AS cantidad_faltante
    FROM productos p
    LEFT JOIN proveedores prov
           ON prov.id = p.proveedor_id
          AND prov.empresa_id = p.empresa_id
    WHERE p.estado <> 'inactivo'
      AND p.stock <= p.min_stock
    ORDER BY
        CASE WHEN p.stock = 0 THEN 0 ELSE 1 END,
        (p.min_stock - p.stock) DESC;

-- ------------------------------------------------------------------
-- v_op_import: historial de importaciones con el usuario que la lanzó.
-- Mismo criterio en el LEFT JOIN: sin acotarlo, una fila de importación
-- autenticada por un usuario de otra empresa expondría su nombre. Un LEFT JOIN
-- acotado no pierde la fila de importación, solo deja el usuario a NULL, que es
-- el comportamiento correcto cuando la relación es inconsistente.
-- ------------------------------------------------------------------
CREATE OR REPLACE VIEW `v_op_import` AS
    SELECT
        i.empresa_id     AS empresa_id,
        i.id             AS registro_id,
        'Importación CSV' AS operacion,
        CONCAT('Archivo: ', i.archivo_nombre, ' | Estado: ', i.estado) AS detalles,
        i.fecha          AS fecha,
        u.nombre         AS usuario
    FROM importaciones i
    LEFT JOIN usuarios u
           ON u.id = i.usuario_id
          AND u.empresa_id = i.empresa_id
    WHERE i.tipo = 'csv'
    ORDER BY i.fecha DESC;

-- Verificación: las tres vistas de datos de cliente deben proyectar empresa_id.
-- No debe devolver ninguna fila.
SELECT TABLE_NAME
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND COLUMN_NAME = 'empresa_id'
  AND TABLE_NAME IN ('v_low_stock', 'v_op_import', 'vw_productos_stock_bajo_activos');
