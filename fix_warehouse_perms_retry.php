<?php
require_once 'php/db.php';

try {
    $pdo = getDbConnection();
    echo "Conexión exitosa. Verificando permisos para api_warehouses.php...\n";

    $pages = ['api_warehouses.php'];
    $roles = ['admin', 'supervisor', 'operador'];

    foreach ($roles as $rol) {
        foreach ($pages as $pagina) {
            // Check existence
            $check = $pdo->prepare("SELECT COUNT(*) FROM permisos_rol WHERE rol = ? AND pagina = ?");
            $check->execute([$rol, $pagina]);

            if ($check->fetchColumn() == 0) {
                // Insert
                $insert = $pdo->prepare("INSERT INTO permisos_rol (rol, pagina) VALUES (?, ?)");
                $insert->execute([$rol, $pagina]);
                echo "[OK] Permiso agregado: $rol -> $pagina\n";
            } else {
                echo "[SKIP] Permiso ya existe: $rol -> $pagina\n";
            }
        }
    }

    echo "Verificación completada.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
