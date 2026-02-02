<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/ImportController.php';

// Ensure high memory/time limit for big imports
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);

$controller = new ImportController();
$controller->handleRequest();
