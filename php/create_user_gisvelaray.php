<?php
/**
 * Script para crear usuario gisvelaray@gmail.com
 */

require_once __DIR__ . '/db.php';

try {
    $pdo = getDbConnection();

    echo "=== Creando Usuario ===\n\n";

    // Datos del usuario
    $email = 'gisvelaray@gmail.com';
    $nombre = 'Gisvelaray';
    $password = 'admin123'; // Contraseña por defecto
    $rol = 'admin'; // Rol del usuario: 'admin', 'operador', 'supervisor'

    // Verificar si el usuario ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $existingUser = $stmt->fetch();

    if ($existingUser) {
        echo "⚠️  El usuario ya existe. Actualizando...\n";
        // Actualizar el usuario existente
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET nombre = ?,
                password = ?,
                rol = ?,
                estado = 'activo',
                intentos_fallidos = 0,
                actualizado_at = NOW()
            WHERE email = ?
        ");
        $stmt->execute([$nombre, $hashedPassword, $rol, $email]);
        echo "✅ Usuario actualizado exitosamente!\n\n";
    } else {
        echo "Creando nuevo usuario...\n";
        // Crear nuevo usuario
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nombre, email, password, rol, estado, creado_at, intentos_fallidos)
            VALUES (?, ?, ?, ?, 'activo', NOW(), 0)
        ");
        $stmt->execute([$nombre, $email, $hashedPassword, $rol]);
        echo "✅ Usuario creado exitosamente!\n\n";
    }

    // Mostrar información del usuario
    $stmt = $pdo->prepare("SELECT id, nombre, email, rol, estado, creado_at FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Información del usuario:\n";
    echo "========================\n";
    echo "ID:       " . $usuario['id'] . "\n";
    echo "Nombre:   " . $usuario['nombre'] . "\n";
    echo "Email:    " . $usuario['email'] . "\n";
    echo "Rol:      " . $usuario['rol'] . "\n";
    echo "Estado:   " . $usuario['estado'] . "\n";
    echo "Creado:   " . $usuario['creado_at'] . "\n";
    echo "\n";
    echo "Credenciales de acceso:\n";
    echo "========================\n";
    echo "Email:     gisvelaray@gmail.com\n";
    echo "Password:  admin123\n";
    echo "\n";
    echo "⚠️  IMPORTANTE: Cambia la contraseña después del primer login.\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

