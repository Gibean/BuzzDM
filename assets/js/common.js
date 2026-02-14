
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

    // Create Toast Card
    const toast = document.createElement('div');
    toast.className = `toast-card ${type}`;
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

    const iconClass = icons[type] || icons.info;
    const titleText = titles[type] || 'Notificación';

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

    // Update classes
    toast.className = `toast-card ${type} show`;

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

    const iconClass = icons[type] || icons.info;
    const titleText = titles[type] || 'Notificación';
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
    card.addEventListener('animationend', () => {
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
            card.addEventListener('animationend', () => card.remove());
        }, index * 50); // Staggered removal
    });
    setTimeout(updateClearButtonVisibility, toasts.length * 50 + 300);
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
    window.location.href = "php/logout.php"
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
