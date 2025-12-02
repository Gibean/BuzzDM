<?php
require_once 'sesion.php';
// Simple dashboard redirect or placeholder
if ($_SESSION['usuario_rol'] === 'admin') {
    header('Location: ../admin.php');
} else {
    echo "<h1>Dashboard</h1><p>Bienvenido, " . htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') . "</p>";
    echo '<a href="logout.php">Cerrar Sesión</a>';
}
?>
