<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Pedidos</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/filter-layout.css">
</head>
<body>
     <!-- Sidebar  -->
    <?php require_once 'php/sidebar.php'; ?>

     <!-- Main Content  -->
    <main class="main-content">
         <!-- Top Navbar  -->
        <!-- Top Navbar -->
        <?php
        $nav_title = 'PEDIDOS';
        $nav_subtitle = 'GESTIÓN';
        $nav_actions = '<button class="btn btn-buzz" id="newOrderBtn"><i class="fas fa-plus"></i> Nuevo Pedido</button>';
        require_once 'php/top_navbar.php';
        ?>


            <!-- Filtros -->
            <div class="buzz-card mb-4 pb-3">
                <div class="filter-wrapper">
                    <!-- Basic Filters (Always Visible) -->
                    <div class="filter-group filter-search">
                        <label class="text-muted">Buscar</label>
                        <input type="text" class="form-control form-control-buzz" id="searchInput" placeholder="ID, cliente...">
                    </div>
                    <div class="filter-group filter-select">
                        <label class="text-muted">Estado de pago</label>
                        <select class="form-control form-control-buzz" id="paymentFilter">
                            <option value="">Todos</option>
                            <option value="pagado">Pagado</option>
                            <option value="no-pagado">No Pagado</option>
                        </select>
                    </div>

                    <div class="filter-group filter-actions">
                        <button class="btn btn-outline-buzz btn-filter-action" id="toggleAdvancedFilters" title="Más filtros">
                            <i class="fas fa-filter"></i>
                            <span class="btn-text">Filtros Avanzados</span>
                            <span class="badge bg-primary ms-1 d-none" id="activeFiltersBadge">0</span>
                        </button>
                        <button class="btn btn-outline-buzz btn-filter-action" onclick="clearFilters()" title="Limpiar">
                            <i class="fas fa-times"></i><span class="btn-text">Limpiar</span>
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div id="advancedFiltersCollapse" class="advanced-filters-section mt-3 d-none">
                    <hr class="border-secondary opacity-25">
                    <div class="filter-wrapper">
                        <div class="filter-group filter-date">
                            <label class="text-muted">Desde</label>
                            <input type="date" class="form-control form-control-buzz" id="dateFrom">
                        </div>
                        <div class="filter-group filter-date">
                            <label class="text-muted">Hasta</label>
                            <input type="date" class="form-control form-control-buzz" id="dateTo">
                        </div>
                        <div class="filter-group filter-select">
                            <label class="text-muted">Estado de envío</label>
                            <select class="form-control form-control-buzz" id="shippingFilter">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="enviado">Enviado</option>
                                <option value="recibido">Recibido</option>
                            </select>
                        </div>
                        <div class="filter-group filter-check d-flex align-items-center mb-0 mt-3 mt-md-0">
                            <div class="form-check form-switch custom-switch-buzz">
                                <input class="form-check-input" type="checkbox" id="showDeletedFilter">
                                <label class="form-check-label text-muted" for="showDeletedFilter">Ver eliminados</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!--  Orders List  -->
        <div class="buzz-card">
            <div id="ordersList">
                 Orders will be populated by JavaScript
            </div>
        </div>

        <!-- System Footer -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <!-- Create Order Modal -->
    <div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content buzz-modal-dark-override">
                <div class="modal-header">
                    <h5 class="modal-title font-primary text-primary" id="createOrderModalLabel">Nuevo Pedido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createOrderForm">
                    <div class="modal-body position-relative">
                        <div class="row">
                            <div class="col-md-7 mb-3">
                                <label class="form-label text-muted">Buscar Cliente *</label>
                                <input type="text" class="form-control form-control-buzz" id="createOrderClientSearch" placeholder="Nombre o Cédula" required>
                                <input type="hidden" id="createOrderSelectedClientId">
                                <div id="createOrderClientSearchResults" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                <div id="createOrderSelectedClientDisplay" class="mt-2 text-success"></div>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label class="form-label text-muted">Fecha del Pedido</label>
                                <input type="datetime-local" class="form-control form-control-buzz" id="createOrderDate" value="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Dirección de envío *</label>
                            <textarea class="form-control form-control-buzz" id="createOrderShippingAddress" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Notas de envío</label>
                            <textarea class="form-control form-control-buzz" id="createOrderShippingNotes" rows="2"></textarea>
                        </div>

                        <!-- Products Section -->
                        <div class="mb-3">
                            <label class="form-label text-muted">Productos</label>
                            <div id="createOrderProductsContainer" style="max-height: 200px; overflow-y: auto;">
                                <!-- Product rows will be added here by JavaScript -->
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-buzz btn-sm" id="addCreateOrderProductRow">
                                    <i class="fas fa-plus"></i> Añadir Producto
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="totals-box py-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="view-label mb-0">Total USD</span>
                                        <span class="total-value usd" id="createOrderCalculatedTotal">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="view-label mb-0">Total VES</span>
                                        <span class="total-value ves" id="createOrderCalculatedTotalVes" style="font-size: 1rem;">Bs. 0,00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Estado de pago</label>
                                <select class="form-control form-control-buzz" id="createOrderPaymentStatus">
                                    <option value="no_pagado">No Pagado</option>
                                    <option value="pagado">Pagado</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Estado de envío</label>
                                <select class="form-control form-control-buzz" id="createOrderShippingStatus">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="enviado">Enviado</option>
                                    <option value="recibido">Recibido</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-buzz" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-buzz">
                            <i class="fas fa-save"></i> Guardar Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div class="modal-buzz" id="orderDetailModal">
        <div class="modal-content-buzz modal-lg">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary">
                    <i class="fas fa-file-invoice"></i> Pedido <span id="detailOrderReference" class="font-mono"></span>
                </h5>
                <button class="close-buzz" onclick="closeModal('orderDetailModal')" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body-buzz">
                <div class="row">
                    <!-- Left Column: Order Info -->
                    <div class="col-md-6">
                        <!-- Customer Info Section -->
                        <div class="modal-section">
                            <div class="modal-section-title">
                                <i class="fas fa-user"></i> CLIENTE
                            </div>
                            <div class="view-field">
                                <span class="view-label">Nombre</span>
                                <span class="view-value" id="detailOrderCustomer">-</span>
                            </div>
                            <div class="view-field">
                                <span class="view-label"><i class="fas fa-envelope me-1"></i> Email</span>
                                <span class="view-value" id="detailOrderEmail">-</span>
                            </div>
                        </div>

                        <!-- Order Status Section -->
                        <div class="modal-section">
                            <div class="modal-section-title">
                                <i class="fas fa-clock"></i> ESTADO DEL PEDIDO
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="view-field">
                                        <span class="view-label">Fecha</span>
                                        <span class="view-value" id="detailOrderDate">-</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="view-field">
                                        <span class="view-label">ID Pedido</span>
                                        <span class="view-value font-mono" id="detailOrderId">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 mt-1">
                                <div class="col-6">
                                    <div class="view-field">
                                        <span class="view-label">Estado Pago</span>
                                        <span class="view-value" id="detailOrderPaymentStatus">-</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="view-field">
                                        <span class="view-label">Estado Envío</span>
                                        <span class="view-value" id="detailOrderShippingStatus">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Section -->
                        <div class="modal-section">
                            <div class="modal-section-title">
                                <i class="fas fa-truck"></i> ENVÍO
                            </div>
                            <div class="view-field">
                                <span class="view-label">Dirección</span>
                                <span class="view-value" id="detailOrderShippingAddress">-</span>
                            </div>
                            <div class="view-field">
                                <span class="view-label">Notas</span>
                                <span class="view-value text-muted fst-italic" id="detailOrderShippingNotes">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Products & Totals -->
                    <div class="col-md-6">
                        <!-- Products Section -->
                        <div class="modal-section">
                            <div class="modal-section-title">
                                <i class="fas fa-boxes"></i> PRODUCTOS
                            </div>
                            <div id="detailOrderProducts" style="max-height: 200px; overflow-y: auto;">
                                <!-- Products table will be loaded here -->
                            </div>
                        </div>

                        <!-- Totals Section -->
                        <div class="modal-section">
                            <div class="modal-section-title">
                                <i class="fas fa-calculator"></i> TOTALES
                            </div>
                            <div class="totals-box">
                                <div class="total-row">
                                    <span class="total-label">Total USD</span>
                                    <span class="total-value usd" id="detailOrderTotalUsd">$0.00</span>
                                </div>
                                <div class="total-row">
                                    <span class="total-label">Total Bolívares</span>
                                    <span class="total-value ves" id="detailOrderTotalVes">Bs. 0,00</span>
                                </div>
                                <div class="rate-info" id="detailOrderRateInfo">
                                    Tasa BCV: Cargando...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer-buzz">
                <button type="button" class="btn btn-outline-buzz" onclick="closeModal('orderDetailModal')">
                    <i class="fas fa-times me-2"></i>Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Order Modal -->
    <div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-primary text-primary" id="editOrderModalLabel">Editar Pedido #<span id="editOrderId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOrderForm">
                    <div class="modal-body">
                        <input type="hidden" id="editOrderIdHidden">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">ID Cliente *</label>
                                <input type="text" class="form-control form-control-buzz" id="editOrderCustomerId" required readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Referencia</label>
                                <input type="text" class="form-control form-control-buzz" id="editOrderReference" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Fecha</label>
                                <input type="datetime-local" class="form-control form-control-buzz" id="editOrderDate">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Dirección de envío *</label>
                            <textarea class="form-control form-control-buzz" id="editOrderShippingAddress" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Notas de envío</label>
                            <textarea class="form-control form-control-buzz" id="editOrderShippingNotes" rows="2"></textarea>
                        </div>

                        <!-- Products Section -->
                        <div class="mb-3">
                            <label class="form-label text-muted">Productos</label>
                            <div id="editOrderProductsContainer" style="max-height: 200px; overflow-y: auto;">
                                <!-- Products will be dynamically loaded here -->
                            </div>
                        </div>

                        <div class="row align-items-center mb-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-outline-buzz btn-sm" id="addEditOrderProductRow">
                                    <i class="fas fa-plus"></i> Añadir Producto
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="totals-box py-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="view-label mb-0">Total USD</span>
                                        <span class="total-value usd" id="editOrderCalculatedTotal">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="view-label mb-0">Total VES</span>
                                        <span class="total-value ves" id="editOrderCalculatedTotalVes" style="font-size: 1rem;">Bs. 0,00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Estado de pago</label>
                                <select class="form-control form-control-buzz" id="editOrderPaymentStatus">
                                    <option value="no_pagado">No Pagado</option>
                                    <option value="pagado">Pagado</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Estado de envío</label>
                                <select class="form-control form-control-buzz" id="editOrderShippingStatus">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="enviado">Enviado</option>
                                    <option value="recibido">Recibido</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-buzz" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-buzz">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/confirmation_modal.js"></script>
    <script src="assets/js/pedidos.js"></script>
</body>
</html>
