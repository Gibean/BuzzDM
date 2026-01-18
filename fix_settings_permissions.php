<?php
require_once 'php/db.php';

try {
    $pdo = getDbConnection();
    echo "Conexión exitosa. Verificando permisos para api_settings.php...\n";

    $page = 'api_settings.php';
    $roles = ['admin']; // Only admin can access settings

    foreach ($roles as $rol) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO permisos_rol (rol, pagina) VALUES (?, ?)");
        $stmt->execute([$rol, $page]);
        if ($stmt->rowCount() > 0) {
            echo "[OK] Permiso insertado: $rol -> $page\n";
        } else {
            echo "[SKIP] Permiso ya existe: $rol -> $page\n";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
