<?php
/** Tablas con empresa_id pero sin FK hacia empresa.id. */
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/db.php';

$pdo = getDbConnection();

$conCol = $pdo->query("
    SELECT TABLE_NAME FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'empresa_id'
    ORDER BY TABLE_NAME
")->fetchAll(PDO::FETCH_COLUMN);

$conFk = $pdo->query("
    SELECT DISTINCT kcu.TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE kcu
    JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
      ON rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
     AND rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
    WHERE kcu.TABLE_SCHEMA = DATABASE() AND kcu.REFERENCED_TABLE_NAME = 'empresa'
")->fetchAll(PDO::FETCH_COLUMN);

$faltan = array_values(array_diff($conCol, $conFk));

echo "Tablas con empresa_id: " . count($conCol) . "\n";
echo "Con FK a empresa.id:   " . count($conFk) . "\n";
echo "SIN FK (" . count($faltan) . "):\n";
foreach ($faltan as $t) {
    $total = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    $nulos = $pdo->query("SELECT COUNT(*) FROM `$t` WHERE empresa_id IS NULL")->fetchColumn();
    $esNull = $pdo->query(
        "SELECT IS_NULLABLE FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$t' AND COLUMN_NAME = 'empresa_id'"
    )->fetchColumn();
    echo "   $t  filas=$total nulos=$nulos nullable=$esNull\n";
}
