/**
 * Custom Modal Component
 * Replaces native JavaScript dialogs (alert, confirm, prompt)
 * Supports types: 'default' | 'info' | 'success' | 'warning' | 'danger'
 */

const Modal = {

    _typeConfig: {
        default: { icon: 'fa-sliders-h',        label: 'default' },
        info:    { icon: 'fa-info-circle',       label: 'info'    },
        success: { icon: 'fa-check-circle',      label: 'success' },
        warning: { icon: 'fa-exclamation-triangle', label: 'warning' },
        danger:  { icon: 'fa-trash-alt',         label: 'danger'  },
    },

    /**
     * Show confirm modal
     * @param {object} opts
     * @param {string} opts.title
     * @param {string} opts.message
     * @param {string} [opts.type='default']  - 'default'|'info'|'success'|'warning'|'danger'
     * @param {string} [opts.confirmText]
     * @param {string} [opts.cancelText]
     * @param {string} [opts.icon]            - override fa icon class (tanpa prefix 'fa-')
     * @param {Function} opts.onConfirm
     */
    confirm({ title = 'Konfirmasi', message, type = 'default', confirmText = 'Konfirmasi', cancelText = 'Batal', icon, onConfirm }) {
        const cfg  = this._typeConfig[type] || this._typeConfig.default;
        const ico  = icon ? `fa-${icon}` : cfg.icon;

        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal-container modal-confirm type-${type}">
                <div class="modal-header">
                    <div class="modal-header-icon type-${type}">
                        <i class="fas ${ico}"></i>
                    </div>
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

        overlay.querySelector('.modal-confirm-btn').onclick = () => {
            if (onConfirm) onConfirm();
            this.close(overlay.querySelector('.modal-confirm-btn'));
        };

        this._setupCloseHandlers(overlay);
        return overlay;
    },

    /**
     * Show alert modal
     * @param {object} opts
     * @param {string} opts.title
     * @param {string} opts.message
     * @param {string} [opts.type='info']
     */
    alert({ title = 'Info', message, type = 'info' }) {
        const icons = {
            success: 'fa-check-circle',
            error:   'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info:    'fa-info-circle',
            danger:  'fa-times-circle',
        };

        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal-container modal-alert type-${type}">
                <div class="modal-body">
                    <div class="modal-alert-icon ${type}">
                        <i class="fas ${icons[type] || icons.info}"></i>
                    </div>
                    <h3 class="modal-title" style="text-align:center;">${title}</h3>
                    <p class="modal-alert-message">${message}</p>
                </div>
                <div class="modal-footer" style="justify-content:center; border-top: none; padding-top:0;">
                    <button class="btn btn-primary" style="min-width:100px;" onclick="Modal.close(this)">OK</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        this._setupCloseHandlers(overlay);
        return overlay;
    },

    /**
     * Show form modal
     * @param {object} opts
     * @param {string} opts.title
     * @param {string} opts.content       - HTML string
     * @param {string} [opts.type='default']
     * @param {string} [opts.icon]        - override fa icon class (tanpa prefix 'fa-')
     * @param {string} [opts.size]        - 'medium' | 'large'
     * @param {Function} [opts.onSubmit]
     */
    form({ title, content, type = 'default', icon, onSubmit, size = 'medium' }) {
        const cfg = this._typeConfig[type] || this._typeConfig.default;
        const ico = icon ? `fa-${icon}` : cfg.icon;
        const maxW = size === 'large' ? '800px' : '520px';

        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal-container modal-form type-${type}" style="max-width:${maxW}">
                <div class="modal-header">
                    <div class="modal-header-icon type-${type}">
                        <i class="fas ${ico}"></i>
                    </div>
                    <h3 class="modal-title">${title}</h3>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        const form = overlay.querySelector('form');
        if (form && onSubmit) {
            form.onsubmit = (e) => {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(form));
                onSubmit(data, overlay);
            };
        }

        this._setupCloseHandlers(overlay);
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
            <div class="modal-container" style="max-width:300px;">
                <div class="modal-loading">
                    <div class="modal-loading-spinner"></div>
                    <p class="modal-loading-text">${message}</p>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        overlay.onclick = (e) => e.stopPropagation();
        return overlay;
    },

    /**
     * Close modal
     */
    close(element) {
        const overlay = element?.closest?.('.modal-overlay') ?? document.querySelector('.modal-overlay');
        if (!overlay) return;
        overlay.classList.add('to-close');
        overlay.querySelector('.modal-container')?.classList.add('to-close');
        setTimeout(() => overlay.remove(), 200);
    },

    /**
     * Close loading modal
     */
    closeLoading() {
        const el = document.getElementById('loading-modal');
        if (el) this.close(el);
    },

    /**
     * @private
     */
    _setupCloseHandlers(overlay) {
        const handleEsc = (e) => {
            if (e.key === 'Escape') {
                this.close(overlay);
                document.removeEventListener('keydown', handleEsc);
            }
        };
        document.addEventListener('keydown', handleEsc);

        overlay.onclick = (e) => {
            if (e.target === overlay) this.close(overlay);
        };
    }
};

window.Modal = Modal;
