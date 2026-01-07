<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Inventario</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/filter-layout.css">
</head>
<body>
    <!-- Sidebar -->
    <?php require_once 'php/sidebar.php'; ?>
    <main class="main-content">
       <!--  Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">GESTIÓN DE INVENTARIO</h1>
                <span class="text-muted ms-2 font-mono">/ PRODUCTOS</span>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn btn-buzz" onclick="openProductModal()">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </button>
            </div>
        </nav>

       <!-- Filters -->
        <div class="buzz-card mb-4 pb-3"> <!-- Added padding for better distribution -->
            <div class="filter-wrapper">
                <!-- Main Filter Row (Always Visible) -->
                <div class="filter-group filter-search">
                    <label class="text-muted">Buscar producto</label>
                    <div class="position-relative">
                        <input type="text" class="form-control form-control-buzz" id="searchInput" placeholder="SKU, nombre o descripción...">
                        <i class="fas fa-search position-absolute end-0 top-50 translate-middle-y me-3 text-muted"></i>
                    </div>
                </div>

                <div class="filter-group filter-select">
                    <label class="text-muted">Estado</label>
                    <select class="form-control form-control-buzz" id="statusFilter">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="stock-bajo">Stock Bajo</option>
                        <option value="agotado">Agotado</option>
                    </select>
                </div>

                <div class="filter-group filter-actions">
                    <button class="btn btn-outline-buzz btn-filter-action" id="toggleAdvancedFilters" title="Más filtros">
                        <i class="fas fa-filter"></i>
                        <span class="btn-text">Filtros Avanzados</span>
                        <span class="badge bg-primary ms-1 d-none" id="activeFiltersBadge">0</span>
                    </button>
                    <button class="btn btn-outline-buzz btn-filter-action" onclick="clearProductFilters()" title="Limpiar filtros">
                        <i class="fas fa-times"></i><span class="btn-text">Limpiar</span>
                    </button>
                </div>
            </div>

            <!-- Advanced Filters (Collapsible) -->
            <div id="advancedFiltersCollapse" class="advanced-filters-section mt-3 d-none">
                <hr class="border-secondary opacity-25">
                <div class="filter-wrapper">
                    <div class="filter-group filter-select">
                        <label class="text-muted">Marca</label>
                        <select class="form-control form-control-buzz" id="marcaFilter">
                            <option value="">Todas las marcas</option>
                        </select>
                    </div>
                    <div class="filter-group filter-select">
                        <label class="text-muted">Categoría</label>
                        <select class="form-control form-control-buzz" id="categoriaFilter">
                            <option value="">Todas las categorías</option>
                        </select>
                    </div>
                    <div class="filter-group filter-search" style="flex: 1 1 120px;">
                        <label class="text-muted">Modelo</label>
                        <input type="text" class="form-control form-control-buzz" id="modeloFilter" placeholder="Ej: G2 Pro...">
                    </div>
                    <div class="filter-group filter-select">
                        <label class="text-muted">UND</label>
                        <select class="form-control form-control-buzz" id="undFilter">
                            <option value="">Todas</option>
                            <option value="UND">UND</option>
                            <option value="KG">KG</option>
                            <option value="LT">LT</option>
                            <option value="MT">MT</option>
                            <option value="M2">M2</option>
                            <option value="PACK">PACK</option>
                            <option value="CAJA">CAJA</option>
                            <option value="PAR">PAR</option>
                        </select>
                    </div>
                    <div class="filter-group filter-select">
                        <label class="text-muted">Ordenar por</label>
                        <select class="form-control form-control-buzz" id="sortBy">
                            <option value="name">Nombre</option>
                            <option value="sku">SKU</option>
                            <option value="stock">Stock</option>
                            <option value="price">Precio</option>
                        </select>
                    </div>
                    <div class="filter-group filter-checkbox pt-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showDeletedFilter">
                            <label class="form-check-label text-muted" for="showDeletedFilter">
                                Ver eliminados
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="buzz-card">
            <div class="table-responsive">
                <table class="table table-buzz">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Nombre</th>
                            <th>Marca</th>
                            <th>Categoría</th>
                            <th>Modelo</th>
                            <th title="Unidad de Venta">UND</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                    </tbody>
                </table>
            </div>

             <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted font-mono">
                    Mostrando <span id="showingFrom">1</span>-<span id="showingTo">10</span> de <span id="totalProducts">0</span> productos
                </div>
                <nav>
                    <ul class="pagination pagination-sm" id="pagination">
                        <li class="page-item" id="prevPage">
                            <a class="page-link" href="#" onclick="changePage(-1)">&laquo; Anterior</a>
                        </li>
                        <li class="page-item active"><span class="page-link" id="currentPage">1</span></li>
                        <li class="page-item" id="nextPage">
                            <a class="page-link" href="#" onclick="changePage(1)">Siguiente &raquo;</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- System Footer -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <!--- Product Modal --->
    <div class="modal-buzz" id="productModal">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary" id="modalTitle">
                    <i class="fas fa-box"></i> Nuevo Producto
                </h5>
                <button class="close-buzz" onclick="closeProductModal()" aria-label="Cerrar">&times;</button>
            </div>
            <form id="productForm">
                <div class="modal-body-buzz">
                    <input type="hidden" id="productIdHidden">

                    <!-- Basic Information Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-info-circle"></i> INFORMACIÓN BÁSICA
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SKU *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="productSku" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="productName" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <div class="input-group">
                                <span class="input-group-text align-items-start pt-2"><i class="fas fa-align-left"></i></span>
                                <textarea class="form-control form-control-buzz" id="productDescription" rows="3" placeholder="Detalles del producto..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Product Type Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-cube"></i> TIPO DE PRODUCTO
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Tipo *</label>
                                <select class="form-control form-control-buzz" id="productTipoProducto" onchange="onTipoProductoChange()">
                                    <option value="fisico">📦 Producto Físico</option>
                                    <option value="servicio">🔧 Servicio</option>
                                    <option value="kit">📦📦 Kit / Combo</option>
                                </select>
                                <small class="text-muted font-mono" id="tipoProductoHint">Producto con inventario físico</small>
                            </div>
                            <div class="col-md-6 mb-3" id="kitTypeContainer" style="display: none;">
                                <label class="form-label text-muted">Tipo de Kit</label>
                                <select class="form-control form-control-buzz" id="productEsKitVirtual" onchange="onKitTypeChange()">
                                    <option value="1">🔄 Kit Virtual (descuenta componentes)</option>
                                    <option value="0">📦 Kit Pre-armado (stock propio)</option>
                                </select>
                            </div>
                        </div>
                        <!-- Variant Section -->
                        <div class="row" id="varianteSection" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="esVariante" onchange="onEsVarianteChange()">
                                    <label class="form-check-label" for="esVariante">
                                        Es variante de otro producto
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3" id="productoPadreContainer" style="display: none;">
                                <label class="form-label text-muted">Producto Padre</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control form-control-buzz" id="productoPadreBusqueda"
                                           placeholder="Buscar producto padre..." autocomplete="off">
                                    <input type="hidden" id="productoPadreId">
                                    <div id="productoPadreResultados" class="autocomplete-results"></div>
                                </div>
                                <div id="productoPadreSeleccionado" class="selected-item mt-2" style="display: none;">
                                    <span class="selected-name"></span>
                                    <button type="button" class="btn-clear" onclick="limpiarProductoPadre()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="varianteAtributoRow" style="display: none;">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Atributo de Variante</label>
                                <input type="text" class="form-control form-control-buzz" id="productVarianteAtributo"
                                       placeholder="Ej: Talla M, Color Rojo">
                                <small class="text-muted">Identifica esta variante</small>
                            </div>
                        </div>
                    </div>

                    <!-- Kit Components Section (only for kits) -->
                    <div class="modal-section" id="kitComponentesSection" style="display: none;">
                        <div class="modal-section-title">
                            <i class="fas fa-boxes"></i> COMPONENTES DEL KIT
                            <button type="button" class="btn btn-sm btn-outline-buzz ms-3" onclick="agregarComponenteKit()">
                                <i class="fas fa-plus"></i> Añadir
                            </button>
                        </div>
                        <div id="kitComponentesContainer">
                            <!-- Componentes se agregan dinámicamente -->
                        </div>
                        <div id="sinComponentes" class="text-center text-muted py-3">
                            <i class="fas fa-puzzle-piece"></i> Añade productos para formar el kit
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="resumen-item">
                                    <span class="text-muted">Stock Disponible del Kit:</span>
                                    <span class="text-accent font-mono" id="stockKitCalculado">0</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="resumen-item">
                                    <span class="text-muted">Costo Componentes:</span>
                                    <span class="text-accent font-mono" id="costoComponentes">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing & Stock Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-dollar-sign"></i> PRECIO & STOCK
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio Unitario *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                    <input type="number" class="form-control form-control-buzz" id="productPrice" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Actual *</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-boxes"></i></span>
                                    <input type="number" class="form-control form-control-buzz" id="productStock" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Stock Mínimo</label>
                                <input type="number" class="form-control form-control-buzz" id="productMinStock" value="10" min="0">
                                <small class="text-muted font-mono">Alerta cuando el stock sea menor</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <select class="form-control form-control-buzz" id="productStatus">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Categorization Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-tags"></i> CATEGORIZACIÓN
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Marca *</label>
                                <select class="form-control form-control-buzz" id="productMarca" required>
                                    <option value="">Seleccionar marca...</option>
                                    <!-- Opciones cargadas dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Categoría</label>
                                <select class="form-control form-control-buzz" id="productCategoria">
                                    <option value="">Sin categoría</option>
                                    <!-- Opciones cargadas dinámicamente -->
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Proveedor</label>
                                <select class="form-control form-control-buzz" id="productProveedor">
                                    <option value="">Sin proveedor</option>
                                    <!-- Opciones cargadas dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Modelo</label>
                                <input type="text" class="form-control form-control-buzz" id="productModelo"
                                       placeholder="Ej: G2 Pro, Clásico...">
                            </div>
                        </div>
                    </div>

                    <!-- Attributes Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-sliders-h"></i> ATRIBUTOS FÍSICOS
                            <small class="text-muted ms-2">(Opcional)</small>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Color</label>
                                <input type="text" class="form-control form-control-buzz" id="productColor"
                                       placeholder="Ej: Negro, Azul...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Tamaño/Talla</label>
                                <input type="text" class="form-control form-control-buzz" id="productTamanio"
                                       placeholder="Ej: M, Grande, A4...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Medidas</label>
                                <input type="text" class="form-control form-control-buzz" id="productMedidas"
                                       placeholder="Ej: 21x27 cm">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Peso (Kg)</label>
                                <input type="number" class="form-control form-control-buzz" id="productPeso"
                                       step="0.001" min="0" placeholder="0.000">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Material</label>
                                <input type="text" class="form-control form-control-buzz" id="productMaterial"
                                       placeholder="Ej: Plástico, Madera...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label text-muted">Unidad de Venta</label>
                                <select class="form-control form-control-buzz" id="productUnidadVenta">
                                    <option value="UND">Unidad (UND)</option>
                                    <option value="KG">Kilogramo (KG)</option>
                                    <option value="LT">Litro (LT)</option>
                                    <option value="MT">Metro (MT)</option>
                                    <option value="M2">Metro Cuadrado (M2)</option>
                                    <option value="PACK">Pack</option>
                                    <option value="CAJA">Caja</option>
                                    <option value="PAR">Par</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-buzz">
                    <button type="button" class="btn btn-outline-buzz" onclick="closeProductModal()">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-save me-2"></i>Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/confirmation_modal.js"></script>
    <script src="assets/js/inventario.js"></script>
</body>
</html>
