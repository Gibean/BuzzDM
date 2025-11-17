<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Acerca de</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
     Sidebar 
    <?php require_once 'php/sidebar.php'; ?>

     Main Content 
    <main class="main-content">
         <!-- Top Navbar --> 
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">ACERCA DEL SISTEMA</h1>
                <span class="text-muted ms-2 font-mono">/ INFORMACIÓN</span>
            </div>
        </nav>

         <!-- System Info  -->
        <div class="row">
            <div class="col-lg-8">
                <div class="buzz-card mb-4">
                    <div class="text-center mb-4">
                        <div class="d-flex align-items-center justify-content-center">
            <img src="assets/img/logov1.png" alt="BUZZ Logo" class="sidebar-logo">
        </div>
                        <p class="text-muted font-mono">Sistema de Gestión de Pedidos e Inventario</p>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="border border-secondary rounded p-3">
                                <h5 class="font-primary text-primary">VERSIÓN</h5>
                                <div class="font-mono">v1.2.3.2</div>
                                <small class="text-muted">Build 2025.08.13</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border border-secondary rounded p-3">
                                <h5 class="font-primary text-primary">ESTADO</h5>
                                <div class="text-success font-mono">OPERATIVO</div>
                                <small class="text-muted">Todos los sistemas funcionando</small>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border border-secondary rounded p-3">
                                <h5 class="font-primary text-primary">UPTIME</h5>
                                <div class="font-mono">99.9%</div>
                                <small class="text-muted">Últimos 30 días</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="buzz-card mb-4">
                    <h5 class="font-primary text-primary mb-3">CARACTERÍSTICAS PRINCIPALES</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Gestión inventario</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Control de pedidos</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Seguimiento de envíos</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Importación masiva de datos</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Recuperación de contraseñas por correo</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Encriptación de contraseñas</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Respaldo de datos interno</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Dashboard analítico</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Gestión de usuarios</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Interfaz responsive</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span class="font-mono">Visualización de movimientos administrativos</span>
                                </li>
                                
                                <li class="mb-2">
                                    <i class="fa-solid fa-spinner"></i>
                                    <span class="font-mono">2FA Autentication</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-spinner"></i>
                                    <span class="font-mono">Respaldos automáticos</span>
                                </li>
                                <li class="mb-2">
                                    <i class="fa-solid fa-spinner"></i>
                                    <span class="font-mono">Múltiples sucursales</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="buzz-card">
                    <h5 class="font-primary text-primary mb-3">TECNOLOGÍAS UTILIZADAS</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="font-mono text-muted">Frontend</h6>
                            <ul class="list-unstyled">
                                <li class="font-mono">• HTML5</li>
                                <li class="font-mono">• CSS3 (Custom)</li>
                                <li class="font-mono">• Bootstrap 5.3.0</li>
                                <li class="font-mono">• JavaScript (Vanilla JS)</li>
                                <li class="font-mono">• Chart.js (v4.5.0)</li>
                                <li class="font-mono">• Google Fonts</li>
                                <li class="font-mono">• Font Awesome</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="font-mono text-muted">Backend</h6>
                            <ul class="list-unstyled">
                                <li class="font-mono">• PHP</li>
                                <li class="font-mono">• MariaDB/MySQL</li>
                                <li class="font-mono">• Composer</li>
                                <li class="font-mono">• Monolog</li>
                                <li class="font-mono">• PHPSpreadsheet</li>
                                <li class="font-mono">• Dompdf</li>
                                <li class="font-mono">• Dompdf</li>
                                <li class="font-mono">• PHPmailer</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="buzz-card mb-4">
                    <h5 class="font-primary text-primary mb-3">INFORMACIÓN DEL SISTEMA</h5>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Servidor:</span>
                            <span class="font-mono"><?php echo php_uname('n'); ?></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Base de datos:</span>
                            <span class="font-mono">MariaDB 11.8.2 (as per dbuzz.sql)</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">PHP:</span>
                            <span class="font-mono"><?php echo phpversion(); ?></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Memoria (PHP Peak):</span>
                            <span class="font-mono"><?php echo round(memory_get_peak_usage() / (1024 * 1024), 2) . ' MB'; ?></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Espacio (Libre/Total):</span>
                            <span class="font-mono"><?php echo round(disk_free_space('/') / (1024 * 1024 * 1024), 2) . ' GB / ' . round(disk_total_space('/') / (1024 * 1024 * 1024), 2) . ' GB'; ?></span>
                        </div>
                    </div>
                </div>

                <div class="buzz-card mb-4">
                    <h5 class="font-primary text-primary mb-3">SOPORTE</h5>
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <span class="font-mono">gisvelaray@gmail.com</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <span class="font-mono">+58 412 875 9068</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-book text-primary me-2"></i>
                            <a href="#" class="font-mono text-primary" style="text-decoration: none;">Manual de Usuario</a>
                        </div>
                    </div>
                </div>

                <div class="buzz-card">
                    <h5 class="font-primary text-primary mb-3">LICENCIA</h5>
                    <p class="text-muted font-mono" style="font-size: 0.9rem;">
                        © 2025 <b>Proyecto Socio-Tecnológico BUZZ Data Manager.</b> Este software es para fines educativos y está protegido por derechos de autor. No se permite la distribución sin autorización.
                        <br><br> Todos los derechos reservados a Gisvel Aray V-30535128, Estudiante del PNFI T4F1 desarrolladora full stack del proyecto bajo el seudónimo de "HareBit"
                    
                    </p>
                    <img src="assets/img/harebit.png" alt="HareBit Development Logo" style="width: 100%; height: auto; display: block; margin-top: 1rem;">
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
</body>
</html>
