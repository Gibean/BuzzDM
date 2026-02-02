<?php
/**
 * API Endpoint for Two-Factor Authentication
 */
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/TwoFactorController.php';

$controller = new TwoFactorController();
$controller->handleRequest();
