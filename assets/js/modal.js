// ============================================
// MODAL UTILITY FUNCTIONS
// Professional modal management with unsaved-changes guard
// ============================================

const ModalUtils = {
    // Open modal instantly
    openModal: function(modalId, options = {}) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Show modal instantly
        modal.style.display = 'flex';

        // Snapshot form state for dirty-checking (skip if guardUnsaved === false)
        if (options.guardUnsaved !== false) {
            setTimeout(() => this._snapshotFormState(modalId), 80);
        }
        // Store guard preference on modal element
        modal._guardUnsaved = options.guardUnsaved !== false;

        // Add event listeners
        this.addModalEventListeners(modalId, options);

        // Focus first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) firstInput.focus();
        }, 50);

        // Prevent body scroll
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
    },

    // Close modal instantly (force = skip dirty check)
    closeModal: function(modalId, callbackOrForce, forceParam) {
        // Normalize arguments: closeModal(id), closeModal(id, cb), closeModal(id, force), closeModal(id, cb, force)
        let callback = null;
        let force = false;

        if (typeof callbackOrForce === 'function') {
            callback = callbackOrForce;
            force = !!forceParam;
        } else if (typeof callbackOrForce === 'boolean') {
            force = callbackOrForce;
        }
        // Fallback: always honour forceParam if set (covers closeModal(id, null, true))
        if (!force && forceParam === true) {
            force = true;
        }

        const modal = document.getElementById(modalId);
        if (!modal) return;

        // If not forced and guard is active, check for dirty form
        if (!force && modal._guardUnsaved && this._isFormDirty(modalId)) {
            this._showUnsavedGuard(modalId, callback);
            return;
        }

        // Dismiss any open guard dialog
        this._dismissGuard(modalId);

        // Hide modal instantly
        modal.style.display = 'none';

        // Clean up state
        delete modal._formSnapshot;
        delete modal._guardUnsaved;

        // Remove event listeners
        this.removeModalEventListeners(modalId);

        // Restore body scroll (only if no other modals are open)
        const openModals = document.querySelectorAll('.modal-buzz[style*="flex"]');
        if (openModals.length === 0) {
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        }

        // Execute callback if provided
        if (callback && typeof callback === 'function') {
            callback();
        }
    },

    // ── Form dirty-checking helpers ──────────────────────

    // Snapshot the current form values so we can detect changes later
    _snapshotFormState: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const form = modal.querySelector('form');
        if (!form) return;

        modal._formSnapshot = this._serializeForm(form);
    },

    // Serialize form into a comparable string
    _serializeForm: function(form) {
        const parts = [];

        form.querySelectorAll('input, select, textarea').forEach(el => {
            // Skip hidden inputs (IDs, tokens, etc.) and disabled/readonly inputs
            if (el.type === 'hidden') return;

            const key = el.id || el.name || '';
            if (!key) return;

            if (el.type === 'checkbox' || el.type === 'radio') {
                parts.push(key + '=' + el.checked);
            } else {
                parts.push(key + '=' + (el.value || ''));
            }
        });

        return parts.join('&');
    },

    // Check if the form has been modified since it was opened
    _isFormDirty: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal || !modal._formSnapshot) return false;

        const form = modal.querySelector('form');
        if (!form) return false;

        const current = this._serializeForm(form);
        return current !== modal._formSnapshot;
    },

    // ── Unsaved-changes guard UI ─────────────────────────

    _showUnsavedGuard: function(modalId, callback) {
        // Use the existing showModal confirmation dialog from common.js
        if (typeof showModal !== 'function') {
            // Fallback: force-close if showModal is not available
            this.closeModal(modalId, callback, true);
            return;
        }

        showModal({
            title: 'Cambios sin guardar',
            message: 'Si sales ahora, los datos ingresados se perderán. ¿Deseas descartar los cambios?',
            type: 'confirm',
            onConfirm: () => {
                this.closeModal(modalId, callback, true);
            }
        });
    },

    _dismissGuard: function(modalId) {
        // Nothing to dismiss — showModal manages its own lifecycle via hideModal()
    },

    // ── Existing functionality ───────────────────────────

    // Show loading state
    showLoading: function(modalId, message = 'Cargando...') {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const modalContent = modal.querySelector('.modal-content-buzz');
        if (!modalContent) return;

        // Check if loading overlay already exists
        let loadingOverlay = modalContent.querySelector('.modal-loading');
        if (!loadingOverlay) {
            loadingOverlay = document.createElement('div');
            loadingOverlay.className = 'modal-loading';
            loadingOverlay.innerHTML = `
                <div style="text-align: center;">
                    <div class="spinner"></div>
                    <p class="mt-3 text-muted font-mono">${message}</p>
                </div>
            `;
            modalContent.appendChild(loadingOverlay);
        }
    },

    // Hide loading state
    hideLoading: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const loadingOverlay = modal.querySelector('.modal-loading');
        if (loadingOverlay) {
            loadingOverlay.remove();
        }
    },

    // Add event listeners for modal
    addModalEventListeners: function(modalId, options = {}) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Close on ESC key (only if not static)
        if (!options.static) {
            const escHandler = (e) => {
                if (e.key === 'Escape') {
                    this.closeModal(modalId);
                }
            };
            modal._escHandler = escHandler;
            document.addEventListener('keydown', escHandler);
        }

        // Close on outside click (only if not static)
        if (!options.static) {
            const outsideClickHandler = (e) => {
                if (e.target === modal) {
                    this.closeModal(modalId);
                }
            };
            modal._outsideClickHandler = outsideClickHandler;
            modal.addEventListener('click', outsideClickHandler);
        }
    },

    // Remove event listeners
    removeModalEventListeners: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        if (modal._escHandler) {
            document.removeEventListener('keydown', modal._escHandler);
            delete modal._escHandler;
        }

        if (modal._outsideClickHandler) {
            modal.removeEventListener('click', modal._outsideClickHandler);
            delete modal._outsideClickHandler;
        }
    },

    // Reset form in modal
    resetForm: function(modalId, formId) {
        const form = document.getElementById(formId);
        if (form) {
            form.reset();

            // Clear validation states
            const inputs = form.querySelectorAll('.is-invalid, .is-valid');
            inputs.forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
            });
        }
    },

    // Set modal title
    setTitle: function(modalId, title) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const titleElement = modal.querySelector('.modal-header-buzz h5, .modal-header-buzz h4');
        if (titleElement) {
            // Preserve icon if present
            const icon = titleElement.querySelector('i');
            if (icon) {
                titleElement.innerHTML = '';
                titleElement.appendChild(icon);
                titleElement.appendChild(document.createTextNode(' ' + title));
            } else {
                titleElement.textContent = title;
            }
        }
    },

    // Force-close helper (for programmatic closes after save)
    forceClose: function(modalId, callback) {
        this.closeModal(modalId, callback, true);
    }
};

// Make globally available
window.ModalUtils = ModalUtils;

window.openModal = (modalId, options = {}) => ModalUtils.openModal(modalId, options);
window.closeModal = (modalId, callbackOrForce, forceParam) => ModalUtils.closeModal(modalId, callbackOrForce, forceParam);
window.showModalLoading = (modalId, message) => ModalUtils.showLoading(modalId, message);
window.hideModalLoading = (modalId) => ModalUtils.hideLoading(modalId);
window.setTitle = (modalId, title) => ModalUtils.setTitle(modalId, title);
