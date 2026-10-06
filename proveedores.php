<?php require_once 'php/sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUZZ Data Manager - Proveedores</title>
    <link rel="shortcut icon" href="assets/img/logov1_icon.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/modal-enhanced.css">
    <link rel="stylesheet" href="assets/css/toast.css">
    <link rel="stylesheet" href="assets/css/filter-layout.css">
    <style>
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--buzz-text-muted, #888);
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        /* Products table in modal */
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .productos-table th,
        .productos-table td {
            padding: 0.5rem;
            text-align: left;
            border-bottom: 1px solid var(--buzz-border-color, #333);
        }
        .productos-table th {
            background: rgba(255, 160, 0, 0.1);
            font-weight: 600;
        }
        .precio-compra {
            color: var(--buzz-primary-color, #ffa000);
            font-weight: 600;
        }
        /* Proveedor info in table - subtle text */
        .proveedor-contacto-cell {
            font-size: 0.85rem;
            color: var(--buzz-text-muted, #888);
            line-height: 1.5;
        }
        .proveedor-contacto-cell i {
            width: 16px;
            color: var(--buzz-primary-color, #ffa000);
            margin-right: 0.25rem;
            font-size: 0.8rem;
        }
        .proveedor-contacto-cell .contacto-item {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 250px;
        }
        .proveedor-nombre-cell {
            font-weight: 600;
            color: var(--buzz-text-primary, #fff);
        }
        .proveedor-rif-badge {
            font-size: 0.75rem;
            font-family: 'Courier New', monospace;
            color: var(--buzz-primary-color, #ffa000);
            background: rgba(255, 160, 0, 0.1);
            padding: 0.15rem 0.5rem;
            border-radius: 4px;
            display: inline-block;
        }
        .proveedor-direccion-cell {
            font-size: 0.85rem;
            color: var(--buzz-text-muted, #888);
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* Table column distribution */
        .table-buzz.table-proveedores {
            table-layout: fixed;
        }
        .table-proveedores .col-nombre { width: 18%; }
        .table-proveedores .col-rif { width: 12%; }
        .table-proveedores .col-contacto { width: 30%; }
        .table-proveedores .col-direccion { width: 25%; }
        .table-proveedores .col-acciones { width: 15%; text-align: right; }
        .table-proveedores td:last-child {
            text-align: right;
            white-space: nowrap;
        }
        /* Responsive table for proveedores */
        @media (max-width: 768px) {
            .table-buzz .hide-mobile {
                display: none;
            }
            .proveedor-contacto-cell .contacto-item {
                max-width: 160px;
            }
            .table-proveedores .col-nombre { width: 25%; }
            .table-proveedores .col-rif { width: 15%; }
            .table-proveedores .col-contacto { width: 35%; }
            .table-proveedores .col-acciones { width: 25%; }
        }
    </style>
</head>
<body>
    <?php require_once 'php/sidebar.php'; ?>

    <main class="main-content">
        <!-- Top Navbar -->
        <!-- Top Navbar -->
        <?php
        $nav_title = 'GESTIÓN DE PROVEEDORES';
        $nav_subtitle = 'PROVEEDORES';
        $nav_actions = '<button class="btn btn-buzz" id="btnNewProveedor" onclick="abrirModalProveedor()"><i class="fas fa-plus"></i> Nuevo Proveedor</button>';
        require_once 'php/top_navbar.php';
        ?>

        <!-- Filters -->
        <div class="buzz-card mb-4 pb-3">
            <div class="filter-wrapper">
                <div class="filter-group filter-search">
                    <label class="text-muted">Buscar proveedor</label>
                    <div class="position-relative">
                        <input type="text" class="form-control form-control-buzz" id="searchProveedorInput" placeholder="Nombre, RIF, email o teléfono...">
                        <i class="fas fa-search position-absolute end-0 top-50 translate-middle-y me-3 text-muted"></i>
                    </div>
                </div>
                <div class="filter-group filter-actions">
                    <button class="btn btn-outline-buzz btn-filter-action" onclick="limpiarFiltroProveedores()" title="Limpiar filtros">
                        <i class="fas fa-times"></i><span class="btn-text">Limpiar</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="buzz-card">
            <div class="table-responsive">
                <table class="table table-buzz table-proveedores">
                    <thead>
                        <tr>
                            <th class="col-nombre">Nombre</th>
                            <th class="col-rif">RIF</th>
                            <th class="col-contacto">Contacto</th>
                            <th class="col-direccion hide-mobile">Dirección</th>
                            <th class="col-acciones">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="proveedoresTableBody">
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2 text-muted">Cargando proveedores...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div id="proveedoresPagination" class="buzz-pagination"></div>
        </div>

        <?php require_once 'php/system_footer.php'; ?>
    </main>

    <!-- Modal Proveedor -->
    <div id="modalProveedor" class="modal-buzz">
        <div class="modal-content-buzz">
            <div class="modal-header-buzz">
                <h5 class="font-primary text-primary">
                    <i class="fas fa-truck"></i> <span id="modalProveedorTitulo">Nuevo Proveedor</span>
                </h5>
                <button type="button" class="close-buzz" onclick="cerrarModalProveedor()">&times;</button>
            </div>
            <form id="formProveedor" novalidate>
                <input type="hidden" id="proveedorId">
                <div class="modal-body-buzz">
                    <!-- Información Básica Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-info-circle"></i> INFORMACIÓN BÁSICA
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <input type="text" class="form-control form-control-buzz" id="proveedorNombre" required placeholder="Nombre del proveedor" maxlength="150">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RIF/Identificación</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="proveedorRif" placeholder="J-12345678-9" maxlength="12">
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">País</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    <select class="form-select form-control-buzz" id="proveedorPais">
                                        <option value="+58">🇻🇪 +58</option>
                                        <option value="+1">🇺🇸 +1</option>
                                        <option value="+57">🇨🇴 +57</option>
                                        <option value="+54">🇦🇷 +54</option>
                                        <option value="+34">🇪🇸 +34</option>
                                        <option value="+52">🇲🇽 +52</option>
                                        <option value="+55">🇧🇷 +55</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control form-control-buzz" id="proveedorTelefono" placeholder="412 1234567" maxlength="10">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contacto Section -->
                    <div class="modal-section">
                        <div class="modal-section-title">
                            <i class="fas fa-address-book"></i> CONTACTO
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control form-control-buzz" id="proveedorEmail" placeholder="contacto@proveedor.com" maxlength="150">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-text align-items-start pt-2"><i class="fas fa-map-marker-alt"></i></span>
                                <textarea class="form-control form-control-buzz" id="proveedorDireccion" rows="2" placeholder="Dirección del proveedor" maxlength="500"></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <div class="input-group">
                                <span class="input-group-text align-items-start pt-2"><i class="fas fa-sticky-note"></i></span>
                                <textarea class="form-control form-control-buzz" id="proveedorNotas" rows="2" placeholder="Notas adicionales..." maxlength="500"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer-buzz">
                    <button type="button" class="btn btn-outline-buzz" onclick="cerrarModalProveedor()">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-buzz" onclick="guardarProveedor()">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/modal.js"></script>
    <script src="assets/js/confirmation_modal.js"></script>
    <script nonce="<?= cspNonce() ?>">
        // =====================================================
        // PROVEEDORES MANAGEMENT
        // =====================================================

        let proveedores = [];
        let proveedoresFiltrados = [];
        let currentPageProv = 1;
        const proveedoresPerPage = 15;

        document.addEventListener('DOMContentLoaded', function() {
            cargarProveedores();
            if (typeof initVenezuelaRifValidation === 'function') {
                initVenezuelaRifValidation(document.getElementById('proveedorRif'));
            }
            if (typeof initVenezuelaPhoneValidation === 'function') {
                initVenezuelaPhoneValidation(document.getElementById('proveedorTelefono'));
            }
            if (typeof initEmailValidation === 'function') {
                initEmailValidation(document.getElementById('proveedorEmail'));
            }

            // Search filter listener
            const searchInput = document.getElementById('searchProveedorInput');
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    currentPageProv = 1;
                    filtrarYRenderProveedores();
                });
            }
        });

        function limpiarFiltroProveedores() {
            const searchInput = document.getElementById('searchProveedorInput');
            if (searchInput) searchInput.value = '';
            currentPageProv = 1;
            filtrarYRenderProveedores();
        }

        async function cargarProveedores() {
            try {
                const response = await fetch('php/api_suppliers.php');
                const data = await response.json();

                if (data.success) {
                    proveedores = data.proveedores || [];
                    filtrarYRenderProveedores();
                } else {
                    showToast('Error al cargar proveedores: ' + data.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión', 'danger');
            }
        }

        function filtrarYRenderProveedores() {
            const searchTerm = (document.getElementById('searchProveedorInput')?.value || '').toLowerCase().trim();

            if (!searchTerm) {
                proveedoresFiltrados = [...proveedores];
            } else {
                proveedoresFiltrados = proveedores.filter(p => {
                    return (p.nombre && p.nombre.toLowerCase().includes(searchTerm)) ||
                           (p.rif && p.rif.toLowerCase().includes(searchTerm)) ||
                           (p.email && p.email.toLowerCase().includes(searchTerm)) ||
                           (p.telefono && p.telefono.toLowerCase().includes(searchTerm)) ||
                           (p.direccion && p.direccion.toLowerCase().includes(searchTerm));
                });
            }

            renderProveedores();
        }

        function renderProveedores() {
            const tbody = document.getElementById('proveedoresTableBody');

            if (!proveedoresFiltrados || proveedoresFiltrados.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-truck"></i>
                                <h5>No hay proveedores registrados</h5>
                                <p>Haz clic en "Nuevo Proveedor" para agregar el primero.</p>
                            </div>
                        </td>
                    </tr>
                `;
                // Clear pagination
                const pagContainer = document.getElementById('proveedoresPagination');
                if (pagContainer) pagContainer.innerHTML = '';
                return;
            }

            // Paginate
            const totalPages = Math.ceil(proveedoresFiltrados.length / proveedoresPerPage);
            if (currentPageProv > totalPages) currentPageProv = totalPages;
            const startIdx = (currentPageProv - 1) * proveedoresPerPage;
            const endIdx = startIdx + proveedoresPerPage;
            const pageItems = proveedoresFiltrados.slice(startIdx, endIdx);

            tbody.innerHTML = pageItems.map(p => {
                // Build contact info
                let contactoHtml = '';
                if (p.telefono) contactoHtml += `<span class="contacto-item"><i class="fas fa-phone"></i> ${escapeHtml(p.telefono)}</span>`;
                if (p.email) contactoHtml += `<span class="contacto-item"><i class="fas fa-envelope"></i> ${escapeHtml(p.email)}</span>`;
                if (!contactoHtml) contactoHtml = '<span class="text-muted">—</span>';

                return `<tr>
                    <td class="proveedor-nombre-cell">${escapeHtml(p.nombre)}</td>
                    <td>${p.rif ? `<span class="proveedor-rif-badge">${escapeHtml(p.rif)}</span>` : '<span class="text-muted">—</span>'}</td>
                    <td class="proveedor-contacto-cell">${contactoHtml}</td>
                    <td class="proveedor-direccion-cell hide-mobile" title="${escapeHtml(p.direccion || '')}">${p.direccion ? escapeHtml(p.direccion) : '<span class="text-muted">—</span>'}</td>
                    <td>
                        <button class="btn-action btn-edit me-1" onclick="verProveedor(${p.id})" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-action btn-edit me-1" onclick="verProductosProveedor(${p.id}, '${escapeHtml(p.nombre).replace(/'/g, "\\\'")}')" title="Ver productos">
                            <i class="fas fa-boxes"></i>
                        </button>
                        <button class="btn-action btn-edit me-1" onclick="editarProveedor(${p.id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-action btn-delete" onclick="eliminarProveedor(${p.id}, '${escapeHtml(p.nombre).replace(/'/g, "\\\'")}')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            }).join('');

            // Render pagination
            if (typeof renderPaginationControls === 'function') {
                renderPaginationControls({
                    containerId: 'proveedoresPagination',
                    currentPage: currentPageProv,
                    totalPages: totalPages,
                    totalItems: proveedoresFiltrados.length,
                    itemsPerPage: proveedoresPerPage,
                    onPageChange: (page) => {
                        currentPageProv = page;
                        renderProveedores();
                    }
                });
            }
        }


        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function verProveedor(id) {
            const p = proveedores.find(prov => prov.id == id);
            if (!p) return;

            const infoRow = (icon, label, value) => {
                if (!value) return '';
                return `<div style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.6rem 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <i class="fas fa-${icon}" style="color: var(--buzz-primary-color, #ffa000); width: 18px; margin-top: 2px;"></i>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--buzz-text-muted, #888); text-transform: uppercase; letter-spacing: 0.5px;">${label}</div>
                        <div style="color: var(--buzz-text-primary, #fff);">${escapeHtml(value)}</div>
                    </div>
                </div>`;
            };

            let html = `<div class="modal-buzz" style="display: flex;">
                <div class="modal-content-buzz" style="max-width: 550px;">
                    <div class="modal-header-buzz">
                        <h5 class="font-primary text-primary"><i class="fas fa-truck"></i> ${escapeHtml(p.nombre)}</h5>
                        <button type="button" class="close-buzz" onclick="this.closest('.modal-buzz').remove()">&times;</button>
                    </div>
                    <div class="modal-body-buzz">
                        ${infoRow('id-card', 'RIF / Identificación', p.rif)}
                        ${infoRow('phone', 'Teléfono', p.telefono)}
                        ${infoRow('envelope', 'Email', p.email)}
                        ${infoRow('map-marker-alt', 'Dirección', p.direccion)}
                        ${infoRow('sticky-note', 'Notas', p.notas)}
                        ${!p.rif && !p.telefono && !p.email && !p.direccion && !p.notas ? '<div class="empty-state" style="padding:1.5rem;"><i class="fas fa-info-circle"></i><p>Sin información de contacto registrada.</p></div>' : ''}
                    </div>
                    <div class="modal-footer-buzz">
                        <button type="button" class="btn btn-outline-buzz" onclick="this.closest('.modal-buzz').remove()">
                            <i class="fas fa-times me-2"></i>Cerrar
                        </button>
                        <button type="button" class="btn btn-buzz" onclick="this.closest('.modal-buzz').remove(); editarProveedor(${p.id})">
                            <i class="fas fa-edit me-2"></i>Editar
                        </button>
                    </div>
                </div>
            </div>`;

            document.body.insertAdjacentHTML('beforeend', html);
        }

        async function verProductosProveedor(id, nombre) {
            try {
                const response = await fetch(`php/api_suppliers.php?action=productos&id=${id}`);
                const data = await response.json();

                let html = `<div class="modal-buzz" style="display: flex;">
                    <div class="modal-content-buzz" style="max-width: 700px;">
                        <div class="modal-header-buzz">
                            <h3><i class="fas fa-boxes me-2"></i>Productos de ${nombre}</h3>
                            <button type="button" class="close-buzz" onclick="this.closest('.modal-buzz').remove()">&times;</button>
                        </div>
                        <div class="modal-body-buzz">`;

                if (data.success && data.productos && data.productos.length > 0) {
                    html += `<table class="productos-table">
                        <thead><tr>
                            <th>SKU</th>
                            <th>Producto</th>
                            <th>Último Precio</th>
                            <th>Fecha</th>
                            <th>Cargo</th>
                        </tr></thead>
                        <tbody>`;
                    data.productos.forEach(p => {
                        html += `<tr>
                            <td>${escapeHtml(p.sku)}</td>
                            <td>${escapeHtml(p.producto_nombre)}</td>
                            <td class="precio-compra">${p.precio_compra ? '$' + parseFloat(p.precio_compra).toFixed(2) : '<span class="text-muted">-</span>'}</td>
                            <td>${p.fecha_registro || '<span class="text-muted">-</span>'}</td>
                            <td>${escapeHtml(p.cargo_codigo || '-')}</td>
                        </tr>`;
                    });
                    html += `</tbody></table>`;
                } else {
                    html += `<div class="empty-state"><i class="fas fa-box-open"></i>
                        <h5>Sin productos registrados</h5>
                        <p>Este proveedor aún no tiene precios de compra registrados.</p>
                    </div>`;
                }

                html += `</div>
                        <div class="modal-footer-buzz">
                            <button type="button" class="btn btn-outline-buzz" onclick="this.closest('.modal-buzz').remove()">
                                <i class="fas fa-times me-2"></i>Cerrar
                            </button>
                        </div>
                    </div>
                </div>`;

                document.body.insertAdjacentHTML('beforeend', html);
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al cargar productos del proveedor', 'danger');
            }
        }

        function abrirModalProveedor() {
            document.getElementById('proveedorId').value = '';
            document.getElementById('formProveedor').reset();
            document.getElementById('modalProveedorTitulo').textContent = 'Nuevo Proveedor';
            ['proveedorNombre', 'proveedorRif', 'proveedorEmail', 'proveedorTelefono'].forEach(id => {
                document.getElementById(id)?.classList.remove('is-invalid');
            });
            window.openModal('modalProveedor');
        }

        function cerrarModalProveedor(force) {
            window.closeModal('modalProveedor', !!force);
        }

        async function editarProveedor(id) {
            const proveedor = proveedores.find(p => p.id == id);
            if (!proveedor) return;

            ['proveedorNombre', 'proveedorRif', 'proveedorEmail', 'proveedorTelefono'].forEach(fid => {
                document.getElementById(fid)?.classList.remove('is-invalid');
            });
            document.getElementById('proveedorId').value = proveedor.id;
            document.getElementById('proveedorNombre').value = proveedor.nombre || '';
            document.getElementById('proveedorRif').value = proveedor.rif || '';
            parseAndSetPhoneFields(proveedor.telefono, 'proveedorPais', 'proveedorTelefono');
            document.getElementById('proveedorEmail').value = proveedor.email || '';
            document.getElementById('proveedorDireccion').value = proveedor.direccion || '';
            document.getElementById('proveedorNotas').value = proveedor.notas || '';
            document.getElementById('modalProveedorTitulo').textContent = 'Editar Proveedor';
            window.openModal('modalProveedor');
        }

        async function guardarProveedor() {
            const form = document.getElementById('formProveedor');
            if (typeof validateFormWithToast === 'function' && !validateFormWithToast(form)) return;

            const id = document.getElementById('proveedorId').value;
            const nombre = document.getElementById('proveedorNombre').value.trim();
            const rif = document.getElementById('proveedorRif').value.trim();

            if (rif && typeof isValidVenezuelaRif === 'function') {
                const rifCheck = isValidVenezuelaRif(rif);
                if (!rifCheck.valid) {
                    showToast(rifCheck.message, 'warning');
                    document.getElementById('proveedorRif').classList.add('is-invalid');
                    document.getElementById('proveedorRif').focus();
                    return;
                }
            }

            const email = document.getElementById('proveedorEmail').value.trim();
            if (email && typeof isValidEmail === 'function') {
                const emailCheck = isValidEmail(email);
                if (!emailCheck.valid) {
                    showToast(emailCheck.message, 'warning');
                    document.getElementById('proveedorEmail').classList.add('is-invalid');
                    document.getElementById('proveedorEmail').focus();
                    return;
                }
            }

            const fullPhone = combinePhoneNumber('proveedorPais', 'proveedorTelefono');
            const countryCode = document.getElementById('proveedorPais').value;
            if (fullPhone && typeof isValidVenezuelaPhone === 'function') {
                const digits = fullPhone.replace(/\D/g, '');
                const phoneValid = (countryCode === '+58')
                    ? isValidVenezuelaPhone(fullPhone)
                    : (digits.length >= 10 ? { valid: true } : { valid: false, message: 'El teléfono debe tener al menos 10 dígitos.' });
                if (!phoneValid.valid) {
                    showToast(phoneValid.message, 'warning');
                    document.getElementById('proveedorTelefono').classList.add('is-invalid');
                    document.getElementById('proveedorTelefono').focus();
                    return;
                }
            }

            const data = {
                nombre: nombre,
                rif: rif,
                telefono: combinePhoneNumber('proveedorPais', 'proveedorTelefono'),
                email: email,
                direccion: document.getElementById('proveedorDireccion').value.trim(),
                notas: document.getElementById('proveedorNotas').value.trim()
            };

            try {
                const url = id ? `php/api_suppliers.php?id=${id}` : 'php/api_suppliers.php';
                const method = id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    cerrarModalProveedor(true);
                    cargarProveedores();
                } else {
                    showToast('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error al guardar proveedor', 'danger');
            }
        }

        function eliminarProveedor(id, nombre) {
            showConfirmation(
                'Eliminar Proveedor',
                `¿Estás seguro de eliminar al proveedor "${nombre}"?`,
                async function() {
                    try {
                        const response = await fetch(`php/api_suppliers.php?id=${id}`, {
                            method: 'DELETE'
                        });
                        const result = await response.json();

                        if (result.success) {
                            showToast(result.message, 'success');
                            cargarProveedores();
                        } else {
                            showToast('Error: ' + result.message, 'danger');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showToast('Error al eliminar proveedor', 'danger');
                    }
                }
            );
        }

        // Helper function to combine country code and phone number
        function combinePhoneNumber(countryCodeId, phoneId) {
            const countryCode = document.getElementById(countryCodeId).value;
            let phoneNumber = document.getElementById(phoneId).value.trim();
            // Remove leading zero if present
            if (phoneNumber.startsWith("0")) {
                phoneNumber = phoneNumber.substring(1);
            }
            return countryCode + phoneNumber;
        }

        // Helper function to parse stored phone and set fields
        function parseAndSetPhoneFields(fullPhone, countryCodeId, phoneId) {
            if (!fullPhone) return;

            const countryCodes = ["+58", "+1", "+57", "+54", "+34", "+52", "+55"];
            let matchedCode = "+58"; // Default
            let localNumber = fullPhone;

            for (const code of countryCodes) {
                if (fullPhone.startsWith(code)) {
                    matchedCode = code;
                    localNumber = fullPhone.substring(code.length);
                    break;
                }
            }

            document.getElementById(countryCodeId).value = matchedCode;
            document.getElementById(phoneId).value = localNumber;
        }
    </script>
</body>
</html>
