/**
 * Catalogs Module JS - Redesigned
 * Grid layout with chips, color swatches, and brand lists
 */

// Store all catalog data
const catalogsData = {
    categorias: [],
    marcas: [],
    colores: [],
    materiales: []
};

// DOM Elements
const searchInput = document.getElementById('catalogSearchInput');

// Colors hex sync + preview
const itemHexInput = document.getElementById('itemHex');
const itemHexTextInput = document.getElementById('itemHexText');
const colorPreviewSwatch = document.getElementById('colorPreviewSwatch');

itemHexInput?.addEventListener('input', (e) => {
    const color = e.target.value.toUpperCase();
    itemHexTextInput.value = color;
    if (colorPreviewSwatch) colorPreviewSwatch.style.backgroundColor = color;
});

itemHexTextInput?.addEventListener('input', (e) => {
    const val = e.target.value;
    if (/^#[0-9A-F]{6}$/i.test(val)) {
        itemHexInput.value = val;
        if (colorPreviewSwatch) colorPreviewSwatch.style.backgroundColor = val;
    }
});

/**
 * Toggle card collapse
 */
window.toggleCard = function(header) {
    const card = header.closest('.catalog-card');
    card.classList.toggle('collapsed');
}

/**
 * Fetch all catalogs data
 */
async function fetchAllCatalogs() {
    const catalogs = ['categorias', 'marcas', 'colores', 'materiales'];

    try {
        await Promise.all(catalogs.map(async (catalog) => {
            const response = await fetch(`php/catalogos_actions.php?catalog=${catalog}`);
            const result = await response.json();
            if (result.success) {
                catalogsData[catalog] = result.data || [];
            }
        }));

        renderAllCatalogs();
    } catch (error) {
        console.error('Fetch error:', error);
        showToast('Error cargando catálogos', 'error');
    }
}

/**
 * Render all catalogs
 */
function renderAllCatalogs(filter = '') {
    renderCategories(filter);
    renderBrands(filter);
    renderColors(filter);
    renderMaterials(filter);
}

/**
 * Filter data by search term
 */
function filterData(data, filter, fields = ['nombre']) {
    if (!filter) return data;
    const term = filter.toLowerCase();
    return data.filter(item =>
        fields.some(field => (item[field] || '').toLowerCase().includes(term))
    );
}

/**
 * Render Categories as chips
 */
function renderCategories(filter = '') {
    const container = document.getElementById('body-categorias');
    const data = filterData(catalogsData.categorias, filter, ['nombre', 'descripcion']);

    document.getElementById('count-categorias').textContent = catalogsData.categorias.length;

    if (data.length === 0) {
        container.innerHTML = `
            <div class="catalog-empty">
                <i class="fas fa-tags"></i>
                <p>${filter ? 'Sin resultados' : 'No hay categorías'}</p>
            </div>
        `;
        return;
    }

    container.innerHTML = data.map(item => `
        <div class="chip" title="${escapeHtml(item.descripcion || '')}">
            <span>${escapeHtml(item.nombre)}</span>
            <button class="chip-edit" onclick="openEditModal('categorias', ${item.id})" title="Editar">
                <i class="fas fa-pen"></i>
            </button>
            <button class="chip-delete" onclick="deleteItem('categorias', ${item.id})" title="Eliminar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

/**
 * Render Brands as list
 */
function renderBrands(filter = '') {
    const container = document.getElementById('body-marcas');
    const data = filterData(catalogsData.marcas, filter, ['nombre', 'descripcion', 'sitio_web']);

    document.getElementById('count-marcas').textContent = catalogsData.marcas.length;

    if (data.length === 0) {
        container.innerHTML = `
            <div class="catalog-empty">
                <i class="fas fa-copyright"></i>
                <p>${filter ? 'Sin resultados' : 'No hay marcas'}</p>
            </div>
        `;
        return;
    }

    container.innerHTML = data.map(item => `
        <div class="brand-item">
            <div class="brand-info">
                <span class="brand-name">${escapeHtml(item.nombre)}</span>
                <span class="brand-desc">${escapeHtml(item.descripcion || '')}</span>
            </div>
            ${item.sitio_web ? `
                <a href="${item.sitio_web}" target="_blank" class="brand-link" title="Visitar sitio">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            ` : ''}
            <div class="brand-actions">
                <button class="color-action-btn" onclick="openEditModal('marcas', ${item.id})" title="Editar">
                    <i class="fas fa-pen"></i>
                </button>
                <button class="color-action-btn delete" onclick="deleteItem('marcas', ${item.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

/**
 * Render Colors as visual grid
 */
function renderColors(filter = '') {
    const container = document.getElementById('body-colores');
    const data = filterData(catalogsData.colores, filter, ['nombre', 'codigo_hex']);

    document.getElementById('count-colores').textContent = catalogsData.colores.length;

    if (data.length === 0) {
        container.innerHTML = `
            <div class="catalog-empty">
                <i class="fas fa-palette"></i>
                <p>${filter ? 'Sin resultados' : 'No hay colores'}</p>
            </div>
        `;
        return;
    }

    container.innerHTML = data.map(item => `
        <div class="color-item" onclick="openEditModal('colores', ${item.id})">
            <div class="color-swatch" style="background-color: ${item.codigo_hex || '#ccc'}"></div>
            <div class="color-info">
                <span class="color-name">${escapeHtml(item.nombre)}</span>
                <span class="color-hex">${item.codigo_hex || '-'}</span>
            </div>
            <div class="color-actions">
                <button class="color-action-btn delete" onclick="event.stopPropagation(); deleteItem('colores', ${item.id})" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

/**
 * Render Materials as chips
 */
function renderMaterials(filter = '') {
    const container = document.getElementById('body-materiales');
    const data = filterData(catalogsData.materiales, filter, ['nombre']);

    document.getElementById('count-materiales').textContent = catalogsData.materiales.length;

    if (data.length === 0) {
        container.innerHTML = `
            <div class="catalog-empty">
                <i class="fas fa-box-open"></i>
                <p>${filter ? 'Sin resultados' : 'No hay materiales'}</p>
            </div>
        `;
        return;
    }

    container.innerHTML = data.map(item => `
        <div class="chip">
            <span>${escapeHtml(item.nombre)}</span>
            <button class="chip-edit" onclick="openEditModal('materiales', ${item.id})" title="Editar">
                <i class="fas fa-pen"></i>
            </button>
            <button class="chip-delete" onclick="deleteItem('materiales', ${item.id})" title="Eliminar">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

/**
 * Modal Functions
 */
const modalIcons = {
    'categorias': 'fa-tags',
    'marcas': 'fa-copyright',
    'colores': 'fa-palette',
    'materiales': 'fa-box-open'
};

const modalLabels = {
    create: {
        'categorias': 'Nueva Categoría',
        'marcas': 'Nueva Marca',
        'colores': 'Nuevo Color',
        'materiales': 'Nuevo Material'
    },
    edit: {
        'categorias': 'Editar Categoría',
        'marcas': 'Editar Marca',
        'colores': 'Editar Color',
        'materiales': 'Editar Material'
    }
};

function setModalHeader(catalog, isEdit = false) {
    const icon = modalIcons[catalog] || 'fa-edit';
    const title = isEdit ? modalLabels.edit[catalog] : modalLabels.create[catalog];

    document.getElementById('modalIcon').innerHTML = `<i class="fas ${icon}"></i>`;
    document.getElementById('modalTitleText').textContent = title || 'Item';
}

function updateColorPreview(color) {
    const swatch = document.getElementById('colorPreviewSwatch');
    if (swatch) {
        swatch.style.backgroundColor = color;
    }
}

window.openCreateModal = function(catalog) {
    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = '';
    document.getElementById('itemCatalog').value = catalog;

    setModalHeader(catalog, false);
    toggleFields(catalog);

    // Default color for new colors
    if (catalog === 'colores') {
        const defaultColor = '#FFA000';
        document.getElementById('itemHex').value = defaultColor;
        document.getElementById('itemHexText').value = defaultColor;
        updateColorPreview(defaultColor);
    }

    window.openModal('itemModal');
}

window.openEditModal = function(catalog, id) {
    const item = catalogsData[catalog].find(i => i.id == id);
    if (!item) return;

    document.getElementById('itemForm').reset();
    document.getElementById('itemId').value = item.id;
    document.getElementById('itemCatalog').value = catalog;
    document.getElementById('itemName').value = item.nombre;

    if (catalog === 'categorias' || catalog === 'marcas') {
        document.getElementById('itemDescription').value = item.descripcion || '';
        if (catalog === 'marcas') {
            document.getElementById('itemWeb').value = item.sitio_web || '';
        }
    } else if (catalog === 'colores') {
        const color = item.codigo_hex || '#FFA000';
        document.getElementById('itemHex').value = color;
        document.getElementById('itemHexText').value = color.toUpperCase();
        updateColorPreview(color);
    }

    setModalHeader(catalog, true);
    toggleFields(catalog);
    window.openModal('itemModal');
}

window.closeItemModal = function() {
    window.closeModal('itemModal');
}

function toggleFields(catalog) {
    // Hide all special fields
    document.querySelectorAll('.field-desc').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.field-web').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.field-color').forEach(el => el.style.display = 'none');

    // Show by catalog
    if (catalog === 'categorias' || catalog === 'marcas') {
        document.querySelectorAll('.field-desc').forEach(el => el.style.display = 'block');
        if (catalog === 'marcas') {
            document.querySelectorAll('.field-web').forEach(el => el.style.display = 'block');
        }
    } else if (catalog === 'colores') {
        document.querySelectorAll('.field-color').forEach(el => el.style.display = 'block');
    }
}

/**
 * Save Item (POST/PUT)
 */
window.saveItem = async function() {
    const id = document.getElementById('itemId').value;
    const catalog = document.getElementById('itemCatalog').value;

    const data = {
        catalog: catalog,
        id: id || null,
        nombre: document.getElementById('itemName').value.trim()
    };

    if (catalog === 'categorias' || catalog === 'marcas') {
        data.descripcion = document.getElementById('itemDescription').value.trim();
        if (catalog === 'marcas') {
            data.sitio_web = document.getElementById('itemWeb').value.trim();
        }
    } else if (catalog === 'colores') {
        data.codigo_hex = document.getElementById('itemHex').value;
    }

    try {
        const response = await fetch('php/catalogos_actions.php', {
            method: id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        if (result.success) {
            showToast(result.message, 'success');
            closeItemModal();
            fetchAllCatalogs();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error al guardar', 'error');
    }
}

/**
 * Delete Item
 */
window.deleteItem = async function(catalog, id) {
    if (!confirm('¿Estás seguro de eliminar este registro?')) return;

    try {
        const response = await fetch(`php/catalogos_actions.php?catalog=${catalog}&id=${id}`, {
            method: 'DELETE'
        });

        const result = await response.json();
        if (result.success) {
            showToast(result.message, 'success');
            fetchAllCatalogs();
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        showToast('Error al eliminar', 'error');
    }
}

/**
 * Search functionality
 */
let searchTimeout;
searchInput?.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        renderAllCatalogs(e.target.value.trim());
    }, 200);
});

/**
 * HTML escape
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Init
document.addEventListener('DOMContentLoaded', fetchAllCatalogs);
