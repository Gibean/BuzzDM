<?php
require_once 'php/db.php';

try {
    $pdo = getDbConnection();
    echo "Conexión exitosa. Verificando permisos para api_import.php...\n";

    $page = 'api_import.php';
    $roles = ['admin', 'supervisor'];

    foreach ($roles as $rol) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM permisos_rol WHERE rol = ? AND pagina = ?");
        $check->execute([$rol, $page]);

        if ($check->fetchColumn() == 0) {
            $insert = $pdo->prepare("INSERT INTO permisos_rol (rol, pagina) VALUES (?, ?)");
            $insert->execute([$rol, $page]);
            echo "[OK] Permiso agregado: $rol -> $page\n";
        } else {
            echo "[SKIP] Permiso ya existe: $rol -> $page\n";
        }
    }

    echo "Verificación completada.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
