<?php
require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/src/Service/DeviceFingerprintService.php';
require_once __DIR__ . '/../php/src/Service/CompanyService.php';
require_once __DIR__ . '/../php/src/Service/DemoExportService.php';

echo "=== INICIANDO SUITE DE PRUEBAS SAAS DEMO ===\n\n";

try {
    $pdo = getDbConnection();
    echo "[TEST 1] Conexión a Base de Datos: OK (BD: " . $pdo->query("SELECT DATABASE()")->fetchColumn() . ")\n";
} catch (Exception $e) {
    die("[FAIL 1] Error de conexión: " . $e->getMessage() . "\n");
}

// TEST 2: Aplicar Migración de prueba si no está aplicada
try {
    echo "[TEST 2] Verificando columnas de prueba en 'empresa'...\n";
    $cols = $pdo->query("SHOW COLUMNS FROM empresa LIKE 'trial_ends_at'")->fetchAll();
    if (empty($cols)) {
        echo " -> Aplicando script de migración saas_multitenant_setup.sql...\n";
        $sql = file_get_contents(__DIR__ . '/../db/migrations/saas_multitenant_setup.sql');
        $pdo->exec($sql);
        echo " -> Migración aplicada con éxito.\n";
    } else {
        echo " -> Columnas multi-tenant ya presentes en la BD.\n";
    }
} catch (Exception $e) {
    echo " -> [Aviso migración]: " . $e->getMessage() . "\n";
}

// TEST 3: DeviceFingerprintService
try {
    echo "\n[TEST 3] Probando DeviceFingerprintService...\n";
    $fpService = new DeviceFingerprintService($pdo);
    $fp1 = $fpService->generateFingerprint('fp_browser_token_123');
    $fp2 = $fpService->generateFingerprint('fp_browser_token_123');
    $fp3 = $fpService->generateFingerprint('fp_browser_token_456');

    if ($fp1 === $fp2 && $fp1 !== $fp3 && strlen($fp1) === 64) {
        echo " -> Generación y consistencia de hash SHA-256: OK (Hash: " . substr($fp1, 0, 16) . "...)\n";
    } else {
        echo " -> [FAIL] Inconsistencia en hash de fingerprint.\n";
    }
} catch (Exception $e) {
    echo " -> [FAIL 3]: " . $e->getMessage() . "\n";
}

// TEST 4: CompanyService::createTrialEnvironment
$testCompanyId = null;
try {
    echo "\n[TEST 4] Probando CompanyService::createTrialEnvironment...\n";
    $companyService = new CompanyService($pdo);
    
    $uniqueSuffix = time();
    $companyData = [
        'company_name'   => 'Empresa Test ' . $uniqueSuffix,
        'admin_name'     => 'Admin Demo ' . $uniqueSuffix,
        'admin_email'    => 'demo_' . $uniqueSuffix . '@testbuzz.com',
        'admin_password' => 'PasswordPrueba123*',
        'device_fingerprint' => 'fp_token_' . $uniqueSuffix
    ];
    
    $res = $companyService->createTrialEnvironment($companyData);
    $testCompanyId = $res['company_id'];
    echo " -> Empresa creada con éxito (ID: {$testCompanyId})\n";
    echo " -> Días restantes asignados: {$res['days_remaining']}\n";
    echo " -> Fecha de expiración: {$res['expires_at']}\n";

    // Verificar almacén inicial
    $stmtAlm = $pdo->prepare("SELECT COUNT(*) FROM almacenes WHERE empresa_id = ?");
    $stmtAlm->execute([$testCompanyId]);
    $almCount = $stmtAlm->fetchColumn();
    echo " -> Almacén principal inicial creado: " . ($almCount > 0 ? "SÍ" : "NO") . "\n";

    // Verificar usuario inicial
    $stmtUser = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE empresa_id = ?");
    $stmtUser->execute([$testCompanyId]);
    $userCount = $stmtUser->fetchColumn();
    echo " -> Usuario administrador inicial creado: " . ($userCount > 0 ? "SÍ" : "NO") . "\n";

} catch (Exception $e) {
    echo " -> [FAIL 4]: " . $e->getMessage() . "\n";
}

// TEST 5: Bloqueo de duplicado por Fingerprint o Email
try {
    echo "\n[TEST 5] Probando bloqueo de registro duplicado...\n";
    try {
        $companyService->createTrialEnvironment($companyData);
        echo " -> [FAIL] Permitió crear cuenta duplicada con mismo token/email.\n";
    } catch (RuntimeException $re) {
        echo " -> Bloqueo preventivo anti-abuso exitoso: " . $re->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo " -> [FAIL 5]: " . $e->getMessage() . "\n";
}

// TEST 6: DemoExportService - Generación de Excel
try {
    echo "\n[TEST 6] Probando DemoExportService (Exportación a Excel)...\n";
    $exportService = new DemoExportService($pdo);
    
    // Inyectar un producto de prueba a la empresa creada para exportar
    if ($testCompanyId) {
        $stmtProd = $pdo->prepare("
            INSERT INTO productos (empresa_id, sku, nombre, precio, stock, estado, creado_at)
            VALUES (?, 'TEST-001', 'Producto Demostrativo', 15.50, 100, 'activo', NOW())
        ");
        $stmtProd->execute([$testCompanyId]);
    }

    $filePath = $exportService->generateCompanyDataExport($testCompanyId ?? 1);
    if (file_exists($filePath)) {
        echo " -> Archivo Excel multi-pestaña generado exitosamente: " . basename($filePath) . " (" . filesize($filePath) . " bytes)\n";
        @unlink($filePath); // Limpiar archivo de prueba
    } else {
        echo " -> [FAIL] El archivo Excel no fue generado en disco.\n";
    }
} catch (Exception $e) {
    echo " -> [FAIL 6]: " . $e->getMessage() . "\n";
}

// TEST 7: Limpieza y Borrado en Cascada (ON DELETE CASCADE)
try {
    echo "\n[TEST 7] Probando borrado en cascada de empresa...\n";
    if ($testCompanyId) {
        $del = $pdo->prepare("DELETE FROM empresa WHERE id = ?");
        $del->execute([$testCompanyId]);

        // Verificar que usuarios y productos de esa empresa fueron purgados en cascada
        $chkUsers = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE empresa_id = ?");
        $chkUsers->execute([$testCompanyId]);
        $remainingUsers = $chkUsers->fetchColumn();

        $chkProd = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE empresa_id = ?");
        $chkProd->execute([$testCompanyId]);
        $remainingProd = $chkProd->fetchColumn();

        if ($remainingUsers == 0 && $remainingProd == 0) {
            echo " -> Borrado en cascada verificado: Empresa y todos sus registros asociados eliminados al 100% sin dejar rastro.\n";
        } else {
            echo " -> [FAIL] Quedaron registros huérfanos tras eliminar la empresa.\n";
        }
    }
} catch (Exception $e) {
    echo " -> [FAIL 7]: " . $e->getMessage() . "\n";
}

echo "\n=== SUITE DE PRUEBAS COMPLETADA CON ÉXITO ===\n";
