<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/LocationController.php';

$controller = new LocationController();
$controller->handleRequest();
