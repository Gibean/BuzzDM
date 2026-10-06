<?php

// Solo ejecucion desde linea de comandos: estos scripts introspeccionan el esquema
// y algunos escriben en la base de datos. Sobre HTTP devuelven 403.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("403 - Solo ejecutable desde CLI.\n");
}
/**
 * Elimina una empresa de prueba y verifica que no queden filas huérfanas.
 * Valida en vivo la cobertura de purga de la migración 004.
 *
 * Uso: php db/tools/_purge_test_company.php <empresa_id>
 */
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/db.php';

$empresaId = (int)($argv[1] ?? 0);
if ($empresaId <= 0) {
    fwrite(STDERR, "Uso: php _purge_test_company.php <empresa_id>\n");
    exit(1);
}

$pdo = getDbConnection();

/** Tablas con empresa_id, más las hijas sin columna propia. */
$tablas = $pdo->query("
    SELECT TABLE_NAME FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'empresa_id'
    ORDER BY TABLE_NAME
")->fetchAll(PDO::FETCH_COLUMN);

$hijas = [
    'detalle_pedido', 'detalle_cargo_existencias', 'detalle_requisicion',
    'detalle_transferencia', 'producto_componentes',
    'notas_adjuntos', 'notas_checklists', 'notas_items_inventario',
];
$tablas = array_values(array_unique(array_merge($tablas, $hijas)));

function contarRestos(PDO $pdo, array $tablas, int $empresaId): array
{
    $restos = [];
    foreach ($tablas as $t) {
        $tiene = $pdo->query("SHOW COLUMNS FROM `$t` LIKE 'empresa_id'")->fetchColumn();
        if ($tiene) {
            $s = $pdo->prepare("SELECT COUNT(*) FROM `$t` WHERE empresa_id = ?");
            $s->execute([$empresaId]);
            $n = (int)$s->fetchColumn();
        } else {
            // Tabla hija: se cuenta a través de su padre de negocio.
            $n = 0;
        }
        if ($n > 0) {
            $restos[$t] = $n;
        }
    }
    return $restos;
}

echo "--- Antes de purgar la empresa $empresaId ---\n";
$antes = contarRestos($pdo, $tablas, $empresaId);
foreach ($antes as $t => $n) {
    echo "   $t: $n\n";
}
echo '   Total tablas con datos: ' . count($antes) . "\n";

$existe = $pdo->prepare("SELECT COUNT(*) FROM empresa WHERE id = ?");
$existe->execute([$empresaId]);
if (!$existe->fetchColumn()) {
    echo "La empresa $empresaId no existe. Nada que hacer.\n";
    exit(0);
}

$pdo->beginTransaction();
$pdo->prepare("DELETE FROM empresa WHERE id = ?")->execute([$empresaId]);
$pdo->commit();

echo "\n--- Después de purgar ---\n";
$despues = contarRestos($pdo, $tablas, $empresaId);

$huerfanos = 0;
foreach ($tablas as $t) {
    $tiene = $pdo->query("SHOW COLUMNS FROM `$t` LIKE 'empresa_id'")->fetchColumn();
    if (!$tiene) {
        continue;
    }
    $s = $pdo->prepare("SELECT COUNT(*) FROM `$t` WHERE empresa_id = ?");
    $s->execute([$empresaId]);
    $n = (int)$s->fetchColumn();
    if ($n > 0) {
        echo "   [HUÉRFANO] $t: $n fila(s)\n";
        $huerfanos += $n;
    }
}

$sigue = $pdo->prepare("SELECT COUNT(*) FROM empresa WHERE id = ?");
$sigue->execute([$empresaId]);
echo '   Empresa presente: ' . ($sigue->fetchColumn() ? 'SI' : 'no') . "\n";
echo "\nResultado: " . ($huerfanos === 0 ? "sin filas huérfanas: purga completa." : "quedan $huerfanos fila(s) huérfana(s).") . "\n";
