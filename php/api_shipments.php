<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/ShipmentController.php';

$controller = new ShipmentController();
$controller->handleRequest();
