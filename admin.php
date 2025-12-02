<?php
require_once 'php/sesion.php';
require_once 'php/db.php'; // Include db.php to access getCompanyData()

$companyData = getCompanyData(); // Fetch company data

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Administración</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
     <!-- Sidebar  -->
    <?php require_once 'php/sidebar.php'; ?>

     <!-- Main Content  -->
    <main class="main-content">
         <!-- Top Navbar  -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">ADMINISTRACIÓN DEL SISTEMA</h1>
                <span class="text-muted ms-2 font-mono">/ CONFIGURACIÓN</span>
            </div>
        </nav>

         <!-- Admin Tabs  -->
        <div class="buzz-card mb-4">
            <ul class="nav nav-tabs border-0" id="adminTabs">
                <li class="nav-item">
                    <button class="nav-link active bg-transparent border-0 text-primary font-mono" data-tab="users">
                        <i class="fas fa-users"></i> Usuarios
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link bg-transparent border-0 text-muted font-mono" data-tab="logs">
                        <i class="fas fa-list-alt"></i> Logs de Sesión
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link bg-transparent border-0 text-muted font-mono" data-tab="settings">
                        <i class="fas fa-cog"></i> Configuración
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link bg-transparent border-0 text-muted font-mono" data-tab="reports">
                        <i class="fas fa-file-alt"></i> Obtener Reportes de Sistema
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link bg-transparent border-0 text-muted font-mono" data-tab="backups">
                        <i class="fas fa-database"></i> Backups
                    </button>
                </li>
            </ul>
        </div>

         <!-- Users Tab  -->
        <div class="tab-content" id="usersTab">
            <!-- User Management Table - Full Width -->
            <div class="buzz-card mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-primary text-primary mb-0">GESTIÓN DE USUARIOS</h5>
                    <button class="btn btn-buzz" id="newUserBtn">
                        <i class="fas fa-user-plus"></i> Nuevo Usuario
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-buzz">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último Acceso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usersTable">
                             <!-- Users will be populated by JavaScript  -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- User Statistics - Below Table -->
            <div class="buzz-card">
                <h6 class="font-primary text-primary mb-3">ESTADÍSTICAS DE USUARIOS</h6>
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex gap-2">
                            <span class="text-muted">Total Usuarios:</span>
                            <span class="font-mono text-primary" id="totalUsers">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex gap-2">
                            <span class="text-muted">Usuarios Activos:</span>
                            <span class="font-mono text-success" id="activeUsers">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex gap-2">
                            <span class="text-muted">Administradores:</span>
                            <span class="font-mono text-warning" id="adminUsers">0</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="d-flex gap-2">
                            <span class="text-muted">Sesiones Hoy:</span>
                            <span class="font-mono text-primary" id="todaySessions">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       <!--   Logs Tab  -->
        <div class="tab-content" id="logsTab" style="display: none;">
            <div class="buzz-card">
                <h5 class="font-primary text-primary mb-3">LOGS DE SESIÓN</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-buzz" id="logSearch" placeholder="Buscar por usuario...">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control form-control-buzz" id="logDateFrom">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control form-control-buzz" id="logDateTo">
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-outline-buzz w-100" onclick="filterLogs()">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-outline-buzz w-100" onclick="clearLogsFilters()" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-buzz">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody id="logsTable">
                             <!-- Logs will be populated by JavaScript  -->
                        </tbody>
                    </table>
                </div>
                <div id="logsPagination" class="d-flex justify-content-between align-items-center mt-3" style="display: none !important;">
                    <div>
                        <span id="logsInfoText" class="text-muted"></span>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <button class="btn btn-outline-buzz btn-sm" id="logsPrevPage">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="logsCurrentPage" class="text-muted"></span>
                        <button class="btn btn-outline-buzz btn-sm" id="logsNextPage">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

       <!--   Backups Tab  -->
        <div class="tab-content" id="backupsTab" style="display: none;">
            <div class="row">
                <div class="col-md-8">
                    <div class="buzz-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="font-primary text-primary mb-0">BACKUPS DE BASE DE DATOS</h5>
                            <button class="btn btn-buzz" onclick="createBackup()">
                                <i class="fas fa-download"></i> Crear Backup
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-buzz">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Tamaño</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="backupsTable">
                                     <!-- Backups will be populated by JavaScript  -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="buzz-card">
                        <h6 class="font-primary text-primary mb-3">CONFIGURACIÓN DE BACKUP</h6>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="autoBackup" checked>
                                <label class="form-check-label text-muted" for="autoBackup">
                                    Activar backup automático
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Frecuencia</label>
                            <select class="form-control form-control-buzz" id="backupFrequency">
                                <option value="daily">Diario</option>
                                <option value="weekly">Semanal</option>
                                <option value="monthly">Mensual</option>
                            </select>
                        </div>
                        <div class="mb-3">
    <!--   Reports Tab  -->
        <div class="tab-content" id="reportsTab" style="display: none;">
            <div class="buzz-card">
                <h5 class="font-primary text-primary mb-3">OBTENER REPORTES DE SISTEMA</h5>
                <p class="text-muted mb-4">Para generar un reporte, seleccione el tipo y luego ingrese su contraseña de administrador.</p>

                <div class="row">
                    <!-- Report Selection -->
                    <div class="col-md-8">
                        <h6 class="font-primary text-primary mb-3">1. SELECCIONE EL TIPO DE REPORTE</h6>
                        <div class="row g-3" id="reportOptionsContainer">
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="historial_importaciones">
                                    <i class="fas fa-file-import fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Importaciones</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="historial_login">
                                    <i class="fas fa-sign-in-alt fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Inicios de Sesión</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="pedidos">
                                    <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Pedidos</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="clientes">
                                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Clientes</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="inventario">
                                    <i class="fas fa-boxes fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Inventario</p>
                                </div>
                            </div>
                             <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option" data-report-type="historial_acciones">
                                    <i class="fas fa-history fa-2x text-primary mb-2"></i>
                                    <p class="mb-0 text-muted small">Acciones Sistema</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Password and Generate Button -->
                    <div class="col-md-4">
                        <h6 class="font-primary text-primary mb-3">2. CONFIRMAR Y GENERAR</h6>
                        <div class="buzz-card p-3">
                            <form id="reportGenerationForm">
                                <div class="mb-3">
                                    <label for="adminPasswordReport" class="form-label text-muted">Contraseña de Administrador</label>
                                    <input type="password" class="form-control form-control-buzz" id="adminPasswordReport" placeholder="Ingrese su contraseña" required autocomplete="current-password">
                                </div>
                                <button type="button" class="btn btn-buzz w-100" id="generateReportBtn" disabled>
                                    <i class="fas fa-download"></i> Generar Reporte
                                </button>
                                <p id="reportError" class="text-danger small mt-2"></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!--      Settings Tab  -->
        <div class="tab-content" id="settingsTab" style="display: none;">
            <div class="row">
                <div class="col-md-6">
                    <div class="buzz-card">
                        <h5 class="font-primary text-primary mb-3">TU EMPRESA</h5>
                        <p class="text-muted mb-1"><strong>RIF / Cédula:</strong> <?php echo htmlspecialchars($companyData['rif'] ?? ''); ?></p>
                        <p class="text-muted mb-1"><strong>Nombre de la Empresa:</strong> <?php echo htmlspecialchars($companyData['nombre'] ?? ''); ?></p>
                        <p class="text-muted mb-1"><strong>Dirección:</strong> <?php echo htmlspecialchars($companyData['direccion'] ?? ''); ?></p>
                        <p class="text-muted mb-1"><strong>Teléfono:</strong> <?php echo htmlspecialchars($companyData['telefono'] ?? ''); ?></p>
                        <p class="text-muted mb-1"><strong>Email de Contacto:</strong> <?php echo htmlspecialchars($companyData['email'] ?? ''); ?></p>
                        <div class="text-end mt-3">
                            <a href="company_settings.php" class="btn btn-buzz">
                                <i class="fas fa-edit"></i> Editar Datos
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="buzz-card">
                        <h5 class="font-primary text-primary mb-3">CONFIGURACIÓN DE SEGURIDAD (PRONTO DISPONIBLE)</h5>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="twoFactor" checked>
                                <label class="form-check-label text-muted" for="twoFactor">
                                    Autenticación de dos factores
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="sessionTimeout" checked>
                                <label class="form-check-label text-muted" for="sessionTimeout">
                                    Timeout de sesión automático
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Duración de sesión (minutos)</label>
                            <input type="number" class="form-control form-control-buzz" value="60">
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="loginLogs" checked>
                                <label class="form-check-label text-muted" for="loginLogs">
                                    Registrar intentos de login
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="buzz-card">
                        <h5 class="font-primary text-primary mb-3">CONFIGURACIÓN GENERAL (PRONTO DISPONIBLE)</h5>
                        <p class="text-muted mb-1"><strong>Zona Horaria:</strong> Caracas/Venezuela</p>
                        <p class="text-muted mb-1"><strong>Moneda:</strong> USD ($)</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

 <!--     User Modal  -->
    <div class="modal-buzz" id="userModal">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary" id="userModalTitle">Nuevo Usuario</h5>
                <span class="close-buzz" onclick="closeUserModal()">&times;</span>
            </div>
            <form id="userForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Nombre *</label>
                        <input type="text" class="form-control form-control-buzz" id="userName" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email *</label>
                        <input type="email" class="form-control form-control-buzz" id="userEmail" required autocomplete="email">
                    </div>
                </div>
                <div class="row" id="userPasswordGroup">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Contraseña *</label>
                        <input type="password" class="form-control form-control-buzz" id="userPassword" required autocomplete="new-password">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Confirmar Contraseña *</label>
                        <input type="password" class="form-control form-control-buzz" id="userPasswordConfirm" required autocomplete="new-password">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Rol</label>
                        <select class="form-control form-control-buzz" id="userRole">
                            <option value="user">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                </div>
                <div id="userFormAlert" class="alert-buzz" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span id="userFormAlertText"></span>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-buzz" onclick="closeUserModal()">Cancelar</button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-save"></i> Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js?v=2"></script>
    <script src="assets/js/admin.js?v=2"></script>
</body>
</html>
