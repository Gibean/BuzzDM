<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/BackupController.php';

$controller = new BackupController();
$controller->handleRequest();
