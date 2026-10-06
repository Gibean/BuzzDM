<?php

// Solo ejecucion desde linea de comandos: estos scripts introspeccionan el esquema
// y algunos escriben en la base de datos. Sobre HTTP devuelven 403.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("403 - Solo ejecutable desde CLI.\n");
}
/**
 * Auditoría de reglas FK: detecta CASCADE que puedan borrar datos de más.
 * Uso: php db/tools/_audit_fk_cascade.php
 */
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/db.php';

$pdo = getDbConnection();

$sql = "SELECT kcu.TABLE_NAME AS tab, kcu.COLUMN_NAME AS col,
               kcu.REFERENCED_TABLE_NAME AS ref_tab, kcu.REFERENCED_COLUMN_NAME AS ref_col,
               rc.DELETE_RULE AS del, rc.UPDATE_RULE AS upd
        FROM information_schema.REFERENTIAL_CONSTRAINTS rc
        JOIN information_schema.KEY_COLUMN_USAGE kcu
          ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
         AND kcu.CONSTRAINT_SCHEMA = rc.CONSTRAINT_SCHEMA
        WHERE kcu.TABLE_SCHEMA = DATABASE()
        ORDER BY kcu.TABLE_NAME, kcu.COLUMN_NAME";

$filas = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$porRegla = [];
foreach ($filas as $f) {
    $porRegla[$f['del']][] = $f;
}

echo "Total de FKs: " . count($filas) . "\n\n";

foreach (['CASCADE', 'SET NULL', 'RESTRICT', 'NO ACTION'] as $regla) {
    $items = $porRegla[$regla] ?? [];
    echo strtoupper($regla) . " en DELETE: " . count($items) . "\n";
    foreach ($items as $f) {
        $marca = '';
        // CASCADE desde empresa.id es lo esperado (purga de la demo).
        // CASCADE desde cualquier otra tabla significa que borrar un registro
        // de catálogo borra documentos de negocio: hay que revisarlo a mano.
        if ($regla === 'CASCADE' && $f['ref_tab'] !== 'empresa') {
            $marca = '  <-- REVISAR: borrar ' . $f['ref_tab'] . ' elimina filas de ' . $f['tab'];
        }
        echo "   {$f['tab']}.{$f['col']} -> {$f['ref_tab']}.{$f['ref_col']}$marca\n";
    }
    echo "\n";
}
