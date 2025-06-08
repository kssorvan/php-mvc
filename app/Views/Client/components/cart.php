<?php use App\Helpers\Helper; ?>

<!-- Modal Shopping Cart -->
<div class="wrap-header-cart js-panel-cart">
    <div class="s-full js-hide-cart"></div>

    <div class="header-cart flex-col-l p-l-65 p-r-25">
        <div class="header-cart-title flex-w flex-sb-m p-b-8">
            <span class="mtext-103 cl2">Your Cart</span>

            <div class="fs-35 lh-10 cl2 p-lr-5 pointer hov-cl1 trans-04 js-hide-cart">
                <i class="zmdi zmdi-close"></i>
            </div>
        </div>
        
        <div class="header-cart-content flex-w js-pscroll">
            <ul class="header-cart-wrapitem w-full" id="header-cart-items">
                <!-- Cart Items will be loaded here dynamically -->
            </ul>
            
            <!-- Empty Cart Message -->
            <div id="empty-cart-message" class="text-center p-t-20 p-b-20" style="display: none;">
                <p class="stext-113 cl6">Your cart is empty</p>
            </div>
            
            <!-- Cart Summary -->
            <div id="cart-summary" style="display: none;">
                <div class="w-full">
                    <div class="header-cart-total w-full p-tb-40" id="header-cart-total">
                        Total: $0.00
                    </div>

                    <div class="header-cart-buttons flex-w w-full">
                        <a href="<?= Helper::url('/checkout') ?>" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-lr-auto">
                            Check Out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript Configuration - FIXED for both Development & Production
window.OneStoreClient = window.OneStoreClient || {
    baseUrl: '<?= APP_URL ?>',
    url: function(path) {
        const cleanPath = (path || '').replace(/^\//, '');
        const baseUrl = '<?= APP_URL ?>';
        return baseUrl + (cleanPath ? '/' + cleanPath : '');
    }
};
// Header Cart Management
class HeaderCart {
    constructor() {
        this.init();
    }

    init() {
        this.loadCartData();
        this.bindEvents();
        
        // Listen for cart updates from other parts of the site
        document.addEventListener('cartUpdated', () => {
            this.loadCartData();
        });
    }

    async loadCartData() {
        try {
            const response = await fetch(window.OneStoreClient.url('/cart/get'));
            const data = await response.json();
            
            if (data.success) {
                this.updateCartDisplay(data.cart_items, data.cart_totals);
            } else {
                this.showEmptyCart();
            }
        } catch (error) {
            console.error('Error loading cart:', error);
            this.showEmptyCart();
        }
    }

    updateCartDisplay(items, totals) {
        const itemsContainer = document.getElementById('header-cart-items');
        const totalElement = document.getElementById('header-cart-total');
        const summaryElement = document.getElementById('cart-summary');
        const emptyMessage = document.getElementById('empty-cart-message');
        
        // Safety check for items array
        if (!items || !Array.isArray(items) || items.length === 0) {
            this.showEmptyCart();
            return;
        }

        // Show cart content
        summaryElement.style.display = 'block';
        emptyMessage.style.display = 'none';
        
        // Update items using reduce for total quantity
        itemsContainer.innerHTML = items.map(item => `
            <li class="header-cart-item flex-w flex-t m-b-12" data-product-id="${item.product_id || item.productID}">
                <div class="header-cart-item-img">
                    <img src="${window.OneStoreClient.url('/uploads/')}${item.image_path || item.image || 'placeholder.jpg'}" alt="${item.name || item.productName}" style="width: 60px; height: 60px; object-fit: cover;">
                </div>

                <div class="header-cart-item-txt p-t-8 flex-grow-1">
                    <a href="${window.OneStoreClient.url('/product/')}${item.product_id || item.productID}" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
                        ${item.name || item.productName}
                    </a>

                    <div class="flex-w flex-sb-m">
                        <span class="header-cart-item-info">
                            ${item.quantity} x $${parseFloat(item.price).toFixed(2)}
                        </span>
                        <button class="btn-remove-header-cart cl2 hov-cl1 trans-04" data-product-id="${item.product_id || item.productID}" data-cart-id="${item.cartID}">
                            <i class="zmdi zmdi-close"></i>
                        </button>
                    </div>
                </div>
            </li>
        `).join('');

        // Update total
        totalElement.textContent = `Total: $${parseFloat(totals?.total || 0).toFixed(2)}`;
        
        // Update cart count in header using reduce
        const totalQuantity = items.reduce((sum, item) => sum + parseInt(item.quantity || 0), 0);
        this.updateCartCount(totalQuantity);
        
        // Bind remove buttons
        this.bindRemoveButtons();
    }

    showEmptyCart() {
        const summaryElement = document.getElementById('cart-summary');
        const emptyMessage = document.getElementById('empty-cart-message');
        const itemsContainer = document.getElementById('header-cart-items');
        
        summaryElement.style.display = 'none';
        emptyMessage.style.display = 'block';
        itemsContainer.innerHTML = '';
        
        this.updateCartCount(0);
    }

    updateCartCount(count) {
        const cartCountElement = document.querySelector('[data-notify]');
        if (cartCountElement) {
            cartCountElement.setAttribute('data-notify', count || 0);
        }
    }

    bindEvents() {
        // Add to cart buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.add-to-cart-btn') || e.target.closest('.add-to-cart-btn')) {
                e.preventDefault();
                const button = e.target.matches('.add-to-cart-btn') ? e.target : e.target.closest('.add-to-cart-btn');
                this.addToCart(button);
            }
        });
    }

    bindRemoveButtons() {
        document.querySelectorAll('.btn-remove-header-cart').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const cartId = button.getAttribute('data-cart-id');
                const productId = button.getAttribute('data-product-id');
                this.removeFromCart(cartId || productId, cartId ? 'cart_id' : 'product_id');
            });
        });
    }

    async addToCart(button) {
        const productData = {
            product_id: button.getAttribute('data-product-id'),
            name: button.getAttribute('data-product-name'),
            price: button.getAttribute('data-product-price'),
            image: button.getAttribute('data-product-image'),
            quantity: 1
        };

        try {
            const response = await fetch(window.OneStoreClient.url('/cart/add'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(productData)
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification(`${productData.name} added to cart!`, 'success');
                this.loadCartData();
                
                // Dispatch cart updated event
                document.dispatchEvent(new CustomEvent('cartUpdated'));
            } else {
                this.showNotification(data.message || 'Failed to add item to cart', 'error');
            }
        } catch (error) {
            console.error('Error adding to cart:', error);
            this.showNotification('An error occurred while adding to cart', 'error');
        }
    }

    async removeFromCart(id, idType = 'product_id') {
        try {
            const requestBody = {};
            requestBody[idType] = id;
            
            const response = await fetch(window.OneStoreClient.url('/cart/remove'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestBody)
            });

            const data = await response.json();
            
            if (data.success) {
                this.showNotification('Item removed from cart', 'success');
                this.loadCartData();
                
                // Dispatch cart updated event
                document.dispatchEvent(new CustomEvent('cartUpdated'));
            } else {
                this.showNotification(data.message || 'Failed to remove item', 'error');
            }
        } catch (error) {
            console.error('Error removing from cart:', error);
            this.showNotification('An error occurred', 'error');
        }
    }

    showNotification(message, type) {
        // Use SweetAlert if available, otherwise fallback to simple alert
        if (typeof swal !== 'undefined') {
            swal(message, "", type === 'error' ? 'error' : 'success');
        } else {
            alert(message);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new HeaderCart();
});
</script> 