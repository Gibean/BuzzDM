<?php
/** Comprueba que los ajustes de la empresa 1 son visibles con filtro de tenant. */
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/db.php';
require_once __DIR__ . '/../../php/src/Tenant/TenantContext.php';

$pdo = getDbConnection();

echo "--- configuraciones por empresa ---\n";
foreach ($pdo->query("SELECT empresa_id, COUNT(*) n FROM configuraciones GROUP BY empresa_id") as $r) {
    echo "  empresa {$r['empresa_id']}: {$r['n']}\n";
}

echo "\n--- visibles con filtro de tenant = 1 ---\n";
$stmt = $pdo->prepare("SELECT clave, valor FROM configuraciones WHERE empresa_id = ? ORDER BY clave");
$stmt->execute([1]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
    echo "  {$r['clave']} = {$r['valor']}\n";
}

echo "\n--- visibles con filtro de tenant = 14 ---\n";
$stmt->execute([14]);
$n = $stmt->fetchAll();
echo '  ' . count($n) . " ajuste(s). Correcto: una demo nueva no ve los ajustes de otra empresa.\n";

echo "\n--- logs_login por empresa ---\n";
foreach ($pdo->query("SELECT COALESCE(CAST(empresa_id AS CHAR),'(null)') e, accion, COUNT(*) n FROM logs_login GROUP BY empresa_id, accion") as $r) {
    echo "  empresa {$r['e']} / {$r['accion']}: {$r['n']}\n";
}
