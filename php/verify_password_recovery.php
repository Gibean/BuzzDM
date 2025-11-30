<?php
/**
 * Script de verificación e instalación del sistema de recuperación de contraseña
 * Ejecuta este script para verificar que todo esté configurado correctamente
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== BUZZ Data Manager - Verificación del Sistema de Recuperación de Contraseña ===\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Verificar conexión a la base de datos
echo "1. Verificando conexión a la base de datos...\n";
try {
    require_once __DIR__ . '/db.php';
    $pdo = getDbConnection();
    $success[] = "✓ Conexión a la base de datos exitosa";
} catch (Exception $e) {
    $errors[] = "✗ Error de conexión a la base de datos: " . $e->getMessage();
}

// 2. Verificar tabla de tokens
echo "2. Verificando tabla password_reset_tokens...\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'password_reset_tokens'");
    if ($stmt->rowCount() > 0) {
        $success[] = "✓ Tabla password_reset_tokens existe";

        // Verificar estructura de la tabla
        $stmt = $pdo->query("DESCRIBE password_reset_tokens");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $requiredColumns = ['id', 'usuario_id', 'token', 'expiracion', 'usado', 'creado_at'];
        $missingColumns = array_diff($requiredColumns, $columns);

        if (empty($missingColumns)) {
            $success[] = "✓ Estructura de la tabla correcta";
        } else {
            $warnings[] = "⚠ Faltan columnas en la tabla: " . implode(', ', $missingColumns);
        }
    } else {
        $errors[] = "✗ Tabla password_reset_tokens no existe";
        echo "   Para crear la tabla, ejecuta: mysql -u root -p dbuzz < db/password_reset_tokens.sql\n";
    }
} catch (Exception $e) {
    $errors[] = "✗ Error al verificar tabla: " . $e->getMessage();
}

// 3. Verificar PHPMailer
echo "3. Verificando PHPMailer...\n";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';

    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        $success[] = "✓ PHPMailer está instalado";
    } else {
        $errors[] = "✗ PHPMailer no está disponible";
        echo "   Para instalar, ejecuta: composer require phpmailer/phpmailer\n";
    }
} else {
    $errors[] = "✗ Composer autoload no encontrado";
    echo "   Para instalar dependencias, ejecuta: composer install\n";
}

// 4. Verificar archivos del sistema
echo "4. Verificando archivos del sistema...\n";
$requiredFiles = [
    'forgot_password.php' => '../forgot_password.php',
    'reset_password.php' => '../reset_password.php',
    'php/email_config.php' => 'email_config.php',
    'php/forgot_password_action.php' => 'forgot_password_action.php',
    'php/validate_token.php' => 'validate_token.php',
    'php/reset_password_action.php' => 'reset_password_action.php',
    'php/cleanup_tokens.php' => 'cleanup_tokens.php'
];

foreach ($requiredFiles as $name => $path) {
    if (file_exists(__DIR__ . '/' . $path)) {
        $success[] = "✓ Archivo $name existe";
    } else {
        $errors[] = "✗ Archivo $name no encontrado";
    }
}

// 5. Verificar configuración de email
echo "5. Verificando configuración de email...\n";
if (file_exists(__DIR__ . '/email_config.php')) {
    require_once __DIR__ . '/email_config.php';

    // Verificar que las constantes estén definidas
    $requiredConstants = [
        'SMTP_HOST', 'SMTP_PORT', 'SMTP_SECURE',
        'SMTP_USERNAME', 'SMTP_PASSWORD',
        'MAIL_FROM_EMAIL', 'MAIL_FROM_NAME', 'APP_BASE_URL'
    ];

    $allDefined = true;
    foreach ($requiredConstants as $constant) {
        if (!defined($constant)) {
            $errors[] = "✗ Constante $constant no está definida";
            $allDefined = false;
        }
    }

    if ($allDefined) {
        // Verificar si están usando valores por defecto
        if (SMTP_USERNAME === 'tu-email@gmail.com' || SMTP_PASSWORD === 'tu-contraseña-o-app-password') {
            $warnings[] = "⚠ Configuración de email usa valores por defecto - debe ser actualizada";
        } else {
            $success[] = "✓ Configuración de email definida";
        }

        if (APP_BASE_URL === 'http://localhost/BuzzDM') {
            $warnings[] = "⚠ APP_BASE_URL usa valor por defecto - verifica que sea correcto";
        } else {
            $success[] = "✓ APP_BASE_URL configurada: " . APP_BASE_URL;
        }
    }
} else {
    $errors[] = "✗ Archivo email_config.php no encontrado";
}

// 6. Verificar extensiones PHP necesarias
echo "6. Verificando extensiones PHP...\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'openssl', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        $success[] = "✓ Extensión $ext está habilitada";
    } else {
        $errors[] = "✗ Extensión $ext no está habilitada";
    }
}

// 7. Verificar permisos de escritura (si es necesario)
echo "7. Verificando permisos...\n";
if (is_writable(__DIR__)) {
    $success[] = "✓ Directorio php/ tiene permisos de escritura";
} else {
    $warnings[] = "⚠ Directorio php/ no tiene permisos de escritura (puede no ser necesario)";
}

// Resumen
echo "\n=== RESUMEN ===\n\n";

if (!empty($success)) {
    echo "EXITOSO (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "ADVERTENCIAS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "ERRORES (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
    echo "Por favor, corrige los errores antes de usar el sistema.\n";
    exit(1);
} else {
    echo "✓✓✓ SISTEMA LISTO PARA USAR ✓✓✓\n";
    if (!empty($warnings)) {
        echo "Sin embargo, revisa las advertencias antes de pasar a producción.\n";
    }
    exit(0);
}
?>

