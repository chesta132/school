// Pagination Component
class Pagination {
    constructor(options) {
        this.itemsPerPage = options.itemsPerPage || 10;
        this.currentPage = 1;
        this.allItems = [];
        this.filteredItems = [];
        this.containerId = options.containerId;
        this.onPageChange = options.onPageChange;
        this.perPageOptions = options.perPageOptions || [10, 25, 50, 100];
    }

    setItems(items) {
        this.allItems = items;
        this.filteredItems = items;
        this.currentPage = 1;
        this.render();
    }

    setFilteredItems(items) {
        this.filteredItems = items;
        this.currentPage = 1;
        this.render();
    }

    getCurrentPageItems() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.filteredItems.slice(start, end);
    }

    getTotalPages() {
        return Math.ceil(this.filteredItems.length / this.itemsPerPage);
    }

    goToPage(page) {
        const totalPages = this.getTotalPages();
        if (page < 1 || page > totalPages) return;
        
        this.currentPage = page;
        this.render();
        if (this.onPageChange) {
            this.onPageChange(this.getCurrentPageItems());
        }
    }

    changeItemsPerPage(count) {
        this.itemsPerPage = parseInt(count);
        this.currentPage = 1;
        this.render();
        if (this.onPageChange) {
            this.onPageChange(this.getCurrentPageItems());
        }
    }

    render() {
        const container = document.getElementById(this.containerId);
        if (!container) return;

        const totalPages = this.getTotalPages();
        const totalItems = this.filteredItems.length;
        const start = (this.currentPage - 1) * this.itemsPerPage + 1;
        const end = Math.min(start + this.itemsPerPage - 1, totalItems);

        if (totalItems === 0) {
            container.innerHTML = '';
            return;
        }

        // Generate page numbers
        const pageNumbers = this.generatePageNumbers(totalPages);

        container.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 16px; border-top: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="color: var(--text-muted); font-size: 14px;">
                        Tampilkan
                    </span>
                    <select onchange="window.${this.containerId}_instance.changeItemsPerPage(this.value)" 
                            class="form-control" 
                            style="width: 80px; padding: 6px 8px;">
                        ${this.perPageOptions.map(opt => 
                            `<option value="${opt}" ${opt === this.itemsPerPage ? 'selected' : ''}>${opt}</option>`
                        ).join('')}
                    </select>
                    <span style="color: var(--text-muted); font-size: 14px;">
                        dari ${totalItems.toLocaleString('id-ID')} data
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: var(--text-muted); font-size: 14px; margin-right: 8px;">
                        ${start.toLocaleString('id-ID')} - ${end.toLocaleString('id-ID')}
                    </span>
                    
                    <button onclick="window.${this.containerId}_instance.goToPage(1)" 
                            ${this.currentPage === 1 ? 'disabled' : ''}
                            class="btn btn-secondary btn-sm"
                            style="padding: 6px 10px;">
                        <i class="fas fa-angle-double-left"></i>
                    </button>
                    
                    <button onclick="window.${this.containerId}_instance.goToPage(${this.currentPage - 1})" 
                            ${this.currentPage === 1 ? 'disabled' : ''}
                            class="btn btn-secondary btn-sm"
                            style="padding: 6px 10px;">
                        <i class="fas fa-angle-left"></i>
                    </button>

                    ${pageNumbers.map(page => {
                        if (page === '...') {
                            return `<span style="padding: 6px 8px; color: var(--text-muted);">...</span>`;
                        }
                        return `
                            <button onclick="window.${this.containerId}_instance.goToPage(${page})"
                                    class="btn ${page === this.currentPage ? 'btn-primary' : 'btn-secondary'} btn-sm"
                                    style="padding: 6px 12px; min-width: 36px;">
                                ${page}
                            </button>
                        `;
                    }).join('')}

                    <button onclick="window.${this.containerId}_instance.goToPage(${this.currentPage + 1})" 
                            ${this.currentPage === totalPages ? 'disabled' : ''}
                            class="btn btn-secondary btn-sm"
                            style="padding: 6px 10px;">
                        <i class="fas fa-angle-right"></i>
                    </button>
                    
                    <button onclick="window.${this.containerId}_instance.goToPage(${totalPages})" 
                            ${this.currentPage === totalPages ? 'disabled' : ''}
                            class="btn btn-secondary btn-sm"
                            style="padding: 6px 10px;">
                        <i class="fas fa-angle-double-right"></i>
                    </button>
                </div>
            </div>
        `;

        // Store instance in window for onclick handlers
        window[`${this.containerId}_instance`] = this;
    }

    generatePageNumbers(totalPages) {
        if (totalPages <= 7) {
            return Array.from({ length: totalPages }, (_, i) => i + 1);
        }

        const current = this.currentPage;
        const pages = [];

        // Always show first page
        pages.push(1);

        if (current > 3) {
            pages.push('...');
        }

        // Show pages around current
        for (let i = Math.max(2, current - 1); i <= Math.min(totalPages - 1, current + 1); i++) {
            pages.push(i);
        }

        if (current < totalPages - 2) {
            pages.push('...');
        }

        // Always show last page
        if (totalPages > 1) {
            pages.push(totalPages);
        }

        return pages;
    }
}
