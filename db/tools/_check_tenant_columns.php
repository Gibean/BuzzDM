<?php
/**
 * Comprueba que las tablas que los repositorios dan por tenant tengan la
 * columna empresa_id. Uso: php db/tools/_check_tenant_columns.php
 */
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/db.php';

$pdo = getDbConnection();

$tablas = [
    'productos', 'clientes', 'proveedores', 'pedidos', 'almacenes', 'ubicaciones',
    'categorias', 'marcas', 'colores', 'materiales', 'configuraciones',
    'cargos_existencias', 'detalle_cargo_existencias', 'requisiciones',
    'detalle_requisicion', 'transferencias_almacen', 'detalle_transferencia',
    'movimientos_inventario', 'stock_ubicacion', 'producto_componentes',
    'precios_proveedor', 'notas_kanban', 'usuarios', 'importaciones',
    'notificaciones', 'backups', 'logs', 'logs_login', 'detalle_pedido',
    'notas_adjuntos', 'notas_checklists', 'notas_items_inventario',
];

$sinColumna = [];
$sinDatos = [];
$vacias = [];

foreach ($tablas as $t) {
    $existe = $pdo->query("SHOW TABLES LIKE '$t'")->fetchColumn();
    if (!$existe) {
        $sinColumna[] = "$t (no existe la tabla)";
        continue;
    }

    $col = $pdo->query("SHOW COLUMNS FROM `$t` LIKE 'empresa_id'")->fetchColumn();
    if (!$col) {
        $sinColumna[] = $t;
        continue;
    }

    $nulo = $pdo->query("SHOW COLUMNS FROM `$t` LIKE 'empresa_id'")->fetch(PDO::FETCH_ASSOC);
    $nulls = $pdo->query("SELECT COUNT(*) FROM `$t` WHERE empresa_id IS NULL")->fetchColumn();
    $total = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();

    if ((int)$nulls > 0) {
        $sinDatos[] = sprintf('%s: %d de %d filas con empresa_id NULL', $t, $nulls, $total);
    }
    if ((int)$total === 0) {
        $vacias[] = $t;
    }
}

echo "Tablas revisadas: " . count($tablas) . "\n\n";

echo "SIN columna empresa_id (" . count($sinColumna) . "):\n";
foreach ($sinColumna as $t) {
    echo "   $t\n";
}
if (!$sinColumna) {
    echo "   (ninguna)\n";
}

echo "\nCon filas sin empresa_id (" . count($sinDatos) . "):\n";
foreach ($sinDatos as $t) {
    echo "   $t\n";
}
if (!$sinDatos) {
    echo "   (ninguna)\n";
}

echo "\nTablas vacías (" . count($vacias) . "): " . implode(', ', $vacias) . "\n";
