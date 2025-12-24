// ============================================
// MODAL UTILITY FUNCTIONS
// Professional modal management (no animations)
// ============================================

const ModalUtils = {
    // Open modal instantly
    openModal: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Show modal instantly
        modal.style.display = 'flex';

        // Add event listeners
        this.addModalEventListeners(modalId);

        // Focus first input
        setTimeout(() => {
            const firstInput = modal.querySelector('input:not([type="hidden"]), select, textarea');
            if (firstInput) firstInput.focus();
        }, 50);

        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    },

    // Close modal instantly
    closeModal: function(modalId, callback) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Hide modal instantly
        modal.style.display = 'none';

        // Remove event listeners
        this.removeModalEventListeners(modalId);

        // Restore body scroll
        document.body.style.overflow = '';

        // Execute callback if provided
        if (callback && typeof callback === 'function') {
            callback();
        }
    },

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
    addModalEventListeners: function(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Close on ESC key
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                this.closeModal(modalId);
            }
        };
        modal._escHandler = escHandler;
        document.addEventListener('keydown', escHandler);

        // Close on outside click
        const outsideClickHandler = (e) => {
            if (e.target === modal) {
                this.closeModal(modalId);
            }
        };
        modal._outsideClickHandler = outsideClickHandler;
        modal.addEventListener('click', outsideClickHandler);
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
    }
};

// Make globally available
window.ModalUtils = ModalUtils;

// Convenience functions
window.openModal = (modalId) => ModalUtils.openModal(modalId);
window.closeModal = (modalId, callback) => ModalUtils.closeModal(modalId, callback);
window.showModalLoading = (modalId, message) => ModalUtils.showLoading(modalId, message);
window.hideModalLoading = (modalId) => ModalUtils.hideLoading(modalId);
