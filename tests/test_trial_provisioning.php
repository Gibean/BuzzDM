<?php
/**
 * Verifica que dos empresas nuevas puedan coexistir con los mismos códigos,
 * catálogos y ajustes. Regresión del fallo que rompía el segundo registro demo.
 *
 * Todo ocurre dentro de una transacción que se revierte: el objetivo es
 * validar las restricciones del esquema, no crear empresas de verdad.
 *
 * Uso: php tests/test_trial_provisioning.php
 */

require_once __DIR__ . '/../php/config.php';
require_once __DIR__ . '/../php/db.php';
require_once __DIR__ . '/../php/src/Tenant/TenantContext.php';

$passed = 0;
$failed = 0;

function check(string $descripcion, bool $condicion, string $detalle = ''): void
{
    global $passed, $failed;
    if ($condicion) {
        $passed++;
        echo "  [OK]   $descripcion\n";
    } else {
        $failed++;
        echo "  [FALLA] $descripcion" . ($detalle !== '' ? " -> $detalle" : '') . "\n";
    }
}

/** Reproduce los INSERT de CompanyService::createTrialEnvironment. */
function aprovisionar(PDO $pdo, string $nombreEmpresa, string $email): int
{
    global $sello;

    $rif = 'J-' . str_pad((string)random_int(10000000, 99999999), 9, '0', STR_PAD_LEFT);
    $stmt = $pdo->prepare("
        INSERT INTO empresa (
            nombre, tipo_rif, rif, email, admin_email, device_fingerprint,
            trial_ends_at, is_demo, status, creado_at
        ) VALUES (
            ?, 'J', ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 15 DAY), 1, 'pending_verification', NOW()
        )
    ");
    $stmt->execute([$nombreEmpresa, $rif, $email, $email, hash('sha256', $email . $sello)]);
    $empresaId = (int)$pdo->lastInsertId();

    // Código fijo 'ALM-001': idéntico en todas las empresas por diseño.
    $pdo->prepare("
        INSERT INTO almacenes (empresa_id, codigo, nombre, tipo, es_principal, activo, creado_at)
        VALUES (?, 'ALM-001', 'Almacén Principal', 'almacen', 1, 1, NOW())
    ")->execute([$empresaId]);

    $pdo->prepare("
        INSERT INTO usuarios (empresa_id, nombre, email, password, rol, estado, creado_at)
        VALUES (?, 'Admin', ?, 'x', 'admin', 'activo', NOW())
    ")->execute([$empresaId, $email]);

    foreach (['General', 'Papelería y Oficina', 'Tecnología'] as $cat) {
        $pdo->prepare("INSERT INTO categorias (empresa_id, nombre, descripcion) VALUES (?, ?, ?)")
            ->execute([$empresaId, $cat, "Categoría inicial $cat"]);
    }

    foreach ([['store_name', $nombreEmpresa], ['currency', 'VES'], ['tax_percent', '16.00'], ['backup_fallback_enabled', '0']] as [$clave, $valor]) {
        $pdo->prepare("
            INSERT INTO configuraciones (empresa_id, clave, valor, descripcion, fecha_actualizacion)
            VALUES (?, ?, ?, '', NOW())
        ")->execute([$empresaId, $clave, $valor]);
    }

    foreach ([['Negro', '#000000'], ['Blanco', '#FFFFFF'], ['Azul', '#1E5AA8']] as [$n, $h]) {
        $pdo->prepare("INSERT INTO colores (empresa_id, nombre, codigo_hex) VALUES (?, ?, ?)")
            ->execute([$empresaId, $n, $h]);
    }

    foreach (['Algodón', 'Poliéster', 'Denim'] as $m) {
        $pdo->prepare("INSERT INTO materiales (empresa_id, nombre) VALUES (?, ?)")
            ->execute([$empresaId, $m]);
    }

    $pdo->prepare("
        INSERT INTO productos (empresa_id, sku, nombre, precio, stock, min_stock, estado, unidad_venta, tipo_producto, creado_at)
        VALUES (?, 'SKU-001', 'Producto de ejemplo', 10.00, 5, 1, 'activo', 'UND', 'fisico', NOW())
    ")->execute([$empresaId]);

    return $empresaId;
}

echo "=== APROVISIONAMIENTO DE DOS DEMOS SIMULTÁNEAS ===\n\n";

$pdo = getDbConnection();
$sello = bin2hex(random_bytes(4));

$pdo->beginTransaction();
try {
    echo "[1] Dos empresas con el mismo código de almacén y los mismos catálogos\n";
    $empX = aprovisionar($pdo, "Empresa Demo X $sello", "x_$sello@test.invalid");
    $empY = aprovisionar($pdo, "Empresa Demo Y $sello", "y_$sello@test.invalid");
    check('La primera empresa se aprovisiona', $empX > 0);
    check('La segunda empresa se aprovisiona con los mismos "ALM-001" y "General"', $empY > 0);

    echo "\n[2] Aislamiento de los catálogos creados\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categorias WHERE empresa_id = ?");
    $stmt->execute([$empX]);
    check('X tiene sus 3 categorías', (int)$stmt->fetchColumn() === 3);
    $stmt->execute([$empY]);
    check('Y tiene sus 3 categorías', (int)$stmt->fetchColumn() === 3);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE empresa_id = ? AND sku = 'SKU-001'");
    $stmt->execute([$empX]);
    check('X tiene su SKU-001', (int)$stmt->fetchColumn() === 1);
    $stmt->execute([$empY]);
    check('Y tiene su propio SKU-001', (int)$stmt->fetchColumn() === 1);

    echo "\n[3] Los ajustes no se comparten\n";
    $stmt = $pdo->prepare("SELECT valor FROM configuraciones WHERE empresa_id = ? AND clave = 'store_name'");
    $stmt->execute([$empX]);
    check('X ve su propio nombre de tienda', $stmt->fetchColumn() === "Empresa Demo X $sello");
    $stmt->execute([$empY]);
    check('Y ve su propio nombre de tienda', $stmt->fetchColumn() === "Empresa Demo Y $sello");

    echo "\n[4] Unicidad dentro de la misma empresa\n";
    $duplicado = false;
    try {
        $pdo->prepare("INSERT INTO almacenes (empresa_id, codigo, nombre, tipo, es_principal, activo, creado_at)
                       VALUES (?, 'ALM-001', 'Duplicado', 'almacen', 0, 1, NOW())")->execute([$empX]);
    } catch (PDOException $e) {
        $duplicado = str_contains($e->getMessage(), 'uk_almacenes_empresa_codigo');
    }
    check('El código de almacén sigue siendo único dentro de la empresa', $duplicado);

    echo "\n=== RESUMEN: $passed correctas, $failed fallidas ===\n";
} catch (Throwable $e) {
    $failed++;
    echo "\n[ERROR] " . get_class($e) . ': ' . $e->getMessage() . "\n";
} finally {
    $pdo->rollBack();
    echo "\nTransacción revertida: no queda ninguna empresa de prueba.\n";
}

exit($failed > 0 ? 1 : 0);
