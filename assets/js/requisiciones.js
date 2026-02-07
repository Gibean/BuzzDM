/**
 * requisiciones.js
 * Purchase Requisitions Frontend Logic
 * Features: Inline editing, supplier suggestions, real-time summary
 */

// ===========================================
// GLOBAL STATE
// ===========================================
let currentPage = 1;
let filters = {};
let currentRequisicion = null;
let lineaIndex = 0;

// ===========================================
// INITIALIZATION
// ===========================================
document.addEventListener('DOMContentLoaded', () => {
    initEventListeners();
    loadRequisiciones();
    setDefaultDate();
});

function initEventListeners() {
    // Navigation
    document.getElementById('btnNuevaRequisicion').addEventListener('click', showFormularioNuevo);
    document.getElementById('btnCancelar').addEventListener('click', showHistorial);
    document.getElementById('btnFromLowStock').addEventListener('click', createFromLowStock);

    // Form
    document.getElementById('formRequisicion').addEventListener('submit', handleSubmit);
    document.getElementById('btnAddLinea').addEventListener('click', addProductRow);

    // Workflow actions
    document.getElementById('btnAprobar')?.addEventListener('click', aprobarRequisicion);
    document.getElementById('btnGenerarCargos')?.addEventListener('click', generarCargos);

    // Filters
    document.getElementById('filterSearch').addEventListener('input', debounce(applyFilters, 300));
    document.getElementById('filterEstado').addEventListener('change', applyFilters);
    document.getElementById('filterPrioridad').addEventListener('change', applyFilters);
    document.getElementById('btnLimpiarFiltros').addEventListener('click', clearFilters);

    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.product-search-cell')) {
            hideProductDropdown();
        }
    });
}

function setDefaultDate() {
    document.getElementById('fechaRequisicion').value = new Date().toISOString().split('T')[0];
}

// ===========================================
// LIST VIEW
// ===========================================
async function loadRequisiciones() {
    try {
        const params = new URLSearchParams({
            page: currentPage,
            ...filters
        });

        const response = await fetch(`php/api_requisiciones.php?${params}`);
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        renderTable(result.data);
        renderPagination(result.pages, result.page);
        document.getElementById('infoRequisiciones').textContent =
            `Mostrando ${result.data.length} de ${result.total} requisiciones`;

    } catch (error) {
        showToast('Error al cargar requisiciones: ' + error.message, 'danger');
    }
}

function renderTable(data) {
    const tbody = document.getElementById('tablaRequisiciones');

    if (!data.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No hay requisiciones</td></tr>';
        return;
    }

    tbody.innerHTML = data.map(req => `
        <tr data-id="${req.id}">
            <td><strong>${req.codigo}</strong></td>
            <td>${formatDate(req.fecha_solicitud)}</td>
            <td>${getPrioridadBadge(req.prioridad)}</td>
            <td>${req.total_lineas || 0} productos (${req.total_unidades || 0} uds)</td>
            <td>${getEstadoBadge(req.estado)}</td>
            <td>${req.usuario_nombre || '-'}</td>
            <td class="text-center">
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-buzz btn-sm" onclick="editRequisicion(${req.id})" title="Ver/Editar">
                        <i class="fas fa-eye"></i>
                    </button>
                    ${req.estado === 'pendiente' ? `
                        <button class="btn btn-outline-danger btn-sm" onclick="deleteRequisicion(${req.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function getPrioridadBadge(prioridad) {
    const badges = {
        'urgente': '<span class="badge bg-danger">🔴 Urgente</span>',
        'alta': '<span class="badge bg-warning text-dark">🟠 Alta</span>',
        'media': '<span class="badge bg-info text-dark">🟡 Media</span>',
        'baja': '<span class="badge bg-success">🟢 Baja</span>'
    };
    return badges[prioridad] || prioridad;
}

function getEstadoBadge(estado) {
    const badges = {
        'pendiente': '<span class="badge bg-secondary">Pendiente</span>',
        'aprobada': '<span class="badge bg-primary">Aprobada</span>',
        'rechazada': '<span class="badge bg-danger">Rechazada</span>',
        'procesada': '<span class="badge bg-success">Procesada</span>'
    };
    return badges[estado] || estado;
}

function renderPagination(totalPages, currentPage) {
    const container = document.getElementById('paginationRequisiciones');
    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';
    for (let i = 1; i <= totalPages; i++) {
        html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
        </li>`;
    }
    container.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    loadRequisiciones();
}

function applyFilters() {
    filters = {
        search: document.getElementById('filterSearch').value,
        estado: document.getElementById('filterEstado').value,
        prioridad: document.getElementById('filterPrioridad').value
    };
    currentPage = 1;
    loadRequisiciones();
}

function clearFilters() {
    document.getElementById('filterSearch').value = '';
    document.getElementById('filterEstado').value = '';
    document.getElementById('filterPrioridad').value = '';
    filters = {};
    currentPage = 1;
    loadRequisiciones();
}

// ===========================================
// FORM VIEW
// ===========================================
function showFormularioNuevo() {
    currentRequisicion = null;
    document.getElementById('requisicionId').value = '';
    document.getElementById('codigoRequisicion').value = '';
    document.getElementById('fechaRequisicion').value = new Date().toISOString().split('T')[0];
    document.getElementById('prioridadRequisicion').value = 'media';
    document.getElementById('estadoRequisicion').value = 'Pendiente';
    document.getElementById('notasRequisicion').value = '';
    document.getElementById('lineasRequisicion').innerHTML = '';
    lineaIndex = 0;

    // Add initial empty row
    addProductRow();

    // Show/hide buttons
    document.getElementById('btnAprobar').style.display = 'none';
    document.getElementById('btnGenerarCargos').style.display = 'none';

    updateSummary();
    showFormulario();
}

async function editRequisicion(id) {
    try {
        const response = await fetch(`php/api_requisiciones.php?id=${id}`);
        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        const req = result.requisicion;
        currentRequisicion = req;

        document.getElementById('requisicionId').value = req.id;
        document.getElementById('codigoRequisicion').value = req.codigo;
        document.getElementById('fechaRequisicion').value = req.fecha_solicitud;
        document.getElementById('prioridadRequisicion').value = req.prioridad;
        document.getElementById('estadoRequisicion').value = req.estado.charAt(0).toUpperCase() + req.estado.slice(1);
        document.getElementById('notasRequisicion').value = req.notas || '';

        // Load lines
        document.getElementById('lineasRequisicion').innerHTML = '';
        lineaIndex = 0;
        req.detalles.forEach(detalle => {
            addProductRow(detalle);
        });

        // Show/hide action buttons based on state
        const isPendiente = req.estado === 'pendiente';
        const isAprobada = req.estado === 'aprobada';
        const userRole = document.querySelector('meta[name="user-role"]')?.content || 'operador';
        const canApprove = ['admin', 'supervisor'].includes(userRole);

        document.getElementById('btnAprobar').style.display = isPendiente && canApprove ? '' : 'none';
        document.getElementById('btnGenerarCargos').style.display = isAprobada && canApprove ? '' : 'none';

        // Disable form if not editable
        const formInputs = document.querySelectorAll('#formRequisicion input:not([readonly]), #formRequisicion select, #formRequisicion textarea');
        formInputs.forEach(input => {
            input.disabled = !isPendiente;
        });
        document.getElementById('btnAddLinea').style.display = isPendiente ? '' : 'none';
        document.querySelector('#formRequisicion button[type="submit"]').style.display = isPendiente ? '' : 'none';

        updateSummary();
        showFormulario();

    } catch (error) {
        showToast('Error al cargar requisición: ' + error.message, 'danger');
    }
}

function showFormulario() {
    document.getElementById('vistaHistorial').style.display = 'none';
    document.getElementById('vistaFormulario').style.display = 'block';
}

function showHistorial() {
    document.getElementById('vistaFormulario').style.display = 'none';
    document.getElementById('vistaHistorial').style.display = 'block';
    loadRequisiciones();
}

// ===========================================
// PRODUCT ROWS (Inline Editing)
// ===========================================
function addProductRow(data = null) {
    const tbody = document.getElementById('lineasRequisicion');
    const idx = lineaIndex++;

    const row = document.createElement('tr');
    row.dataset.index = idx;
    row.innerHTML = `
        <td class="product-search-cell">
            <input type="hidden" class="product-id" name="lineas[${idx}][producto_id]" value="${data?.producto_id || ''}">
            <input type="hidden" class="proveedor-sugerido-id" name="lineas[${idx}][proveedor_sugerido_id]" value="${data?.proveedor_sugerido_id || ''}">
            <input type="text" class="form-control form-control-sm product-search"
                   placeholder="Buscar SKU..."
                   value="${data?.sku || ''}"
                   data-idx="${idx}"
                   autocomplete="off">
        </td>
        <td class="product-name">${data?.producto_nombre || '<span class="text-muted">-</span>'}</td>
        <td class="stock-actual text-center ${getStockClass(data?.stock_actual)}">${data?.stock_actual ?? '-'}</td>
        <td>
            <input type="number" class="form-control form-control-sm cantidad-input"
                   name="lineas[${idx}][cantidad_solicitada]"
                   value="${data?.cantidad_solicitada || 1}"
                   min="1" required>
        </td>
        <td class="proveedor-sugerido">
            ${data?.proveedor_sugerido_nombre
                ? `<span class="badge bg-success-subtle text-success">${data.proveedor_sugerido_nombre}</span>`
                : '<span class="badge bg-warning-subtle text-warning">Sin datos</span>'}
        </td>
        <td class="precio-sugerido text-end">${data?.precio_sugerido ? formatCurrency(data.precio_sugerido) : '-'}</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" title="Eliminar">
                <i class="fas fa-times"></i>
            </button>
        </td>
    `;

    tbody.appendChild(row);

    // Event listeners
    const searchInput = row.querySelector('.product-search');
    searchInput.addEventListener('input', debounce((e) => searchProducts(e.target), 300));
    searchInput.addEventListener('focus', (e) => searchProducts(e.target));

    row.querySelector('.cantidad-input').addEventListener('input', updateSummary);
    row.querySelector('.btn-remove-row').addEventListener('click', () => {
        row.remove();
        updateSummary();
    });

    if (!data) {
        searchInput.focus();
    }
}

function getStockClass(stock) {
    if (stock === null || stock === undefined) return '';
    if (stock === 0) return 'text-danger fw-bold';
    if (stock <= 5) return 'text-warning';
    return 'text-success';
}

async function searchProducts(input) {
    const query = input.value.trim();
    if (query.length < 2) {
        hideProductDropdown();
        return;
    }

    try {
        const response = await fetch(`php/api_products.php?action=search&q=${encodeURIComponent(query)}&activo=1`);
        const result = await response.json();

        if (result.success && result.products?.length) {
            showProductDropdown(input, result.products);
        } else {
            hideProductDropdown();
        }
    } catch (error) {
        console.error('Error searching products:', error);
    }
}

function showProductDropdown(input, products) {
    const dropdown = document.getElementById('productSearchDropdown');
    const rect = input.getBoundingClientRect();

    dropdown.style.left = rect.left + 'px';
    dropdown.style.top = (rect.bottom + window.scrollY) + 'px';
    dropdown.style.width = Math.max(300, rect.width) + 'px';

    dropdown.innerHTML = products.map(p => `
        <div class="autocomplete-item" data-product='${JSON.stringify(p)}'>
            <strong>${p.sku}</strong> - ${p.nombre}
            <span class="text-muted ms-2">(Stock: ${p.stock})</span>
        </div>
    `).join('');

    dropdown.style.display = 'block';

    // Click handlers
    dropdown.querySelectorAll('.autocomplete-item').forEach(item => {
        item.addEventListener('click', () => selectProduct(input, JSON.parse(item.dataset.product)));
    });
}

function hideProductDropdown() {
    document.getElementById('productSearchDropdown').style.display = 'none';
}

async function selectProduct(input, product) {
    const row = input.closest('tr');

    // Set product data
    row.querySelector('.product-id').value = product.id;
    row.querySelector('.product-search').value = product.sku;
    row.querySelector('.product-name').textContent = product.nombre;
    row.querySelector('.stock-actual').textContent = product.stock;
    row.querySelector('.stock-actual').className = 'stock-actual text-center ' + getStockClass(product.stock);

    // Get supplier suggestion
    try {
        const response = await fetch(`php/api_requisiciones.php?action=suggest&producto_id=${product.id}`);
        const result = await response.json();

        if (result.success && result.suggestion) {
            const s = result.suggestion;
            row.querySelector('.proveedor-sugerido-id').value = s.proveedor_id;
            row.querySelector('.proveedor-sugerido').innerHTML =
                `<span class="badge bg-success-subtle text-success" title="Fuente: ${s.fuente}">${s.proveedor_nombre}</span>`;
            row.querySelector('.precio-sugerido').textContent = formatCurrency(s.precio_compra);
        } else {
            row.querySelector('.proveedor-sugerido').innerHTML =
                '<span class="badge bg-warning-subtle text-warning">Sin historial</span>';
            row.querySelector('.precio-sugerido').textContent = '-';
        }
    } catch (error) {
        console.error('Error getting suggestion:', error);
    }

    hideProductDropdown();
    row.querySelector('.cantidad-input').focus();
    updateSummary();
}

// ===========================================
// SUMMARY PANEL
// ===========================================
function updateSummary() {
    const rows = document.querySelectorAll('#lineasRequisicion tr');
    let totalProductos = 0;
    let totalUnidades = 0;
    let costoEstimado = 0;
    const porProveedor = {};

    rows.forEach(row => {
        const productoId = row.querySelector('.product-id')?.value;
        if (!productoId) return;

        totalProductos++;

        const cantidad = parseInt(row.querySelector('.cantidad-input')?.value) || 0;
        totalUnidades += cantidad;

        const precioText = row.querySelector('.precio-sugerido')?.textContent || '0';
        const precio = parseFloat(precioText.replace(/[^0-9.-]/g, '')) || 0;
        const subtotal = cantidad * precio;
        costoEstimado += subtotal;

        // Group by supplier
        const provNombre = row.querySelector('.proveedor-sugerido .badge')?.textContent || 'Sin proveedor';
        if (!porProveedor[provNombre]) {
            porProveedor[provNombre] = { cantidad: 0, subtotal: 0 };
        }
        porProveedor[provNombre].cantidad += cantidad;
        porProveedor[provNombre].subtotal += subtotal;
    });

    document.getElementById('totalProductos').textContent = totalProductos;
    document.getElementById('totalUnidades').textContent = totalUnidades;
    document.getElementById('costoEstimado').textContent = formatCurrency(costoEstimado);

    // Render supplier summary
    const resumenDiv = document.getElementById('resumenProveedores');
    if (Object.keys(porProveedor).length === 0) {
        resumenDiv.innerHTML = '<div class="text-muted small text-center py-3">Sin productos</div>';
    } else {
        resumenDiv.innerHTML = Object.entries(porProveedor)
            .map(([nombre, data]) => `
                <div class="d-flex justify-content-between align-items-center mb-2 py-1 border-bottom">
                    <span class="small">${nombre}</span>
                    <span class="badge bg-primary">${formatCurrency(data.subtotal)}</span>
                </div>
            `).join('');
    }
}

// ===========================================
// FORM SUBMISSION
// ===========================================
async function handleSubmit(e) {
    e.preventDefault();

    const lineas = collectLineas();
    if (lineas.length === 0) {
        showToast('Debe agregar al menos un producto', 'warning');
        return;
    }

    const data = {
        fecha_solicitud: document.getElementById('fechaRequisicion').value,
        prioridad: document.getElementById('prioridadRequisicion').value,
        notas: document.getElementById('notasRequisicion').value,
        lineas: lineas
    };

    const id = document.getElementById('requisicionId').value;
    const method = id ? 'PUT' : 'POST';
    const url = id ? `php/api_requisiciones.php?id=${id}` : 'php/api_requisiciones.php';

    try {
        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        showToast(result.message || 'Requisición guardada', 'success');
        showHistorial();

    } catch (error) {
        showToast('Error: ' + error.message, 'danger');
    }
}

function collectLineas() {
    const rows = document.querySelectorAll('#lineasRequisicion tr');
    const lineas = [];

    rows.forEach(row => {
        const productoId = row.querySelector('.product-id')?.value;
        const cantidad = parseInt(row.querySelector('.cantidad-input')?.value) || 0;

        if (productoId && cantidad > 0) {
            lineas.push({
                producto_id: parseInt(productoId),
                cantidad_solicitada: cantidad,
                proveedor_sugerido_id: row.querySelector('.proveedor-sugerido-id')?.value || null
            });
        }
    });

    return lineas;
}

// ===========================================
// WORKFLOW ACTIONS
// ===========================================
async function createFromLowStock() {
    if (!confirm('¿Crear requisición automática con todos los productos con stock bajo?')) return;

    try {
        const response = await fetch('php/api_requisiciones.php?action=fromLowStock', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prioridad: 'alta' })
        });
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        showToast(`Requisición ${result.codigo} creada con productos de stock bajo`, 'success');
        editRequisicion(result.requisicion_id);

    } catch (error) {
        showToast('Error: ' + error.message, 'danger');
    }
}

async function aprobarRequisicion() {
    const id = document.getElementById('requisicionId').value;
    if (!id || !confirm('¿Aprobar esta requisición?')) return;

    try {
        const response = await fetch(`php/api_requisiciones.php?action=approve&id=${id}`, {
            method: 'POST'
        });
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        showToast('Requisición aprobada', 'success');
        editRequisicion(id); // Reload to update buttons

    } catch (error) {
        showToast('Error: ' + error.message, 'danger');
    }
}

async function generarCargos() {
    const id = document.getElementById('requisicionId').value;
    if (!id || !confirm('¿Generar cargos borrador desde esta requisición?')) return;

    try {
        const response = await fetch(`php/api_requisiciones.php?action=generate&id=${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({})
        });
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        showToast(result.mensaje || 'Cargos generados', 'success');

        // Offer to navigate to cargos
        if (confirm(`Se generaron ${result.cargos.length} cargo(s). ¿Ir a Cargos?`)) {
            window.location.href = 'cargos_existencias';
        } else {
            showHistorial();
        }

    } catch (error) {
        showToast('Error: ' + error.message, 'danger');
    }
}

async function deleteRequisicion(id) {
    if (!confirm('¿Eliminar esta requisición?')) return;

    try {
        const response = await fetch(`php/api_requisiciones.php?id=${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (!result.success) throw new Error(result.message);

        showToast('Requisición eliminada', 'success');
        loadRequisiciones();

    } catch (error) {
        showToast('Error: ' + error.message, 'danger');
    }
}

// ===========================================
// UTILITIES
// ===========================================
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('es-VE', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatCurrency(amount) {
    return '$' + parseFloat(amount || 0).toFixed(2);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}


