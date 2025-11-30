/**
 * Confirmation Modal Component
 * Provides a reusable modal for confirming destructive actions.
 */

(function() {
    // Create modal HTML structure
    const modalHTML = `
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content buzz-modal-dark-override">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title font-primary text-warning" id="confirmationModalTitle">
                        <i class="fas fa-exclamation-triangle me-2"></i> Confirmación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmationModalMessage" class="text-muted mb-0"></p>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="confirmationModalCancelBtn">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmationModalConfirmBtn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
    `;

    // Append modal to body when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        if (!document.getElementById('confirmationModal')) {
            document.body.insertAdjacentHTML('beforeend', modalHTML);
        }
    });

    // Global function to show confirmation
    window.showConfirmation = function(title, message, onConfirm, onCancel) {
        const modalEl = document.getElementById('confirmationModal');
        const titleEl = document.getElementById('confirmationModalTitle');
        const messageEl = document.getElementById('confirmationModalMessage');
        const confirmBtn = document.getElementById('confirmationModalConfirmBtn');
        const cancelBtn = document.getElementById('confirmationModalCancelBtn');

        if (!modalEl) {
            console.error('Confirmation modal not found in DOM');
            return;
        }

        // Update content
        if (title) titleEl.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> ${title}`;
        messageEl.textContent = message || '¿Está seguro de realizar esta acción?';

        // Initialize Bootstrap modal
        const modal = new bootstrap.Modal(modalEl);

        // Handle Confirm
        const handleConfirm = () => {
            if (typeof onConfirm === 'function') {
                onConfirm();
            }
            modal.hide();
            cleanup();
        };

        // Handle Cancel
        const handleCancel = () => {
            if (typeof onCancel === 'function') {
                onCancel();
            }
            // Modal is closed by data-bs-dismiss, just cleanup listeners
            cleanup();
        };

        // Cleanup event listeners to prevent duplicates
        const cleanup = () => {
            confirmBtn.removeEventListener('click', handleConfirm);
            cancelBtn.removeEventListener('click', handleCancel);
            modalEl.removeEventListener('hidden.bs.modal', cleanup);
        };

        confirmBtn.addEventListener('click', handleConfirm);
        cancelBtn.addEventListener('click', handleCancel);

        // Also cleanup on modal hide (e.g. clicking outside or X)
        modalEl.addEventListener('hidden.bs.modal', cleanup);

        modal.show();
    };
})();
