/**
 * Custom Modal Component
 * Replaces native JavaScript dialogs (alert, confirm, prompt)
 */

const Modal = {
    /**
     * Show alert modal
     */
    alert({ title = 'Info', message, type = 'info' }) {
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal-container modal-alert">
                <div class="modal-body">
                    <div class="modal-alert-icon ${type}">
                        <i class="fas ${icons[type]}"></i>
                    </div>
                    <h3 class="modal-title">${title}</h3>
                    <p class="modal-alert-message">${message}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" onclick="Modal.close(this)">OK</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        this.setupCloseHandlers(overlay);
    },

    /**
     * Show confirm modal
     */
    confirm({ title = 'Konfirmasi', message, confirmText = 'Konfirmasi', cancelText = 'Batal', onConfirm }) {
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal-container modal-confirm">
                <div class="modal-header">
                    <h3 class="modal-title">${title}</h3>
                </div>
                <div class="modal-body">
                    <p class="modal-confirm-message">${message}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="Modal.close(this)">${cancelText}</button>
                    <button class="btn btn-primary modal-confirm-btn">${confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        
        const confirmBtn = overlay.querySelector('.modal-confirm-btn');
        confirmBtn.onclick = () => {
            if (onConfirm) onConfirm();
            this.close(confirmBtn);
        };

        this.setupCloseHandlers(overlay);
    },

    /**
     * Show form modal
     */
    form({ title, content, onSubmit, size = 'medium' }) {
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal-container modal-form" style="max-width: ${size === 'large' ? '800px' : '500px'}">
                <div class="modal-header">
                    <h3 class="modal-title">${title}</h3>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        // Setup form submit handler
        const form = overlay.querySelector('form');
        if (form && onSubmit) {
            form.onsubmit = (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);
                onSubmit(data, overlay);
            };
        }

        this.setupCloseHandlers(overlay);
        return overlay;
    },

    /**
     * Show loading modal
     */
    loading(message = 'Loading...') {
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.id = 'loading-modal';
        overlay.innerHTML = `
            <div class="modal-container">
                <div class="modal-loading">
                    <div class="modal-loading-spinner"></div>
                    <p class="modal-loading-text">${message}</p>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        
        // Don't allow closing loading modal
        overlay.onclick = (e) => e.stopPropagation();
    },

    /**
     * Close modal
     */
    close(element) {
        const overlay = element.closest ? element.closest('.modal-overlay') : document.querySelector('.modal-overlay');
        if (overlay) {
            overlay.classList.add('to-close');
            overlay.querySelector('.modal-container').classList.add('to-close');
            setTimeout(() => overlay.remove(), 200);
        }
    },

    /**
     * Close loading modal specifically
     */
    closeLoading() {
        const loadingModal = document.getElementById('loading-modal');
        if (loadingModal) {
            this.close(loadingModal);
        }
    },

    /**
     * Setup close handlers (ESC key, click outside)
     */
    setupCloseHandlers(overlay) {
        // Close on ESC key
        const handleEsc = (e) => {
            if (e.key === 'Escape') {
                this.close(overlay);
                document.removeEventListener('keydown', handleEsc);
            }
        };
        document.addEventListener('keydown', handleEsc);

        // Close on backdrop click
        overlay.onclick = (e) => {
            if (e.target === overlay) {
                this.close(overlay);
            }
        };
    }
};

// Make Modal available globally
window.Modal = Modal;
