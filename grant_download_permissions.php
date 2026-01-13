<?php
require_once __DIR__ . '/php/db.php';

try {
    $pdo = getDbConnection();

    $pages = ['download_template.php'];
    $roles = ['admin', 'supervisor'];

    echo "Agregando permisos para descarga de plantillas...\n";

    foreach ($roles as $rol) {
        foreach ($pages as $pagina) {
            $sql = "INSERT INTO permisos_rol (rol, pagina) VALUES (:rol, :pagina)
                    ON DUPLICATE KEY UPDATE pagina = pagina";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':rol' => $rol, ':pagina' => $pagina]);
            echo "Permiso concedido: $rol -> $pagina\n";
        }
    }

    echo "¡Permisos actualizados correctamente!";

} catch (PDOException $e) {
    die("Error actualizando permisos: " . $e->getMessage());
}
