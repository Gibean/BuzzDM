<?php
require_once 'db.php';
header('Content-Type: text/plain');

try {
    $pdo = getDbConnection();
    echo "Checking permissions for api_orders.php...\n";

    $roles = ['admin', 'supervisor', 'operador'];
    $page = 'api_orders.php';

    foreach ($roles as $rol) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM permisos_rol WHERE rol = ? AND pagina = ?");
        $stmt->execute([$rol, $page]);
        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            echo "[$rol] Permission for $page ALREADY EXISTS.\n";
        } else {
            echo "[$rol] Permission for $page MISSING. Inserting...\n";
            $ins = $pdo->prepare("INSERT INTO permisos_rol (rol, pagina) VALUES (?, ?)");
            $ins->execute([$rol, $page]);
            echo "[$rol] Permission INSERTED.\n";
        }
    }

    echo "Done.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
