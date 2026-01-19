<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Cargar Existencias</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/cargos.css">
    <link rel="stylesheet" href="assets/css/filter-layout.css">
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
                <h1 class="font-primary mb-0">CARGAR EXISTENCIAS</h1>
                <span class="text-muted ms-2 font-mono">/ ENTRADA DE INVENTARIO</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-buzz" onclick="verHistorial()">
                    <i class="fas fa-history"></i> Historial
                </button>
                <button class="btn btn-buzz" onclick="nuevoCargo()">
                    <i class="fas fa-plus"></i> Nuevo Cargo
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
                        <input type="text" class="form-control form-control-buzz" id="searchCargos"
                               placeholder="Código, proveedor, referencia...">
                    </div>
                    <div class="filter-group filter-date">
                        <label class="text-muted">Desde</label>
                        <input type="date" class="form-control form-control-buzz" id="filterFechaDesde">
                    </div>
                    <div class="filter-group filter-date">
                        <label class="text-muted">Hasta</label>
                        <input type="date" class="form-control form-control-buzz" id="filterFechaHasta">
                    </div>

                    <div class="filter-group filter-actions">
                        <button class="btn btn-outline-buzz btn-filter-action" id="toggleAdvancedFilters" title="Más filtros">
                            <i class="fas fa-filter"></i>
                            <span class="btn-text">Filtros Avanzados</span>
                            <span class="badge bg-primary ms-1 d-none" id="activeFiltersBadge">0</span>
                        </button>
                        <button class="btn btn-outline-buzz btn-filter-action" onclick="limpiarFiltros()" title="Limpiar">
                            <i class="fas fa-times"></i><span class="btn-text">Limpiar</span>
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div id="advancedFiltersCollapse" class="advanced-filters-section mt-3 d-none">
                    <hr class="border-secondary opacity-25">
                    <div class="filter-wrapper">
                        <div class="filter-group filter-select">
                            <label class="text-muted">Estado</label>
                            <select class="form-control form-control-buzz" id="filterEstado">
                                <option value="">Todos</option>
                                <option value="borrador">Borrador</option>
                                <option value="confirmado">Confirmado</option>
                                <option value="anulado">Anulado</option>
                            </select>
                        </div>
                        <div class="filter-group filter-select">
                            <label class="text-muted">Concepto</label>
                            <select class="form-control form-control-buzz" id="filterConcepto">
                                <option value="">Todos</option>
                                <option value="compra">Compra</option>
                                <option value="donacion">Donación</option>
                                <option value="ajuste_positivo">Ajuste Positivo</option>
                                <option value="devolucion_cliente">Devolución</option>
                                <option value="inventario_inicial">Inv. Inicial</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Cargos -->
            <div class="buzz-card">
                <div class="table-responsive">
                    <table class="table table-buzz">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Concepto</th>
                                <th>Proveedor</th>
                                <th>Productos</th>
                                <th>Unidades</th>
                                <th>Valor Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCargosBody">
                            <!-- Contenido dinámico -->
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted font-mono">
                        Mostrando <span id="showingFrom">0</span>-<span id="showingTo">0</span>
                        de <span id="totalCargos">0</span> cargos
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm" id="paginationCargos"></ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Vista de Formulario de Cargo -->
        <div id="vistaFormulario" style="display: none;">
            <form id="formCargo">
                <input type="hidden" id="cargoId" value="">

                <!-- Información del Cargo -->
                <div class="buzz-card mb-4">
                    <div class="card-body-buzz p-4">
                        <div class="row g-4">
                            <!-- Columna Izquierda: Identificación y Fecha -->
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">CÓDIGO</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-primary"><i class="fas fa-hashtag"></i></span>
                                        <input type="text" class="form-control form-control-buzz" id="cargoCodigo"
                                               readonly tabindex="-1" style="pointer-events: none;" placeholder="Auto-generado">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted small fw-bold">FECHA *</label>
                                    <input type="date" class="form-control form-control-buzz" id="cargoFecha" required>
                                </div>
                            </div>

                            <!-- Columna Central: Concepto y Almacén -->
                            <div class="col-lg-5">
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">CONCEPTO *</label>
                                    <select class="form-control form-control-buzz" id="cargoConcepto" required onchange="onConceptoChange()">
                                        <option value="compra">📦 Compra a Proveedor</option>
                                        <option value="transferencia">🔄 Transferencia entre Almacenes</option>
                                        <option value="ajuste_positivo">➕ Ajuste Positivo</option>
                                        <option value="inventario_inicial">📋 Inventario Inicial</option>
                                        <option value="devolucion_cliente">↩️ Devolución de Cliente</option>
                                        <option value="donacion">🎁 Donación Recibida</option>
                                        <option value="otro">📝 Otro</option>
                                    </select>
                                    <div id="conceptoOtroContainer" class="mt-2" style="display: none;">
                                        <input type="text" class="form-control form-control-buzz" id="cargoConceptoOtro" placeholder="Especifique el concepto...">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted small fw-bold">ALMACÉN DE DESTINO *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-info"><i class="fas fa-warehouse"></i></span>
                                        <select class="form-control form-control-buzz" id="cargoAlmacenId" required>
                                            <option value="">Seleccionar almacén...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna Derecha: Notas -->
                            <div class="col-lg-4">
                                <label class="form-label text-muted small fw-bold">NOTAS Y OBSERVACIONES</label>
                                <textarea class="form-control form-control-buzz" id="cargoNotas" rows="4" placeholder="Detalles internos sobre este movimiento..."></textarea>
                            </div>

                            <!-- Fila Inferior: Proveedor y Referencias (Sutil) -->
                            <div class="col-12 mt-4 pt-3 border-top border-secondary border-opacity-25">
                                <div class="row align-items-end">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <label class="form-label text-muted small fw-bold">
                                            PROVEEDOR (OPCIONAL)
                                            <a href="javascript:void(0)" class="ms-2 text-primary text-decoration-none small" onclick="abrirModalNuevoProveedor()">
                                                <i class="fas fa-plus-circle"></i> Nuevo
                                            </a>
                                        </label>
                                        <div class="position-relative">
                                            <div class="input-group">
                                                <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-truck"></i></span>
                                                <input type="text" class="form-control form-control-buzz" id="proveedorBusqueda" placeholder="Buscar proveedor..." autocomplete="off">
                                            </div>
                                            <input type="hidden" id="cargoProveedorId">
                                            <div id="proveedorResultados" class="autocomplete-results"></div>
                                        </div>
                                        <div id="proveedorSeleccionado" class="selected-item mt-2" style="display: none;">
                                            <span class="selected-name"></span>
                                            <button type="button" class="btn-clear" onclick="limpiarProveedor()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small fw-bold">REFERENCIA / FACTURA</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark border-secondary text-muted"><i class="fas fa-receipt"></i></span>
                                            <input type="text" class="form-control form-control-buzz" id="cargoReferencia" placeholder="Nº de factura, guía o código externo...">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos del Cargo -->
                <div class="buzz-card mb-4">
                    <div class="card-header-buzz d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-boxes"></i> PRODUCTOS DEL CARGO</span>
                        <button type="button" class="btn btn-outline-buzz btn-sm" onclick="agregarLineaProducto()">
                            <i class="fas fa-plus"></i> Añadir Producto
                        </button>
                    </div>
                    <div class="card-body-buzz">
                        <!-- Encabezado de la tabla de líneas -->
                        <div class="cargo-lineas-header">
                            <div class="linea-col col-sku">SKU/Código</div>
                            <div class="linea-col col-nombre">Producto</div>
                            <div class="linea-col col-cantidad">Cantidad</div>
                            <div class="linea-col col-acciones"></div>
                        </div>

                        <!-- Contenedor de líneas de productos -->
                        <div id="lineasProductosContainer">
                            <!-- Las líneas se agregan dinámicamente aquí -->
                        </div>

                        <!-- Mensaje cuando no hay productos -->
                        <div id="sinProductos" class="text-center text-muted py-4">
                            <i class="fas fa-box-open fa-2x mb-2"></i>
                            <p>No hay productos agregados. Haz clic en "Añadir Producto" para comenzar.</p>
                        </div>
                    </div>
                </div>

                <!-- Sección de Totales (Stat Cards) -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="stat-card-buzz stat-primary">
                            <div class="stat-icon"><i class="fas fa-boxes"></i></div>
                            <div class="stat-content">
                                <div class="stat-label">TOTAL PRODUCTOS</div>
                                <div class="stat-value" id="totalProductos">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card-buzz stat-success">
                            <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                            <div class="stat-content">
                                <div class="stat-label">TOTAL UNIDADES</div>
                                <div class="stat-value" id="totalUnidades">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-buzz" onclick="cancelarCargo()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-buzz" onclick="guardarBorrador()" id="btnBorrador">
                            <i class="fas fa-save"></i> Guardar Borrador
                        </button>
                        <button type="button" class="btn btn-buzz" onclick="confirmarCargo()" id="btnConfirmar">
                            <i class="fas fa-check"></i> Confirmar Cargo
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- System Footer -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <!-- Modal de Detalle de Cargo -->
    <div class="modal-buzz" id="modalDetalleCargo">
        <div class="modal-content-buzz modal-lg">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary">
                    <i class="fas fa-file-alt"></i> Detalle del Cargo
                </h5>
                <button class="close-buzz" onclick="cerrarModalDetalle()">&times;</button>
            </div>
            <div class="modal-body-buzz" id="modalDetalleContenido">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer-buzz">
                <button type="button" class="btn btn-outline-buzz" onclick="cerrarModalDetalle()">
                    Cerrar
                </button>
                <button type="button" class="btn btn-outline-warning" id="btnEditarCargo" style="display: none;">
                    <i class="fas fa-edit"></i> Editar
                </button>
                <button type="button" class="btn btn-buzz" id="btnConfirmarDesdeModal" style="display: none;">
                    <i class="fas fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Anulación -->
    <div class="modal-buzz" id="modalAnular">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-warning">
                    <i class="fas fa-exclamation-triangle"></i> Anular Cargo
                </h5>
                <button class="close-buzz" onclick="cerrarModalAnular()">&times;</button>
            </div>
            <div class="modal-body-buzz">
                <p>¿Estás seguro de que deseas anular este cargo?</p>
                <p class="text-warning"><strong>Esta acción revertirá el stock de todos los productos.</strong></p>
                <input type="hidden" id="anularCargoId">
                <div class="mb-3">
                    <label class="form-label text-muted">Motivo de anulación *</label>
                    <textarea class="form-control form-control-buzz" id="motivoAnulacion" rows="2"
                              placeholder="Ingresa el motivo..." required></textarea>
                </div>
            </div>
            <div class="modal-footer-buzz">
                <button type="button" class="btn btn-outline-buzz" onclick="cerrarModalAnular()">
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" onclick="ejecutarAnulacion()">
                    <i class="fas fa-ban"></i> Anular Cargo
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Proveedor (rápido) -->
    <div id="modalNuevoProveedor" class="modal-buzz">
        <div class="modal-content-buzz" style="max-width: 500px;">
            <div class="modal-header-buzz">
                <h5><i class="fas fa-truck"></i> Agregar Nuevo Proveedor</h5>
                <button type="button" class="close-buzz" onclick="cerrarModalNuevoProveedor()">&times;</button>
            </div>
            <div class="modal-body-buzz">
                <div class="mb-3">
                    <label class="form-label text-muted">Nombre del Proveedor *</label>
                    <input type="text" class="form-control form-control-buzz" id="nuevoProveedorNombre"
                           placeholder="Ej: Distribuidora ABC" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">RIF/Identificación</label>
                        <input type="text" class="form-control form-control-buzz" id="nuevoProveedorRif"
                               placeholder="J-12345678-9">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Teléfono</label>
                        <input type="text" class="form-control form-control-buzz" id="nuevoProveedorTelefono"
                               placeholder="+58 412 1234567">
                    </div>
                </div>
            </div>
            <div class="modal-footer-buzz">
                <button type="button" class="btn btn-outline-buzz" onclick="cerrarModalNuevoProveedor()">Cancelar</button>
                <button type="button" class="btn btn-buzz" onclick="guardarNuevoProveedor()">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/confirmation_modal.js"></script>
    <script src="assets/js/cargos_existencias.js"></script>
</body>
</html>
