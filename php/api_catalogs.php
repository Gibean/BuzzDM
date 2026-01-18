<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/CatalogController.php';

$controller = new CatalogController();
$controller->handleRequest();
