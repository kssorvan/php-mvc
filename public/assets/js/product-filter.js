/**
 * Shared Product Filtering Functionality
 * Used by both home and shop pages to prevent code duplication
 */

class ProductFilter {
    constructor(options = {}) {
        this.productGrid = document.getElementById(options.gridId || 'product-grid');
        this.loadMoreBtn = document.getElementById(options.loadMoreBtnId || 'load-more-btn');
        this.searchInput = document.querySelector(options.searchSelector || 'input[name="search-product"]');
        this.filterButtons = document.querySelectorAll('[data-filter]');
        this.enableLoadMore = options.enableLoadMore || false;
        
        this.init();
    }
    
    init() {
        this.setupFilterButtons();
        this.setupSearch();
        this.setupPanelToggles();
        if (this.enableLoadMore) {
            this.setupLoadMore();
        }
        this.setupWishlist();
    }
    
    setupFilterButtons() {
        this.filterButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const filter = button.getAttribute('data-filter');
                this.filterProducts(filter);
                this.updateActiveButton(button);
            });
        });
    }
    
    setupSearch() {
        if (this.searchInput) {
            this.searchInput.addEventListener('input', this.debounce((e) => {
                this.searchProducts(e.target.value);
            }, 300));
        }
    }
    
    setupLoadMore() {
        if (this.loadMoreBtn) {
            this.loadMoreBtn.addEventListener('click', () => {
                const page = this.loadMoreBtn.getAttribute('data-page');
                this.loadMoreProducts(page);
            });
        }
    }
    
    setupWishlist() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('.js-addwish-b2')) {
                e.preventDefault();
                const productId = e.target.closest('.js-addwish-b2').getAttribute('data-product-id');
                this.addToWishlist(productId);
            }
        });
    }
    
    setupPanelToggles() {
        // Filter panel toggle
        const filterToggle = document.querySelector('.js-show-filter');
        const filterPanel = document.querySelector('.panel-filter');
        if (filterToggle && filterPanel) {
            filterToggle.addEventListener('click', () => {
                this.togglePanel(filterPanel, filterToggle);
            });
        }
        
        // Search panel toggle
        const searchToggle = document.querySelector('.js-show-search');
        const searchPanel = document.querySelector('.panel-search');
        if (searchToggle && searchPanel) {
            searchToggle.addEventListener('click', () => {
                this.togglePanel(searchPanel, searchToggle);
            });
        }
    }
    
    togglePanel(panel, toggle) {
        const isVisible = !panel.classList.contains('dis-none');
        
        if (isVisible) {
            panel.classList.add('dis-none');
            toggle.querySelector('.icon-close-filter, .icon-close-search')?.classList.add('dis-none');
            toggle.querySelector('.icon-filter, .icon-search')?.classList.remove('dis-none');
        } else {
            panel.classList.remove('dis-none');
            toggle.querySelector('.icon-filter, .icon-search')?.classList.add('dis-none');
            toggle.querySelector('.icon-close-filter, .icon-close-search')?.classList.remove('dis-none');
        }
    }
    
    filterProducts(filter) {
        const products = document.querySelectorAll('.isotope-item');
        let visibleCount = 0;
        
        products.forEach(product => {
            if (filter === '*' || product.classList.contains(filter.replace('.', ''))) {
                product.style.display = 'block';
                product.style.animation = 'fadeIn 0.3s ease-in-out';
                visibleCount++;
            } else {
                product.style.display = 'none';
            }
        });
        
        this.updateEmptyState(visibleCount === 0);
    }
    
    searchProducts(query) {
        if (query.length < 2) {
            this.filterProducts('*');
            return;
        }
        
        const products = document.querySelectorAll('.isotope-item');
        let visibleCount = 0;
        
        products.forEach(product => {
            const productName = product.querySelector('.js-name-b2');
            if (productName) {
                const name = productName.textContent.toLowerCase();
                if (name.includes(query.toLowerCase())) {
                    product.style.display = 'block';
                    product.style.animation = 'fadeIn 0.3s ease-in-out';
                    visibleCount++;
                } else {
                    product.style.display = 'none';
                }
            }
        });
        
        this.updateEmptyState(visibleCount === 0);
    }
    
    loadMoreProducts(page) {
        if (!this.loadMoreBtn) return;
        
        // Add loading state
        this.loadMoreBtn.disabled = true;
        this.loadMoreBtn.innerHTML = 'Loading...';
        
        // Get current filters to maintain them
        const urlParams = new URLSearchParams(window.location.search);
        const queryString = urlParams.toString();
        const separator = queryString ? '&' : '';
        
        fetch(`/shop/load-more?${queryString}${separator}page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.products && data.products.length > 0) {
                    // Append new products to the grid
                    data.products.forEach(product => {
                        const productHtml = this.createProductCard(product);
                        this.productGrid.insertAdjacentHTML('beforeend', productHtml);
                    });
                    
                    // Update button state
                    if (data.hasMore) {
                        this.loadMoreBtn.disabled = false;
                        this.loadMoreBtn.innerHTML = 'Load More';
                        this.loadMoreBtn.setAttribute('data-page', parseInt(page) + 1);
                    } else {
                        this.showNoMoreProducts();
                    }
                } else {
                    this.showNoMoreProducts();
                }
            } else {
                this.showLoadError();
            }
        })
        .catch(error => {
            console.error('Load more error:', error);
            this.loadMoreBtn.innerHTML = 'Error - Try Again';
            this.loadMoreBtn.disabled = false;
        });
    }
    
    createProductCard(product) {
        const imageUrl = product.image_path 
            ? `/uploads/${product.image_path}` 
            : '/uploads/placeholder.jpg';
        
        const categoryClass = product.categoryName 
            ? product.categoryName.toLowerCase() 
            : 'general';
        
        const salePrice = product.sale_price && product.sale_price < product.price 
            ? `<span class="old-price text-muted text-decoration-line-through">$${parseFloat(product.sale_price).toFixed(2)}</span>` 
            : '';
        
        return `
            <div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item ${categoryClass}">
                <div class="block2">
                    <div class="block2-pic hov-img0">
                        <img src="${imageUrl}" alt="${product.productName}">
                        <a href="/product/${product.productID}" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04">
                            Quick View
                        </a>
                    </div>
                    <div class="block2-txt flex-w flex-t p-t-14">
                        <div class="block2-txt-child1 flex-col-l">
                            <a href="/product/${product.productID}" class="stext-104 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
                                ${product.productName}
                            </a>
                            <span class="stext-105 cl3">
                                $${parseFloat(product.price).toFixed(2)}
                                ${salePrice}
                            </span>
                        </div>
                        <div class="block2-txt-child2 flex-r p-t-3">
                            <a href="#" class="btn-addwish-b2 dis-block pos-relative js-addwish-b2" data-product-id="${product.productID}">
                                <img class="icon-heart1 dis-block trans-04" src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/client/images/icons/icon-heart-01.png" alt="ICON">
                                <img class="icon-heart2 dis-block trans-04 ab-t-l" src="https://cdn.jsdelivr.net/gh/BroPinn/cdn-file@main/client/images/icons/icon-heart-02.png" alt="ICON">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    addToWishlist(productId) {
        // For now, just show a notification
        this.showNotification('Added to wishlist!', 'success');
        
        // TODO: Implement actual wishlist functionality
        /*
        fetch('/wishlist/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('Added to wishlist!', 'success');
            } else {
                this.showNotification(data.message || 'Failed to add to wishlist', 'error');
            }
        })
        .catch(error => {
            console.error('Wishlist error:', error);
            this.showNotification('An error occurred', 'error');
        });
        */
    }
    
    updateActiveButton(activeButton) {
        this.filterButtons.forEach(btn => btn.classList.remove('how-active1'));
        activeButton.classList.add('how-active1');
    }
    
    updateEmptyState(isEmpty) {
        let emptyState = document.querySelector('.filter-empty-state');
        
        if (isEmpty) {
            if (!emptyState) {
                emptyState = document.createElement('div');
                emptyState.className = 'col-12 filter-empty-state';
                emptyState.innerHTML = `
                    <div class="text-center p-t-50 p-b-50">
                        <i class="zmdi zmdi-search fs-60 cl6 m-b-20"></i>
                        <h4 class="mtext-111 cl2 p-b-16">No Products Found</h4>
                        <p class="stext-113 cl6">Try different filters or search terms.</p>
                    </div>
                `;
                this.productGrid.appendChild(emptyState);
            }
            emptyState.style.display = 'block';
        } else {
            if (emptyState) {
                emptyState.style.display = 'none';
            }
        }
    }
    
    showNoMoreProducts() {
        this.loadMoreBtn.innerHTML = 'No More Products';
        this.loadMoreBtn.disabled = true;
        setTimeout(() => {
            this.loadMoreBtn.style.display = 'none';
        }, 2000);
    }
    
    showLoadError() {
        this.loadMoreBtn.innerHTML = 'Error Loading Products';
        this.loadMoreBtn.disabled = true;
    }
    
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : 'success'} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        `;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
} 