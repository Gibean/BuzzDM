<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Importación</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/dashboard-effects.css">
</head>
<body>
     <!-- Sidebar (same structure as other pages)  -->
    <?php require_once 'php/sidebar.php'; ?>

    <!--  Main Content  -->
    <main class="main-content">
         <!-- Top Navbar  -->
        <!-- Top Navbar -->
        <?php
        $nav_title = 'IMPORTACIÓN DE DATOS';
        $nav_subtitle = 'SINCRONIZACIÓN';
        require_once 'php/top_navbar.php';
        ?>

        <!--  Upload Section  -->
        <div class="buzz-card mb-4">
            <h5 class="font-primary text-primary mb-3">SUBIR ARCHIVO</h5>
            <form id="importForm" action="php/api_import.php" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="upload-area border border-dashed border-secondary rounded p-4 text-center" id="uploadArea">
                            <i class="fas fa-cloud-upload-alt text-primary" style="font-size: 3rem;"></i>
                            <h6 class="mt-3 font-mono">Arrastra y suelta tu archivo aquí</h6>
                            <p class="text-muted">o haz clic para seleccionar</p>
                            <input type="file" id="fileInput" name="import_file" accept=".csv,.xlsx,.xls" style="display: none;">
                            <button type="button" class="btn btn-outline-buzz" id="selectFileBtn">
                                <i class="fas fa-folder-open"></i> Seleccionar Archivo
                            </button>
                            <div class="mt-3">
                                <small class="text-muted">Formatos soportados: CSV, Excel (.xlsx, .xls)</small>
                            </div>
                        </div>
                        <div id="fileInfo" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <i class="fas fa-file"></i>
                                <span id="fileName"></span>
                                <span class="float-end">
                                    <span id="fileSize"></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearFile()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="font-mono text-muted mb-3">OPCIONES DE IMPORTACIÓN</h6>

                        <div class="mb-4">
                            <label class="form-label small text-muted">Descargar Plantillas:</label>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <button type="button" onclick="downloadTemplate('productos')" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="fas fa-box"></i> Productos
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" onclick="downloadTemplate('clientes')" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="fas fa-users"></i> Clientes
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" onclick="downloadTemplate('proveedores')" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="fas fa-truck"></i> Proveedores
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" onclick="downloadTemplate('catalogos')" class="btn btn-sm btn-outline-secondary w-100">
                                        <i class="fas fa-tags"></i> Catálogos
                                    </button>
                                </div>
                            </div>
                            <hr class="border-secondary opacity-10 my-2">
                            <button type="button" onclick="downloadTemplate('referencias')" class="btn btn-sm btn-outline-warning w-100" title="Descarga un Excel con todos los IDs de referencia del sistema">
                                <i class="fas fa-book"></i> Hoja de Referencias (IDs)
                            </button>
                        </div>

                        <hr class="border-secondary opacity-10 my-3">

                        <button type="submit" class="btn btn-buzz w-100" id="importBtn" disabled>
                            <i class="fas fa-upload"></i> Iniciar Importación
                        </button>
                    </div>
                </div>
            </form>
        </div>

         <!-- Import Progress  -->
        <div class="buzz-card mb-4" id="importProgress" style="display: none;">
            <h5 class="font-primary text-primary mb-3">PROGRESO DE IMPORTACIÓN</h5>
            <div class="progress mb-3" style="height: 20px;">
                <div class="progress-bar bg-primary" id="progressBar" style="width: 0%"></div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="font-mono text-primary" id="processedCount">0</div>
                        <small class="text-muted">Procesados</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="font-mono text-success" id="successCount">0</div>
                        <small class="text-muted">Exitosos</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="font-mono text-danger" id="errorCount">0</div>
                        <small class="text-muted">Errores</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="font-mono" id="totalCount">0</div>
                        <small class="text-muted">Total</small>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <div class="font-mono text-muted" id="currentStatus">Preparando importación...</div>
            </div>
        </div>

       <!--   Import History  -->
        <div class="buzz-card">
            <h5 class="font-primary text-primary mb-3">HISTORIAL DE IMPORTACIONES</h5>
            <div class="table-responsive">
                <table class="table table-buzz">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Archivo</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>
                    <tbody id="importHistoryTable">
                         <!-- History will be populated by JavaScript  -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System Footer -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/importacion.js"></script>

    <!-- Notification Container -->
    <div id="notificationContainer"></div>

    <script>
        // Logic moved to assets/js/importacion.js
    </script>

    <!-- Error Modal Structure -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Detalles de Errores de Importación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Errors will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
