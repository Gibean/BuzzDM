<?php
// Standardized API Endpoint for Clientes
// Part of the Service-Repository Pattern Refactor

require_once 'sesion.php'; // Handles Auth
require_once __DIR__ . '/src/Controller/ClienteController.php';

$controller = new ClienteController();
$controller->handleRequest();
