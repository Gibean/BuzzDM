<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Transferencias</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/cargos.css">
    <link rel="stylesheet" href="assets/css/filter-layout.css">
    <link rel="stylesheet" href="assets/css/transferencias.css">
</head>
<body>
    <!-- Sidebar -->
    <?php require_once 'php/sidebar.php'; ?>

    <main class="main-content">
        <!-- Top Navbar -->
        <!-- Top Navbar -->
        <?php
        $nav_title = 'TRANSFERENCIAS';
        $nav_subtitle = 'MOVIMIENTO DE STOCK';
        $nav_actions = '
                <button class="btn btn-outline-buzz" id="btnVerHistorial" onclick="verHistorial()"><i class="fas fa-history"></i> Historial</button>
                <button class="btn btn-buzz" id="btnNueva" onclick="nuevaTransferencia()"><i class="fas fa-plus"></i> Nueva Transferencia</button>';
        require_once 'php/top_navbar.php';
        ?>

        <!-- Vista Historial -->
        <div id="vistaHistorial">
            <!-- Filtros -->
            <div class="buzz-card mb-4 pb-3">
                <div class="filter-wrapper">
                    <div class="filter-group filter-search">
                        <label class="text-muted">Buscar</label>
                        <input type="text" class="form-control form-control-buzz" id="searchTransferencias"
                               placeholder="Código, referencia...">
                    </div>
                    <div class="filter-group filter-select">
                        <label class="text-muted">Estado</label>
                        <select class="form-control form-control-buzz" id="filterEstado" onchange="cargarTransferencias()">
                            <option value="">Todos</option>
                            <option value="borrador">Borrador</option>
                            <option value="en_transito">En Tránsito</option>
                            <option value="recibido">Recibido</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="filter-group filter-actions">
                        <button class="btn btn-outline-buzz btn-filter-action" onclick="limpiarFiltros()" title="Limpiar">
                            <i class="fas fa-times"></i><span class="btn-text">Limpiar</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla -->
            <div class="buzz-card">
                <div class="table-responsive">
                    <table class="table table-buzz">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Origen → Destino</th>
                                <th>Productos</th>
                                <th>Unidades</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaTransferenciasBody">
                            <!-- Contenido dinámico -->
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted font-mono">
                        Mostrando <span id="showingFrom">0</span>-<span id="showingTo">0</span>
                        de <span id="totalTransferencias">0</span>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm" id="paginationTransferencias"></ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Vista Formulario -->
        <div id="vistaFormulario" style="display: none;">
            <form id="formTransferencia">
                <input type="hidden" id="transferenciaId">

                <div class="buzz-card mb-4">
                    <div class="card-body-buzz p-4">
                        <div class="row g-4">
                            <!-- Código autogenerado (sin label) -->
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-primary"><i class="fas fa-hashtag"></i></span>
                                        <input type="text" class="form-control form-control-buzz form-control-autogen" id="transferenciaCodigo"
                                               readonly tabindex="-1" placeholder="Código auto-generado">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted small fw-bold">FECHA SOLICITUD *</label>
                                    <input type="date" class="form-control form-control-buzz" id="transferenciaFecha" required>
                                </div>
                            </div>

                            <!-- Almacenes -->
                            <div class="col-lg-5">
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">ALMACÉN ORIGEN *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-danger"><i class="fas fa-warehouse"></i></span>
                                        <select class="form-control form-control-buzz" id="almacenOrigen" required onchange="validarAlmacenes()">
                                            <option value="">Seleccionar origen...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted small fw-bold">ALMACÉN DESTINO *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-success"><i class="fas fa-warehouse"></i></span>
                                        <select class="form-control form-control-buzz" id="almacenDestino" required onchange="validarAlmacenes()">
                                            <option value="">Seleccionar destino...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Motivo y Notas -->
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">MOTIVO</label>
                                    <input type="text" class="form-control form-control-buzz" id="transferenciaMotivo"
                                           placeholder="Ej: Reabastecimiento sucursal">
                                </div>
                                <div class="mb-0">
                                    <label class="form-label text-muted small fw-bold">REFERENCIA EXTERNA</label>
                                    <input type="text" class="form-control form-control-buzz" id="transferenciaReferencia"
                                           placeholder="Guía de envío...">
                                </div>
                            </div>

                            <!-- Notas -->
                            <div class="col-12">
                                <label class="form-label text-muted small fw-bold">NOTAS</label>
                                <textarea class="form-control form-control-buzz" id="transferenciaNotas" rows="2"
                                          placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos -->
                <div class="buzz-card mb-4">
                    <div class="card-header-buzz d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-boxes"></i> PRODUCTOS A TRANSFERIR</span>
                        <button type="button" class="btn btn-outline-buzz btn-sm" onclick="agregarLineaProducto()">
                            <i class="fas fa-plus"></i> Añadir Producto
                        </button>
                    </div>
                    <div class="card-body-buzz transferencias-grid">
                        <div class="cargo-lineas-header">
                            <div class="linea-col col-sku">SKU/Código</div>
                            <div class="linea-col col-nombre">Producto</div>
                            <div class="linea-col col-stock">Stock Origen</div>
                            <div class="linea-col col-cantidad">Cantidad</div>
                            <div class="linea-col col-acciones"></div>
                        </div>
                        <div id="lineasProductosContainer"></div>
                        <div id="sinProductos" class="text-center text-muted py-4">
                            <i class="fas fa-box-open fa-2x mb-2"></i>
                            <p>No hay productos agregados. Haz clic en "Añadir Producto" para comenzar.</p>
                        </div>
                    </div>
                </div>

                <!-- Totales -->
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

                <!-- Botones -->
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-buzz" onclick="cancelarFormulario()">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-buzz" onclick="guardarBorrador()" id="btnBorrador">
                            <i class="fas fa-save"></i> Guardar Borrador
                        </button>
                        <button type="button" class="btn btn-buzz" onclick="enviarTransferencia()" id="btnEnviar">
                            <i class="fas fa-truck"></i> Enviar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <!-- System Footer -->
    <?php require_once 'php/system_footer.php'; ?>

    <!-- Modal de Detalle -->
    <div class="modal-buzz" id="modalDetalle">
        <div class="modal-content-buzz modal-lg">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary">
                    <i class="fas fa-exchange-alt"></i> Detalle de Transferencia
                </h5>
                <button class="close-buzz" onclick="cerrarModalDetalle()">&times;</button>
            </div>
            <div class="modal-body-buzz" id="modalDetalleContenido">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer-buzz">
                <button type="button" class="btn btn-outline-buzz" onclick="cerrarModalDetalle()">Cerrar</button>
                <button type="button" class="btn btn-warning d-none" id="btnCancelarTransferencia" onclick="cancelarTransferencia()">
                    <i class="fas fa-ban"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success d-none" id="btnRecibirTransferencia" onclick="recibirTransferencia()">
                    <i class="fas fa-check"></i> Marcar Recibida
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/confirmation_modal.js"></script>
    <script src="assets/js/transferencias.js"></script>
</body>
</html>
