<?php
require 'php/db.php';
try {
    $pdo = getDbConnection();
    echo "CONNECTED OK to: " . $pdo->query('SELECT DATABASE()')->fetchColumn() . "\n";
} catch(Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
