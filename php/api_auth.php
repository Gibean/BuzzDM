<?php
/**
 * Unified Auth API Entry Point
 * Handles: login, forgot_password, reset_password, logout
 */
require_once __DIR__ . '/src/Controller/AuthController.php';

$controller = new AuthController();
$controller->handleRequest();
