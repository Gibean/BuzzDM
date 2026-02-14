<?php
require_once 'db.php';
try {
    $db = Database::getConnection();
    $db->exec("DROP VIEW IF EXISTS vw_productos_stock_bajo_activos;");
    $db->exec("CREATE VIEW vw_productos_stock_bajo_activos AS
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
        WHERE p.estado != 'inactivo'
          AND p.stock <= p.min_stock
        ORDER BY
            CASE WHEN p.stock = 0 THEN 0 ELSE 1 END,
            (p.min_stock - p.stock) DESC");
    echo "View recreated successfully\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
