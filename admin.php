    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Administración</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css?v=3">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
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
                            <!-- New Reports -->
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option border-warning" data-report-type="inventario_critico">
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                    <p class="mb-0 text-muted small">Inv. Crítico</p>
                                </div>
                            </div>
                            <div class="col-md-4 col-lg-3">
                                <div class="buzz-card text-center p-3 report-option border-success" data-report-type="resumen_financiero">
                                    <i class="fas fa-file-invoice-dollar fa-2x text-success mb-2"></i>
                                    <p class="mb-0 text-muted small">Resumen Financiero</p>
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
                                    <div style="position: relative;">
                                        <input type="password" class="form-control form-control-buzz" id="adminPasswordReport" placeholder="Ingrese su contraseña" required autocomplete="current-password" style="padding-right: 45px;">
                                        <button type="button" id="toggleAdminPasswordReport" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--buzz-text-muted); cursor: pointer; padding: 5px; transition: color 0.3s ease;" aria-label="Toggle password visibility">
                                            <i class="fas fa-eye" id="toggleAdminPasswordReportIcon"></i>
                                        </button>
                                    </div>
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

    <!--   Settings Tab  -->
        <div class="tab-content" id="settingsTab" style="display: none;">
            <div class="row">
                <!-- Company Information Form -->
                <div class="col-lg-7">
                    <div class="buzz-card h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="font-primary text-primary mb-0">DATOS DE LA EMPRESA</h5>
                            <i class="fas fa-building text-primary"></i>
                        </div>
                        <form id="companyInfoForm">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Tipo RIF</label>
                                    <select class="form-select form-control-buzz" id="rifType" name="rifType">
                                        <option value="J">J - Jurídico</option>
                                        <option value="V">V - Venezolano</option>
                                        <option value="G">G - Gubernamental</option>
                                        <option value="E">E - Extranjero</option>
                                        <option value="P">P - Pasaporte</option>
                                    </select>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label text-muted small">Número de RIF / Cédula</label>
                                    <input type="text" class="form-control form-control-buzz" id="rifNumber" name="rifNumber" placeholder="Ej: 123456780">
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted small">Nombre de la Empresa</label>
                                    <input type="text" class="form-control form-control-buzz" id="companyName" name="companyName" placeholder="Nombre Oficial">
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted small">Dirección Fiscal / Principal</label>
                                    <textarea class="form-control form-control-buzz" id="companyAddress" name="companyAddress" rows="2" placeholder="Dirección completa..."></textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-muted small">Cód. Área / País</label>
                                    <input type="text" class="form-control form-control-buzz" id="phoneCode" name="phoneCode" placeholder="+58">
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label text-muted small">Teléfono</label>
                                    <input type="text" class="form-control form-control-buzz" id="phoneNumber" name="phoneNumber" placeholder="4121234567">
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted small">Email de Contacto</label>
                                    <input type="email" class="form-control form-control-buzz" id="companyEmail" name="companyEmail" placeholder="admin@empresa.com">
                                </div>
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-buzz btn-lg w-100">
                                    <i class="fas fa-save me-2"></i>GUARDAR CAMBIOS
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Logo and Security Placeholder -->
                <div class="col-lg-5">
                    <!-- Logo Management -->
                    <div class="buzz-card mb-4">
                        <h6 class="font-primary text-primary mb-3">LOGO DE LA EMPRESA (SVG)</h6>
                        <div id="currentLogoPreview" class="text-center mb-3 p-3 rounded" style="background: rgba(0,0,0,0.2); min-height: 100px; display: flex; align-items: center; justify-content: center;">
                            <!-- JS will populate current logo here -->
                            <i class="fas fa-image fa-3x text-muted opacity-25"></i>
                        </div>

                        <div class="logo-upload-box p-3 border border-dashed rounded text-center" id="logoUploadArea" style="border-color: rgba(255,160,0,0.3) !important;">
                            <i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
                            <p class="small text-muted mb-2">Suelte el archivo SVG aquí o</p>
                            <input type="file" id="logoFileInput" accept=".svg" style="display: none;">
                            <button type="button" class="btn btn-sm btn-outline-buzz" onclick="document.getElementById('logoFileInput').click()">
                                <i class="fas fa-search me-1"></i>Buscar Archivo
                            </button>
                        </div>

                        <div id="logoFileInfo" class="mt-2 small" style="display: none;">
                            <div class="alert alert-info py-1 px-2 d-flex justify-content-between align-items-center">
                                <span id="logoFileName" class="text-truncate me-2"></span>
                                <button type="button" class="btn btn-sm text-danger p-0" id="clearLogoFileBtn">&times;</button>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-100 mt-1" id="uploadLogoBtn">
                                <i class="fas fa-upload me-1"></i>SUBIR LOGO
                            </button>
                        </div>
                    </div>

                    <!-- Security (Visual only for now as in JS) -->
                    <div class="buzz-card">
                        <h6 class="font-primary text-primary mb-3">SEGURIDAD</h6>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="twoFactor">
                            <label class="form-check-label text-muted small" for="twoFactor">Autenticación de 2 Factores</label>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="sessionTimeout" checked>
                            <label class="form-check-label text-muted small" for="sessionTimeout">Timeout de sesión automático</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="loginLogs" checked>
                            <label class="form-check-label text-muted small" for="loginLogs">Logs de inicio de sesión</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

       <!--   Backups Tab  -->
        <div class="tab-content" id="backupsTab" style="display: none;">
            <!-- Auto-Backup Status Card - Responsive -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="buzz-card">
                        <div class="row align-items-center">
                            <!-- Status Indicator -->
                            <div class="col-12 col-md-4 mb-3 mb-md-0">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="status-dot-large" id="statusDot"></div>
                                    <div>
                                        <span class="font-primary" id="statusLabel">VERIFICANDO...</span>
                                        <p class="text-muted mb-0 small" id="statusMessage">Cargando estado...</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats - Responsive Grid -->
                            <div class="col-12 col-md-5">
                                <div class="row text-center text-md-start">
                                    <div class="col-4">
                                        <span class="text-muted small d-block">Último Auto</span>
                                        <span class="font-mono small" id="lastAutoBackup">--</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-muted small d-block">Próximo</span>
                                        <span class="font-mono small" id="nextAutoBackup">--</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-muted small d-block">Total</span>
                                        <span class="font-mono text-primary" id="totalAutoBackups">--</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Toggle Switch -->
                            <div class="col-12 col-md-3 mt-3 mt-md-0">
                                <div class="d-flex align-items-center justify-content-center justify-content-md-end gap-2">
                                    <span class="text-muted small">Auto-Backup</span>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" id="autoBackupToggle" checked>
                                        <label class="form-check-label visually-hidden" for="autoBackupToggle">
                                            Activar backup automático
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Backups Table -->
                <div class="col-lg-8 col-12 mb-3 mb-lg-0">
                    <div class="buzz-card">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-3 gap-2">
                            <h5 class="font-primary text-primary mb-0">HISTORIAL DE BACKUPS</h5>
                            <button class="btn btn-buzz btn-sm" onclick="createBackup()">
                                <i class="fas fa-plus"></i> Nuevo Backup
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

                <!-- Info Panel -->
                <div class="col-lg-4 col-12">
                    <div class="buzz-card">
                        <h6 class="font-primary text-primary mb-3">
                            <i class="fas fa-cog"></i> CONFIGURACIÓN
                        </h6>

                        <!-- Auto Backup Toggle Info -->
                        <div class="mb-3 p-2 rounded" style="background: rgba(255,160,0,0.1);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-robot text-primary"></i>
                                <strong class="small">Sistema Fallback</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                Si no hay backups en 24h, el sistema crea uno automáticamente cuando un admin visita la página.
                            </p>
                        </div>

                        <!-- Cron Info -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-clock text-primary"></i>
                                <strong class="small">Cron Job (Hosting)</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                Para mayor fiabilidad, configure un cron job en su hosting que ejecute <code class="small">auto_backup.php</code>
                            </p>
                        </div>

                        <!-- Warning -->
                        <div class="p-2 rounded" style="background: rgba(255,193,7,0.1);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <strong class="small text-warning">Restaurar</strong>
                            </div>
                            <p class="text-muted small mb-0">
                                Sobrescribe todos los datos actuales. Acción irreversible.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- System Footer -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

 <!--     User Modal  -->
    <div class="modal-buzz" id="userModal">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary" id="userModalTitle">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </h5>
                <button class="close-buzz" onclick="closeUserModal()" aria-label="Cerrar">&times;</button>
            </div>
            <form id="userForm">
                <div class="modal-body-buzz">
                    <!-- Basic Information Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-info-circle"></i> INFORMACIÓN BÁSICA
                        </div>
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
                    </div>

                    <!-- Security Section -->
                    <div class="modal-section" id="userPasswordGroup">
                        <div class="modal-section-title">
                            <i class="fas fa-lock"></i> SEGURIDAD
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Contraseña *</label>
                                <input type="password" class="form-control form-control-buzz" id="userPassword" required autocomplete="new-password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Confirmar Contraseña *</label>
                                <input type="password" class="form-control form-control-buzz" id="userPasswordConfirm" required autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-user-shield"></i> PERMISOS Y ESTADO
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Rol</label>
                                <select class="form-control form-control-buzz" id="userRole">
                                    <option value="user">Usuario</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <select class="form-control form-control-buzz" id="userStatus">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="userFormAlert" class="alert-buzz" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="userFormAlertText"></span>
                    </div>
                </div>
                <div class="modal-footer-buzz">
                    <button type="button" class="btn btn-outline-buzz" onclick="closeUserModal()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-save"></i> Guardar Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/common.js?v=4"></script>
    <script src="assets/js/admin.js?v=7"></script>
</body>
</html>
