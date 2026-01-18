<?php
require_once 'db.php';

try {
    $pdo = getDbConnection();
    echo "Conexión exitosa.\n";

    // Define all pages as per sesion.php fallback, PLUS new APIs
    $permissions = [
        'admin' => [
            'dashboard.php', 'pedidos.php', 'envios.php', 'inventario.php', 'importacion.php',
            'admin.php', 'about.php', 'manual.php', 'login.php', 'logout.php',
            'api_sales_chart.php', 'create_admin.php',
            'users.php', 'sesion.php', 'db.php', 'sidebar.php', 'reports.php', 'pedidos_actions.php',
            'search_api.php', 'clientes.php', 'api_clientes.php', 'api_products.php', 'api_cargos.php',
            'api_sales_by_status_chart.php',
            'envios_actions.php', 'company_settings.php', 'company_settings_actions.php', 'backup.php',
            'backups.php', 'backup_status.php', 'logs.php', 'settings_actions.php',
            'cargos_existencias.php', 'almacenes.php', 'cargos_actions.php', 'productos_search.php',
            'catalogos_actions.php', 'kit_actions.php', 'almacenes_actions.php', 'ubicaciones_actions.php',
            'proveedores.php', 'proveedores_actions.php', 'proveedores_search.php', 'todo.php', 'notas_actions.php',
            'api_notifications.php', 'api_stats.php',
            // Legacy but still might be referenced or safe to keep for a moment
            'product_actions.php', 'clientes_actions.php', 'api_import.php'
        ],
        'supervisor' => [
            'dashboard.php', 'pedidos.php', 'envios.php', 'inventario.php', 'importacion.php',
            'about.php', 'manual.php', 'login.php', 'logout.php', 'api_sales_chart.php',
            'api_products.php', 'api_clientes.php', 'api_cargos.php',
            'users.php', 'sesion.php', 'db.php',
            'sidebar.php', 'reports.php', 'pedidos_actions.php', 'search_api.php', 'clientes.php',
            'api_sales_by_status_chart.php', 'envios_actions.php',
            'process_import.php', 'cargos_existencias.php', 'almacenes.php', 'cargos_actions.php',
            'productos_search.php', 'proveedores_search.php', 'catalogos_actions.php', 'kit_actions.php',
            'almacenes_actions.php', 'ubicaciones_actions.php', 'proveedores.php', 'proveedores_actions.php',
            'todo.php', 'notas_actions.php', 'api_notifications.php', 'api_stats.php', 'api_import.php'
        ],
        'operador' => [
            'dashboard.php', 'pedidos.php', 'envios.php', 'about.php', 'manual.php', 'login.php',
            'logout.php', 'api_sales_chart.php', 'sesion.php', 'db.php', 'sidebar.php',
            'pedidos_actions.php', 'search_api.php', 'api_sales_by_status_chart.php',
            'envios_actions.php', 'todo.php', 'notas_actions.php',
            'api_notifications.php', 'api_stats.php'
        ]
    ];

    $stmtInsert = $pdo->prepare("INSERT IGNORE INTO permisos_rol (rol, pagina) VALUES (?, ?)");

    foreach ($permissions as $rol => $pages) {
        foreach ($pages as $pagina) {
            $stmtInsert->execute([$rol, $pagina]);
        }
        echo "Permisos procesados para rol: $rol\n";
    }

    echo "Actualización de permisos completa.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
