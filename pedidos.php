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
                <h1 class="font-primary mb-0">PEDIDOS</h1>
                <span class="text-muted ms-2 font-mono">/ GESTIÓN</span>
            </div>
            <div>
                <button class="btn btn-buzz" id="newOrderBtn">
                    <i class="fas fa-plus"></i> Nuevo Pedido
                </button>
            </div>
        </nav>

        <!--  Stats Cards  -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <span class="stats-number" id="totalOrders">0</span>
                    <span class="stats-label">Total Pedidos</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <span class="stats-number" id="pendingOrders">0</span>
                    <span class="stats-label">Pendientes</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <span class="stats-number" id="paidOrders">0</span>
                    <span class="stats-label">Pagados</span>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stats-card">
                    <span class="stats-number" id="totalRevenue">€0</span>
                    <span class="stats-label">Ingresos Totales</span>
                </div>
            </div>
        </div>

        <!--  Filters  -->
        <div class="buzz-card mb-4">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">Buscar pedido</label>
                    <input type="text" class="form-control form-control-buzz" id="searchInput" placeholder="ID, cliente...">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">Estado de pago</label>
                    <select class="form-control form-control-buzz" id="paymentFilter">
                        <option value="">Todos</option>
                        <option value="pagado">Pagado</option>
                        <option value="no-pagado">No Pagado</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">Fecha desde</label>
                    <input type="date" class="form-control form-control-buzz" id="dateFrom">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">Fecha hasta</label>
                    <input type="date" class="form-control form-control-buzz" id="dateTo">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label text-muted">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="showDeletedFilter">
                        <label class="form-check-label text-muted" for="showDeletedFilter">
                            Ver eliminados
                        </label>
                    </div>
                </div>
                <div class="col-md-1 mb-3">
                    <label class="form-label text-muted">&nbsp;</label>
                    <button class="btn btn-outline-buzz w-100" onclick="clearFilters()" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!--  Orders List  -->
        <div class="buzz-card">
            <div id="ordersList">
                 Orders will be populated by JavaScript
            </div>
        </div>
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
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-muted">Buscar Cliente *</label>
                                <input type="text" class="form-control form-control-buzz" id="createOrderClientSearch" placeholder="Nombre o Cédula" required>
                                <input type="hidden" id="createOrderSelectedClientId">
                                <div id="createOrderClientSearchResults" class="list-group position-absolute w-100" style="z-index: 1000;"></div>
                                <div id="createOrderSelectedClientDisplay" class="mt-2 text-success"></div>
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
                            <div class="col-md-6 text-end">
                                <div class="buzz-card p-3">
                                    <h5 class="font-primary text-primary mb-0">Total: <span id="createOrderCalculatedTotal">€0.00</span></h5>
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
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-labelledby="orderDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-primary text-primary" id="orderDetailModalLabel">Detalle del Pedido #<span id="detailOrderId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Referencia:</strong> <span id="detailOrderReference"></span></p>
                    <p><strong>Cliente:</strong> <span id="detailOrderCustomer"></span> (<span id="detailOrderEmail"></span>)</p>
                    <p><strong>Fecha Creación:</strong> <span id="detailOrderDate"></span></p>
                    <p><strong>Total:</strong> <span id="detailOrderTotal"></span></p>
                    <p><strong>Estado Pago:</strong> <span id="detailOrderPaymentStatus"></span></p>
                    <p><strong>Estado Envío:</strong> <span id="detailOrderShippingStatus"></span></p>
                    <p><strong>Dirección Envío:</strong> <span id="detailOrderShippingAddress"></span></p>
                    <p><strong>Notas Envío:</strong> <span id="detailOrderShippingNotes"></span></p>
                    <div id="detailOrderProducts">
                        <!-- Products will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-buzz" data-bs-dismiss="modal">Cerrar</button>
                </div>
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
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">ID Cliente *</label>
                                <input type="text" class="form-control form-control-buzz" id="editOrderCustomerId" required readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Referencia</label>
                                <input type="text" class="form-control form-control-buzz" id="editOrderReference" readonly>
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
                            <div class="col-md-6 text-end">
                                <div class="buzz-card p-3">
                                    <h5 class="font-primary text-primary mb-0">Total: <span id="editOrderCalculatedTotal">€0.00</span></h5>
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
    <script src="assets/js/common.js"></script>
    <script src="assets/js/confirmation_modal.js"></script>
    <script src="assets/js/pedidos.js"></script>
</body>
</html>
