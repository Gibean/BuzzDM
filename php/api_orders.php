<?php
// Standardized API Endpoint for Orders (Pedidos)
// Part of the Service-Repository Pattern Refactor

require_once 'sesion.php'; // Handles Auth
require_once __DIR__ . '/src/Controller/OrderController.php';

$controller = new OrderController();
$controller->handleRequest();
