document.addEventListener('DOMContentLoaded', () => {
    // Crea el contenedor para las notificaciones toast si no existe.
    if (!document.getElementById('toast-container')) {
        const container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
});

/**
 * Muestra una notificación estilo toast en la esquina superior derecha.
 * @param {string} message El mensaje a mostrar.
 * @param {string} type Tipo de notificación (success, error, info, warning). Por defecto es 'info'.
 * @param {number} duration Duración en milisegundos. Por defecto es 4000.
 */
function showNotification(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `<span>${message}</span>`;

    container.appendChild(toast);

    // Animar la entrada
    setTimeout(() => {
        toast.classList.add('show');
    }, 100); // Pequeño retraso para asegurar que la transición ocurra

    // Ocultar y eliminar la notificación después de la duración especificada
    setTimeout(() => {
        toast.classList.remove('show');
        toast.addEventListener('transitionend', () => toast.remove());
    }, duration);
}

/**
 * Muestra un diálogo modal genérico.
 * @param {string} title Título del modal.
 * @param {string} message Mensaje o contenido HTML del cuerpo del modal.
 * @param {string} type Tipo de modal para colorear el encabezado (success, error, warning, info, confirm).
 * @param {Array<Object>} buttons Array de objetos para los botones del footer.
 *                                Cada objeto: { text, class, onClick }
 */
function showModal(title, message, type, buttons = []) {
    // Eliminar cualquier modal existente
    const existingModal = document.querySelector('.modal-overlay');
    if (existingModal) existingModal.remove();

    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'modal-overlay';

    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';

    const modalHeader = document.createElement('div');
    modalHeader.className = `modal-header modal-header-${type}`;
    modalHeader.textContent = title;

    const modalBody = document.createElement('div');
    modalBody.className = 'modal-body';
    modalBody.innerHTML = message;

    const modalFooter = document.createElement('div');
    modalFooter.className = 'modal-footer';

    // Función para cerrar el modal
    const closeModal = () => {
        modalOverlay.classList.remove('show');
        modalOverlay.addEventListener('transitionend', () => modalOverlay.remove());
    };

    buttons.forEach(btnInfo => {
        const button = document.createElement('button');
        button.className = `modal-button ${btnInfo.class}`;
        button.textContent = btnInfo.text;
        button.onclick = (e) => {
            if (btnInfo.onClick) {
                btnInfo.onClick(e);
            }
            closeModal(); // Cierra el modal por defecto al hacer clic en un botón
        };
        modalFooter.appendChild(button);
    });

    modalContent.append(modalHeader, modalBody, modalFooter);
    modalOverlay.appendChild(modalContent);
    document.body.appendChild(modalOverlay);

    // Animar la entrada
    setTimeout(() => {
        modalOverlay.classList.add('show');
    }, 50);
}

/**
 * Muestra un modal de confirmación simple con botones Aceptar/Cancelar.
 * @param {string} title Título del diálogo.
 * @param {string} message Mensaje de confirmación.
 * @param {function} onConfirm Función a ejecutar si el usuario confirma.
 */
function showConfirmation(title, message, onConfirm) {
    showModal(title, message, 'confirm', [
        {
            text: 'Cancelar',
            class: 'btn-secondary',
            onClick: () => {}
        },
        {
            text: 'Aceptar',
            class: 'btn-primary',
            onClick: onConfirm
        }
    ]);
}

/**
 * Muestra un modal de error simple.
 * @param {string} title Título del error.
 * @param {string} message Mensaje del error.
 */
function showErrorModal(title, message) {
    showModal(title, message, 'error', [
        {
            text: 'Cerrar',
            class: 'btn-danger',
            onClick: () => {}
        }
    ]);
}
