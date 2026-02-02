<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/UserController.php';

$controller = new UserController();
$controller->handleRequest();
