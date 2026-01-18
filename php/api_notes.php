<?php
require_once 'sesion.php';
require_once __DIR__ . '/src/Controller/NoteController.php';

$controller = new NoteController();
$controller->handleRequest();
