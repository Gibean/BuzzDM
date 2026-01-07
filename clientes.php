<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Clientes</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/ui-enhancements.css">
    <link rel="stylesheet" href="assets/css/filter-layout.css">
</head>
<body>
    <!-- Sidebar -->
    <?php require_once 'php/sidebar.php'; ?>
    <main class="main-content">
         <!-- Top Navbar  -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">GESTIÓN DE CLIENTES</h1>
                <span class="text-muted ms-2 font-mono">/ CLIENTES</span>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn btn-buzz" onclick="openModal('createCustomerModal')">
                    <i class="fas fa-plus"></i> Nuevo Cliente
                </button>
            </div>
        </nav>

        <!-- Frequent Customers Section (Full Width) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="buzz-card frequent-customers-banner">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="font-primary text-primary mb-0">
                            <i class="fas fa-crown me-2"></i>Clientes Frecuentes
                        </h5>
                        <small class="text-muted"><span id="totalCustomersNum">0</span> clientes totales</small>
                    </div>
                    <div id="frequentCustomersList" class="frequent-customers-grid">
                        <!-- Frequent customers will be populated by JavaScript -->
                        <div class="text-center py-3 w-100">
                            <i class="fas fa-spinner fa-spin text-muted me-2"></i>
                            <span class="text-muted">Cargando clientes frecuentes...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--  Filters  -->
        <div class="buzz-card mb-4">
            <div class="filter-wrapper">
                <div class="filter-group filter-search">
                    <label class="text-muted">Buscar cliente</label>
                    <input type="text" class="form-control form-control-buzz" id="customerSearchInput" placeholder="ID, nombre, email...">
                </div>
                <div class="filter-group filter-select">
                    <label class="text-muted">Tipo de Cliente</label>
                    <select class="form-control form-control-buzz" id="customerTypeFilter">
                        <option value="">Todos</option>
                        <option value="vip">VIP</option>
                        <option value="regular">Regular</option>
                    </select>
                </div>
                <div class="filter-group filter-checkbox">
                    <input class="form-check-input" type="checkbox" id="showDeletedFilter">
                    <label class="form-check-label text-muted" for="showDeletedFilter">
                        Ver eliminados
                    </label>
                </div>
                <div class="filter-group filter-actions">
                    <button class="btn btn-buzz btn-filter-action" id="applyCustomerFilters" title="Buscar">
                        <i class="fas fa-search"></i><span class="btn-text">Buscar</span>
                    </button>
                    <button class="btn btn-outline-buzz btn-filter-action" onclick="clearFilters()" title="Limpiar filtros">
                        <i class="fas fa-times"></i><span class="btn-text">Limpiar</span>
                    </button>
                </div>
            </div>
        </div>

        <!--  Customers List  -->
        <div class="buzz-card">
            <div id="customersList">
                 Customers will be populated by JavaScript
            </div>
        </div>
    </main>

    <!-- Create Customer Modal -->
    <div class="modal-buzz" id="createCustomerModal">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary">
                    <i class="fas fa-user-plus"></i> Nuevo Cliente
                </h5>
                <button class="close-buzz" onclick="closeModal('createCustomerModal')" aria-label="Cerrar">&times;</button>
            </div>
            <form id="createCustomerForm">
                <div class="modal-body-buzz">
                    <!-- Basic Information Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-info-circle"></i> INFORMACIÓN BÁSICA
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">ID Cliente *</label>
                                <div class="input-group">
                                    <select class="form-select form-control-buzz" id="createCustomerIdType" style="max-width: 80px;" required>
                                        <option value="V">V</option>
                                        <option value="E">E</option>
                                        <option value="J">J</option>
                                        <option value="G">G</option>
                                        <option value="P">P</option>
                                    </select>
                                    <input type="text" class="form-control form-control-buzz" id="createCustomerId" maxlength="20" pattern="[0-9]*" inputmode="numeric" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Nombre *</label>
                                <input type="text" class="form-control form-control-buzz" id="createCustomerName" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <input type="email" class="form-control form-control-buzz" id="createCustomerEmail">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="createCustomerVip">
                            <label class="form-check-label text-muted" for="createCustomerVip">
                                <i class="fas fa-star text-warning"></i> Cliente VIP
                            </label>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-phone"></i> CONTACTO
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted">Código País</label>
                                <select class="form-select form-control-buzz" id="createCustomerPhoneCountryCode" required>
                                    <option value="+58">Venezuela (+58)</option>
                                    <option value="+1">USA/Canada (+1)</option>
                                    <option value="+57">Colombia (+57)</option>
                                    <option value="+54">Argentina (+54)</option>
                                    <option value="+34">España (+34)</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-muted">Teléfono *</label>
                                <input type="text" class="form-control form-control-buzz" id="createCustomerPhone" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Dirección</label>
                            <textarea class="form-control form-control-buzz" id="createCustomerAddress" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Notas</label>
                            <textarea class="form-control form-control-buzz" id="createCustomerNotes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-buzz">
                    <button type="button" class="btn btn-outline-buzz" onclick="closeModal('createCustomerModal')">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-save"></i> Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Customer Modal -->
    <div class="modal-buzz" id="viewCustomerModal">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary">
                    <i class="fas fa-eye"></i> Detalle del Cliente #<span id="viewCustomerId"></span>
                </h5>
                <button class="close-buzz" onclick="closeModal('viewCustomerModal')" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body-buzz">
                <div class="modal-section">
                    <div class="modal-section-title">
                        <i class="fas fa-info-circle"></i> INFORMACIÓN DEL CLIENTE
                    </div>
                    <p><strong>Nombre:</strong> <span id="viewCustomerName"></span></p>
                    <p><strong>Email:</strong> <span id="viewCustomerEmail"></span></p>
                    <p><strong>Teléfono:</strong> <span id="viewCustomerPhone"></span></p>
                    <p><strong>Dirección:</strong> <span id="viewCustomerAddress"></span></p>
                    <p><strong>Notas:</strong> <span id="viewCustomerNotes"></span></p>
                    <p><strong>VIP:</strong> <span id="viewCustomerVip"></span></p>
                    <p><strong>Creado en:</strong> <span id="viewCustomerCreatedAt"></span></p>
                </div>
            </div>
            <div class="modal-footer-buzz">
                <button type="button" class="btn btn-outline-buzz" onclick="closeModal('viewCustomerModal')">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal-buzz" id="editCustomerModal">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary">
                    <i class="fas fa-user-edit"></i> Editar Cliente #<span id="editCustomerId"></span>
                </h5>
                <button class="close-buzz" onclick="closeModal('editCustomerModal')" aria-label="Cerrar">&times;</button>
            </div>
            <form id="editCustomerForm">
                <input type="hidden" id="editCustomerIdHidden">
                <div class="modal-body-buzz">
                    <!-- Basic Information Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-info-circle"></i> INFORMACIÓN BÁSICA
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Nombre *</label>
                            <input type="text" class="form-control form-control-buzz" id="editCustomerName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <input type="email" class="form-control form-control-buzz" id="editCustomerEmail">
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="editCustomerVip">
                            <label class="form-check-label text-muted" for="editCustomerVip">
                                <i class="fas fa-star text-warning"></i> Cliente VIP
                            </label>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-phone"></i> CONTACTO
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Teléfono *</label>
                            <input type="text" class="form-control form-control-buzz" id="editCustomerPhone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Dirección</label>
                            <textarea class="form-control form-control-buzz" id="editCustomerAddress" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Notas</label>
                            <textarea class="form-control form-control-buzz" id="editCustomerNotes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-buzz">
                    <button type="button" class="btn btn-outline-buzz" onclick="closeModal('editCustomerModal')">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/common.js?v=2"></script>
    <script src="assets/js/confirmation_modal.js?v=2"></script>
    <script src="assets/js/clientes.js?v=2"></script>
</body>
</html>


