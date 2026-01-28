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
            <!-- Dashboard - Siempre visible -->
            <li class="nav-item">
                <a class="nav-link-enhanced <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>"
                    href="dashboard.php">
                    <i class="fas fa-home nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Dashboard</span>
                </a>
            </li>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- PRODUCTOS - Solo admin/supervisor -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <?php if ($usuarioRol === 'admin' || $usuarioRol === 'supervisor'): ?>
            <?php
            $productosPages = ['inventario.php', 'catalogos.php', 'almacenes.php', 'proveedores.php'];
            $productosActive = in_array($currentPage, $productosPages);
            ?>
            <li class="nav-item nav-dropdown <?php echo $productosActive ? 'open' : ''; ?>">
                <a class="nav-link-enhanced nav-dropdown-toggle <?php echo $productosActive ? 'active' : ''; ?>">
                    <i class="fas fa-boxes nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Productos</span>
                    <i class="fas fa-chevron-down nav-dropdown-arrow"></i>
                </a>
                <ul class="nav-dropdown-menu">
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'inventario.php' ? 'active' : ''; ?>"
                           href="inventario.php">
                            <i class="fas fa-box"></i> Inventario
                        </a>
                    </li>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'catalogos.php' ? 'active' : ''; ?>"
                           href="catalogos.php">
                            <i class="fas fa-list"></i> Catálogos
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
                            <i class="fas fa-truck-loading"></i> Proveedores
                        </a>
                    </li>
                </ul>
            </li>
            <?php endif; ?>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- OPERACIONES - Mixto (permisos por item) -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <?php
            $operacionesPages = ['clientes.php', 'pedidos.php', 'envios.php', 'cargos_existencias.php'];
            $operacionesActive = in_array($currentPage, $operacionesPages);
            ?>
            <li class="nav-item nav-dropdown <?php echo $operacionesActive ? 'open' : ''; ?>">
                <a class="nav-link-enhanced nav-dropdown-toggle <?php echo $operacionesActive ? 'active' : ''; ?>">
                    <i class="fas fa-briefcase nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Operaciones</span>
                    <i class="fas fa-chevron-down nav-dropdown-arrow"></i>
                </a>
                <ul class="nav-dropdown-menu">
                    <?php if ($usuarioRol === 'admin' || $usuarioRol === 'supervisor'): ?>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'clientes.php' ? 'active' : ''; ?>"
                           href="clientes.php">
                            <i class="fas fa-users"></i> Clientes
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'pedidos.php' ? 'active' : ''; ?>"
                           href="pedidos.php">
                            <i class="fas fa-shopping-cart"></i> Pedidos
                        </a>
                    </li>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'envios.php' ? 'active' : ''; ?>"
                           href="envios.php">
                            <i class="fas fa-truck"></i> Envíos
                        </a>
                    </li>
                    <?php if ($usuarioRol === 'admin' || $usuarioRol === 'supervisor'): ?>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'cargos_existencias.php' ? 'active' : ''; ?>"
                           href="cargos_existencias.php">
                            <i class="fas fa-dolly"></i> Cargar Existencias
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- HERRAMIENTAS - Mixto (permisos por item) -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <?php
            $herramientasPages = ['todo.php', 'importacion.php'];
            $herramientasActive = in_array($currentPage, $herramientasPages);
            ?>
            <li class="nav-item nav-dropdown <?php echo $herramientasActive ? 'open' : ''; ?>">
                <a class="nav-link-enhanced nav-dropdown-toggle <?php echo $herramientasActive ? 'active' : ''; ?>">
                    <i class="fas fa-tools nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Herramientas</span>
                    <i class="fas fa-chevron-down nav-dropdown-arrow"></i>
                </a>
                <ul class="nav-dropdown-menu">
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'todo.php' ? 'active' : ''; ?>"
                           href="todo.php">
                            <i class="fas fa-tasks"></i> Notas
                        </a>
                    </li>
                    <?php if ($usuarioRol === 'admin' || $usuarioRol === 'supervisor'): ?>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'importacion.php' ? 'active' : ''; ?>"
                           href="importacion.php">
                            <i class="fas fa-upload"></i> Importación
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </li>

            <!-- ═══════════════════════════════════════════════════════════ -->
            <!-- SISTEMA - Visible para todos, items con permisos -->
            <!-- ═══════════════════════════════════════════════════════════ -->
            <?php
            $sistemaPages = ['admin.php', 'about.php'];
            $sistemaActive = in_array($currentPage, $sistemaPages);
            ?>
            <li class="nav-item nav-dropdown <?php echo $sistemaActive ? 'open' : ''; ?>">
                <a class="nav-link-enhanced nav-dropdown-toggle <?php echo $sistemaActive ? 'active' : ''; ?>">
                    <i class="fas fa-cog nav-icon-enhanced"></i>
                    <span class="nav-text-enhanced">Sistema</span>
                    <i class="fas fa-chevron-down nav-dropdown-arrow"></i>
                </a>
                <ul class="nav-dropdown-menu">
                    <?php if ($usuarioRol === 'admin'): ?>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'admin.php' ? 'active' : ''; ?>"
                           href="admin.php">
                            <i class="fas fa-users-cog"></i> Administración
                        </a>
                    </li>
                    <?php endif; ?>
                    <li>
                        <a class="nav-dropdown-item <?php echo $currentPage === 'about.php' ? 'active' : ''; ?>"
                           href="about.php">
                            <i class="fas fa-info-circle"></i> Acerca de
                        </a>
                    </li>
                </ul>
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
        <button class="action-link" id="markAllRead">
            <i class="fas fa-check-double"></i> Marcar todo leído
        </button>
        <button class="action-link text-danger" id="clearHistory">
            <i class="fas fa-trash-alt"></i> Borrar historial
        </button>
    </div>
    <div class="offcanvas-body" id="notifList">
        <!-- Notificaciones inyectadas por JS -->
    </div>
</div>

<script src="assets/js/notifications.js"></script>

