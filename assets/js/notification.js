/**
 * Custom Notification/Toast Component
 */

const Notification = {
    container: null,

    /**
     * Initialize notification container
     */
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'notification-container';
            this.container.style.cssText = `
                position: fixed;
                top: 80px;
                right: 24px;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                gap: 12px;
                max-width: 400px;
            `;
            document.body.appendChild(this.container);
        }
    },

    /**
     * Show notification
     */
    show({ message, type = 'info', duration = 3000 }) {
        this.init();

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const colors = {
            success: '#3ecf8e',
            error: '#ff4444',
            warning: '#f5a623',
            info: '#3b9eff'
        };

        const toast = document.createElement('div');
        toast.style.cssText = `
            background: #0a0a0a;
            border: 1px solid #333333;
            border-left: 3px solid ${colors[type]};
            border-radius: 8px;
            padding: 14px 18px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            animation: slideIn 200ms ease;
            position: relative;
            overflow: hidden;
        `;

        toast.innerHTML = `
            <i class="fas ${icons[type]}" style="color: ${colors[type]}; font-size: 18px;"></i>
            <span style="flex: 1; color: #ededed; font-size: 14px; font-weight: 500;">${message}</span>
            <i class="fas fa-times" style="color: #555; font-size: 13px;"></i>
            <div style="
                position: absolute;
                bottom: 0;
                left: 0;
                height: 3px;
                background: ${colors[type]};
                animation: progress ${duration}ms linear;
            "></div>
        `;

        // Add CSS animations
        if (!document.getElementById('notification-animations')) {
            const style = document.createElement('style');
            style.id = 'notification-animations';
            style.textContent = `
                @keyframes slideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
                @keyframes progress {
                    from {
                        width: 100%;
                    }
                    to {
                        width: 0%;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        // Click to dismiss
        toast.onclick = () => this.dismiss(toast);

        this.container.appendChild(toast);

        // Auto dismiss
        if (duration > 0) {
            setTimeout(() => this.dismiss(toast), duration);
        }
    },

    /**
     * Dismiss notification
     */
    dismiss(toast) {
        toast.style.animation = 'slideOut 200ms ease';
        setTimeout(() => toast.remove(), 200);
    }
};

// Make Notification available globally
window.Notification = Notification;
