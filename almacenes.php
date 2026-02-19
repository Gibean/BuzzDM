<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Almacenes</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/almacenes.css">
</head>
<body>
    <!-- Sidebar -->
    <?php require_once 'php/sidebar.php'; ?>
    <main class="main-content">
        <!-- Top Navbar -->
        <!-- Top Navbar -->
        <?php
        $nav_title = 'GESTIÓN DE ALMACENES';
        $nav_subtitle = 'UBICACIONES';
        $nav_actions = '<button class="btn btn-buzz" id="btnNewAlmacen" onclick="openAlmacenModal()"><i class="fas fa-plus"></i> Nuevo Almacén</button>';
        require_once 'php/top_navbar.php';
        ?>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="buzz-card stat-card">
                    <div class="stat-icon"><i class="fas fa-warehouse"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="totalAlmacenes">0</span>
                        <span class="stat-label">Almacenes Activos</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="buzz-card stat-card">
                    <div class="stat-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="totalUbicaciones">0</span>
                        <span class="stat-label">Ubicaciones</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="buzz-card stat-card">
                    <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="totalProductos">0</span>
                        <span class="stat-label">Productos Distribuidos</span>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="buzz-card stat-card">
                    <div class="stat-icon"><i class="fas fa-star"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="almacenPrincipal">-</span>
                        <span class="stat-label">Almacén Principal</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Almacenes Grid -->
        <div class="buzz-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-accent"><i class="fas fa-warehouse me-2"></i>Lista de Almacenes</h5>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="showInactivos">
                    <label class="form-check-label text-muted" for="showInactivos">
                        Ver inactivos
                    </label>
                </div>
            </div>

            <div class="almacenes-grid" id="almacenesGrid">
                <!-- Almacenes se cargan dinámicamente -->
            </div>
        </div>

        <!-- Ubicaciones Section (shown when almacen is selected) -->
        <div class="buzz-card mt-4" id="ubicacionesSection" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0 text-accent">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    Ubicaciones de <span id="selectedAlmacenName">-</span>
                </h5>
                <button class="btn btn-sm btn-buzz" onclick="openUbicacionModal()">
                    <i class="fas fa-plus"></i> Nueva Ubicación
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-buzz">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Pasillo</th>
                            <th>Estante</th>
                            <th>Nivel</th>
                            <th>Capacidad</th>
                            <th>Productos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ubicacionesTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Almacén Modal -->
    <div id="almacenModal" class="modal-buzz">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary" id="almacenModalTitle">
                    <i class="fas fa-warehouse"></i> Nuevo Almacén
                </h5>
                <button type="button" class="close-buzz" onclick="closeAlmacenModal()">&times;</button>
            </div>
            <form id="almacenForm" onsubmit="event.preventDefault(); saveAlmacen();">
                <input type="hidden" id="almacenId">
                <div class="modal-body-buzz">
                    <!-- Información Básica -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-info-circle"></i> INFORMACIÓN BÁSICA
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Código <small class="text-muted">(auto)</small></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    <input type="text" class="form-control form-control-buzz form-control-autogen" id="almacenCodigo"
                                           placeholder="Se genera automáticamente" maxlength="20" readonly>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Nombre *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="almacenNombre"
                                           placeholder="Nombre del almacén" required>
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <select class="form-control form-control-buzz" id="almacenTipo">
                                        <option value="almacen">Almacén</option>
                                        <option value="bodega">Bodega</option>
                                        <option value="tienda">Tienda</option>
                                        <option value="deposito">Depósito</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="almacenTelefono"
                                           placeholder="+1 234 567 8900">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <input type="text" class="form-control form-control-buzz" id="almacenDireccion"
                                       placeholder="Calle, número, ciudad...">
                            </div>
                        </div>
                    </div>

                    <!-- Configuración -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-cog"></i> CONFIGURACIÓN
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="almacenPrincipalCheck">
                                    <label class="form-check-label" for="almacenPrincipalCheck">
                                        <i class="fas fa-star text-warning me-1"></i> Almacén Principal
                                    </label>
                                    <small class="text-muted d-block">Se usará como destino por defecto</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="almacenActivo" checked>
                                    <label class="form-check-label" for="almacenActivo">
                                        <i class="fas fa-check-circle text-success me-1"></i> Activo
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <div class="input-group">
                                <span class="input-group-text align-items-start pt-2"><i class="fas fa-sticky-note"></i></span>
                                <textarea class="form-control form-control-buzz" id="almacenNotas" rows="2"
                                          placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-buzz">
                    <button type="button" class="btn btn-outline-buzz" onclick="closeAlmacenModal()">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ubicación Modal -->
    <div id="ubicacionModal" class="modal-buzz">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary" id="ubicacionModalTitle">
                    <i class="fas fa-map-marker-alt"></i> Nueva Ubicación
                </h5>
                <button type="button" class="close-buzz" onclick="closeUbicacionModal()">&times;</button>
            </div>
            <form id="ubicacionForm" onsubmit="event.preventDefault(); saveUbicacion();">
                <input type="hidden" id="ubicacionId">
                <input type="hidden" id="ubicacionAlmacenId">
                <div class="modal-body-buzz">
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-map-pin"></i> DATOS DE UBICACIÓN
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Código *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="ubicacionCodigo"
                                           placeholder="A-01-E1-N1" required>
                                </div>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Nombre</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="ubicacionNombre"
                                           placeholder="Descripción de la ubicación">
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pasillo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-road"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="ubicacionPasillo"
                                           placeholder="A">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Estante</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-th-large"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="ubicacionEstante"
                                           placeholder="01">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nivel</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="ubicacionNivel"
                                           placeholder="N1">
                                </div>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Capacidad Máxima (unidades)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-cubes"></i></span>
                                    <input type="number" class="form-control form-control-buzz" id="ubicacionCapacidad"
                                           placeholder="0" min="0">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="ubicacionActivo" checked>
                                    <label class="form-check-label" for="ubicacionActivo">
                                        <i class="fas fa-check-circle text-success me-1"></i> Activa
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-buzz">
                    <button type="button" class="btn btn-outline-buzz" onclick="closeUbicacionModal()">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="toast-container"></div>

    <!-- System Footer (Includes Tutorial Scripts) -->
    <?php require_once 'php/system_footer.php'; ?>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/almacenes.js"></script>
</body>
</html>
