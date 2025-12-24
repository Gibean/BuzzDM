// ============================================
// TOAST NOTIFICATION SYSTEM
// Professional toast notifications with queue
// ============================================

const ToastSystem = {
    container: null,
    toasts: [],
    idCounter: 0,

    // Initialize toast container
    init: function() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    // Show toast notification
    show: function(message, type = 'info', duration = 5000, title = null) {
        this.init();

        const toastId = 'toast-' + (++this.idCounter);
        const toast = this.createToast(toastId, message, type, duration, title);

        this.container.appendChild(toast);
        this.toasts.push({ id: toastId, element: toast, timeout: null });

        // Trigger animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Auto dismiss
        if (duration > 0) {
            const timeout = setTimeout(() => {
                this.hide(toastId);
            }, duration);

            // Store timeout reference
            const toastObj = this.toasts.find(t => t.id === toastId);
            if (toastObj) {
                toastObj.timeout = timeout;
            }

            // Animate progress bar
            const progressBar = toast.querySelector('.toast-progress');
            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.style.transition = `width ${duration}ms linear`;
                setTimeout(() => {
                    progressBar.style.width = '0%';
                }, 10);
            }
        }

        return toastId;
    },

    // Create toast element
    createToast: function(id, message, type, duration, title) {
        const toast = document.createElement('div');
        toast.id = id;
        toast.className = `toast toast-${type}`;

        // Get icon based on type
        const icon = this.getIcon(type);

        // Build content
        let contentHTML = '';
        if (title) {
            contentHTML = `
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
            `;
        } else {
            contentHTML = `
                <div class="toast-content">
                    <div class="toast-message">${message}</div>
                </div>
            `;
        }

        toast.innerHTML = `
            <i class="toast-icon ${icon}"></i>
            ${contentHTML}
            <button class="toast-close" onclick="ToastSystem.hide('${id}')" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
            ${duration > 0 ? '<div class="toast-progress"></div>' : ''}
        `;

        return toast;
    },

    // Get icon for toast type
    getIcon: function(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    },

    // Hide toast
    hide: function(toastId) {
        const toastObj = this.toasts.find(t => t.id === toastId);
        if (!toastObj) return;

        // Clear timeout if exists
        if (toastObj.timeout) {
            clearTimeout(toastObj.timeout);
        }

        // Add hiding class
        toastObj.element.classList.add('hiding');
        toastObj.element.classList.remove('show');

        // Remove after animation
        setTimeout(() => {
            if (toastObj.element && toastObj.element.parentNode) {
                toastObj.element.parentNode.removeChild(toastObj.element);
            }
            // Remove from array
            this.toasts = this.toasts.filter(t => t.id !== toastId);
        }, 400);
    },

    // Convenience methods
    success: function(message, title = 'Éxito', duration = 5000) {
        return this.show(message, 'success', duration, title);
    },

    error: function(message, title = 'Error', duration = 6000) {
        return this.show(message, 'error', duration, title);
    },

    warning: function(message, title = 'Advertencia', duration = 5000) {
        return this.show(message, 'warning', duration, title);
    },

    info: function(message, title = null, duration = 5000) {
        return this.show(message, 'info', duration, title);
    },

    // Clear all toasts
    clearAll: function() {
        this.toasts.forEach(toast => {
            if (toast.timeout) {
                clearTimeout(toast.timeout);
            }
            if (toast.element && toast.element.parentNode) {
                toast.element.parentNode.removeChild(toast.element);
            }
        });
        this.toasts = [];
    }
};

// Make globally available
window.ToastSystem = ToastSystem;

// Convenience functions
window.showToast = (message, type, duration, title) => ToastSystem.show(message, type, duration, title);
window.toastSuccess = (message, title, duration) => ToastSystem.success(message, title, duration);
window.toastError = (message, title, duration) => ToastSystem.error(message, title, duration);
window.toastWarning = (message, title, duration) => ToastSystem.warning(message, title, duration);
window.toastInfo = (message, title, duration) => ToastSystem.info(message, title, duration);
