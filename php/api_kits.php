<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/KitController.php';

$controller = new KitController();
$controller->handleRequest();
