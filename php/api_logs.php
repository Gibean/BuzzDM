<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/LogController.php';

$controller = new LogController();
$controller->handleRequest();
