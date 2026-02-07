<?php
require_once 'db.php';
try {
    $pdo = getDbConnection();

    $tables = ['requisiciones', 'detalle_requisicion'];
    foreach ($tables as $t) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$t'");
        if ($stmt->fetch()) {
            echo "Table $t: OK\n";
        } else {
            echo "Table $t: MISSING\n";
        }
    }

    $views = ['vw_productos_stock_bajo_activos'];
    foreach ($views as $v) {
        $stmt = $pdo->query("SHOW FULL TABLES LIKE '$v'");
        $result = $stmt->fetch();
        if ($result && $result[1] == 'VIEW') {
            echo "View $v: OK\n";
        } else {
            echo "View $v: MISSING\n";
        }
    }

} catch (Throwable $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
