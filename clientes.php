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
                <button class="btn btn-buzz" data-bs-toggle="modal" data-bs-target="#createCustomerModal">
                    <i class="fas fa-plus"></i> Nuevo Cliente
                </button>
            </div>
        </nav>

        <!--  Stats Cards  -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="stats-card">
                    <span class="stats-number" id="totalCustomers">0</span>
                    <span class="stats-label">Total Clientes</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3 ms-auto">
                <div class="stats-card">
                    <h5 class="font-primary text-primary mb-3">Clientes Frecuentes</h5>
                    <div id="frequentCustomersList">
                        <!-- Frequent customers will be populated by JavaScript -->
                        <p class="text-muted">Cargando clientes frecuentes...</p>
                    </div>
                </div>
            </div>
        </div>

        <!--  Filters  -->
        <div class="buzz-card mb-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Buscar cliente</label>
                    <input type="text" class="form-control form-control-buzz" id="customerSearchInput" placeholder="ID, nombre, email...">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">Tipo de Cliente</label>
                    <select class="form-control form-control-buzz" id="customerTypeFilter">
                        <option value="">Todos</option>
                        <option value="vip">VIP</option>
                        <option value="regular">Regular</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="showDeletedFilter">
                        <label class="form-check-label text-muted" for="showDeletedFilter">
                            Ver eliminados
                        </label>
                    </div>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button class="btn btn-outline-buzz w-100" id="applyCustomerFilters">Aplicar Filtros</button>
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
    <div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content buzz-modal-dark-override">
                <div class="modal-header">
                    <h5 class="modal-title font-primary text-primary" id="createCustomerModalLabel">Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createCustomerForm">
                    <div class="modal-body">
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label class="form-label text-muted">Email</label>
                                <input type="email" class="form-control form-control-buzz" id="createCustomerEmail">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Nombre *</label>
                            <input type="text" class="form-control form-control-buzz" id="createCustomerName" required>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label text-muted">Teléfono</label>
                                <select class="form-select form-control-buzz" id="createCustomerPhoneCountryCode" required>
                                    <option value="+58">Venezuela (+58)</option>
                                    <option value="+1">USA/Canada (+1)</option>
                                    <option value="+57">Colombia (+57)</option>
                                    <option value="+54">Argentina (+54)</option>
                                    <option value="+34">España (+34)</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-muted">&nbsp;</label>
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
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="createCustomerVip">
                            <label class="form-check-label text-muted" for="createCustomerVip">
                                Cliente VIP
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-buzz" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-buzz">
                            <i class="fas fa-save"></i> Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Customer Modal -->
    <div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content buzz-modal-dark-override">
                <div class="modal-header">
                    <h5 class="modal-title font-primary text-primary" id="viewCustomerModalLabel">Detalle del Cliente #<span id="viewCustomerId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Nombre:</strong> <span id="viewCustomerName"></span></p>
                    <p><strong>Email:</strong> <span id="viewCustomerEmail"></span></p>
                    <p><strong>Teléfono:</strong> <span id="viewCustomerPhone"></span></p>
                    <p><strong>Dirección:</strong> <span id="viewCustomerAddress"></span></p>
                    <p><strong>Notas:</strong> <span id="viewCustomerNotes"></span></p>
                    <p><strong>VIP:</strong> <span id="viewCustomerVip"></span></p>
                    <p><strong>Creado en:</strong> <span id="viewCustomerCreatedAt"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-buzz" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content buzz-modal-dark-override">
                <div class="modal-header">
                    <h5 class="modal-title font-primary text-primary" id="editCustomerModalLabel">Editar Cliente #<span id="editCustomerId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCustomerForm">
                    <div class="modal-body">
                        <input type="hidden" id="editCustomerIdHidden">
                        <div class="mb-3">
                            <label class="form-label text-muted">Nombre *</label>
                            <input type="text" class="form-control form-control-buzz" id="editCustomerName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <input type="email" class="form-control form-control-buzz" id="editCustomerEmail">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Teléfono</label>
                            <input type="text" class="form-control form-control-buzz" id="editCustomerPhone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Dirección</label>
                            <textarea class="form-control form-control-buzz" id="editCustomerAddress" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Notas</label>
                            <textarea class="form-control form-control-buzz" id="editCustomerNotes" rows="2"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="editCustomerVip">
                            <label class="form-check-label text-muted" for="editCustomerVip">
                                Cliente VIP
                            </label>
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
        </nav>

        <!--  Stats Cards  -->
        <div class="row mb-4">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="stats-card">
                    <span class="stats-number" id="totalCustomers">0</span>
                    <span class="stats-label">Total Clientes</span>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3 ms-auto">
                <div class="stats-card">
                    <h5 class="font-primary text-primary mb-3">Clientes Frecuentes</h5>
                    <div id="frequentCustomersList">
                        <!-- Frequent customers will be populated by JavaScript -->
                        <p class="text-muted">Cargando clientes frecuentes...</p>
                    </div>
                </div>
            </div>
        </div>

        <!--  Filters  -->
        <div class="buzz-card mb-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted">Buscar cliente</label>
                    <input type="text" class="form-control form-control-buzz" id="customerSearchInput" placeholder="ID, nombre, email...">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">Tipo de Cliente</label>
                    <select class="form-control form-control-buzz" id="customerTypeFilter">
                        <option value="">Todos</option>
                        <option value="vip">VIP</option>
                        <option value="regular">Regular</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button class="btn btn-outline-buzz w-100" id="applyCustomerFilters">Aplicar Filtros</button>
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
    <div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content buzz-modal-dark-override">
                <div class="modal-header">
                    <h5 class="modal-title font-primary text-primary" id="createCustomerModalLabel">Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createCustomerForm">
                    <div class="modal-body">
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label class="form-label text-muted">Email</label>
                                <input type="email" class="form-control form-control-buzz" id="createCustomerEmail">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Nombre *</label>
                            <input type="text" class="form-control form-control-buzz" id="createCustomerName" required>
                        </div>
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label text-muted">Teléfono</label>
                                <select class="form-select form-control-buzz" id="createCustomerPhoneCountryCode" required>
                                    <option value="+58">Venezuela (+58)</option>
                                    <option value="+1">USA/Canada (+1)</option>
                                    <option value="+57">Colombia (+57)</option>
                                    <option value="+54">Argentina (+54)</option>
                                    <option value="+34">España (+34)</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label text-muted">&nbsp;</label>
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
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="createCustomerVip">
                            <label class="form-check-label text-muted" for="createCustomerVip">
                                Cliente VIP
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-buzz" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-buzz">
                            <i class="fas fa-save"></i> Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Customer Modal -->
    <div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content buzz-modal-dark-override">
                <div class="modal-header">
                    <h5 class="modal-title font-primary text-primary" id="viewCustomerModalLabel">Detalle del Cliente #<span id="viewCustomerId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Nombre:</strong> <span id="viewCustomerName"></span></p>
                    <p><strong>Email:</strong> <span id="viewCustomerEmail"></span></p>
                    <p><strong>Teléfono:</strong> <span id="viewCustomerPhone"></span></p>
                    <p><strong>Dirección:</strong> <span id="viewCustomerAddress"></span></p>
                    <p><strong>Notas:</strong> <span id="viewCustomerNotes"></span></p>
                    <p><strong>VIP:</strong> <span id="viewCustomerVip"></span></p>
                    <p><strong>Creado en:</strong> <span id="viewCustomerCreatedAt"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-buzz" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content buzz-modal-dark-override">
                <div class="modal-header">
                    <h5 class="modal-title font-primary text-primary" id="editCustomerModalLabel">Editar Cliente #<span id="editCustomerId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCustomerForm">
                    <div class="modal-body">
                        <input type="hidden" id="editCustomerIdHidden">
                        <div class="mb-3">
                            <label class="form-label text-muted">Nombre *</label>
                            <input type="text" class="form-control form-control-buzz" id="editCustomerName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <input type="email" class="form-control form-control-buzz" id="editCustomerEmail">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Teléfono</label>
                            <input type="text" class="form-control form-control-buzz" id="editCustomerPhone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Dirección</label>
                            <textarea class="form-control form-control-buzz" id="editCustomerAddress" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Notas</label>
                            <textarea class="form-control form-control-buzz" id="editCustomerNotes" rows="2"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="editCustomerVip">
                            <label class="form-check-label text-muted" for="editCustomerVip">
                                Cliente VIP
                            </label>
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
    <script src="assets/js/clientes.js"></script>
</body>
</html>
