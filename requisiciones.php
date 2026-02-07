<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="user-role" content="<?php echo htmlspecialchars($_SESSION['usuario_rol'] ?? 'operador'); ?>">
    <title>BUZZ Data Manager - Requisiciones de Compra</title>
    <!-- External Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- App Styles -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/filter-layout.css">
    <link rel="stylesheet" href="assets/css/requisiciones.css">
</head>
<body>
    <!-- Sidebar -->
    <?php require_once 'php/sidebar.php'; ?>

    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="h5 mb-0">Requisiciones de Compra</h1>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-warning" id="btnFromLowStock" title="Crear desde productos con stock bajo">
                    <i class="fas fa-magic"></i><span class="btn-text"> Auto-Stock</span>
                </button>
                <button class="btn btn-buzz" id="btnNuevaRequisicion">
                    <i class="fas fa-plus"></i><span class="btn-text"> Nueva Requisición</span>
                </button>
            </div>
        </nav>

        <!-- Vista de Historial (por defecto) -->
        <div id="vistaHistorial">
            <!-- Filtros -->
            <div class="buzz-card mb-4 pb-3">
                <div class="filter-wrapper">
                    <div class="filter-group filter-search">
                        <label class="text-muted">Buscar</label>
                        <input type="text" class="form-control form-control-buzz" id="filterSearch" placeholder="Código, notas...">
                    </div>
                    <div class="filter-group filter-select">
                        <label class="text-muted">Estado</label>
                        <select class="form-select form-select-buzz" id="filterEstado">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="aprobada">Aprobada</option>
                            <option value="rechazada">Rechazada</option>
                            <option value="procesada">Procesada</option>
                        </select>
                    </div>
                    <div class="filter-group filter-select">
                        <label class="text-muted">Prioridad</label>
                        <select class="form-select form-select-buzz" id="filterPrioridad">
                            <option value="">Todas</option>
                            <option value="urgente">🔴 Urgente</option>
                            <option value="alta">🟠 Alta</option>
                            <option value="media">🟡 Media</option>
                            <option value="baja">🟢 Baja</option>
                        </select>
                    </div>
                    <div class="filter-group filter-actions">
                        <button class="btn btn-outline-buzz btn-filter-action" id="btnLimpiarFiltros" title="Limpiar filtros">
                            <i class="fas fa-times"></i><span class="btn-text">Limpiar</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de Requisiciones -->
            <div class="buzz-card">
                <div class="table-responsive">
                    <table class="table table-buzz">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Prioridad</th>
                                <th>Productos</th>
                                <th>Estado</th>
                                <th>Solicitante</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaRequisiciones">
                            <tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center px-3 py-2">
                    <span class="text-muted small" id="infoRequisiciones"></span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationRequisiciones"></ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Vista de Formulario -->
        <div id="vistaFormulario" style="display: none;">
            <form id="formRequisicion">
                <input type="hidden" id="requisicionId" value="">

                <!-- Layout 70/30 -->
                <div class="row g-4">
                    <!-- Left Column: Products Table (70%) -->
                    <div class="col-lg-8">
                        <div class="buzz-card">
                            <div class="card-body-buzz p-3">
                                <!-- Header Row -->
                                <div class="row g-3 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Código</label>
                                        <input type="text" class="form-control form-control-buzz form-control-sm" id="codigoRequisicion" readonly placeholder="Auto">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Fecha</label>
                                        <input type="date" class="form-control form-control-buzz form-control-sm" id="fechaRequisicion" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Prioridad</label>
                                        <select class="form-select form-select-buzz form-select-sm" id="prioridadRequisicion">
                                            <option value="baja">🟢 Baja</option>
                                            <option value="media" selected>🟡 Media</option>
                                            <option value="alta">🟠 Alta</option>
                                            <option value="urgente">🔴 Urgente</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-muted">Estado</label>
                                        <input type="text" class="form-control form-control-buzz form-control-sm" id="estadoRequisicion" readonly value="Pendiente">
                                    </div>
                                </div>

                                <!-- Products Table (High Density) -->
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0" id="tablaProductos">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width: 120px;">SKU</th>
                                                <th>Producto</th>
                                                <th style="width: 80px;">Stock</th>
                                                <th style="width: 80px;">Cantidad</th>
                                                <th style="width: 150px;">Proveedor Sugerido</th>
                                                <th style="width: 100px;">Costo Est.</th>
                                                <th style="width: 50px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="lineasRequisicion">
                                            <!-- Dynamic rows -->
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-light">
                                                <td colspan="7">
                                                    <button type="button" class="btn btn-sm btn-outline-buzz w-100" id="btnAddLinea">
                                                        <i class="fas fa-plus"></i> Agregar producto
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Notes -->
                                <div class="mt-3">
                                    <label class="form-label small text-muted">Notas</label>
                                    <textarea class="form-control form-control-buzz form-control-sm" id="notasRequisicion" rows="2" placeholder="Observaciones opcionales..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Summary (30%) -->
                    <div class="col-lg-4">
                        <div class="buzz-card sticky-top" style="top: 1rem;">
                            <div class="card-body-buzz p-3">
                                <h6 class="mb-3"><i class="fas fa-chart-pie me-2"></i>Resumen por Proveedor</h6>
                                <div id="resumenProveedores" class="mb-3">
                                    <div class="text-muted small text-center py-3">Sin productos</div>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Productos:</span>
                                    <strong id="totalProductos">0</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Unidades:</span>
                                    <strong id="totalUnidades">0</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Costo Estimado:</span>
                                    <strong class="text-success" id="costoEstimado">$0.00</strong>
                                </div>

                                <hr>

                                <!-- Action Buttons -->
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-buzz">
                                        <i class="fas fa-save me-1"></i> Guardar
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="btnAprobar" style="display: none;">
                                        <i class="fas fa-check me-1"></i> Aprobar
                                    </button>
                                    <button type="button" class="btn btn-success" id="btnGenerarCargos" style="display: none;">
                                        <i class="fas fa-truck-loading me-1"></i> Generar Cargos
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="btnCancelar">
                                        <i class="fas fa-arrow-left me-1"></i> Volver
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- Product Search Dropdown Template -->
    <div id="productSearchDropdown" class="autocomplete-dropdown" style="display: none;"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/requisiciones.js"></script>
</body>
</html>
