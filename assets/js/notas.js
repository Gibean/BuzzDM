/**
 * BuzzDM - Notas Kanban JS (Enhanced Trello Style)
 */

document.addEventListener('DOMContentLoaded', () => {
    fetchNotes();
    setupEventListeners();
});

let allNotes = [];
let activeLabels = []; // Temporary local labels
let activeMembers = []; // Temporary local members

const AVAILABLE_LABELS = [
    { color: '#61bd4f', name: 'Listo' },
    { color: '#f2d600', name: 'Pendiente' },
    { color: '#ff9f1a', name: 'Urgente' },
    { color: '#eb5a46', name: 'Problema' },
    { color: '#c377e0', name: 'Consulta' },
    { color: '#0079bf', name: 'Venta' }
];

/**
 * Debounce utility - delays function execution until after wait ms
 */
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

/**
 * Escape HTML to prevent XSS
 */
function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>"']/g, function(m) {
        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[m];
    });
}

function setupEventListeners() {
    document.getElementById('noteForm').addEventListener('submit', handleNoteSubmit);
    document.getElementById('addProductLine').addEventListener('click', () => addProductRow());
    document.getElementById('deleteNoteBtn').addEventListener('click', deleteNote);
}

function closeNoteModal() {
    closeModal('noteModal');
    // Remove pickers if open
    const lp = document.getElementById('labelPicker');
    if (lp) lp.remove();
    const mp = document.getElementById('memberPicker');
    if (mp) mp.remove();
}

/**
 * Carga inicial de notas
 */
async function fetchNotes() {
    try {
        const response = await fetchJSON('php/api_notes.php');
        if (response.success) {
            allNotes = response.data;
            renderKanban();
        }
    } catch (error) {
        console.error('Error fetching notes:', error);
        showToast('Error al cargar notas', 'error');
    }
}

/**
 * Renderiza el tablero Kanban
 */
function renderKanban() {
    const statuses = ['pendiente', 'seguimiento', 'resuelto', 'cancelado'];

    statuses.forEach(status => {
        const container = document.getElementById(`cards-${status}`);
        container.innerHTML = '';
        document.getElementById(`count-${status}`).textContent = '0';
    });

    const statusCounts = { pendiente: 0, seguimiento: 0, resuelto: 0, cancelado: 0 };

    allNotes.forEach(note => {
        const container = document.getElementById(`cards-${note.estado}`);
        if (container) {
            container.appendChild(createCardElement(note));
            statusCounts[note.estado]++;
        }
    });

    statuses.forEach(status => {
        document.getElementById(`count-${status}`).textContent = statusCounts[status];
    });
}

/**
 * Crea el elemento DOM de una tarjeta (Estilo Trello)
 */
function createCardElement(note) {
    const card = document.createElement('div');
    card.className = 'kanban-card';
    card.draggable = true;
    card.id = `note-${note.id}`;
    card.dataset.id = note.id;

    // Parsear etiquetas
    let labelsHTML = '';
    try {
        const labels = JSON.parse(note.etiquetas || '[]');
        labelsHTML = labels.map(l => `<div class="kanban-label" style="background:${l.color}" title="${l.name}"></div>`).join('');
    } catch(e) {}

    // Calcular progreso de checklist
    const hasChecklist = note.total_check > 0;
    const isCompleted = hasChecklist && note.total_check == note.total_completadas;

    card.innerHTML = `
        <div class="kanban-card-labels">${labelsHTML}</div>
        <span class="kanban-card-title">${escapeHTML(note.titulo)}</span>

        <div class="kanban-card-indicators">
            ${note.contenido ? `<div class="indicator-item" title="Esta tarjeta tiene una descripción."><i class="fas fa-align-left"></i></div>` : ''}

            ${hasChecklist ? `
                <div class="indicator-item ${isCompleted ? 'completed' : ''}" title="Tareas del checklist">
                    <i class="far fa-check-square"></i>
                    <span>${note.total_completadas}/${note.total_check}</span>
                </div>` : ''}

            ${note.total_adjuntos > 0 ? `
                <div class="indicator-item" title="Adjuntos">
                    <i class="fas fa-paperclip"></i>
                    <span>${note.total_adjuntos}</span>
                </div>` : ''}

            ${note.total_productos > 0 ? `
                <div class="indicator-item" title="Productos vinculados">
                    <i class="fas fa-boxes"></i>
                    <span>${note.total_productos}</span>
                </div>` : ''}
        </div>

        <!-- Previsualización de contenido -->
        <div class="kanban-card-preview-list">
            ${(() => {
                let previews = [];
                // Preview de Checklist items
                if (note.checklist && note.checklist.length > 0) {
                    note.checklist.slice(0, 2).forEach(item => {
                        previews.push(`
                            <div class="preview-item ${item.completada == 1 ? 'done' : ''}">
                                <i class="fas ${item.completada == 1 ? 'fa-check-circle text-success' : 'fa-circle-notch'}"></i>
                                ${escapeHTML(item.tarea)}
                            </div>
                        `);
                    });
                }
                // Preview de Productos
                if (note.items && note.items.length > 0) {
                    const productsList = note.items.slice(0, 2).map(p => p.producto_nombre).join(', ');
                    previews.push(`
                        <div class="preview-item">
                            <i class="fas fa-box text-primary"></i>
                            <span class="view-product-tag">${escapeHTML(productsList)}</span>
                        </div>
                    `);
                }
                return previews.join('');
            })()}
        </div>

        <div class="kanban-card-footer">
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted font-mono" style="font-size: 0.7rem;" title="Creado por">
                    <i class="fas fa-edit me-1"></i> ${note.usuario_nombre || '...'}
                </small>
                ${(() => {
                    try {
                        const m = JSON.parse(note.miembros || '[]');
                        return m.length > 0 ? `<small class="text-primary font-mono" style="font-size: 0.7rem;" title="${m.map(u => u.name).join(', ')}"><i class="fas fa-users me-1"></i>${m.length}</small>` : '';
                    } catch(e) { return ''; }
                })()}
            </div>
            ${note.fecha_entrega ? `<small class="text-muted font-mono" style="font-size: 0.7rem;">
                <i class="far fa-clock me-1"></i> ${formatDateShort(note.fecha_entrega)}
            </small>` : ''}
        </div>
    `;

    card.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', note.id);
        card.classList.add('dragging');
    });

    card.addEventListener('dragend', () => {
        card.classList.remove('dragging');
    });

    card.addEventListener('click', (e) => {
        if (!e.target.closest('button')) {
            openEditNoteModal(note.id);
        }
    });

    return card;
}

/**
 * Funciones de Drag & Drop
 */
function allowDrop(ev) {
    ev.preventDefault();
}

async function drop(ev) {
    ev.preventDefault();
    const id = ev.dataTransfer.getData('text/plain');
    const targetColumn = ev.target.closest('.kanban-column');

    if (targetColumn) {
        const newStatus = targetColumn.dataset.status;
        const note = allNotes.find(n => n.id == id);

        if (note && note.estado !== newStatus) {
            note.estado = newStatus;
            renderKanban();

            try {
                await fetchJSON('php/api_notes.php', {
                    method: 'PUT',
                    body: JSON.stringify({
                        id: id,
                        estado: newStatus,
                        action: 'move'
                    })
                });
                showToast(`Movido a ${newStatus}`, 'success');
            } catch (error) {
                console.error('Error moving note:', error);
                fetchNotes();
            }
        }
    }
}

/**
 * Gestión del Modal
 */
function openNewNoteModal(status = 'pendiente') {
    document.getElementById('noteForm').reset();
    document.getElementById('noteId').value = '';
    document.getElementById('noteStatus').value = status;
    document.getElementById('noteProductsContainer').innerHTML = '';
    document.getElementById('noteChecklistContainer').innerHTML = '';
    document.getElementById('noteAttachmentsContainer').innerHTML = '';
    document.getElementById('activeLabels').innerHTML = '';
    document.getElementById('activeMembers').innerHTML = '';
    document.getElementById('deleteNoteBtn').style.display = 'none';
    document.getElementById('noteModalTitle').innerHTML = '<i class="fas fa-plus"></i> NUEVA NOTA';
    document.getElementById('noteIdLabel').textContent = '# NUEVA';

    activeLabels = [];
    activeMembers = [];
    updateChecklistProgress();

    // Ensure edit mode for new notes
    currentViewMode = false;
    const modal = document.getElementById('noteModal');
    modal.classList.remove('view-mode');

    // Re-enable all inputs
    const form = document.getElementById('noteForm');
    form.querySelectorAll('input:not([type="hidden"]), textarea, select').forEach(el => {
        el.removeAttribute('readonly');
        el.removeAttribute('disabled');
        el.style.pointerEvents = '';
    });
    form.querySelectorAll('.btn-outline-buzz[onclick*="add"], .btn-outline-buzz[onclick*="toggle"]').forEach(el => el.style.display = '');

    // Show save button, hide edit button
    form.querySelector('button[type="submit"]').style.display = 'inline-block';
    const editBtn = document.getElementById('editModeBtn');
    if (editBtn) editBtn.style.display = 'none';

    openModal('noteModal', { static: true });
}

let currentViewMode = false; // Track if modal is in view mode

async function openEditNoteModal(id) {
    try {
        const response = await fetchJSON(`php/api_notes.php?id=${id}`);
        if (response.success) {
            const note = response.data;
            console.log('Note Details Loaded:', note); // Debug log
            console.log('Miembros Type:', typeof note.miembros, Array.isArray(note.miembros));
            console.log('Etiquetas Type:', typeof note.etiquetas, Array.isArray(note.etiquetas));

            document.getElementById('noteId').value = note.id;
            document.getElementById('noteTitle').value = note.titulo;
            document.getElementById('noteClientName').value = note.cliente_nombre || '';
            document.getElementById('notePriority').value = note.prioridad;
            document.getElementById('notePhone').value = note.telefono || '';
            document.getElementById('noteEmail').value = note.email || '';
            document.getElementById('noteContent').value = note.contenido || '';
            document.getElementById('noteStatus').value = note.estado;
            document.getElementById('noteDueDate').value = note.fecha_entrega ? note.fecha_entrega.replace(' ', 'T') : '';
            document.getElementById('noteIdLabel').textContent = `# ${note.id}`;

            // Etiquetas y Miembros
            activeLabels = note.etiquetas || [];
            activeMembers = note.miembros || [];
            renderActiveLabels();
            renderActiveMembers();

            document.getElementById('deleteNoteBtn').style.display = 'block';

            // Productos
            const pContainer = document.getElementById('noteProductsContainer');
            pContainer.innerHTML = '';
            if (note.items) note.items.forEach(item => addProductRow({...item, id: item.producto_id, nombre: item.producto_nombre}));

            // Checklist
            const cContainer = document.getElementById('noteChecklistContainer');
            cContainer.innerHTML = '';
            if (note.checklist) note.checklist.forEach(item => addChecklistItem(item.tarea, item.completada));
            updateChecklistProgress();

            // Adjuntos
            const aContainer = document.getElementById('noteAttachmentsContainer');
            aContainer.innerHTML = '';
            if (note.adjuntos) note.adjuntos.forEach(item => addAttachmentRow(item.nombre, item.url));

            // Open in VIEW mode by default (passing the detailed note object)
            setModalViewMode(true, note.id, note);

            openModal('noteModal', { static: true });
        }
    } catch (error) {
        console.error('Error loading note details:', error);
        showToast('Error al cargar detalle', 'error');
    }
}

/**
 * Toggle between view and edit mode
 */
function setModalViewMode(viewMode, noteId = null, noteData = null) {
    currentViewMode = viewMode;
    const modal = document.getElementById('noteModal');
    const form = document.getElementById('noteForm');

    // All editable inputs
    const inputs = form.querySelectorAll('input:not([type="hidden"]), textarea, select');
    const addButtons = form.querySelectorAll('.btn-outline-buzz[onclick*="add"], .btn-outline-buzz[onclick*="toggle"]');
    const removeButtons = form.querySelectorAll('.text-danger[onclick*="remove"], .text-danger[onclick*="closest"]');

    // Footer buttons
    const saveBtn = form.querySelector('button[type="submit"]');
    const deleteBtn = document.getElementById('deleteNoteBtn');
    let editBtn = document.getElementById('editModeBtn');

    if (viewMode) {
        // VIEW MODE
        document.getElementById('noteModalTitle').innerHTML = `<i class="fas fa-eye"></i> VER NOTA #${noteId || ''}`;

        inputs.forEach(el => {
            el.setAttribute('readonly', true);
            el.setAttribute('disabled', true);
            el.style.pointerEvents = 'none';
        });

        addButtons.forEach(el => el.style.display = 'none');
        removeButtons.forEach(el => el.style.display = 'none');

        saveBtn.style.display = 'none';
        if (deleteBtn) deleteBtn.style.display = 'none';

        // Create or show Edit button
        if (!editBtn) {
            editBtn = document.createElement('button');
            editBtn.type = 'button';
            editBtn.id = 'editModeBtn';
            editBtn.className = 'btn btn-buzz';
            editBtn.innerHTML = '<i class="fas fa-edit me-1"></i> EDITAR';
            editBtn.onclick = () => setModalViewMode(false, noteId);
            saveBtn.parentElement.appendChild(editBtn);
        } else {
            editBtn.style.display = 'inline-block';
            editBtn.onclick = () => setModalViewMode(false, noteId);
        }

        modal.classList.add('view-mode');

        // Toggle visibility of containers
        form.querySelectorAll('.d-edit-only').forEach(el => el.classList.add('d-none'));
        form.querySelectorAll('[id^="view-"]').forEach(el => el.classList.remove('d-none'));

        // Render clean view data
        const id = document.getElementById('noteId').value;
        const note = noteData || allNotes.find(n => n.id == id);
        if (note) renderNoteView(note);

    } else {
        // EDIT MODE
        document.getElementById('noteModalTitle').innerHTML = `<i class="fas fa-sticky-note"></i> EDITAR NOTA #${noteId || document.getElementById('noteId').value}`;

        inputs.forEach(el => {
            el.removeAttribute('readonly');
            el.removeAttribute('disabled');
            el.style.pointerEvents = '';
        });

        addButtons.forEach(el => el.style.display = '');
        removeButtons.forEach(el => el.style.display = '');

        saveBtn.style.display = 'inline-block';
        if (deleteBtn) deleteBtn.style.display = 'block';

        if (editBtn) editBtn.style.display = 'none';

        modal.classList.remove('view-mode');

        // Toggle visibility of containers
        form.querySelectorAll('.d-edit-only').forEach(el => el.classList.remove('d-none'));
        form.querySelectorAll('[id^="view-"]').forEach(el => el.classList.add('d-none'));
    }
}

/**
 * Markdown formatting
 */
function insertMarkdown(before, after) {
    const textarea = document.getElementById('noteContent');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selected = text.substring(start, end);
    const replacement = before + selected + after;

    textarea.value = text.substring(0, start) + replacement + text.substring(end);

    textarea.focus();
    textarea.setSelectionRange(start + before.length, start + before.length + selected.length);
}

/**
 * Checklist Functions
 */
function addChecklistItem(tarea = '', completada = 0) {
    const container = document.getElementById('noteChecklistContainer');
    const div = document.createElement('div');
    div.className = 'checklist-item';
    div.innerHTML = `
        <div class="input-group input-group-sm">
            <div class="input-group-text bg-transparent border-0">
                <input type="checkbox" class="form-check-input mt-0 check-done" ${completada == 1 ? 'checked' : ''} onchange="updateChecklistProgress()">
            </div>
            <input type="text" class="form-control form-control-buzz check-task" value="${tarea}" placeholder="Nueva tarea..." onkeydown="if(event.key==='Enter'){event.preventDefault(); addChecklistItem();}">
            <button type="button" class="btn btn-outline-buzz" onclick="addChecklistItem()" title="Añadir otra"><i class="fas fa-plus"></i></button>
            <button type="button" class="btn btn-outline-danger" onclick="this.closest('.checklist-item').remove(); updateChecklistProgress();">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
    container.appendChild(div);
    div.querySelector('.check-task').focus();
}

function updateChecklistProgress() {
    const items = document.querySelectorAll('.checklist-item');
    if (items.length === 0) {
        document.getElementById('checklist-bar').style.width = '0%';
        document.getElementById('checklist-percent').textContent = '0%';
        return;
    }
    const done = Array.from(items).filter(i => i.querySelector('.check-done').checked).length;
    const percent = Math.round((done / items.length) * 100);
    document.getElementById('checklist-bar').style.width = `${percent}%`;
    document.getElementById('checklist-percent').textContent = `${percent}%`;
}

/**
 * Attachment Functions
 */
function addAttachmentRow(nombre = '', url = '') {
    const container = document.getElementById('noteAttachmentsContainer');

    // Validate: only allow new row if last row has URL filled
    const existingRows = container.querySelectorAll('.attachment-row');
    if (existingRows.length > 0 && !nombre && !url) {
        const lastRow = existingRows[existingRows.length - 1];
        const lastUrl = lastRow.querySelector('.adj-url').value.trim();
        if (!lastUrl) {
            showToast('Completa la URL del enlace anterior primero', 'warning');
            lastRow.querySelector('.adj-url').focus();
            return;
        }
    }

    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 attachment-row align-items-center';
    div.innerHTML = `
        <div class="col-4">
            <input type="text" class="form-control form-control-buzz form-control-sm adj-name" value="${nombre}" placeholder="Nombre (ej: PDF)">
        </div>
        <div class="col-7">
            <div class="input-group input-group-sm">
                <input type="text" class="form-control form-control-buzz adj-url" value="${url}" placeholder="URL del enlace" onkeydown="if(event.key==='Enter'){event.preventDefault(); addAttachmentRow();}">
                <button type="button" class="btn btn-outline-buzz" onclick="addAttachmentRow()" title="Añadir otro"><i class="fas fa-plus"></i></button>
                ${url ? `<a href="${url}" target="_blank" class="btn btn-outline-buzz"><i class="fas fa-external-link-alt"></i></a>` : ''}
            </div>
        </div>
        <div class="col-1">
            <button type="button" class="btn btn-sm text-danger" onclick="this.closest('.attachment-row').remove()"><i class="fas fa-times"></i></button>
        </div>
    `;
    container.appendChild(div);
    div.querySelector('.adj-name').focus();
}

/**
 * Labels Management - Enhanced UI
 */
function toggleLabelEditor() {
    let picker = document.getElementById('labelPicker');
    if (picker) {
        closeLabelPicker();
        return;
    }

    picker = document.createElement('div');
    picker.id = 'labelPicker';
    picker.className = 'picker-popup';
    picker.style.position = 'absolute';
    picker.style.zIndex = '3000';
    picker.style.width = '260px';

    picker.innerHTML = `
        <div class="picker-header">
            <h6><i class="fas fa-tags me-2"></i>Etiquetas</h6>
            <button type="button" class="picker-close" onclick="closeLabelPicker()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="picker-body">
            ${AVAILABLE_LABELS.map(l => {
                const isSelected = activeLabels.some(al => al.name === l.name);
                return `
                    <div class="label-option ${isSelected ? 'selected' : ''}"
                         onclick="selectLabel('${l.name}', '${l.color}')"
                         data-label="${l.name}">
                        <div class="label-checkbox">
                            <i class="fas ${isSelected ? 'fa-check-square' : 'fa-square'}"></i>
                        </div>
                        <div class="label-color-indicator" style="background:${l.color}"></div>
                        <span class="label-name">${l.name}</span>
                    </div>
                `;
            }).join('')}
        </div>
        <div class="picker-footer">
            <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Click para seleccionar</small>
        </div>
    `;

    const btn = event.currentTarget;
    const rect = btn.getBoundingClientRect();
    const modalBody = document.querySelector('.modal-body-buzz');
    const modalRect = modalBody ? modalBody.getBoundingClientRect() : { top: 0, left: 0 };

    picker.style.top = (rect.bottom + 8) + 'px';
    picker.style.left = rect.left + 'px';

    document.body.appendChild(picker);

    // Close on click outside
    setTimeout(() => {
        document.addEventListener('click', handleLabelPickerOutsideClick);
    }, 100);
}

function handleLabelPickerOutsideClick(e) {
    const picker = document.getElementById('labelPicker');
    const btn = document.querySelector('[onclick*="toggleLabelEditor"]');
    if (picker && !picker.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
        closeLabelPicker();
    }
}

function closeLabelPicker() {
    const picker = document.getElementById('labelPicker');
    if (picker) {
        picker.classList.add('closing');
        setTimeout(() => picker.remove(), 150);
    }
    document.removeEventListener('click', handleLabelPickerOutsideClick);
}

function selectLabel(name, color) {
    const idx = activeLabels.findIndex(l => l.name === name);
    if (idx > -1) {
        activeLabels.splice(idx, 1);
    } else {
        activeLabels.push({ name, color });
    }

    // Update picker UI without closing
    const picker = document.getElementById('labelPicker');
    if (picker) {
        const option = picker.querySelector(`[data-label="${name}"]`);
        if (option) {
            const isNowSelected = activeLabels.some(l => l.name === name);
            option.classList.toggle('selected', isNowSelected);
            option.querySelector('.label-checkbox i').className = `fas ${isNowSelected ? 'fa-check-square' : 'fa-square'}`;
        }
    }

    renderActiveLabels();
    updateLabelButtonCounter();
}

function renderActiveLabels() {
    const container = document.getElementById('activeLabels');
    container.innerHTML = activeLabels.map(l => `
        <span class="active-label-badge" style="--label-color: ${l.color}">
            <span class="label-dot" style="background:${l.color}"></span>
            ${l.name}
            <button type="button" class="label-remove" onclick="selectLabel('${l.name}', '${l.color}')">
                <i class="fas fa-times"></i>
            </button>
        </span>
    `).join('');
}

function updateLabelButtonCounter() {
    const btn = document.querySelector('[onclick*="toggleLabelEditor"]');
    if (btn) {
        let counter = btn.querySelector('.picker-counter');
        if (activeLabels.length > 0) {
            if (!counter) {
                counter = document.createElement('span');
                counter.className = 'picker-counter';
                btn.appendChild(counter);
            }
            counter.textContent = activeLabels.length;
        } else if (counter) {
            counter.remove();
        }
    }
}

/**
 * Members Management - Enhanced UI
 */
async function toggleMemberEditor() {
    let picker = document.getElementById('memberPicker');
    if (picker) {
        closeMemberPicker();
        return;
    }

    picker = document.createElement('div');
    picker.id = 'memberPicker';
    picker.className = 'picker-popup';
    picker.style.position = 'absolute';
    picker.style.zIndex = '3000';
    picker.style.width = '300px';

    const assignedHTML = activeMembers.length > 0
        ? activeMembers.map(m => `
            <div class="assigned-member-chip" onclick="selectMember(event, ${m.id}, '${escapeHTML(m.name)}')">
                <i class="fas fa-user-circle me-1"></i>
                ${m.name.split(' ')[0]}
                <i class="fas fa-times remove-icon"></i>
            </div>
        `).join('')
        : '<div class="text-muted small py-2"><i class="fas fa-info-circle me-1 opacity-50"></i>Sin miembros asignados</div>';

    picker.innerHTML = `
        <div class="picker-header">
            <h6><i class="fas fa-users me-2"></i>Miembros</h6>
            <button type="button" class="picker-close" onclick="closeMemberPicker()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="picker-body">
            <div class="picker-section">
                <label class="picker-section-label">Asignados</label>
                <div id="assignedMembersSection" class="assigned-members-list">
                    ${assignedHTML}
                </div>
            </div>
            <div class="picker-section">
                <label class="picker-section-label">Buscar usuario</label>
                <div class="search-input-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" class="form-control form-control-buzz form-control-sm" id="memberSearch" placeholder="Nombre del usuario..." autocomplete="off">
                </div>
                <div id="memberSearchResults" class="member-results-list">
                    <div class="search-loading"><i class="fas fa-spinner fa-spin"></i> Cargando usuarios...</div>
                </div>
            </div>
        </div>
    `;

    const btn = event.currentTarget;
    const rect = btn.getBoundingClientRect();

    picker.style.top = (rect.bottom + 8) + 'px';
    picker.style.left = rect.left + 'px';

    document.body.appendChild(picker);

    const searchInput = picker.querySelector('#memberSearch');
    const resultsDiv = picker.querySelector('#memberSearchResults');

    // Load initial users list
    loadAllUsers(resultsDiv);

    // Search handler
    searchInput.addEventListener('input', debounce(async () => {
        const query = searchInput.value.trim();
        if (query.length < 1) {
            loadAllUsers(resultsDiv);
            return;
        }

        resultsDiv.innerHTML = '<div class="search-loading"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>';

        try {
            const data = await fetchJSON(`php/search_api.php?action=search_users&query=${encodeURIComponent(query)}`);
            renderMemberResults(resultsDiv, data);
        } catch (e) {
            console.error('Error searching users:', e);
            resultsDiv.innerHTML = '<div class="text-muted small py-2 px-1 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error al buscar</div>';
        }
    }, 300));

    // Enter key selects first result
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstResult = resultsDiv.querySelector('.member-result');
            if (firstResult) {
                firstResult.click();
            }
        } else if (e.key === 'Escape') {
            closeMemberPicker();
        }
    });

    // Close picker when clicking outside
    setTimeout(() => {
        document.addEventListener('click', handleMemberPickerOutsideClick);
    }, 100);

    searchInput.focus();
}

/**
 * Load all users initially (limited to 10)
 */
async function loadAllUsers(resultsDiv) {
    try {
        const data = await fetchJSON('php/search_api.php?action=search_users&query=');
        renderMemberResults(resultsDiv, data);
    } catch (e) {
        console.error('Error loading users:', e);
        resultsDiv.innerHTML = '<div class="text-muted small py-2 px-1 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error al cargar usuarios</div>';
    }
}

/**
 * Render member search results
 */
function renderMemberResults(resultsDiv, data) {
    if (!data.success) {
        resultsDiv.innerHTML = '<div class="text-muted small py-2 px-1 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error al buscar</div>';
        return;
    }

    if (data.users.length === 0) {
        resultsDiv.innerHTML = '<div class="text-muted small py-2 px-1"><i class="fas fa-user-slash me-2"></i>Sin usuarios encontrados</div>';
    } else {
        resultsDiv.innerHTML = data.users.map(u => {
            const isAssigned = activeMembers.some(m => m.id == u.id);
            return `
                <div class="member-result ${isAssigned ? 'assigned' : ''}"
                     data-member-id="${u.id}"
                     onclick="selectMember(event, ${u.id}, '${escapeHTML(u.nombre)}')">
                    <i class="fas fa-user-circle member-icon"></i>
                    <span class="member-name">${escapeHTML(u.nombre)}</span>
                    <i class="fas fa-check assign-check" style="display: ${isAssigned ? 'inline' : 'none'}"></i>
                </div>
            `;
        }).join('');
    }
}

function handleMemberPickerOutsideClick(e) {
    const picker = document.getElementById('memberPicker');
    const btn = document.querySelector('[onclick*="toggleMemberEditor"]');
    if (picker && !picker.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
        closeMemberPicker();
    }
}

function closeMemberPicker() {
    const picker = document.getElementById('memberPicker');
    if (picker) {
        picker.classList.add('closing');
        setTimeout(() => picker.remove(), 150);
    }
    document.removeEventListener('click', handleMemberPickerOutsideClick);
}

function selectMember(e, id, name) {
    if (e) e.preventDefault();
    const idx = activeMembers.findIndex(m => m.id == id);
    if (idx > -1) {
        activeMembers.splice(idx, 1);
    } else {
        activeMembers.push({ id, name });
    }

    // Update picker UI
    const picker = document.getElementById('memberPicker');
    if (picker) {
        // Update assigned members section
        updateMemberPickerAssigned(picker);
        // Update checkmarks in search results
        picker.querySelectorAll('.member-result').forEach(el => {
            const memberId = parseInt(el.dataset.memberId);
            const isAssigned = activeMembers.some(m => m.id === memberId);
            el.classList.toggle('assigned', isAssigned);
            const checkIcon = el.querySelector('.assign-check');
            if (checkIcon) {
                checkIcon.style.display = isAssigned ? 'inline' : 'none';
            }
        });
    }

    renderActiveMembers();
    updateMemberButtonCounter();
}

function updateMemberPickerAssigned(picker) {
    const section = picker.querySelector('#assignedMembersSection');
    if (!section) return;

    if (activeMembers.length === 0) {
        section.innerHTML = '<div class="text-muted small py-2"><i class="fas fa-info-circle me-1 opacity-50"></i>Sin miembros asignados</div>';
    } else {
        section.innerHTML = activeMembers.map(m => `
            <div class="assigned-member-chip" onclick="selectMember(event, ${m.id}, '${escapeHTML(m.name)}')">
                <i class="fas fa-user-circle me-1"></i>
                ${m.name.split(' ')[0]}
                <i class="fas fa-times remove-icon"></i>
            </div>
        `).join('');
    }
}

function renderActiveMembers() {
    const container = document.getElementById('activeMembers');
    container.innerHTML = activeMembers.map(m => `
        <div class="active-member-badge" title="${m.name}">
            <i class="fas fa-user-circle"></i>
            <span>${m.name.split(' ')[0]}</span>
            <button type="button" class="member-remove" onclick="removeMemberDirect(event, ${m.id}, '${escapeHTML(m.name)}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `).join('');
}

function removeMemberDirect(e, id, name) {
    if (e) e.stopPropagation();
    selectMember(null, id, name);
}

function updateMemberButtonCounter() {
    const btn = document.querySelector('[onclick*="toggleMemberEditor"]');
    if (btn) {
        let counter = btn.querySelector('.picker-counter');
        if (activeMembers.length > 0) {
            if (!counter) {
                counter = document.createElement('span');
                counter.className = 'picker-counter';
                btn.appendChild(counter);
            }
            counter.textContent = activeMembers.length;
        } else if (counter) {
            counter.remove();
        }
    }
}

/**
 * Envío del formulario
 */
async function handleNoteSubmit(e) {
    if (e) e.preventDefault();

    const id = document.getElementById('noteId').value;
    const isEdit = id !== '';

    const titleEl = document.getElementById('noteTitle');
    const clientNameEl = document.getElementById('noteClientName');

    // Validación de campos obligatorios
    if (!titleEl.value.trim() || !clientNameEl.value.trim()) {
        if (!titleEl.value.trim()) titleEl.classList.add('is-invalid');
        if (!clientNameEl.value.trim()) clientNameEl.classList.add('is-invalid');
        showToast('El título y el nombre del cliente son obligatorios', 'error');
        return;
    }

    // Limpiar estados de validación
    titleEl.classList.remove('is-invalid');
    clientNameEl.classList.remove('is-invalid');

    const dueDateInput = document.getElementById('noteDueDate').value;
    const fecha_entrega = dueDateInput ? dueDateInput.replace('T', ' ') : null;

    const payload = {
        id: id,
        titulo: document.getElementById('noteTitle').value,
        cliente_nombre: document.getElementById('noteClientName').value,
        prioridad: document.getElementById('notePriority').value,
        telefono: document.getElementById('notePhone').value,
        email: document.getElementById('noteEmail').value,
        contenido: document.getElementById('noteContent').value,
        estado: document.getElementById('noteStatus').value,
        fecha_entrega: fecha_entrega,
        etiquetas: JSON.stringify(activeLabels),
        miembros: JSON.stringify(activeMembers),
        items: [],
        checklist: [],
        adjuntos: []
    };

    // Productos
    document.querySelectorAll('.note-item-row').forEach(row => {
        const pId = row.querySelector('.product-id').value;
        if (pId) payload.items.push({ producto_id: pId, cantidad: row.querySelector('.product-qty').value });
    });

    // Checklist
    document.querySelectorAll('.checklist-item').forEach(item => {
        const tarea = item.querySelector('.check-task').value;
        if (tarea) payload.checklist.push({ tarea, completada: item.querySelector('.check-done').checked ? 1 : 0 });
    });

    // Adjuntos
    document.querySelectorAll('.attachment-row').forEach(row => {
        const url = row.querySelector('.adj-url').value;
        if (url) payload.adjuntos.push({ nombre: row.querySelector('.adj-name').value, url });
    });

    try {
        const response = await fetchJSON('php/api_notes.php', {
            method: isEdit ? 'PUT' : 'POST',
            body: JSON.stringify(payload)
        });

        if (response.success) {
            showToast(isEdit ? 'Nota actualizada' : 'Nota creada', 'success');
            closeNoteModal();
            fetchNotes();
        } else {
            showToast(response.message, 'error');
        }
    } catch (error) {
        console.error('Error saving note:', error);
        showToast('Error al guardar', 'error');
    }
}

async function deleteNote() {
    const id = document.getElementById('noteId').value;
    if (!id) return;
    if (confirm('¿Estás seguro de eliminar esta nota?')) {
        try {
            const response = await fetchJSON(`php/api_notes.php?id=${id}`, { method: 'DELETE' });
            if (response.success) {
                showToast('Nota eliminada', 'success');
                closeNoteModal();
                fetchNotes();
            }
        } catch (error) { console.error(error); }
    }
}

/**
 * Autocomplete (similar to previous version but updated for new structure)
 */
function addProductRow(data = null) {
    const container = document.getElementById('noteProductsContainer');

    // Validate: only allow new row if last row has a product selected
    const existingRows = container.querySelectorAll('.note-item-row');
    if (existingRows.length > 0 && !data) {
        const lastRow = existingRows[existingRows.length - 1];
        const lastProductId = lastRow.querySelector('.product-id').value.trim();
        if (!lastProductId) {
            showToast('Selecciona un producto primero', 'warning');
            lastRow.querySelector('.product-search').focus();
            return;
        }
    }

    const div = document.createElement('div');
    div.className = 'row g-2 mb-2 note-item-row align-items-center';
    div.innerHTML = `
        <div class="col-8 position-relative">
            <div class="input-group input-group-sm">
                <input type="text" class="form-control form-control-buzz product-search"
                    value="${data ? `${data.nombre} (${data.sku})` : ''}"
                    placeholder="Buscar por nombre o SKU..." ${data ? 'readonly' : ''}>
                <button type="button" class="btn btn-outline-buzz" onclick="addProductRow()" title="Vincular otro"><i class="fas fa-plus"></i></button>
            </div>
            <input type="hidden" class="product-id" value="${data ? data.id : ''}">
            <div class="search-results list-group position-absolute w-100" style="z-index:2050; display:none;"></div>
        </div>
        <div class="col-2">
            <input type="number" class="form-control form-control-buzz form-control-sm product-qty"
                   value="${data ? data.cantidad : 1}" min="1">
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-sm text-danger w-100" onclick="this.closest('.note-item-row').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(div);
    if (!data) initProductAutocomplete(div);
}

function initProductAutocomplete(row) {
    const input = row.querySelector('.product-search');
    const resultsDiv = row.querySelector('.search-results');
    input.addEventListener('input', debounce(async () => {
        const query = input.value.trim();
        if (query.length < 2) { resultsDiv.style.display = 'none'; return; }
        try {
            const data = await fetchJSON(`php/search_api.php?action=search_products&query=${encodeURIComponent(query)}`);
            if (data.success) {
                if (data.products.length > 0) {
                    resultsDiv.innerHTML = data.products.map(p => {
                        const isOutOfStock = p.stock === 0;
                        const stockInfo = isOutOfStock
                            ? '<span class="badge bg-danger ms-2">Sin stock</span>'
                            : `<span class="badge bg-success ms-2">Stock: ${p.stock}</span>`;

                        return `
                            <a href="#" class="list-group-item list-group-item-action py-1 px-2 small ${isOutOfStock ? 'opacity-50' : ''}"
                            onclick="selectProductForNote(event, ${p.id}, '${p.nombre}', '${p.sku}', ${p.stock}, this)">
                                <strong>${p.nombre}</strong> <span class="text-muted small">(${p.sku})</span>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="text-primary fw-bold">${formatCurrency(p.precio)}</span>
                                    ${stockInfo}
                                </div>
                            </a>
                        `;
                    }).join('');
                } else {
                    resultsDiv.innerHTML = '<div class="list-group-item small">No se encontraron productos</div>';
                }
                resultsDiv.style.display = 'block';
            }
        } catch (e) {
            console.error(e);
            resultsDiv.innerHTML = '<div class="list-group-item small text-danger">Error al buscar</div>';
            resultsDiv.style.display = 'block';
        }
    }, 300));

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const firstResult = resultsDiv.querySelector('a');
            if (firstResult) {
                firstResult.click();
            } else {
                addProductRow();
            }
        }
    });

    document.addEventListener('click', (e) => { if (!row.contains(e.target)) resultsDiv.style.display = 'none'; });
}

function selectProductForNote(e, id, nombre, sku, stock, el) {
    e.preventDefault();
    if (stock === 0) {
        showToast(`${nombre} no tiene stock disponible`, 'warning');
    }
    const row = el.closest('.note-item-row');
    row.querySelector('.product-search').value = `${nombre} (${sku})`;
    row.querySelector('.product-id').value = id;
    row.querySelector('.search-results').style.display = 'none';
}

function formatCurrency(value) {
    return new Intl.NumberFormat('es-PY', { style: 'currency', currency: 'PYG' }).format(value);
}

function formatDateShort(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
}

/**
 * Renderiza la vista de "solo lectura" de la nota
 */
function renderNoteView(note) {
    // Title
    document.getElementById('view-noteTitle').textContent = note.titulo;

    // Content (Markdown)
    const content = note.contenido || '_No hay descripción disponible._';
    document.getElementById('view-noteContent').innerHTML = simpleMarkdown(content);

    // Checklist
    const checkContainer = document.getElementById('view-noteChecklist');
    if (note.checklist && note.checklist.length > 0) {
        checkContainer.innerHTML = `
            <div class="view-list">
                ${note.checklist.map(item => `
                    <div class="view-item-pill">
                        <i class="fas ${item.completada == 1 ? 'fa-check-circle view-check-done' : 'fa-circle view-check-pending'}"></i>
                        <span class="${item.completada == 1 ? 'text-muted text-decoration-line-through' : ''}">${escapeHTML(item.tarea)}</span>
                    </div>
                `).join('')}
            </div>
        `;
    } else {
        checkContainer.innerHTML = '<div class="text-muted small py-2">No hay tareas pendientes.</div>';
    }

    // Products
    const prodContainer = document.getElementById('view-noteProducts');
    if (note.items && note.items.length > 0) {
        prodContainer.innerHTML = `
            <div class="view-list">
                ${note.items.map(p => `
                    <div class="view-item-pill">
                        <i class="fas fa-box text-primary"></i>
                        <div class="d-flex flex-grow-1 justify-content-between align-items-center">
                            <span>${escapeHTML(p.producto_nombre)}</span>
                            <span class="view-product-tag">${p.cantidad} unidades</span>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    } else {
        prodContainer.innerHTML = '<div class="text-muted small py-2">No hay productos vinculados.</div>';
    }

    // Attachments
    const attContainer = document.getElementById('view-noteAttachments');
    if (note.adjuntos && note.adjuntos.length > 0) {
        attContainer.innerHTML = `
            <div class="view-list">
                ${note.adjuntos.map(a => `
                    <div class="view-item-pill">
                        <i class="fas fa-link text-info"></i>
                        <div class="d-flex flex-grow-1 justify-content-between align-items-center">
                            <span>${escapeHTML(a.nombre || 'Enlace')}</span>
                            <a href="${a.url}" target="_blank" class="view-link-btn">Abrir enlace <i class="fas fa-external-link-alt ms-1"></i></a>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    } else {
        attContainer.innerHTML = '<div class="text-muted small py-2">No hay enlaces adjuntos.</div>';
    }

    // Client Info
    document.getElementById('view-clientInfo').innerHTML = `
        <div class="view-info-grid">
            <div class="view-info-item">
                <span class="view-info-label">Nombre del Cliente</span>
                <span class="view-info-value">${escapeHTML(note.cliente_nombre || 'No especificado')}</span>
            </div>
            ${note.telefono ? `
                <div class="view-info-item">
                    <span class="view-info-label">Teléfono</span>
                    <span class="view-info-value">${escapeHTML(note.telefono)}</span>
                </div>` : ''}
            ${note.email ? `
                <div class="view-info-item">
                    <span class="view-info-label">Email</span>
                    <span class="view-info-value">${escapeHTML(note.email)}</span>
                </div>` : ''}
        </div>
    `;

    // Logistics
    document.getElementById('view-dueDate').innerHTML = note.fecha_entrega ? `
        <div class="view-info-item">
            <span class="view-info-label">Fecha Límite</span>
            <span class="view-info-value text-primary"><i class="far fa-clock me-1"></i> ${new Date(note.fecha_entrega).toLocaleString()}</span>
        </div>
    ` : '<div class="text-muted small py-2">Sin fecha de entrega.</div>';
}

/**
 * Procesador de Markdown ultra simple
 */
function simpleMarkdown(text) {
    if (!text) return '';
    return text
        .replace(/[&<>"']/g, m => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[m])) // Escape
        .replace(/^### (.*$)/gim, '<h3>$1</h3>') // Headers
        .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>') // Bold
        .replace(/_(.*)_/gim, '<em>$1</em>') // Italic
        .replace(/^\- (.*$)/gim, '<li>$1</li>') // Unordered list
        .replace(/(<li>.*<\/li>)/gim, '<ul>$1</ul>') // Wrap lists
        .replace(/<\/ul><ul>/gim, '') // Fix double wraps
        .replace(/\n$/gim, '<br />'); // Newlines
}

