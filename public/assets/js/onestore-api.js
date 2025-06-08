/**
 * OneStore API Client
 * Easy-to-use JavaScript library for fetching products, categories, and sliders
 */

class OneStoreAPI {
    constructor(baseUrl = '') {
        this.baseUrl = baseUrl;
    }

    /**
     * Make HTTP request
     */
    async request(url, options = {}) {
        try {
            const response = await fetch(this.baseUrl + url, {
                headers: {
                    'Content-Type': 'application/json',
                    ...options.headers
                },
                ...options
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('OneStore API Error:', error);
            throw error;
        }
    }

    /**
     * Get products with optional filters
     * @param {Object} filters - { category, brand, featured, limit }
     * @returns {Promise<Object>} - { success, products, count }
     */
    async getProducts(filters = {}) {
        const params = new URLSearchParams();
        
        if (filters.category) params.append('category', filters.category);
        if (filters.brand) params.append('brand', filters.brand);
        if (filters.featured) params.append('featured', '1');
        if (filters.limit) params.append('limit', filters.limit);
        
        const queryString = params.toString();
        const url = `/api/products${queryString ? '?' + queryString : ''}`;
        
        return await this.request(url);
    }

    /**
     * Get featured products
     * @param {number} limit - Number of products to fetch
     * @returns {Promise<Object>} - { success, products, count }
     */
    async getFeaturedProducts(limit = 8) {
        return await this.getProducts({ featured: true, limit });
    }

    /**
     * Get products by category
     * @param {number} categoryId - Category ID
     * @param {number} limit - Number of products to fetch
     * @returns {Promise<Object>} - { success, products, count }
     */
    async getProductsByCategory(categoryId, limit = 12) {
        return await this.getProducts({ category: categoryId, limit });
    }

    /**
     * Get products by brand
     * @param {number} brandId - Brand ID
     * @param {number} limit - Number of products to fetch
     * @returns {Promise<Object>} - { success, products, count }
     */
    async getProductsByBrand(brandId, limit = 12) {
        return await this.getProducts({ brand: brandId, limit });
    }

    /**
     * Get all categories
     * @returns {Promise<Object>} - { success, categories }
     */
    async getCategories() {
        return await this.request('/api/categories');
    }

    /**
     * Get all active sliders
     * @returns {Promise<Object>} - { success, sliders }
     */
    async getSliders() {
        return await this.request('/api/sliders');
    }

    /**
     * Format price for display
     * @param {number} price - Price value
     * @param {string} currency - Currency symbol
     * @returns {string} - Formatted price
     */
    formatPrice(price, currency = '$') {
        return `${currency}${parseFloat(price).toFixed(2)}`;
    }

    /**
     * Get product image URL
     * @param {string} imagePath - Image filename
     * @returns {string} - Full image URL
     */
    getProductImageUrl(imagePath) {
        if (!imagePath) return '/public/assets/images/no-image.png';
        return `/public/uploads/products/${imagePath}`;
    }

    /**
     * Get slider image URL
     * @param {string} imagePath - Image filename
     * @returns {string} - Full image URL
     */
    getSliderImageUrl(imagePath) {
        if (!imagePath || imagePath.trim() === '') {
            // Return a placeholder gradient image for sliders without images
            return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTkyMCIgaGVpZ2h0PSI2MDAiIHZpZXdCb3g9IjAgMCAxOTIwIDYwMCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGRlZnM+CjxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZGllbnQiIHgxPSIwJSIgeTE9IjAlIiB4Mj0iMTAwJSIgeTI9IjEwMCUiPgo8c3RvcCBvZmZzZXQ9IjAlIiBzdHlsZT0ic3RvcC1jb2xvcjojNjY3ZWVhO3N0b3Atb3BhY2l0eToxIiAvPgo8c3RvcCBvZmZzZXQ9IjEwMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiM3NjRiYTI7c3RvcC1vcGFjaXR5OjEiIC8+CjwvbGluZWFyR3JhZGllbnQ+CjwvZGVmcz4KPHJLQ3QgeD0iMCIgeT0iMCIgd2lkdGg9IjE5MjAiIGhlaWdodD0iNjAwIiBmaWxsPSJ1cmwoI2dyYWRpZW50KSIvPgo8dGV4dCB4PSI5NjAiIHk9IjMwMCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjQ4IiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk9uZVN0b3JlPC90ZXh0Pgo8L3N2Zz4K';
        }
        return `/public/uploads/slider/${imagePath}`;
    }
}

// Global instance
const oneStoreAPI = new OneStoreAPI();

/**
 * jQuery Plugin (if jQuery is available)
 */
if (typeof $ !== 'undefined') {
    $.extend({
        oneStore: {
            getProducts: (filters) => oneStoreAPI.getProducts(filters),
            getFeaturedProducts: (limit) => oneStoreAPI.getFeaturedProducts(limit),
            getProductsByCategory: (categoryId, limit) => oneStoreAPI.getProductsByCategory(categoryId, limit),
            getProductsByBrand: (brandId, limit) => oneStoreAPI.getProductsByBrand(brandId, limit),
            getCategories: () => oneStoreAPI.getCategories(),
            getSliders: () => oneStoreAPI.getSliders(),
            formatPrice: (price, currency) => oneStoreAPI.formatPrice(price, currency),
            getProductImageUrl: (imagePath) => oneStoreAPI.getProductImageUrl(imagePath),
            getSliderImageUrl: (imagePath) => oneStoreAPI.getSliderImageUrl(imagePath)
        }
    });
}

/**
 * Easy-to-use helper functions
 */
window.OneStore = {
    // Load featured products into a container
    loadFeaturedProducts: async function(containerId, limit = 8) {
        try {
            const container = document.getElementById(containerId);
            if (!container) {
                console.error('Container not found:', containerId);
                return;
            }

            container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

            const response = await oneStoreAPI.getFeaturedProducts(limit);
            
            if (response.success && response.products) {
                let html = '<div class="row">';
                
                response.products.forEach(product => {
                    const imageUrl = oneStoreAPI.getProductImageUrl(product.image_path);
                    const price = oneStoreAPI.formatPrice(product.price);
                    const salePrice = product.sale_price ? oneStoreAPI.formatPrice(product.sale_price) : null;
                    
                    html += `
                        <div class="col-md-6 col-lg-3 mb-4">
                            <div class="card h-100">
                                <img src="${imageUrl}" class="card-img-top" alt="${product.productName}" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">${product.productName}</h5>
                                    <p class="card-text">${product.short_description || ''}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            ${salePrice ? `<span class="text-danger">${salePrice}</span> <s class="text-muted">${price}</s>` : `<span class="fw-bold">${price}</span>`}
                                        </div>
                                        <button class="btn btn-primary btn-sm">Buy Now</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="alert alert-warning">No featured products found.</div>';
            }
        } catch (error) {
            console.error('Error loading featured products:', error);
            document.getElementById(containerId).innerHTML = '<div class="alert alert-danger">Error loading products.</div>';
        }
    },

    // Load categories into a select or list
    loadCategories: async function(containerId, type = 'select') {
        try {
            const container = document.getElementById(containerId);
            if (!container) {
                console.error('Container not found:', containerId);
                return;
            }

            const response = await oneStoreAPI.getCategories();
            
            if (response.success && response.categories) {
                let html = '';
                
                if (type === 'select') {
                    html = '<option value="">All Categories</option>';
                    response.categories.forEach(category => {
                        html += `<option value="${category.categoryID}">${category.catName}</option>`;
                    });
                } else if (type === 'list') {
                    response.categories.forEach(category => {
                        html += `<li><a href="/shop?category=${category.categoryID}" class="text-decoration-none">${category.catName}</a></li>`;
                    });
                }
                
                container.innerHTML = html;
            }
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    },

    // Load sliders into a carousel
    loadSliders: async function(containerId) {
        try {
            const container = document.getElementById(containerId);
            if (!container) {
                console.error('Container not found:', containerId);
                return;
            }

            const response = await oneStoreAPI.getSliders();
            
            if (response.success && response.sliders && response.sliders.length > 0) {
                let html = '<div id="sliderCarousel" class="carousel slide" data-bs-ride="carousel">';
                html += '<div class="carousel-inner">';
                
                response.sliders.forEach((slider, index) => {
                    const imageUrl = oneStoreAPI.getSliderImageUrl(slider.image);
                    const activeClass = index === 0 ? 'active' : '';
                    
                    html += `
                        <div class="carousel-item ${activeClass}">
                            <img src="${imageUrl}" class="d-block w-100" alt="${slider.title}" style="height: 400px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block">
                                <h5>${slider.title}</h5>
                                ${slider.subtitle ? `<p>${slider.subtitle}</p>` : ''}
                                ${slider.button_text && slider.link_url ? `<a href="${slider.link_url}" class="btn btn-primary">${slider.button_text}</a>` : ''}
                            </div>
                        </div>
                    `;
                });
                
                html += '</div>';
                
                if (response.sliders.length > 1) {
                    html += `
                        <button class="carousel-control-prev" type="button" data-bs-target="#sliderCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#sliderCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    `;
                }
                
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="alert alert-info">No sliders available.</div>';
            }
        } catch (error) {
            console.error('Error loading sliders:', error);
            container.innerHTML = '<div class="alert alert-danger">Error loading sliders.</div>';
        }
    }
};

// Auto-load elements with data attributes when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Auto-load featured products
    const featuredContainers = document.querySelectorAll('[data-onestore="featured-products"]');
    featuredContainers.forEach(container => {
        const limit = container.getAttribute('data-limit') || 8;
        OneStore.loadFeaturedProducts(container.id, parseInt(limit));
    });

    // Auto-load categories
    const categoryContainers = document.querySelectorAll('[data-onestore="categories"]');
    categoryContainers.forEach(container => {
        const type = container.getAttribute('data-type') || 'select';
        OneStore.loadCategories(container.id, type);
    });

    // Auto-load sliders
    const sliderContainers = document.querySelectorAll('[data-onestore="sliders"]');
    sliderContainers.forEach(container => {
        OneStore.loadSliders(container.id);
    });
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { OneStoreAPI, oneStoreAPI };
} 