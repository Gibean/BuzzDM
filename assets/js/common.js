
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
        day: 'numeric'
    });
}

/**
 * Formats a number as a currency string.
 * @param {number} amount The amount to format.
 * @returns {string} The formatted currency string.
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('es-VE', { style: 'currency', currency: 'VES' }).format(amount);
}

// --- Notification and Modal System ---

/**
 * Creates and shows a toast notification in the top-right corner.
 * @param {string} message The message to display.
 * @param {string} type The type of toast (success, error, info). Defaults to 'info'.
 * @param {number} duration Duration in ms. Defaults to 3000.
 */
function showToast(message, type = 'info', duration = 3000) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;

    container.appendChild(toast);

    // Animate in
    setTimeout(() => {
        toast.classList.add('show');
    }, 100); // Small delay to allow CSS transition

    // Hide and remove after duration
    setTimeout(() => {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => toast.remove());
    }, duration);
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
function showModal({ title, message, type = 'info', onConfirm, onCancel }) {
    let overlay = document.getElementById('modal-overlay');
    if (!overlay) {
        // Create the modal structure if it doesn't exist
        const modalHTML = `
            <div id="modal-dialog">
                <h3 id="modal-title"></h3>
                <p id="modal-message"></p>
                <div id="modal-buttons"></div>
            </div>
        `;
        overlay = document.createElement('div');
        overlay.id = 'modal-overlay';
        overlay.innerHTML = modalHTML;
        document.body.appendChild(overlay);
    }

    const dialog = overlay.querySelector('#modal-dialog');
    const modalTitle = overlay.querySelector('#modal-title');
    const modalMessage = overlay.querySelector('#modal-message');
    const modalButtons = overlay.querySelector('#modal-buttons');

    // Reset
    modalButtons.innerHTML = '';
    dialog.className = 'modal-dialog'; // Reset classes

    // Set content
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    dialog.classList.add(`modal-${type}`);

    // Create buttons based on type
    if (type === 'confirm') {
        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'btn btn-buzz';
        confirmBtn.textContent = 'Confirmar';
        confirmBtn.onclick = () => {
            if (onConfirm) onConfirm();
            hideModal();
        };

        const cancelBtn = document.createElement('button');
        cancelBtn.className = 'btn btn-outline-buzz';
        cancelBtn.textContent = 'Cancelar';
        cancelBtn.onclick = () => {
            if (onCancel) onCancel();
            hideModal();
        };
        modalButtons.append(cancelBtn, confirmBtn);
    } else {
        const okBtn = document.createElement('button');
        okBtn.className = 'btn btn-buzz';
        okBtn.textContent = 'OK';
        okBtn.onclick = () => {
            if (onConfirm) onConfirm(); // Can be used as a simple OK callback
            hideModal();
        };
        modalButtons.appendChild(okBtn);
    }

    // Show the modal
    overlay.style.display = 'flex';
    setTimeout(() => overlay.classList.add('show'), 10);
}

function hideModal() {
    const overlay = document.getElementById('modal-overlay');
    if (overlay) {
        overlay.classList.remove('show');
        overlay.addEventListener('transitionend', () => {
            if (!overlay.classList.contains('show')) {
                overlay.style.display = 'none';
            }
        }, { once: true });
    }
}

function logout() {
    window.location.href = '/buzz/php/logout.php';
}
