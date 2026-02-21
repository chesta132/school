/**
 * Main JavaScript - Common Functions
 */

/**
 * Handle logout
 */
async function handleLogout() {
    Modal.confirm({
        title: 'Logout',
        message: 'Apakah Anda yakin ingin keluar?',
        confirmText: 'Logout',
        cancelText: 'Batal',
        onConfirm: async () => {
            try {
                const response = await fetch('/api/logout.php', {
                    method: 'POST'
                });
                const data = await response.json();
                
                if (data.success) {
                    Notification.show({ message: 'Logout berhasil', type: 'success' });
                    setTimeout(() => window.location.href = '/login', 1000);
                }
            } catch (error) {
                Notification.show({ message: 'Terjadi kesalahan', type: 'error' });
            }
        }
    });
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
}

/**
 * Format datetime
 */
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Get stock badge class
 */
function getStockBadgeClass(stock) {
    if (stock < 10) return 'badge-danger';
    if (stock < 30) return 'badge-warning';
    return 'badge-success';
}
