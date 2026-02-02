<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/SupplierController.php';

$controller = new SupplierController();
$controller->handleRequest();
