<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/WarehouseController.php';

$controller = new WarehouseController();
$controller->handleRequest();
