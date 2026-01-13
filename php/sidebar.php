<?php
require_once 'sesion.php';

// Obtener el rol y nombre del usuario desde la sesión
$usuarioNombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuarioRol = $_SESSION['usuario_rol'] ?? 'Invitado';

// Definir el icono basado en el rol
$userIcon = 'assets/img/ico_blue.png'; // Por defecto operador
if ($usuarioRol === 'admin') {
    $userIcon = 'assets/img/ico_yellow.png';
} elseif ($usuarioRol === 'supervisor') {
    $userIcon = 'assets/img/ico_gray.png';
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!-- Notification Hub CSS - Global include -->
<link rel="stylesheet" href="assets/css/notification-hub.css">

<nav class="sidebar sidebar-enhanced" id="sidebar">
    <div class="sidebar-header-enhanced">
        <div class="logo-container">
            <img src="assets/img/logov1_long.png" alt="BUZZ Logo" class="sidebar-logo-enhanced">
        </div>
    </div>

    <div class="sidebar-nav-enhanced">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link-enhanced <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>"
                    href="dashboard.php">
                    <i class="fas fa-home nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Dashboard</span>
                </a>
            </li>

            <?php if ($usuarioRol === 'admin' || $usuarioRol === 'supervisor'): ?>
            <?php
            $inventarioPages = ['inventario.php', 'cargos_existencias.php', 'almacenes.php', 'proveedores.php', 'catalogos.php'];
            $inventarioActive = in_array($currentPage, $inventarioPages);
            ?>
            <li class="nav-item nav-dropdown <?php echo $inventarioActive ? 'open' : ''; ?>">
                <a class="nav-link-enhanced nav-dropdown-toggle <?php echo $inventarioActive ? 'active' : ''; ?>">
                    <i class="fas fa-boxes nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Inventario</span>
                    <i class="fas fa-chevron-down nav-dropdown-arrow"></i>
                </a>
                <ul class="nav-dropdown-menu">
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'inventario.php' ? 'active' : ''; ?>"
                           href="inventario.php">
                            <i class="fas fa-box"></i> Productos
                        </a>
                    </li>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'cargos_existencias.php' ? 'active' : ''; ?>"
                           href="cargos_existencias.php">
                            <i class="fas fa-dolly"></i> Cargar Existencias
                        </a>
                    </li>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'almacenes.php' ? 'active' : ''; ?>"
                           href="almacenes.php">
                            <i class="fas fa-warehouse"></i> Almacenes
                        </a>
                    </li>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'proveedores.php' ? 'active' : ''; ?>"
                           href="proveedores.php">
                            <i class="fas fa-truck"></i> Proveedores
                        </a>
                    </li>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'catalogos.php' ? 'active' : ''; ?>"
                           href="catalogos.php">
                            <i class="fas fa-list"></i> Catálogos
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link-enhanced <?php echo $currentPage === 'clientes.php' ? 'active' : ''; ?>" href="clientes.php">
                    <i class="fas fa-users nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Clientes</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link-enhanced <?php echo $currentPage === 'pedidos.php' ? 'active' : ''; ?>" href="pedidos.php">
                    <i class="fas fa-shopping-cart nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Pedidos</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link-enhanced <?php echo $currentPage === 'todo.php' ? 'active' : ''; ?>" href="todo.php">
                    <i class="fas fa-tasks nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">To-Do</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link-enhanced <?php echo $currentPage === 'envios.php' ? 'active' : ''; ?>" href="envios.php">
                    <i class="fas fa-truck nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Envíos</span>
                </a>
            </li>

            <?php if ($usuarioRol === 'admin' || $usuarioRol === 'supervisor'): ?>
            <li class="nav-item">
                <a class="nav-link-enhanced <?php echo $currentPage === 'importacion.php' ? 'active' : ''; ?>"
                    href="importacion.php">
                    <i class="fas fa-upload nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Importación</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($usuarioRol === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link-enhanced <?php echo $currentPage === 'admin.php' ? 'active' : ''; ?>" href="admin.php">
                    <i class="fas fa-users-cog nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Administración</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link-enhanced <?php echo $currentPage === 'about.php' ? 'active' : ''; ?>" href="about.php">
                    <i class="fas fa-info-circle nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Acerca de</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer-enhanced">
        <div class="user-profile-enhanced">
            <div class="user-avatar-container">
                <img src="<?php echo $userIcon; ?>" alt="User Icon" class="user-avatar-enhanced">
                <div class="user-status-indicator"></div>
            </div>
            <div class="user-info-enhanced">
                <div class="user-name-enhanced" id="userName">
                    <?php echo htmlspecialchars($usuarioNombre); ?>
                </div>
                <div class="user-role-enhanced" id="userRole">
                    <i class="fas fa-shield-alt"></i>
                    <?php echo htmlspecialchars(ucfirst($usuarioRol)); ?>
                </div>
            </div>
        </div>
        <form action="php/logout.php" method="POST" class="logout-form-enhanced">
            <button type="submit" class="btn-logout-enhanced">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </button>
        </form>
    </div>
</nav>

<!-- Notification Overlay (closes hub when clicked) -->
<div class="notification-overlay" id="notificationOverlay"></div>

<!-- Notification Hub Offcanvas -->
<div class="notification-offcanvas" id="notificationHub">
    <div class="offcanvas-header">
        <div class="offcanvas-title">
            <i class="fas fa-bell"></i> Centro de Notificaciones
        </div>
        <button class="offcanvas-close" id="closeHub">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="offcanvas-actions">
        <button class="action-link" id="markAllRead">Marcar todo como leído</button>
        <button class="action-link text-danger" id="clearHistory">Borrar historial</button>
    </div>
    <div class="offcanvas-body" id="notifList">
        <!-- Notificaciones inyectadas por JS -->
    </div>
</div>

<script src="assets/js/notifications.js"></script>

