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
</head>
<body>
    <!-- Sidebar -->
    <?php require_once 'php/sidebar.php'; ?>
     Main Content
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

       <!--  Filters -->
        <div class="buzz-card mb-4">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label text-muted">Buscar producto</label>
                    <input type="text" class="form-control form-control-buzz" id="searchInput" placeholder="SKU, nombre o descripción...">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">Estado</label>
                    <select class="form-control form-control-buzz" id="statusFilter">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="stock-bajo">Stock Bajo</option>
                        <option value="agotado">Agotado</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-muted">Ordenar por</label>
                    <select class="form-control form-control-buzz" id="sortBy">
                        <option value="name">Nombre</option>
                        <option value="sku">SKU</option>
                        <option value="stock">Stock</option>
                        <option value="price">Precio</option>
                    </select>
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
                <div class="col-md-2 mb-3">
                    <label class="form-label text-muted">&nbsp;</label>
                    <button class="btn btn-outline-buzz w-100" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
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
                            <th>Descripción</th>
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
    </main>

    <!--- Product Modal --->
    <div class="modal-buzz" id="productModal">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary" id="modalTitle">Nuevo Producto</h5>
                <span class="close-buzz" onclick="closeProductModal()">&times;</span>
            </div>
            <div id="alert-container" class="position-absolute top-0 start-50 translate-middle-x p-3" style="z-index: 1100; width: 90%;"></div>
            <form id="productForm">
                <div class="row">
                    <input type="hidden" id="productIdHidden">
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted">SKU *</label>
                        <input type="text" class="form-control form-control-buzz" id="productSku" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Nombre *</label>
                        <input type="text" class="form-control form-control-buzz" id="productName" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted">Descripción</label>
                    <textarea class="form-control form-control-buzz" id="productDescription" rows="3"></textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Precio Unitario *</label>
                        <input type="number" class="form-control form-control-buzz" id="productPrice" step="0.01" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Stock Actual *</label>
                        <input type="number" class="form-control form-control-buzz" id="productStock" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Stock Mínimo</label>
                        <input type="number" class="form-control form-control-buzz" id="productMinStock" value="10">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Estado</label>
                        <select class="form-control form-control-buzz" id="productStatus">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-buzz" onclick="closeProductModal()">Cancelar</button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-save"></i> Guardar Producto
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/confirmation_modal.js"></script>
    <script src="assets/js/inventario.js"></script>
</body>
</html>
