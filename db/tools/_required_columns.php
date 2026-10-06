<?php

// Solo ejecucion desde linea de comandos: estos scripts introspeccionan el esquema
// y algunos escriben en la base de datos. Sobre HTTP devuelven 403.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("403 - Solo ejecutable desde CLI.\n");
}
/** Columnas NOT NULL sin default en las tablas que inserta el aprovisionamiento. */
require_once __DIR__ . '/../../php/config.php';
require_once __DIR__ . '/../../php/db.php';

$pdo = getDbConnection();

$tablas = ['empresa', 'almacenes', 'usuarios', 'categorias', 'configuraciones', 'colores', 'materiales', 'productos', 'marcas'];

foreach ($tablas as $t) {
    $cols = $pdo->query("
        SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, EXTRA
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$t'
          AND IS_NULLABLE = 'NO' AND COLUMN_DEFAULT IS NULL
          AND EXTRA NOT LIKE '%auto_increment%'
        ORDER BY ORDINAL_POSITION
    ")->fetchAll(PDO::FETCH_ASSOC);

    if ($cols) {
        echo "$t (NOT NULL sin default):\n";
        foreach ($cols as $c) {
            echo "   {$c['COLUMN_NAME']} {$c['COLUMN_TYPE']} extra={$c['EXTRA']}\n";
        }
    }
}
