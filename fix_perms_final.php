<?php
require_once 'php/db.php';

try {
    $pdo = getDbConnection();
    echo "Conectado a DB. Iniciando actualizacion de permisos...\n";

    // 1. Ensure api_warehouses.php is in permissions
    $page = 'api_warehouses.php';
    $roles = ['admin', 'supervisor', 'operador'];

    foreach ($roles as $rol) {
        // Check if exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM permisos_rol WHERE rol = ? AND pagina = ?");
        $stmt->execute([$rol, $page]);

        if ($stmt->fetchColumn() == 0) {
            $ins = $pdo->prepare("INSERT INTO permisos_rol (rol, pagina) VALUES (?, ?)");
            $ins->execute([$rol, $page]);
            echo "[OK] Permiso insertado para $rol -> $page\n";
        } else {
            echo "[SKIP] Permiso ya existe para $rol -> $page\n";
        }
    }

    // 2. Clear Session Cache Logic (if any) - Not applicable here but good practice to mention
    echo "Permisos actualizados correctamente.\n";

} catch (PDOException $e) {
    echo "ERROR SQL: " . $e->getMessage() . "\n";
}
?>
