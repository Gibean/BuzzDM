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
    <style>
        .proveedores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        .proveedor-card {
            background: var(--card-bg, #1a1f28);
            border: 1px solid var(--card-border, rgba(255,160,0,0.2));
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.2s ease;
        }
        .proveedor-card:hover {
            border-color: var(--buzz-primary-color, #ffa000);
            transform: translateY(-2px);
        }
        .proveedor-nombre {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--buzz-text-primary, #fff);
            margin-bottom: 0.5rem;
        }
        .proveedor-info {
            font-size: 0.875rem;
            color: var(--buzz-text-muted, #888);
            margin-bottom: 0.25rem;
        }
        .proveedor-info i {
            width: 20px;
            color: var(--buzz-primary-color, #ffa000);
        }
        .proveedor-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--card-border, rgba(255,160,0,0.2));
        }
        .proveedor-actions button {
            flex: 1;
        }
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
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,160,0,0.2);
        }
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        .page-title i {
            margin-right: 0.5rem;
            color: var(--buzz-primary-color, #ffa000);
        }
        .page-subtitle {
            margin: 0.25rem 0 0 0;
            color: var(--buzz-text-muted, #888);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php require_once 'php/sidebar.php'; ?>

    <main class="main-content">
        <!-- Top Navbar -->
        <nav class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-buzz me-3 d-md-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="font-primary mb-0">GESTIÓN DE PROVEEDORES</h1>
                <span class="text-muted ms-2 font-mono">/ PROVEEDORES</span>
            </div>
            <div class="d-flex align-items-center">
                <button class="btn btn-buzz" onclick="abrirModalProveedor()">
                    <i class="fas fa-plus"></i> Nuevo Proveedor
                </button>
            </div>
        </nav>

        <!-- Lista de proveedores -->
        <div id="proveedoresContainer">
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2 text-muted">Cargando proveedores...</p>
            </div>
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
            <form id="formProveedor">
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
                                <input type="text" class="form-control form-control-buzz" id="proveedorNombre" required placeholder="Nombre del proveedor">
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">RIF/Identificación</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="proveedorRif" placeholder="J-12345678-9">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control form-control-buzz" id="proveedorTelefono" placeholder="+58 412 1234567">
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
                                <input type="email" class="form-control form-control-buzz" id="proveedorEmail" placeholder="contacto@proveedor.com">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <div class="input-group">
                                <span class="input-group-text align-items-start pt-2"><i class="fas fa-map-marker-alt"></i></span>
                                <textarea class="form-control form-control-buzz" id="proveedorDireccion" rows="2" placeholder="Dirección del proveedor"></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <div class="input-group">
                                <span class="input-group-text align-items-start pt-2"><i class="fas fa-sticky-note"></i></span>
                                <textarea class="form-control form-control-buzz" id="proveedorNotas" rows="2" placeholder="Notas adicionales..."></textarea>
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
    <script src="assets/js/confirmation_modal.js"></script>
    <script>
        // =====================================================
        // PROVEEDORES MANAGEMENT
        // =====================================================

        let proveedores = [];

        document.addEventListener('DOMContentLoaded', function() {
            cargarProveedores();
        });

        async function cargarProveedores() {
            try {
                const response = await fetch('php/api_suppliers.php');
                const data = await response.json();

                if (data.success) {
                    proveedores = data.proveedores || [];
                    renderProveedores();
                } else {
                    showToast('Error al cargar proveedores: ' + data.message, 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Error de conexión', 'danger');
            }
        }

        function renderProveedores() {
            const container = document.getElementById('proveedoresContainer');

            if (!proveedores || proveedores.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-truck"></i>
                        <h5>No hay proveedores registrados</h5>
                        <p>Haz clic en "Nuevo Proveedor" para agregar el primero.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="proveedores-grid">
                    ${proveedores.map(p => `
                        <div class="proveedor-card">
                            <div class="proveedor-nombre">${escapeHtml(p.nombre)}</div>
                            ${p.rif ? `<div class="proveedor-info"><i class="fas fa-id-card"></i> ${escapeHtml(p.rif)}</div>` : ''}
                            ${p.telefono ? `<div class="proveedor-info"><i class="fas fa-phone"></i> ${escapeHtml(p.telefono)}</div>` : ''}
                            ${p.email ? `<div class="proveedor-info"><i class="fas fa-envelope"></i> ${escapeHtml(p.email)}</div>` : ''}
                            ${p.direccion ? `<div class="proveedor-info"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(p.direccion)}</div>` : ''}
                            <div class="proveedor-actions">
                                <button class="btn btn-outline-buzz btn-sm" onclick="editarProveedor(${p.id})">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn btn-outline-danger btn-sm" onclick="eliminarProveedor(${p.id}, '${escapeHtml(p.nombre).replace(/'/g, "\\'")}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function abrirModalProveedor() {
            document.getElementById('proveedorId').value = '';
            document.getElementById('formProveedor').reset();
            document.getElementById('modalProveedorTitulo').textContent = 'Nuevo Proveedor';
            document.getElementById('modalProveedor').style.display = 'block';
        }

        function cerrarModalProveedor() {
            document.getElementById('modalProveedor').style.display = 'none';
        }

        async function editarProveedor(id) {
            const proveedor = proveedores.find(p => p.id == id);
            if (!proveedor) return;

            document.getElementById('proveedorId').value = proveedor.id;
            document.getElementById('proveedorNombre').value = proveedor.nombre || '';
            document.getElementById('proveedorRif').value = proveedor.rif || '';
            document.getElementById('proveedorTelefono').value = proveedor.telefono || '';
            document.getElementById('proveedorEmail').value = proveedor.email || '';
            document.getElementById('proveedorDireccion').value = proveedor.direccion || '';
            document.getElementById('proveedorNotas').value = proveedor.notas || '';
            document.getElementById('modalProveedorTitulo').textContent = 'Editar Proveedor';
            document.getElementById('modalProveedor').style.display = 'block';
        }

        async function guardarProveedor() {
            const id = document.getElementById('proveedorId').value;
            const nombre = document.getElementById('proveedorNombre').value.trim();

            if (!nombre) {
                showToast('El nombre es obligatorio', 'warning');
                return;
            }

            const data = {
                nombre: nombre,
                rif: document.getElementById('proveedorRif').value.trim(),
                telefono: document.getElementById('proveedorTelefono').value.trim(),
                email: document.getElementById('proveedorEmail').value.trim(),
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
                    cerrarModalProveedor();
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
    </script>
</body>
</html>
