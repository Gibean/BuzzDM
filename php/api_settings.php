<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/SettingsController.php';

$controller = new SettingsController();
$controller->handleRequest();
