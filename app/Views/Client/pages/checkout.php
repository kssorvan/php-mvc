<?php use App\Helpers\Helper; ?>

<style>
/* Checkout-specific table styling to override shopping cart CSS */
#checkout-content .table-shopping-cart .table_row {
    height: auto !important;
    min-height: 80px;
}

#checkout-content .table-shopping-cart .table_row td {
    padding: 15px 10px !important;
    vertical-align: middle;
}

#checkout-content .table-shopping-cart .table_row td.column-1 {
    padding-bottom: 15px !important;
}

#checkout-content .how-itemcart1 {
    width: 80px;
    height: 80px;
    overflow: hidden;
    border-radius: 8px;
}

#checkout-content .how-itemcart1 img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Guest user notice and user info styling */
.bg-light {
    background-color: #f8f9fa !important;
}

.text-right {
    text-align: right;
}

/* Input field improvements */
.p-tb-15 {
    padding-top: 15px !important;
    padding-bottom: 15px !important;
}

/* Make table responsive */
@media (max-width: 991px) {
    #checkout-content .table-shopping-cart {
        min-width: 600px;
    }
    
    #checkout-content .table-shopping-cart .column-1 {
        width: 100px;
        padding-left: 20px;
    }
    
    #checkout-content .table-shopping-cart .column-5 {
        padding-right: 20px;
    }
}

/* Mobile responsive styles */
@media (max-width: 768px) {
    .text-right {
        text-align: center;
    }
    
    .col-md-4 a {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .col-md-6 .bg-light {
        margin-top: 15px;
    }
}
</style>

<!-- Checkout -->
<div class="bg0 p-t-75 p-b-85">
    <div class="container">
        <!-- Empty Cart State -->
        <div id="empty-cart-state" class="txt-center p-t-50 p-b-50" style="<?= empty($cart_items ?? []) ? '' : 'display: none;' ?>">
            <div class="empty-cart-icon">
                <i class="zmdi zmdi-shopping-cart" style="font-size: 64px; color: #999; margin-bottom: 20px;"></i>
            </div>
            <h4 class="mtext-109 cl2 p-b-30">Your cart is empty</h4>
            <p class="stext-113 cl6 p-b-26">Looks like you haven't added anything to your cart yet</p>
            <a href="<?= Helper::url('/shop') ?>" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer">
                CONTINUE SHOPPING
            </a>
        </div>

        <!-- Checkout Content -->
        <div id="checkout-content" style="<?= empty($cart_items ?? []) ? 'display: none;' : '' ?>">
            <form id="checkout-form" method="POST" action="/checkout/process">
                <div class="row">
                    <div class="col-lg-10 col-xl-7 m-lr-auto m-b-50">
                        <div class="m-l-25 m-r--38 m-lr-0-xl">
                            <div class="wrap-table-shopping-cart">
                                <table class="table-shopping-cart">
                                    <tr class="table_head">
                                        <th class="column-1">Product</th>
                                        <th class="column-2"></th>
                                        <th class="column-3">Price</th>
                                        <th class="column-4">Quantity</th>
                                        <th class="column-5">Total</th>
                                    </tr>
                                    <tbody id="checkout-items">
                                        <!-- Items will be loaded here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Guest User Notice -->
                            <?php if (!isset($customer) || empty($customer)): ?>
                            <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
                            <?php endif; ?>
                            
                            <div class="bg-light bor10 p-lr-40 p-t-25 p-b-25 m-t-40">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5 class="mtext-108 cl2 p-b-10">
                                            <i class="fa fa-user-plus m-r-10 cl6"></i>
                                            Checkout as Guest
                                        </h5>
                                        <p class="stext-113 cl6">
                                            You're checking out as a guest. To save your information and view order history, consider creating an account.
                                        </p>
                                    </div>
                                    <div class="col-md-4 text-right">
                                                                <a href="<?= Helper::url('/login') ?>" class="flex-c-m stext-101 cl0 size-107 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-b-10">
                            <i class="fa fa-sign-in m-r-5"></i>
                            Login
                        </a>
                        <a href="<?= Helper::url('/register') ?>" class="flex-c-m stext-101 cl2 size-107 bor2 hov-btn1 p-lr-15 trans-04">
                            <i class="fa fa-user-plus m-r-5"></i>
                            Register
                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Billing Information -->
                            <div class="bor10 p-lr-40 p-t-30 p-b-40 m-t-40">
                                <h4 class="mtext-109 cl2 p-b-30">
                                    <i class="fa fa-credit-card m-r-10 cl2"></i>
                                    Billing Information
                                </h4>

                                <!-- Name Fields -->
                                <div class="row p-b-25">
                                    <div class="col-md-6 p-b-15">
                                        <label class="stext-102 cl3 p-b-5">First Name *</label>
                                        <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20 p-tb-15" type="text" name="billing_first_name" placeholder="Enter your first name" value="<?= htmlspecialchars($customer['firstName'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6 p-b-15">
                                        <label class="stext-102 cl3 p-b-5">Last Name *</label>
                                        <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20 p-tb-15" type="text" name="billing_last_name" placeholder="Enter your last name" value="<?= htmlspecialchars($customer['lastName'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <!-- Contact Fields -->
                                <div class="row p-b-25">
                                    <div class="col-md-6 p-b-15">
                                        <label class="stext-102 cl3 p-b-5">Email Address *</label>
                                        <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20 p-tb-15" type="email" name="billing_email" placeholder="Enter your email address" value="<?= htmlspecialchars($customer['email'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6 p-b-15">
                                        <label class="stext-102 cl3 p-b-5">Phone Number *</label>
                                        <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20 p-tb-15" type="tel" name="billing_phone" placeholder="Enter your phone number" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <!-- Address Field -->
                                <div class="row p-b-25">
                                    <div class="col-12 p-b-15">
                                        <label class="stext-102 cl3 p-b-5">Street Address *</label>
                                        <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20 p-tb-15" type="text" name="billing_address1" placeholder="Enter your street address" value="<?= htmlspecialchars($customer['addresses'][0]['address1'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <!-- Location Fields -->
                                <div class="row p-b-25">
                                    <div class="col-md-4 p-b-15">
                                        <label class="stext-102 cl3 p-b-5">City *</label>
                                        <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20 p-tb-15" type="text" name="billing_city" placeholder="Enter your city" value="<?= htmlspecialchars($customer['addresses'][0]['city'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-4 p-b-15">
                                        <label class="stext-102 cl3 p-b-5">State *</label>
                                        <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20 p-tb-15" type="text" name="billing_state" placeholder="Enter your state" value="<?= htmlspecialchars($customer['addresses'][0]['state'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-4 p-b-15">
                                        <label class="stext-102 cl3 p-b-5">ZIP Code *</label>
                                        <input class="stext-104 cl2 plh4 size-116 bor13 p-lr-20 p-tb-15" type="text" name="billing_postal_code" placeholder="Enter your ZIP code" value="<?= htmlspecialchars($customer['addresses'][0]['postal_code'] ?? '') ?>" required>
                                    </div>
                                </div>

                                <!-- Country Field -->
                                <div class="row p-b-25">
                                    <div class="col-md-6 p-b-15">
                                        <label class="stext-102 cl3 p-b-5">Country *</label>
                                        <select class="stext-104 cl2 plh4 size-116 bor13 p-lr-20" name="billing_country" required>
                                            <option value="US" <?= ($customer['addresses'][0]['country'] ?? 'US') === 'US' ? 'selected' : '' ?>>United States</option>
                                            <option value="CA" <?= ($customer['addresses'][0]['country'] ?? '') === 'CA' ? 'selected' : '' ?>>Canada</option>
                                            <option value="UK" <?= ($customer['addresses'][0]['country'] ?? '') === 'UK' ? 'selected' : '' ?>>United Kingdom</option>
                                            <option value="CAM" <?= ($customer['addresses'][0]['country'] ?? '') === 'CAM' ? 'selected' : '' ?>>Cambodia</option>
                                        </select>
                                    </div>
                                    <?php if (isset($customer) && !empty($customer)): ?>
                                    <div class="col-md-6 p-b-15">
                                        <div class="bg-light p-lr-15 p-tb-10 bor10" style="border-radius: 8px;">
                                            <p class="stext-115 cl6 m-b-5">
                                                <i class="fa fa-info-circle m-r-5"></i>
                                                Logged in as: <strong><?= htmlspecialchars($customer['firstName'] . ' ' . $customer['lastName']) ?></strong>
                                            </p>
                                            <p class="stext-115 cl6">
                                                <small>Your information has been automatically filled. You can edit it if needed.</small>
                                            </p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Order Notes -->
                                <div class="row">
                                    <div class="col-12">
                                        <label class="stext-102 cl3 p-b-5">Order Notes (Optional)</label>
                                        <textarea class="stext-104 cl2 plh4 size-120 bor13 p-lr-20 m-tb-5" name="order_notes" rows="4" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-10 col-lg-7 col-xl-5 m-lr-auto m-b-50">
                        <div class="bor10 p-lr-40 p-t-30 p-b-40 m-l-63 m-r-40 m-lr-0-xl p-lr-15-sm">
                            <h4 class="mtext-109 cl2 p-b-30">
                                Cart Totals
                            </h4>

                            <div class="flex-w flex-t bor12 p-b-13">
                                <div class="size-208">
                                    <span class="stext-110 cl2">
                                        Subtotal:
                                    </span>
                                </div>

                                <div class="size-209">
                                    <span class="mtext-110 cl2" id="order-subtotal">
                                        $0.00
                                    </span>
                                </div>
                            </div>

                            <div class="flex-w flex-t bor12 p-b-13">
                                <div class="size-208">
                                    <span class="stext-110 cl2">
                                        Shipping:
                                    </span>
                                </div>

                                <div class="size-209">
                                    <span class="mtext-110 cl2">
                                        $10.00
                                    </span>
                                </div>
                            </div>

                            <div class="flex-w flex-t bor12 p-b-13">
                                <div class="size-208">
                                    <span class="stext-110 cl2">
                                        Tax:
                                    </span>
                                </div>

                                <div class="size-209">
                                    <span class="mtext-110 cl2" id="order-tax">
                                        $0.00
                                    </span>
                                </div>
                            </div>

                            <div class="flex-w flex-t p-t-27 p-b-33">
                                <div class="size-208">
                                    <span class="mtext-101 cl2">
                                        Total:
                                    </span>
                                </div>

                                <div class="size-209 p-t-1">
                                    <span class="mtext-110 cl2" id="order-total">
                                        $0.00
                                    </span>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="p-b-30">
                                <h4 class="mtext-109 cl2 p-b-20">
                                    Payment Method
                                </h4>
                                
                                <div class="flex-w flex-sb-m bor12 p-t-15 p-b-15">
                                    <label class="flex-w flex-m pointer p-b-10">
                                        <input type="radio" name="payment_method" value="paypal" checked class="m-r-10" id="paypal-radio">
                                        <span class="stext-110 cl2">
                                            <i class="fa fa-paypal cl1 m-r-10"></i>
                                            PayPal
                                        </span>
                                    </label>
                                </div>

                                <div class="flex-w flex-sb-m bor12 p-t-15 p-b-15">
                                    <label class="flex-w flex-m pointer p-b-10">
                                        <input type="radio" name="payment_method" value="cod" class="m-r-10" id="cod-radio">
                                        <span class="stext-110 cl2">
                                            <i class="fa fa-money cl1 m-r-10"></i>
                                            Cash on Delivery
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- PayPal Button Container (hidden by default) -->
                            <div id="paypal-button-container" class="p-b-30" style="display: block;"></div>

                            <!-- Regular Order Button (for COD) -->
                            <button type="submit" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer" id="place-order-btn" style="display: none;">
                                Place Order
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- PayPal SDK -->
<?php if (!empty($paypal_client_id)): ?>
<script src="https://www.paypal.com/sdk/js?client-id=<?= htmlspecialchars($paypal_client_id) ?>&currency=USD"></script>
<?php else: ?>
<script>
console.warn('PayPal client ID not configured. PayPal payments will not be available.');
</script>
<?php endif; ?>

<script>
// JavaScript Configuration
window.OneStoreClient = window.OneStoreClient || {
    baseUrl: '<?= Helper::url() ?>',
    url: (path) => '<?= Helper::url() ?>' + (path || '')
};

class CheckoutPage {
    constructor() {
        this.paypalRendered = false;
        this.init();
    }

    async init() {
        await this.loadCartData();
        this.bindEvents();
        this.handlePaymentMethodChange();
    }

    async loadCartData() {
        try {
            const response = await fetch(window.OneStoreClient.url('/cart/get'));
            const data = await response.json();
            
            if (data.success && data.cart_items.length > 0) {
                this.displayCheckoutContent(data.cart_items, data.cart_totals);
            } else {
                this.showEmptyCart();
            }
        } catch (error) {
            console.error('Error loading cart:', error);
            this.showEmptyCart();
        }
    }

    showEmptyCart() {
        document.getElementById('empty-cart-state').style.display = 'block';
        document.getElementById('checkout-content').style.display = 'none';
    }

    displayCheckoutContent(items, totals) {
        document.getElementById('empty-cart-state').style.display = 'none';
        document.getElementById('checkout-content').style.display = 'block';
        
        // Store items for quantity updates
        this.cartItems = items;
        
        // Update checkout items table with quantity controls
        const checkoutItemsContainer = document.getElementById('checkout-items');
        checkoutItemsContainer.innerHTML = items.map(item => `
            <tr class="table_row" data-product-id="${item.productID}">
                <td class="column-1">
                    <div class="how-itemcart1">
                        <img src="${window.OneStoreClient.url('/public/uploads/')}${item.image_path || 'placeholder.jpg'}" alt="${item.name}">
                    </div>
                </td>
                <td class="column-2">${item.name}</td>
                <td class="column-3">$ ${parseFloat(item.price).toFixed(2)}</td>
                <td class="column-4">
                    <div class="wrap-num-product flex-w m-l-auto m-r-0">
                        <div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m" data-product-id="${item.productID}">
                            <i class="fs-16 zmdi zmdi-minus"></i>
                        </div>

                        <input class="mtext-104 cl3 txt-center num-product" type="number" 
                               name="quantity" value="${item.quantity}" min="1" 
                               data-product-id="${item.productID}">

                        <div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m" data-product-id="${item.productID}">
                            <i class="fs-16 zmdi zmdi-plus"></i>
                        </div>
                    </div>
                </td>
                <td class="column-5 item-total" data-product-id="${item.productID}">
                    $ ${(parseFloat(item.price) * parseInt(item.quantity)).toFixed(2)}
                </td>
            </tr>
        `).join('');

        this.updateTotals();
        this.bindQuantityControls();
        this.initPayPal();
    }

    initPayPal() {
        // Check if PayPal SDK is loaded
        if (typeof paypal === 'undefined') {
            console.warn('PayPal SDK not loaded. Hiding PayPal payment option.');
            document.getElementById('paypal-radio').closest('.flex-w').style.display = 'none';
            document.getElementById('cod-radio').checked = true;
            this.handlePaymentMethodChange();
            return;
        }

        const subtotal = this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = 10.00;
        const tax = subtotal * 0.1;
        const total = subtotal + shipping + tax;

        if (!this.paypalRendered) {
            paypal.Buttons({
                createOrder: (data, actions) => {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: total.toFixed(2),
                                breakdown: {
                                    item_total: {
                                        currency_code: 'USD',
                                        value: subtotal.toFixed(2)
                                    },
                                    shipping: {
                                        currency_code: 'USD', 
                                        value: shipping.toFixed(2)
                                    },
                                    tax_total: {
                                        currency_code: 'USD',
                                        value: tax.toFixed(2)
                                    }
                                }
                            },
                            items: this.cartItems.map(item => ({
                                name: item.name,
                                unit_amount: {
                                    currency_code: 'USD',
                                    value: parseFloat(item.price).toFixed(2)
                                },
                                quantity: item.quantity
                            }))
                        }]
                    });
                },
                onApprove: async (data, actions) => {
                    try {
                        const details = await actions.order.capture();
                        
                        // Create order in our system
                        const formData = new FormData(document.getElementById('checkout-form'));
                        formData.set('payment_method', 'paypal');
                        formData.set('paypal_order_id', data.orderID);
                        formData.set('paypal_payment_id', details.id);
                        
                        const response = await fetch(window.OneStoreClient.url('/checkout/process'), {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();
                        
                        if (result.success) {
                            window.location.href = window.OneStoreClient.url(`/order-confirmation/${result.order_id}`);
                        } else {
                            this.showError(result.message || 'Failed to process order');
                        }
                    } catch (error) {
                        console.error('PayPal payment error:', error);
                        this.showError('Payment processing error occurred');
                    }
                },
                onError: (err) => {
                    console.error('PayPal error:', err);
                    this.showError('PayPal payment error occurred');
                },
                onCancel: (data) => {
                    console.log('PayPal payment cancelled:', data);
                }
            }).render('#paypal-button-container');
            
            this.paypalRendered = true;
        }
    }

    handlePaymentMethodChange() {
        const paypalRadio = document.getElementById('paypal-radio');
        const codRadio = document.getElementById('cod-radio');
        const paypalContainer = document.getElementById('paypal-button-container');
        const placeOrderBtn = document.getElementById('place-order-btn');

        const togglePaymentMethod = () => {
            if (paypalRadio.checked) {
                paypalContainer.style.display = 'block';
                placeOrderBtn.style.display = 'none';
            } else {
                paypalContainer.style.display = 'none';
                placeOrderBtn.style.display = 'block';
            }
        };

        paypalRadio.addEventListener('change', togglePaymentMethod);
        codRadio.addEventListener('change', togglePaymentMethod);
        
        // Set initial state
        togglePaymentMethod();
    }

    bindQuantityControls() {
        // Quantity increase buttons
        document.querySelectorAll('.btn-num-product-up').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productID = e.currentTarget.dataset.productId;
                this.updateQuantity(productID, 1);
            });
        });

        // Quantity decrease buttons
        document.querySelectorAll('.btn-num-product-down').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const productID = e.currentTarget.dataset.productId;
                this.updateQuantity(productID, -1);
            });
        });

        // Direct input changes
        document.querySelectorAll('.num-product').forEach(input => {
            input.addEventListener('change', (e) => {
                const productID = e.target.dataset.productId;
                const newQuantity = parseInt(e.target.value);
                if (newQuantity >= 1) {
                    this.setQuantity(productID, newQuantity);
                } else {
                    e.target.value = 1;
                    this.setQuantity(productID, 1);
                }
            });
        });
    }

    async updateQuantity(productID, change) {
        const input = document.querySelector(`input[data-product-id="${productID}"]`);
        const currentQty = parseInt(input.value);
        const newQty = currentQty + change;
        
        // If decreasing to 0 or below, handle removal
        if (newQty <= 0) {
            if (confirm('Remove this item from your cart?')) {
                await this.setQuantity(productID, 0);
            }
            // Don't change the input if user cancels
            return;
        }
        
        // Update input and quantity
        input.value = newQty;
        await this.setQuantity(productID, newQty);
    }

    async setQuantity(productID, quantity) {
        try {
            // If quantity is 0, confirm removal
            if (quantity <= 0) {
                if (confirm('Remove this item from your cart?')) {
                    quantity = 0;
                } else {
                    // Reset to 1 if user cancels
                    const input = document.querySelector(`input[data-product-id="${productID}"]`);
                    input.value = 1;
                    return;
                }
            }

            // Update cart in backend
            const response = await fetch(window.OneStoreClient.url('/cart/update'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    productID: productID,
                    quantity: quantity
                })
            });

            const data = await response.json();
            
            if (data.success) {
                if (data.action === 'removed') {
                    // Remove the row from table
                    const row = document.querySelector(`tr[data-product-id="${productID}"]`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Update cart items array
                    this.cartItems = this.cartItems.filter(item => item.productID != productID);
                    
                    // Check if cart is empty
                    if (this.cartItems.length === 0) {
                        this.showEmptyCart();
                        return;
                    }
                } else {
                    // Update local cart items
                    const itemIndex = this.cartItems.findIndex(item => item.productID == productID);
                    if (itemIndex !== -1) {
                        this.cartItems[itemIndex].quantity = quantity;
                        this.cartItems[itemIndex].total = this.cartItems[itemIndex].price * quantity;
                    }
                    
                    // Update item total display
                    const itemTotal = document.querySelector(`.item-total[data-product-id="${productID}"]`);
                    if (itemTotal && itemIndex !== -1) {
                        const price = this.cartItems[itemIndex].price;
                        itemTotal.textContent = `$ ${(price * quantity).toFixed(2)}`;
                    }
                }
                
                // Update overall totals
                this.updateTotals();
                
                // Update header cart count if available
                if (data.cart_count !== undefined) {
                    const cartCountElements = document.querySelectorAll('.js-show-cart-count');
                    cartCountElements.forEach(el => {
                        el.textContent = data.cart_count;
                    });
                }
                
                // Re-render PayPal buttons with new total
                this.rerenderPayPal();
                
            } else {
                console.error('Failed to update quantity:', data.message);
                // Reset input to original value
                const input = document.querySelector(`input[data-product-id="${productID}"]`);
                const originalItem = this.cartItems.find(item => item.productID == productID);
                if (input && originalItem) {
                    input.value = originalItem.quantity;
                }
                
                // Show error message
                this.showError(data.message || 'Failed to update quantity');
            }
        } catch (error) {
            console.error('Error updating quantity:', error);
            this.showError('An error occurred while updating quantity');
        }
    }

    rerenderPayPal() {
        // Only re-render if PayPal SDK is available
        if (typeof paypal === 'undefined') {
            return;
        }
        
        // Clear PayPal container and re-render with new totals
        const container = document.getElementById('paypal-button-container');
        container.innerHTML = '';
        this.paypalRendered = false;
        this.initPayPal();
    }

    updateTotals() {
        // Calculate totals
        const subtotal = this.cartItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shipping = 10.00;
        const tax = subtotal * 0.1;
        const total = subtotal + shipping + tax;

        // Update totals display
        document.getElementById('order-subtotal').textContent = `$${subtotal.toFixed(2)}`;
        document.getElementById('order-tax').textContent = `$${tax.toFixed(2)}`;
        document.getElementById('order-total').textContent = `$${total.toFixed(2)}`;
    }

    bindEvents() {
        // Handle form submission for COD
        document.getElementById('checkout-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            if (paymentMethod === 'cod') {
                await this.processCheckout(e.target);
            }
            // PayPal is handled by PayPal buttons
        });
    }

    async processCheckout(form) {
        const placeOrderBtn = document.getElementById('place-order-btn');
        const originalText = placeOrderBtn.textContent;
        
        // Disable button and show loading
        placeOrderBtn.disabled = true;
        placeOrderBtn.textContent = 'Processing...';

        try {
            const formData = new FormData(form);
            const response = await fetch(window.OneStoreClient.url('/checkout/process'), {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                window.location.href = window.OneStoreClient.url(`/order-confirmation/${data.order_id}`);
            } else {
                this.showError(data.message || 'Failed to process order');
                placeOrderBtn.disabled = false;
                placeOrderBtn.textContent = originalText;
            }
        } catch (error) {
            console.error('Checkout error:', error);
            this.showError('An error occurred while processing your order');
            placeOrderBtn.disabled = false;
            placeOrderBtn.textContent = originalText;
        }
    }

    showError(message) {
        if (typeof swal !== 'undefined') {
            swal('Error', message, 'error');
        } else {
            alert('Error: ' + message);
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    new CheckoutPage();
});
</script> 