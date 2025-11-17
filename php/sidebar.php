<?php
require_once 'sesion.php';

// Obtener el rol y nombre del usuario desde la sesión
$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuarioRol = $_SESSION['usuario_rol'] ?? 'Invitado';

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center">
            <img src="assets/img/logov1_long.png" alt="BUZZ Logo" class="sidebar-logo">
        </div>
    </div>

    <div class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>" href="/buzz/dashboard.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>

            <?php if ($usuarioRol === 'admin' || $usuarioRol === 'supervisor'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'inventario.php' ? 'active' : ''; ?>" href="/buzz/inventario.php">
                    <i class="fas fa-box"></i> Inventario
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'clientes.php' ? 'active' : ''; ?>" href="/buzz/clientes.php">
                    <i class="fas fa-users"></i> Clientes
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'pedidos.php' ? 'active' : ''; ?>" href="/buzz/pedidos.php">
                    <i class="fas fa-shopping-cart"></i> Pedidos
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'envios.php' ? 'active' : ''; ?>" href="/buzz/envios.php">
                    <i class="fas fa-truck"></i> Envíos
                </a>
            </li>

            <?php if ($usuarioRol === 'admin' || $usuarioRol === 'supervisor'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'importacion.php' ? 'active' : ''; ?>" href="/buzz/importacion.php">
                    <i class="fas fa-upload"></i> Importación
                </a>
            </li>
            <?php endif; ?>

            <?php if ($usuarioRol === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'admin.php' ? 'active' : ''; ?>" href="/buzz/admin.php">
                    <i class="fas fa-users-cog"></i> Administración
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link <?php echo $currentPage === 'about.php' ? 'active' : ''; ?>" href="/buzz/about.php">
                    <i class="fas fa-info-circle"></i> Acerca de
                </a>
            </li>
        </ul>
    </div>

    <div class="mt-auto p-3 border-top border-secondary">
        <div class="d-flex align-items-center">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                <i class="fas fa-user text-dark"></i>
            </div>
            <div>
                <div class="font-mono" style="font-size: 0.9rem;" id="userName"><?php echo htmlspecialchars($usuarioNombre); ?></div>
                <small class="text-muted" id="userRole"><?php echo htmlspecialchars($usuarioRol); ?></small>
            </div>
        </div>
        <button class="btn btn-outline-buzz btn-sm mt-2 w-100" onclick="logout()">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>
    </div>
</nav>
