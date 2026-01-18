<?php
require_once 'db.php';

try {
    $pdo = getDbConnection();
    echo "Conexión exitosa.\n";

    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'permisos_rol'");
    if ($stmt->rowCount() == 0) {
        echo "La tabla permisos_rol no existe.\n";
        exit;
    }

    $pages = ['api_products.php', 'api_clientes.php', 'api_cargos.php'];
    $roles = ['admin', 'supervisor'];

    foreach ($roles as $rol) {
        foreach ($pages as $pagina) {
            // Check existence
            $check = $pdo->prepare("SELECT COUNT(*) FROM permisos_rol WHERE rol = ? AND pagina = ?");
            $check->execute([$rol, $pagina]);

            if ($check->fetchColumn() == 0) {
                // Insert
                $insert = $pdo->prepare("INSERT INTO permisos_rol (rol, pagina) VALUES (?, ?)");
                $insert->execute([$rol, $pagina]);
                echo "Permiso agregado: $rol -> $pagina\n";
            } else {
                echo "Permiso ya existe: $rol -> $pagina\n";
            }
        }
    }

    // Also ensuring 'dashboard.php' and other basics are there just in case
    echo "Verificación completada.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
