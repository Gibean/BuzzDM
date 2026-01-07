<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Catálogos</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/catalogos.css">
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
                <h1 class="font-primary mb-0">GESTIÓN DE CATÁLOGOS</h1>
                <span class="text-muted ms-2 font-mono">/ MAESTROS</span>
            </div>
        </nav>

        <div class="container-fluid py-4">
            <!-- Search Bar -->
            <div class="catalog-search">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control form-control-buzz" id="catalogSearchInput"
                       placeholder="Buscar en todos los catálogos...">
            </div>

            <!-- Catalogs Grid -->
            <div class="catalogs-grid">
                <!-- Categories -->
                <div class="catalog-card" data-catalog="categorias">
                    <div class="catalog-card-header" onclick="toggleCard(this)">
                        <h5 class="catalog-card-title">
                            <i class="fas fa-tags"></i>
                            CATEGORÍAS
                            <span class="catalog-count" id="count-categorias">0</span>
                        </h5>
                        <div class="catalog-card-actions">
                            <button class="btn-add-sm" onclick="event.stopPropagation(); openCreateModal('categorias')">
                                <i class="fas fa-plus"></i> Nueva
                            </button>
                            <i class="fas fa-chevron-down catalog-toggle"></i>
                        </div>
                    </div>
                    <div class="catalog-card-body">
                        <div class="chips-container" id="body-categorias">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Brands -->
                <div class="catalog-card" data-catalog="marcas">
                    <div class="catalog-card-header" onclick="toggleCard(this)">
                        <h5 class="catalog-card-title">
                            <i class="fas fa-copyright"></i>
                            MARCAS
                            <span class="catalog-count" id="count-marcas">0</span>
                        </h5>
                        <div class="catalog-card-actions">
                            <button class="btn-add-sm" onclick="event.stopPropagation(); openCreateModal('marcas')">
                                <i class="fas fa-plus"></i> Nueva
                            </button>
                            <i class="fas fa-chevron-down catalog-toggle"></i>
                        </div>
                    </div>
                    <div class="catalog-card-body">
                        <div class="brands-list" id="body-marcas">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Colors -->
                <div class="catalog-card" data-catalog="colores">
                    <div class="catalog-card-header" onclick="toggleCard(this)">
                        <h5 class="catalog-card-title">
                            <i class="fas fa-palette"></i>
                            COLORES
                            <span class="catalog-count" id="count-colores">0</span>
                        </h5>
                        <div class="catalog-card-actions">
                            <button class="btn-add-sm" onclick="event.stopPropagation(); openCreateModal('colores')">
                                <i class="fas fa-plus"></i> Nuevo
                            </button>
                            <i class="fas fa-chevron-down catalog-toggle"></i>
                        </div>
                    </div>
                    <div class="catalog-card-body">
                        <div class="colors-grid" id="body-colores">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Materials -->
                <div class="catalog-card" data-catalog="materiales">
                    <div class="catalog-card-header" onclick="toggleCard(this)">
                        <h5 class="catalog-card-title">
                            <i class="fas fa-box-open"></i>
                            MATERIALES
                            <span class="catalog-count" id="count-materiales">0</span>
                        </h5>
                        <div class="catalog-card-actions">
                            <button class="btn-add-sm" onclick="event.stopPropagation(); openCreateModal('materiales')">
                                <i class="fas fa-plus"></i> Nuevo
                            </button>
                            <i class="fas fa-chevron-down catalog-toggle"></i>
                        </div>
                    </div>
                    <div class="catalog-card-body">
                        <div class="chips-container" id="body-materiales">
                            <!-- Populated by JS -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Footer -->
        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <!-- Modal para Catálogos (Compacto) -->
    <div id="itemModal" class="modal-buzz">
        <div class="modal-content-buzz modal-catalog">
            <div class="modal-header-buzz">
                <h4 id="itemModalTitle">
                    <span class="modal-icon" id="modalIcon"><i class="fas fa-edit"></i></span>
                    <span id="modalTitleText">Editar Item</span>
                </h4>
                <button type="button" class="close-buzz" onclick="closeItemModal()">&times;</button>
            </div>
            <form id="itemForm" onsubmit="event.preventDefault(); saveItem();">
                <div class="modal-body-buzz">
                    <input type="hidden" id="itemId">
                    <input type="hidden" id="itemCatalog">

                    <!-- Vista previa de color (solo para colores) -->
                    <div class="color-preview-large field-color" style="display:none;">
                        <div class="color-swatch-large" id="colorPreviewSwatch"></div>
                    </div>

                    <!-- Campo Nombre -->
                    <div class="mb-3">
                        <label class="form-label text-muted">
                            <i class="fas fa-font me-1"></i> Nombre *
                        </label>
                        <input type="text" class="form-control form-control-buzz" id="itemName" required
                               placeholder="Nombre del item" autocomplete="off">
                    </div>

                    <!-- Descripción (Categorías/Marcas) -->
                    <div class="mb-3 field-desc">
                        <label class="form-label text-muted">
                            <i class="fas fa-align-left me-1"></i> Descripción
                        </label>
                        <textarea class="form-control form-control-buzz" id="itemDescription" rows="2"
                                  placeholder="Breve descripción (opcional)"></textarea>
                    </div>

                    <!-- Sitio Web (Solo Marcas) -->
                    <div class="mb-3 field-web" style="display:none;">
                        <label class="form-label text-muted">
                            <i class="fas fa-globe me-1"></i> Sitio Web
                        </label>
                        <input type="url" class="form-control form-control-buzz" id="itemWeb"
                               placeholder="https://ejemplo.com">
                    </div>

                    <!-- Selector de Color (Solo Colores) -->
                    <div class="field-color" style="display:none;">
                        <label class="form-label text-muted">
                            <i class="fas fa-palette me-1"></i> Código de Color
                        </label>
                        <div class="color-picker-row">
                            <input type="color" class="color-picker-input" id="itemHex" value="#FFA000">
                            <input type="text" class="form-control form-control-buzz font-mono color-hex-input"
                                   id="itemHexText" placeholder="#FFA000" maxlength="7">
                        </div>
                    </div>
                </div>
                <div class="modal-footer-buzz">
                    <button type="button" class="btn btn-outline-buzz" onclick="closeItemModal()">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-buzz">
                        <i class="fas fa-check"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Container para Toasts -->
    <div id="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/catalogos.js"></script>
</body>
</html>
