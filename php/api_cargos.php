<?php
// Standardized API Endpoint for Inventory Charges (Cargos)
// Part of the Service-Repository Pattern Refactor

require_once 'sesion.php'; // Handles Auth
require_once __DIR__ . '/src/Controller/CargoController.php';

$controller = new CargoController();
$controller->handleRequest();
