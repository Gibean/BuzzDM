<?php
// Standardized API Endpoint for Products
// Part of the Service-Repository Pattern Refactor

require_once 'sesion.php'; // Handles Auth
require_once __DIR__ . '/src/Controller/ProductController.php';

$controller = new ProductController();
$controller->handleRequest();
