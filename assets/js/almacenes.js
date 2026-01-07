/**
 * Almacenes Module JavaScript
 * Sistema Multi-Almacén BuzzDM
 */

// State
let almacenes = [];
let selectedAlmacenId = null;
let ubicaciones = [];

// DOM Elements
const almacenesGrid = document.getElementById('almacenesGrid');
const ubicacionesSection = document.getElementById('ubicacionesSection');
const ubicacionesTableBody = document.getElementById('ubicacionesTableBody');
const showInactivos = document.getElementById('showInactivos');

// Stats elements
const totalAlmacenesEl = document.getElementById('totalAlmacenes');
const totalUbicacionesEl = document.getElementById('totalUbicaciones');
const totalProductosEl = document.getElementById('totalProductos');
const almacenPrincipalEl = document.getElementById('almacenPrincipal');

// Modal elements - Almacén
const almacenIdInput = document.getElementById('almacenId');
const almacenCodigoInput = document.getElementById('almacenCodigo');
const almacenNombreInput = document.getElementById('almacenNombre');
const almacenTipoInput = document.getElementById('almacenTipo');
const almacenTelefonoInput = document.getElementById('almacenTelefono');
const almacenDireccionInput = document.getElementById('almacenDireccion');
const almacenPrincipalCheck = document.getElementById('almacenPrincipalCheck');
const almacenActivoCheck = document.getElementById('almacenActivo');
const almacenNotasInput = document.getElementById('almacenNotas');

// Modal elements - Ubicación
const ubicacionIdInput = document.getElementById('ubicacionId');
const ubicacionAlmacenIdInput = document.getElementById('ubicacionAlmacenId');
const ubicacionCodigoInput = document.getElementById('ubicacionCodigo');
const ubicacionNombreInput = document.getElementById('ubicacionNombre');
const ubicacionPasilloInput = document.getElementById('ubicacionPasillo');
const ubicacionEstanteInput = document.getElementById('ubicacionEstante');
const ubicacionNivelInput = document.getElementById('ubicacionNivel');
const ubicacionCapacidadInput = document.getElementById('ubicacionCapacidad');
const ubicacionActivoCheck = document.getElementById('ubicacionActivo');

// ========================================
// FETCH FUNCTIONS
// ========================================

async function fetchAlmacenes() {
    try {
        const showInactive = showInactivos?.checked ? '&show_inactive=true' : '';
        const response = await fetch(`php/almacenes_actions.php?${showInactive}`);
        const data = await response.json();

        if (data.success) {
            almacenes = data.almacenes || [];
            renderAlmacenes();
            updateStats(data.stats);
        } else {
            showToast(data.message || 'Error cargando almacenes', 'error');
        }
    } catch (error) {
        console.error('Error fetching almacenes:', error);
        showToast('Error de conexión', 'error');
    }
}

async function fetchUbicaciones(almacenId) {
    try {
        const response = await fetch(`php/ubicaciones_actions.php?almacen_id=${almacenId}`);
        const data = await response.json();

        if (data.success) {
            ubicaciones = data.ubicaciones || [];
            renderUbicaciones();
        } else {
            showToast(data.message || 'Error cargando ubicaciones', 'error');
        }
    } catch (error) {
        console.error('Error fetching ubicaciones:', error);
        showToast('Error de conexión', 'error');
    }
}

// ========================================
// RENDER FUNCTIONS
// ========================================

function renderAlmacenes() {
    if (almacenes.length === 0) {
        almacenesGrid.innerHTML = `
            <div class="almacenes-empty">
                <i class="fas fa-warehouse"></i>
                <h5>No hay almacenes</h5>
                <p>Crea tu primer almacén para comenzar</p>
            </div>
        `;
        return;
    }

    almacenesGrid.innerHTML = almacenes.map(a => `
        <div class="almacen-card ${a.id == selectedAlmacenId ? 'selected' : ''} ${!a.activo ? 'inactivo' : ''}"
             onclick="selectAlmacen(${a.id})" data-id="${a.id}">

            <div class="almacen-actions">
                <button class="btn-action" onclick="event.stopPropagation(); openAlmacenModal(${a.id})" title="Editar">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn-action btn-delete" onclick="event.stopPropagation(); deleteAlmacen(${a.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>

            <div class="almacen-header">
                <div class="almacen-icon">
                    <i class="fas ${getAlmacenIcon(a.tipo)}"></i>
                </div>
                <div class="almacen-badges">
                    ${a.es_principal ? '<span class="badge-principal"><i class="fas fa-star"></i> Principal</span>' : ''}
                    <span class="badge-tipo">${capitalize(a.tipo)}</span>
                </div>
            </div>

            <div class="almacen-body">
                <div class="almacen-codigo">${escapeHtml(a.codigo)}</div>
                <div class="almacen-nombre">${escapeHtml(a.nombre)}</div>
                ${a.direccion ? `
                    <div class="almacen-direccion">
                        <i class="fas fa-map-marker-alt"></i>
                        ${escapeHtml(a.direccion)}
                    </div>
                ` : ''}
            </div>

            <div class="almacen-stats">
                <div class="almacen-stat">
                    <div class="almacen-stat-value">${a.total_ubicaciones || 0}</div>
                    <div class="almacen-stat-label">Ubicaciones</div>
                </div>
                <div class="almacen-stat">
                    <div class="almacen-stat-value">${a.total_productos || 0}</div>
                    <div class="almacen-stat-label">Productos</div>
                </div>
            </div>
        </div>
    `).join('');
}

function renderUbicaciones() {
    if (ubicaciones.length === 0) {
        ubicacionesTableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="fas fa-map-marker-alt fa-2x mb-2" style="opacity: 0.3;"></i>
                    <br>No hay ubicaciones definidas
                </td>
            </tr>
        `;
        return;
    }

    ubicacionesTableBody.innerHTML = ubicaciones.map(u => `
        <tr class="${!u.activo ? 'opacity-50' : ''}">
            <td><span class="ubicacion-codigo">${escapeHtml(u.codigo)}</span></td>
            <td>${escapeHtml(u.nombre || '-')}</td>
            <td>${escapeHtml(u.pasillo || '-')}</td>
            <td>${escapeHtml(u.estante || '-')}</td>
            <td>${escapeHtml(u.nivel || '-')}</td>
            <td>${u.capacidad_maxima || '<span class="text-muted">∞</span>'}</td>
            <td><span class="badge bg-secondary">${u.productos_count || 0}</span></td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-buzz" onclick="openUbicacionModal(${u.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteUbicacion(${u.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function updateStats(stats) {
    if (!stats) return;

    totalAlmacenesEl.textContent = stats.total_almacenes || 0;
    totalUbicacionesEl.textContent = stats.total_ubicaciones || 0;
    totalProductosEl.textContent = stats.total_productos || 0;
    almacenPrincipalEl.textContent = stats.almacen_principal || '-';
}

// ========================================
// ALMACEN MODAL FUNCTIONS
// ========================================

window.openAlmacenModal = function(almacenId = null) {
    // Reset form
    almacenIdInput.value = '';
    almacenCodigoInput.value = '';
    almacenNombreInput.value = '';
    almacenTipoInput.value = 'almacen';
    almacenTelefonoInput.value = '';
    almacenDireccionInput.value = '';
    almacenPrincipalCheck.checked = false;
    almacenActivoCheck.checked = true;
    almacenNotasInput.value = '';

    if (almacenId) {
        // Edit mode
        window.setTitle('almacenModal', 'Editar Almacén');
        const almacen = almacenes.find(a => a.id == almacenId);
        if (almacen) {
            almacenIdInput.value = almacen.id;
            almacenCodigoInput.value = almacen.codigo;
            almacenNombreInput.value = almacen.nombre;
            almacenTipoInput.value = almacen.tipo;
            almacenTelefonoInput.value = almacen.telefono || '';
            almacenDireccionInput.value = almacen.direccion || '';
            almacenPrincipalCheck.checked = almacen.es_principal == 1;
            almacenActivoCheck.checked = almacen.activo == 1;
            almacenNotasInput.value = almacen.notas || '';
        }
    } else {
        window.setTitle('almacenModal', 'Nuevo Almacén');
    }

    window.openModal('almacenModal');
};

window.closeAlmacenModal = function() {
    window.closeModal('almacenModal');
};

window.saveAlmacen = async function() {
    const id = almacenIdInput.value;
    const codigo = almacenCodigoInput.value.trim();
    const nombre = almacenNombreInput.value.trim();

    if (!codigo || !nombre) {
        showToast('Código y nombre son requeridos', 'warning');
        return;
    }

    const data = {
        id: id || null,
        codigo: codigo,
        nombre: nombre,
        tipo: almacenTipoInput.value,
        telefono: almacenTelefonoInput.value.trim(),
        direccion: almacenDireccionInput.value.trim(),
        es_principal: almacenPrincipalCheck.checked,
        activo: almacenActivoCheck.checked,
        notas: almacenNotasInput.value.trim()
    };

    try {
        const method = id ? 'PUT' : 'POST';
        const url = id ? `php/almacenes_actions.php?id=${id}` : 'php/almacenes_actions.php';

        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message || 'Almacén guardado', 'success');
            closeAlmacenModal();
            fetchAlmacenes();
        } else {
            showToast(result.message || 'Error al guardar', 'error');
        }
    } catch (error) {
        console.error('Error saving almacen:', error);
        showToast('Error de conexión', 'error');
    }
};

window.deleteAlmacen = async function(id) {
    if (!confirm('¿Está seguro de desactivar este almacén?')) return;

    try {
        const response = await fetch(`php/almacenes_actions.php?id=${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message || 'Almacén desactivado', 'success');
            if (selectedAlmacenId == id) {
                selectedAlmacenId = null;
                ubicacionesSection.style.display = 'none';
            }
            fetchAlmacenes();
        } else {
            showToast(result.message || 'Error al desactivar', 'error');
        }
    } catch (error) {
        console.error('Error deleting almacen:', error);
        showToast('Error de conexión', 'error');
    }
};

// ========================================
// UBICACIÓN MODAL FUNCTIONS
// ========================================

window.selectAlmacen = function(almacenId) {
    selectedAlmacenId = almacenId;

    // Update selected state visually
    document.querySelectorAll('.almacen-card').forEach(card => {
        card.classList.toggle('selected', card.dataset.id == almacenId);
    });

    // Update section title
    const almacen = almacenes.find(a => a.id == almacenId);
    document.getElementById('selectedAlmacenName').textContent = almacen?.nombre || '-';

    // Show ubicaciones section and fetch data
    ubicacionesSection.style.display = 'block';
    fetchUbicaciones(almacenId);
};

window.openUbicacionModal = function(ubicacionId = null) {
    if (!selectedAlmacenId) {
        showToast('Selecciona un almacén primero', 'warning');
        return;
    }

    // Reset form
    ubicacionIdInput.value = '';
    ubicacionAlmacenIdInput.value = selectedAlmacenId;
    ubicacionCodigoInput.value = '';
    ubicacionNombreInput.value = '';
    ubicacionPasilloInput.value = '';
    ubicacionEstanteInput.value = '';
    ubicacionNivelInput.value = '';
    ubicacionCapacidadInput.value = '';
    ubicacionActivoCheck.checked = true;

    if (ubicacionId) {
        window.setTitle('ubicacionModal', 'Editar Ubicación');
        const ubicacion = ubicaciones.find(u => u.id == ubicacionId);
        if (ubicacion) {
            ubicacionIdInput.value = ubicacion.id;
            ubicacionCodigoInput.value = ubicacion.codigo;
            ubicacionNombreInput.value = ubicacion.nombre || '';
            ubicacionPasilloInput.value = ubicacion.pasillo || '';
            ubicacionEstanteInput.value = ubicacion.estante || '';
            ubicacionNivelInput.value = ubicacion.nivel || '';
            ubicacionCapacidadInput.value = ubicacion.capacidad_maxima || '';
            ubicacionActivoCheck.checked = ubicacion.activo == 1;
        }
    } else {
        window.setTitle('ubicacionModal', 'Nueva Ubicación');
    }

    window.openModal('ubicacionModal');
};

window.closeUbicacionModal = function() {
    window.closeModal('ubicacionModal');
};

window.saveUbicacion = async function() {
    const id = ubicacionIdInput.value;
    const codigo = ubicacionCodigoInput.value.trim();

    if (!codigo) {
        showToast('El código es requerido', 'warning');
        return;
    }

    const data = {
        id: id || null,
        almacen_id: ubicacionAlmacenIdInput.value,
        codigo: codigo,
        nombre: ubicacionNombreInput.value.trim(),
        pasillo: ubicacionPasilloInput.value.trim(),
        estante: ubicacionEstanteInput.value.trim(),
        nivel: ubicacionNivelInput.value.trim(),
        capacidad_maxima: ubicacionCapacidadInput.value ? parseInt(ubicacionCapacidadInput.value) : null,
        activo: ubicacionActivoCheck.checked
    };

    try {
        const method = id ? 'PUT' : 'POST';
        const url = id ? `php/ubicaciones_actions.php?id=${id}` : 'php/ubicaciones_actions.php';

        const response = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message || 'Ubicación guardada', 'success');
            closeUbicacionModal();
            fetchUbicaciones(selectedAlmacenId);
            fetchAlmacenes(); // Refresh stats
        } else {
            showToast(result.message || 'Error al guardar', 'error');
        }
    } catch (error) {
        console.error('Error saving ubicacion:', error);
        showToast('Error de conexión', 'error');
    }
};

window.deleteUbicacion = async function(id) {
    if (!confirm('¿Está seguro de eliminar esta ubicación?')) return;

    try {
        const response = await fetch(`php/ubicaciones_actions.php?id=${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();

        if (result.success) {
            showToast(result.message || 'Ubicación eliminada', 'success');
            fetchUbicaciones(selectedAlmacenId);
            fetchAlmacenes(); // Refresh stats
        } else {
            showToast(result.message || 'Error al eliminar', 'error');
        }
    } catch (error) {
        console.error('Error deleting ubicacion:', error);
        showToast('Error de conexión', 'error');
    }
};

// ========================================
// HELPER FUNCTIONS
// ========================================

function getAlmacenIcon(tipo) {
    const icons = {
        'almacen': 'fa-warehouse',
        'bodega': 'fa-box',
        'tienda': 'fa-store',
        'deposito': 'fa-cubes',
        'otro': 'fa-building'
    };
    return icons[tipo] || 'fa-warehouse';
}

function capitalize(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========================================
// EVENT LISTENERS
// ========================================

showInactivos?.addEventListener('change', fetchAlmacenes);

// Keyboard shortcuts
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAlmacenModal();
        closeUbicacionModal();
    }
});

// ========================================
// INITIALIZATION
// ========================================

document.addEventListener('DOMContentLoaded', () => {
    fetchAlmacenes();
});
