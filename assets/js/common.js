
/**
 * Fetches data from a URL and returns it as a JSON object.
 * @param {string} url The URL to fetch.
 * @param {object} options The options to pass to the fetch call.
 * @returns {Promise<any>} A promise that resolves with the JSON data.
 */
async function fetchJSON(url, options = {}) {
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            // Try to get more specific error from response body
            const errorBody = await response.text();
            let errorMessage = `HTTP error! status: ${response.status}`;
            try {
                // Assuming the error response is JSON
                const errorJson = JSON.parse(errorBody);
                errorMessage = errorJson.message || errorMessage;
            } catch (e) {
                // If not JSON, use the raw text (like for HTML error pages)
                errorMessage = errorBody || errorMessage;
            }
            throw new Error(errorMessage);
        }
        // Check if the content type is JSON before parsing
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json();
        } else {
            // Handle non-JSON responses if necessary, or throw an error
            return response.text().then(text => {
                console.warn("Response was not JSON. Raw response:", text);
                try {
                    // Attempt to parse it as JSON anyway, in case headers are wrong
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error("Response was not valid JSON.");
                }
            });
        }
    } catch (error) {
        console.error(`[fetchJSON] Error fetching ${url}:`, error);
        throw error; // Re-throw the error to be caught by the caller
    }
}

/**
 * Formats a date string into a more readable format.
 * @param {string} dateString The date string to format.
 * @returns {string} The formatted date string.
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
    });
}

/**
 * Formats a date string into a more readable date and time format.
 * @param {string} dateString The date string to format.
 * @returns {string} The formatted date and time string.
 */
function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Formats a number as a currency string.
 * @param {number} amount The amount to format.
 * @returns {string} The formatted currency string.
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
}

// --- Notification and Modal System ---

/**
 * Creates and shows a persistent notification in the top-right corner.
 * @param {string} message The message to display.
 * @param {string} type The type of toast (success, error, info, warning). Defaults to 'info'.
 * @param {number} duration Ignored in this persistent version.
 * @returns {string} The unique ID of the created toast.
 */
function showToast(message, type = 'info', duration = null) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    // Check if Clear All button exists, if not create it but hide it initially
    let clearBtn = container.querySelector('.clear-all-btn');
    if (!clearBtn) {
        clearBtn = document.createElement('button');
        clearBtn.className = 'clear-all-btn';
        clearBtn.innerHTML = '<i class="fas fa-trash-alt"></i> Limpiar Todo';
        clearBtn.onclick = () => clearAllToasts();
        clearBtn.style.display = 'none';
        container.prepend(clearBtn); // Add to top
    }

    // Generate Unique ID
    const toastId = 'toast-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

    // Normalize type for CSS (toast.css uses toast-success, toast-error, etc.)
    const normalizedType = (type === 'danger') ? 'error' : type;

    // Create Toast Card
    const toast = document.createElement('div');
    toast.className = `toast-card toast-${normalizedType}`;
    toast.id = toastId;

    // Icon mapping
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-triangle',
        warning: 'fa-exclamation-circle',
        info: 'fa-info-circle'
    };

    // Title mapping
    const titles = {
        success: 'Éxito',
        error: 'Error',
        warning: 'Alerta',
        info: 'Información'
    };

    const iconClass = icons[normalizedType] || icons.info;
    const titleText = titles[normalizedType] || 'Notificación';

    // Timestamp
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    // Structure matching toast.css (Icon - Content - Close)
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${iconClass}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">
                ${titleText} <span style="opacity:0.6; font-size:0.8em; margin-left:8px; font-weight:normal;">${time}</span>
            </div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this)">
            <i class="fas fa-times"></i>
        </button>
        <div class="toast-progress"></div>
    `;

    // Append to container (below clear button)
    container.appendChild(toast);

    // Trigger animation
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    updateClearButtonVisibility();

    return toastId;
}

/**
 * Updates an existing toast notification with new content and type.
 * @param {string} toastId The ID of the toast to update.
 * @param {string} message The new message body.
 * @param {string} type The new type (success, error, etc.).
 */
function updateToast(toastId, message, type = 'info') {
    const toast = document.getElementById(toastId);
    if (!toast) {
        // Fallback: create new if not found
        return showToast(message, type);
    }

    // Normalize type for CSS
    const normalizedType = (type === 'danger') ? 'error' : type;

    // Update classes
    toast.className = `toast-card toast-${normalizedType} show`;

    // Icon mapping
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-triangle',
        warning: 'fa-exclamation-circle',
        info: 'fa-info-circle'
    };

    const titles = {
        success: 'Éxito',
        error: 'Error',
        warning: 'Alerta',
        info: 'Información'
    };

    const iconClass = icons[normalizedType] || icons.info;
    const titleText = titles[normalizedType] || 'Notificación';
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

    // Re-render inner HTML structure to avoid complex DOM manipulation of different structure
    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${iconClass}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-title">
                ${titleText} <span style="opacity:0.6; font-size:0.8em; margin-left:8px; font-weight:normal;">${time}</span>
            </div>
            <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this)">
            <i class="fas fa-times"></i>
        </button>
        <div class="toast-progress"></div>
    `;
}

function dismissToast(btn) {
    const card = btn.closest('.toast-card');
    card.classList.add('hiding');
    card.addEventListener('transitionend', () => {
        card.remove();
        updateClearButtonVisibility();
    });
}

function clearAllToasts() {
    const container = document.getElementById('toast-container');
    const toasts = container.querySelectorAll('.toast-card');
    toasts.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('hiding');
            card.addEventListener('transitionend', () => card.remove());
        }, index * 50); // Staggered removal
    });
    setTimeout(updateClearButtonVisibility, toasts.length * 50 + 300);
}

/**
 * Valida un formulario y muestra toast con el primer error encontrado.
 * Usar con formularios que tienen atributos required, pattern, etc.
 * @param {HTMLFormElement} form - El formulario a validar
 * @param {Object} [customMessages] - Mensajes personalizados por name: { "campoName": "Mensaje" }
 * @returns {boolean} true si es válido, false si hay errores (y se mostró toast)
 */
function validateFormWithToast(form, customMessages = {}) {
    if (!form || typeof form.checkValidity !== 'function') return true;
    if (form.checkValidity()) return true;

    const invalid = form.querySelector(':invalid');
    if (!invalid) return true;

    // Usar mensajes personalizados o generar uno en español (evitar "Please fill out this field." del navegador)
    let msg = customMessages[invalid.name] || customMessages[invalid.id] || invalid.title;
    if (!msg || msg === '') {
        const label = invalid.closest('.mb-3, .form-group, .input-group')?.querySelector('label')?.textContent?.replace(/\s*\*$/, '').trim();
        msg = label ? `${label} es obligatorio o inválido.` : 'Por favor complete correctamente los campos requeridos.';
    }

    showToast(msg, 'warning');
    invalid.focus();
    invalid.classList.add('is-invalid');
    return false;
}

function updateClearButtonVisibility() {
    const container = document.getElementById('toast-container');
    if (!container) return;

    // Count visible toasts (not hiding)
    const count = container.querySelectorAll('.toast-card:not(.hiding)').length;
    const clearBtn = container.querySelector('.clear-all-btn');

    if (clearBtn) {
        clearBtn.style.display = count > 1 ? 'block' : 'none';
    }
}

/**
 * Shows a customizable modal dialog.
 * @param {object} options - The options for the modal.
 * @param {string} options.title - The title of the modal.
 * @param {string} options.message - The message body of the modal.
 * @param {string} [options.type='info'] - The type (info, success, error, confirm).
 * @param {function} [options.onConfirm] - Callback if the confirm button is clicked.
 * @param {function} [options.onCancel] - Callback if the cancel button is clicked.
 */
function showModal({ title, message, type = "info", onConfirm, onCancel, timer }) {
  let overlay = document.getElementById("modal-overlay")
  if (!overlay) {
    // Create the modal structure if it doesn't exist
    const modalHTML = `
            <div id="modal-dialog">
                <h3 id="modal-title"></h3>
                <p id="modal-message"></p>
                <div id="modal-buttons"></div>
            </div>
        `
    overlay = document.createElement("div")
    overlay.id = "modal-overlay"
    overlay.innerHTML = modalHTML
    document.body.appendChild(overlay)
  }

  const dialog = overlay.querySelector("#modal-dialog")
  const modalTitle = overlay.querySelector("#modal-title")
  const modalMessage = overlay.querySelector("#modal-message")
  const modalButtons = overlay.querySelector("#modal-buttons")

  // Reset
  modalButtons.innerHTML = ""
  dialog.className = "" // Reset classes

  // Set content
  modalTitle.textContent = title
  modalMessage.textContent = message
  dialog.classList.add(`modal-${type}`)

  // Create buttons based on type
  if (type === "confirm") {
    const confirmBtn = document.createElement("button")
    confirmBtn.className = "btn btn-buzz"
    confirmBtn.textContent = "Confirmar"

    // Timer Logic
    if (timer && typeof timer === 'number') {
        confirmBtn.disabled = true;
        let timeLeft = timer;
        const originalText = confirmBtn.textContent;
        confirmBtn.textContent = `${originalText} (${timeLeft})`;

        const timerInterval = setInterval(() => {
            timeLeft--;
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;
            } else {
                confirmBtn.textContent = `${originalText} (${timeLeft})`;
            }
        }, 1000);

        // Clear interval if modal is closed before timer ends (basic cleanup)
        const oldOnclick = confirmBtn.onclick; // This is null here, but for safety
    }

    confirmBtn.onclick = () => {
      if (onConfirm) onConfirm()
      hideModal()
    }

    const cancelBtn = document.createElement("button")
    cancelBtn.className = "btn btn-outline-buzz"
    cancelBtn.textContent = "Cancelar"
    cancelBtn.onclick = () => {
      if (onCancel) onCancel()
      hideModal()
    }
    modalButtons.append(cancelBtn, confirmBtn)
  } else {
    const okBtn = document.createElement("button")
    okBtn.className = "btn btn-buzz"
    okBtn.textContent = "OK"
    okBtn.onclick = () => {
      if (onConfirm) onConfirm() // Can be used as a simple OK callback
      hideModal()
    }
    modalButtons.appendChild(okBtn)
  }

  // Show the modal - using simple display toggle like modal-buzz
  overlay.style.display = "block"
}

function hideModal() {
  const overlay = document.getElementById("modal-overlay")
  if (overlay) {
    overlay.style.display = "none"
  }
}

function logout() {
    // Clear admin state from localStorage to prevent persisting across sessions
    localStorage.removeItem('adminActiveTab');
    localStorage.removeItem('advFiltersVisible_admin_logs');
    window.location.href = "php/logout.php";
}

// --- Mobile Sidebar Toggle ---

/**
 * Initialize mobile sidebar toggle functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = sidebarToggle.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    }

    // --- Sidebar Dropdown Toggle ---
    const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.closest('.nav-dropdown');

            // Close other dropdowns
            document.querySelectorAll('.nav-dropdown.open').forEach(dropdown => {
                if (dropdown !== parent) {
                    dropdown.classList.remove('open');
                }
            });

            // Toggle current dropdown
            parent.classList.toggle('open');
        });
    });
});


// --- Global Error Handling for URL Parameters ---
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const code = urlParams.get('code');

    if (error) {
        let title = "Notificación del Sistema";
        let message = "Se ha registrado un evento.";
        let type = "info";

        if (error === 'access_denied') {
            title = "Acceso Denegado";
            message = "No tienes los permisos necesarios para acceder a esta sección o realizar esta acción.";
            if (code === '403') title += " (403)";
            type = "error";
        } else if (error === 'auth_required') {
            title = "Autenticación Requerida";
            message = "Tu sesión ha expirado o necesitas iniciar sesión para continuar.";
            if (code === '401') title += " (401)";
            type = "warning";
        }

        if (typeof showModal === 'function') {
             showModal({
                title: title,
                message: message,
                type: type
            });
        }

        // Clean URL to prevent modal from reappearing on refresh
        const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: newUrl}, '', newUrl);
    }
});

// ============================================
// UNIFIED PAGINATION CONTROLS
// ============================================

/**
 * Renders unified pagination controls with "Página X de Y" format
 * @param {object} options - Pagination configuration
 * @param {string} options.containerId - ID of container element
 * @param {number} options.currentPage - Current page (1-indexed)
 * @param {number} options.totalPages - Total number of pages
 * @param {number} options.totalItems - Total number of items (optional, for info text)
 * @param {number} options.itemsPerPage - Items per page (optional, for info text)
 * @param {function} options.onPageChange - Callback when page changes, receives new page number
 */
function renderPaginationControls(options) {
    const { containerId, currentPage, totalPages, totalItems, itemsPerPage, onPageChange } = options;

    const container = document.getElementById(containerId);
    if (!container) return;

    // Don't show pagination if only 1 page
    if (totalPages <= 1) {
        container.innerHTML = '';
        container.style.display = 'none';
        return;
    }

    container.style.display = 'flex';

    // Calculate item range
    let infoHtml = '';
    if (totalItems && itemsPerPage) {
        const start = (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, totalItems);
        infoHtml = `
            <span class="pagination-info text-muted small">
                Mostrando <span class="fw-bold text-primary">${start}-${end}</span> de <span class="fw-bold">${totalItems}</span>
            </span>
        `;
    }

    container.innerHTML = `
        ${infoHtml}
        <div class="pagination-controls d-flex align-items-center gap-2">
            <button class="btn btn-outline-buzz btn-sm pagination-prev" ${currentPage <= 1 ? 'disabled' : ''} title="Página anterior">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span class="pagination-current font-mono px-2">
                Página <span class="text-primary fw-bold">${currentPage}</span> de <span class="fw-bold">${totalPages}</span>
            </span>
            <button class="btn btn-outline-buzz btn-sm pagination-next" ${currentPage >= totalPages ? 'disabled' : ''} title="Siguiente página">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    `;

    // Attach event listeners
    const prevBtn = container.querySelector('.pagination-prev');
    const nextBtn = container.querySelector('.pagination-next');

    if (prevBtn && currentPage > 1) {
        prevBtn.addEventListener('click', () => {
            if (onPageChange) onPageChange(currentPage - 1);
        });
    }

    if (nextBtn && currentPage < totalPages) {
        nextBtn.addEventListener('click', () => {
            if (onPageChange) onPageChange(currentPage + 1);
        });
    }
}

// Export for global use
window.renderPaginationControls = renderPaginationControls;

/**
 * Global Mobile Table Enhancer
 * Automatically adds data-label attributes to table cells for mobile card view
 */
function enableMobileTableCards() {
    const processTable = (table) => {
        if (table.dataset.mobileEnhanced) return;

        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (headers[index] && !cell.hasAttribute('data-label')) {
                    cell.setAttribute('data-label', headers[index]);
                }
            });
        });

        // Mark as processed but allow re-processing if rows change
        table.dataset.mobileEnhanced = 'true';
    };

    // Process all existing tables
    document.querySelectorAll('.table-buzz').forEach(processTable);

    // Watch for new tables or changes in existing tables
    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.type === 'childList') {
                // If a table was added
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) {
                         // Check if node itself is a table or contains tables
                        if (node.classList && node.classList.contains('table-buzz')) {
                            processTable(node);
                        } else if (node.querySelectorAll) {
                            node.querySelectorAll('.table-buzz').forEach(processTable);
                        }

                        // Check if rows were added to an existing table
                        if (node.tagName === 'TR' && node.closest('.table-buzz')) {
                            const table = node.closest('.table-buzz');
                            // Re-process just this row if possible, or the whole table headers
                            const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                            const cells = node.querySelectorAll('td');
                            cells.forEach((cell, index) => {
                                if (headers[index]) cell.setAttribute('data-label', headers[index]);
                            });
                        }
                         // Check if tbody changed
                        if (node.tagName === 'TBODY' && node.closest('.table-buzz')) {
                             const table = node.closest('.table-buzz');
                             const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
                             Array.from(node.querySelectorAll('tr')).forEach(row => {
                                 const cells = row.querySelectorAll('td');
                                 cells.forEach((cell, index) => {
                                     if (headers[index]) cell.setAttribute('data-label', headers[index]);
                                 });
                             });
                        }
                    }
                });
            }
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

// Initialize on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enableMobileTableCards);
} else {
    enableMobileTableCards();
}
// =========================================================================
// BUZZ DATA MANAGER - PALETA DE COMANDOS GLOBAL (CTRL + K)
// =========================================================================
// Atajo de navegación entre módulos del ERP. Se abre con Ctrl+K o Cmd+K.
//
// Los módulos NO están escritos aquí a mano: se leen del sidebar que ya ha
// renderizado el servidor. Eso tiene dos consecuencias deliberadas:
//
//   1. Los permisos no se duplican. El sidebar decide qué enlaces existen
//      según $_SESSION['usuario_rol'] y simplemente no imprime los que el
//      usuario no puede usar. Leyéndolo, la paleta hereda esa decisión; si se
//      escribiera una lista aparte, algún día divergirían y la paleta ofrecería
//      enlaces a páginas que responden "no autorizado".
//   2. La lista no se queda obsoleta. Añadir un módulo al menú lo añade a la
//      paleta sin tocar este fichero.
//
// Cuando no hay sidebar (manual.php usa otra plantilla) se recurre a un
// mínimo universal, no a la lista completa: es el camino que falla cerrado.
(function initBuzzCommandPalette() {
    if (window._buzzCmdPaletteInitialized) return;

    // Vistas que el sidebar muestra a cualquier rol. Es el plan B para las
    // páginas sin sidebar: mejor ofrecer poco y correcto que una lista completa
    // con enlaces que el usuario no puede abrir.
    const MODULOS_FALLBACK = [
        { titulo: 'Dashboard', url: 'dashboard', icono: 'fas fa-home', categoria: 'General' },
        { titulo: 'Pedidos', url: 'pedidos', icono: 'fas fa-shopping-cart', categoria: 'Operaciones' },
        { titulo: 'Envíos', url: 'envios', icono: 'fas fa-truck', categoria: 'Operaciones' },
        { titulo: 'Notas', url: 'todo', icono: 'fas fa-tasks', categoria: 'Herramientas' },
        { titulo: 'Ajustes', url: 'ajustes', icono: 'fas fa-sliders-h', categoria: 'Sistema' },
        { titulo: 'Acerca de', url: 'about', icono: 'fas fa-info-circle', categoria: 'Sistema' }
    ];

    function leerModulosDelSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return null;

        const modulos = [];
        const vistos = new Set();

        sidebar.querySelectorAll('.nav-item').forEach((item) => {
            // Categoría = título del desplegable padre. Los enlaces sueltos (sin
            // padre) caen en "General", que es justo lo que son.
            const toggle = item.querySelector('.nav-dropdown-toggle .nav-text-enhanced');
            const categoria = toggle ? toggle.textContent.trim() : 'General';

            item.querySelectorAll('a[href]').forEach((a) => {
                // El propio desplegable no navega: su href="#" es un contenedor.
                if (a.classList.contains('nav-dropdown-toggle')) return;

                const url = (a.getAttribute('href') || '').trim();
                // Los enlaces internos ya vienen sin extensión desde el servidor,
                // que es como los emite el .htaccess. Se respetan tal cual en vez
                // de reescribirlos a .php, que provocaría un 301 en cada clic.
                if (!url || url === '#' || /^javascript:/i.test(url) || url.charAt(0) === '#') return;
                if (vistos.has(url)) return;
                vistos.add(url);

                const etiqueta = a.querySelector('.nav-text-enhanced') || a;
                const icono = a.querySelector('i');
                modulos.push({
                    titulo: (etiqueta.textContent || '').trim(),
                    url: url,
                    icono: icono ? icono.className : 'fas fa-circle',
                    categoria: categoria
                });
            });
        });

        return modulos.length ? modulos : null;
    }

    function inicializar() {
        // La bandera se pone aquí y no antes: si algo falla durante el montaje,
        // no debe quedar marcada como inicializada o la paleta quedaría muerta
        // en esta página sin ningún error visible que lo explique.
        window._buzzCmdPaletteInitialized = true;

        const style = document.createElement('style');
        style.textContent = `
            #buzzCmdOverlay[hidden] { display: none; }
            #buzzCmdOverlay {
                position: fixed; inset: 0; background: rgba(10, 14, 20, 0.75);
                backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
                z-index: 100000; display: flex; align-items: flex-start; justify-content: center;
                padding-top: 12vh;
            }
            #buzzCmdBox {
                background: #151b26; border: 1px solid #2d3848; border-radius: 12px;
                width: 100%; max-width: 580px; box-shadow: 0 24px 60px rgba(0,0,0,0.8);
                overflow: hidden; font-family: system-ui, -apple-system, sans-serif;
                color: #e2e8f0;
            }
            #buzzCmdInputWrap {
                display: flex; align-items: center; gap: 10px;
                padding: 14px 18px; border-bottom: 1px solid #232d3d; background: #111620;
            }
            #buzzCmdInput {
                width: 100%; background: transparent; border: none; outline: none;
                color: #f1f5f9; font-size: 15px;
            }
            #buzzCmdInput::placeholder { color: #64748b; }
            #buzzCmdList { max-height: 320px; overflow-y: auto; padding: 6px; margin: 0; list-style: none; }
            .buzz-cmd-item {
                display: flex; align-items: center; justify-content: space-between;
                padding: 9px 12px; border-radius: 6px; cursor: pointer; font-size: 14px;
                color: #94a3b8; transition: background 0.15s, color 0.15s;
            }
            .buzz-cmd-item:hover, .buzz-cmd-item[aria-selected="true"] {
                background: rgba(255, 160, 0, 0.15); color: #f8fafc;
            }
            .buzz-cmd-item[aria-selected="true"] .buzz-cmd-cat {
                background: rgba(255, 160, 0, 0.25); color: #ffa000;
            }
            .buzz-cmd-item .buzz-cmd-cat {
                font-size: 11px; padding: 2px 6px; border-radius: 4px; white-space: nowrap;
                background: #1e2634; color: #64748b; font-family: monospace;
            }
            .buzz-cmd-empty { padding: 16px; text-align: center; color: #64748b; font-size: 13px; }
            .buzz-cmd-mark { color: #ffa000; font-weight: 600; }
            #buzzCmdFooter {
                display: flex; justify-content: space-between; align-items: center;
                padding: 8px 14px; border-top: 1px solid #232d3d; background: #0c1017;
                font-size: 11px; color: #64748b; font-family: monospace;
            }
            /* Botón de acceso: es lo que convierte el atajo en algo descubrible.
               Sin él la paleta existe pero nadie la encuentra. */
            #buzzCmdTrigger {
                display: flex; align-items: center; gap: 8px; width: 100%;
                margin: 0 0 14px; padding: 8px 10px; cursor: pointer;
                background: rgba(255,255,255,0.04); color: #94a3b8;
                border: 1px solid #2d3848; border-radius: 8px;
                font-size: 13px; font-family: inherit; transition: background 0.15s, color 0.15s;
            }
            #buzzCmdTrigger:hover { background: rgba(255,160,0,0.12); color: #e2e8f0; }
            #buzzCmdTrigger .buzz-cmd-trigger-label { flex: 1; text-align: left; }
            kbd.buzz-cmd-kbd {
                font-family: monospace; font-size: 11px; padding: 2px 6px;
                background: #1e2634; border: 1px solid #2d3848; border-radius: 4px; color: #94a3b8;
            }
            @media (prefers-reduced-motion: reduce) {
                #buzzCmdOverlay, .buzz-cmd-item { transition: none !important; }
            }
        `;
        document.head.appendChild(style);

        // --- Trigger -------------------------------------------------------
        // Un único punto de inserción para todas las plantillas, en vez de
        // editar cada cabecera: primero la cabecera del sidebar y, si la página
        // usa otra plantilla (manual.php), la cabecera de la documentación.
        const iconoTrigger = '<i class="fas fa-search buzz-cmd-trigger-icon" aria-hidden="true"></i>';
        const htmlTrigger =
            '<button type="button" id="buzzCmdTrigger" aria-haspopup="dialog">' +
            iconoTrigger +
            '<span class="buzz-cmd-trigger-label">Buscar módulo</span>' +
            '<kbd class="buzz-cmd-kbd">Ctrl K</kbd>' +
            '</button>';

        // Solo se inyecta en la cabecera del sidebar. La pagina /manual usa
        // .docs-header y no debe mostrar el buscador de modulos.
        const ancla = document.querySelector('.sidebar-header-enhanced');
        if (ancla) ancla.insertAdjacentHTML('beforeend', htmlTrigger);

        // --- Overlay -------------------------------------------------------
        const overlay = document.createElement('div');
        overlay.id = 'buzzCmdOverlay';
        overlay.hidden = true;
        overlay.innerHTML =
            '<div id="buzzCmdBox" role="dialog" aria-modal="true" aria-label="Paleta de comandos">' +
                '<div id="buzzCmdInputWrap">' +
                    '<i class="fas fa-search" style="color:#ffa000;font-size:14px;" aria-hidden="true"></i>' +
                    '<input type="text" id="buzzCmdInput" placeholder="Ir a un módulo..." autocomplete="off"' +
                        ' role="combobox" aria-expanded="true" aria-controls="buzzCmdList"' +
                        ' aria-autocomplete="list" aria-label="Buscar módulo">' +
                    '<kbd class="buzz-cmd-kbd">ESC</kbd>' +
                '</div>' +
                '<ul id="buzzCmdList" role="listbox" aria-label="Módulos disponibles"></ul>' +
                '<div id="buzzCmdFooter">' +
                    '<span>Navega con &uarr; &darr; y pulsa Enter</span>' +
                    '<span>BuzzDM</span>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);

        const input = overlay.querySelector('#buzzCmdInput');
        const list = overlay.querySelector('#buzzCmdList');
        const trigger = document.getElementById('buzzCmdTrigger');

        const modulos = leerModulosDelSidebar() || MODULOS_FALLBACK;
        let filtrados = [];
        let indice = -1;
        let elementoPrevio = null;

        // Resalta lo que el usuario ha escrito para que un nombre largo no
        // obligue a recorrerlo entero con el ratón.
        function resaltar(texto, consulta) {
            if (!consulta) return escapar(texto);
            const pos = texto.toLowerCase().indexOf(consulta.toLowerCase());
            if (pos === -1) return escapar(texto);
            return escapar(texto.slice(0, pos))
                + '<span class="buzz-cmd-mark">' + escapar(texto.slice(pos, pos + consulta.length)) + '</span>'
                + escapar(texto.slice(pos + consulta.length));
        }

        function escapar(valor) {
            return String(valor == null ? '' : valor)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function filtrar(texto) {
            const q = (texto || '').toLowerCase().trim();
            if (!q) return modulos.slice();

            return modulos.filter((m) =>
                m.titulo.toLowerCase().includes(q) ||
                m.categoria.toLowerCase().includes(q) ||
                m.url.toLowerCase().includes(q)
            );
        }

        function pintar() {
            list.innerHTML = '';
            indice = filtrados.length ? 0 : -1;

            if (!filtrados.length) {
                list.innerHTML = '<li class="buzz-cmd-empty" role="presentation">No hay módulos que coincidan</li>';
                input.removeAttribute('aria-activedescendant');
                return;
            }

            filtrados.forEach((m, i) => {
                const li = document.createElement('li');
                li.id = 'buzzCmdItem' + i;
                li.className = 'buzz-cmd-item';
                li.setAttribute('role', 'option');
                li.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
                li.innerHTML =
                    '<span class="buzz-cmd-main" style="display:flex;align-items:center;gap:10px;">' +
                        '<i class="' + escapar(m.icono) + '" style="color:#ffa000;width:16px;text-align:center;" aria-hidden="true"></i>' +
                        '<span>' + resaltar(m.titulo, input.value.trim()) + '</span>' +
                    '</span>' +
                    '<span class="buzz-cmd-cat">' + escapar(m.categoria) + '</span>';
                li.addEventListener('click', () => ir(m));
                list.appendChild(li);
            });

            sincronizarSeleccion();
        }

        function sincronizarSeleccion() {
            const items = list.querySelectorAll('.buzz-cmd-item');
            items.forEach((el, i) => el.setAttribute('aria-selected', i === indice ? 'true' : 'false'));

            if (indice >= 0 && items[indice]) {
                items[indice].scrollIntoView({ block: 'nearest' });
                input.setAttribute('aria-activedescendant', items[indice].id);
            } else {
                input.removeAttribute('aria-activedescendant');
            }
        }

        function mover(delta) {
            const total = filtrados.length;
            if (!total) return;
            indice = (indice + delta + total) % total;
            sincronizarSeleccion();
        }

        function ir(modulo) {
            cerrar();
            window.location.href = modulo.url;
        }

        function abrir() {
            elementoPrevio = document.activeElement;
            overlay.hidden = false;
            input.value = '';
            filtrados = filtrar('');
            pintar();
            document.body.style.overflow = 'hidden';
            input.focus();
            if (trigger) trigger.setAttribute('aria-expanded', 'true');
        }

        function cerrar() {
            overlay.hidden = true;
            document.body.style.overflow = '';
            if (trigger) trigger.removeAttribute('aria-expanded');
            // Devolver el foco evita que al cerrarla el usuario quede con el
            // foco en un input oculto y tenga que tabular para salir.
            if (elementoPrevio && typeof elementoPrevio.focus === 'function') elementoPrevio.focus();
        }

        input.addEventListener('input', () => {
            filtrados = filtrar(input.value);
            pintar();
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowDown') { e.preventDefault(); mover(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); mover(-1); }
            else if (e.key === 'Enter') {
                e.preventDefault();
                if (indice >= 0 && filtrados[indice]) ir(filtrados[indice]);
            }
        });

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) cerrar();
        });

        if (trigger) {
            trigger.addEventListener('click', abrir);

            document.addEventListener('keydown', (e) => {
                const abierto = !overlay.hidden;

                if ((e.ctrlKey || e.metaKey) && !e.altKey && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    abierto ? cerrar() : abrir();
                    return;
                }

                // Escape global: si la paleta está abierta se cierra aunque el foco
                // no esté en el input. Antes solo respondía el input, así que al
                // perder el foco la paleta quedaba bloqueada.
                if (e.key === 'Escape' && abierto) {
                    e.preventDefault();
                    cerrar();
                }
            });
        }
    }

    // El script se carga al final del body, pero se protege igual: si mañana
    // alguien lo incluye en el <head>, aquí se espera al DOM en vez de fallar
    // con document.body === null.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar, { once: true });
    } else {
        inicializar();
    }
})();
