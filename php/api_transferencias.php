<?php
/**
 * API Endpoint: Warehouse Transfers
 */

session_start();
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/src/Controller/TransferenciaController.php';

$controller = new TransferenciaController();
$controller->handleRequest();
