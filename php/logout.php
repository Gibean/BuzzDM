<?php
session_start();
require_once 'sesiones.php';

$token = $_POST['token'] ?? '';

if ($token) {
    cerrarSesion($token);
    echo json_encode(["message" => "Sesión cerrada"]);
} else {
    echo json_encode(["error" => "Token no proporcionado"]);
}
?>
