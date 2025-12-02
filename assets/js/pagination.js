/**
 * Reusable Pagination Component for BuzzDM
 * Provides client-side pagination functionality for tables and lists
 */

class BuzzPagination {
    constructor(options) {
        this.currentPage = options.currentPage || 1;
        this.itemsPerPage = options.itemsPerPage || 10;
        this.totalItems = options.totalItems || 0;
        this.container = options.container;
        this.onPageChange = options.onPageChange || (() => {});

        this.render();
    }

    get totalPages() {
        return Math.ceil(this.totalItems / this.itemsPerPage);
    }

    setTotalItems(total) {
        this.totalItems = total;
        if (this.currentPage > this.totalPages && this.totalPages > 0) {
            this.currentPage = this.totalPages;
        }
        this.render();
    }

    setCurrentPage(page) {
        if (page < 1 || page > this.totalPages) return;
        this.currentPage = page;
        this.render();
        this.onPageChange(this.currentPage);
    }

    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.setCurrentPage(this.currentPage + 1);
        }
    }

    prevPage() {
        if (this.currentPage > 1) {
            this.setCurrentPage(this.currentPage - 1);
        }
    }

    getPageInfo() {
        const start = (this.currentPage - 1) * this.itemsPerPage + 1;
        const end = Math.min(this.currentPage * this.itemsPerPage, this.totalItems);
        return {
            start,
            end,
            total: this.totalItems,
            currentPage: this.currentPage,
            totalPages: this.totalPages
        };
    }

    render() {
        if (!this.container) return;

        const totalPages = this.totalPages;

        // Clear container
        this.container.innerHTML = '';

        if (totalPages <= 1) {
            // Don't show pagination if there's only one page or no items
            return;
        }

        const ul = document.createElement('ul');
        ul.className = 'pagination pagination-sm';

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${this.currentPage === 1 ? 'disabled' : ''}`;
        const prevLink = document.createElement('a');
        prevLink.className = 'page-link';
        prevLink.href = '#';
        prevLink.innerHTML = '&laquo; Anterior';
        prevLink.addEventListener('click', (e) => {
            e.preventDefault();
            this.prevPage();
        });
        prevLi.appendChild(prevLink);
        ul.appendChild(prevLi);

        // Page numbers (smart rendering - show first, last, current and adjacent)
        const pagesToShow = [];

        if (totalPages <= 7) {
            // Show all pages if 7 or fewer
            for (let i = 1; i <= totalPages; i++) {
                pagesToShow.push(i);
            }
        } else {
            // Always show first page
            pagesToShow.push(1);

            if (this.currentPage > 3) {
                pagesToShow.push('...');
            }

            // Show pages around current
            for (let i = Math.max(2, this.currentPage - 1); i <= Math.min(totalPages - 1, this.currentPage + 1); i++) {
                pagesToShow.push(i);
            }

            if (this.currentPage < totalPages - 2) {
                pagesToShow.push('...');
            }

            // Always show last page
            if (totalPages > 1) {
                pagesToShow.push(totalPages);
            }
        }

        pagesToShow.forEach(page => {
            const li = document.createElement('li');

            if (page === '...') {
                li.className = 'page-item disabled';
                const span = document.createElement('span');
                span.className = 'page-link';
                span.textContent = '...';
                li.appendChild(span);
            } else {
                li.className = `page-item ${page === this.currentPage ? 'active' : ''}`;
                const link = document.createElement('a');
                link.className = 'page-link';
                link.href = '#';
                link.textContent = page;
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.setCurrentPage(page);
                });
                li.appendChild(link);
            }
            ul.appendChild(li);
        });

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${this.currentPage === totalPages ? 'disabled' : ''}`;
        const nextLink = document.createElement('a');
        nextLink.className = 'page-link';
        nextLink.href = '#';
        nextLink.innerHTML = 'Siguiente &raquo;';
        nextLink.addEventListener('click', (e) => {
            e.preventDefault();
            this.nextPage();
        });
        nextLi.appendChild(nextLink);
        ul.appendChild(nextLi);

        this.container.appendChild(ul);
    }
}

// Export for use in other scripts
if (typeof window !== 'undefined') {
    window.BuzzPagination = BuzzPagination;
}
