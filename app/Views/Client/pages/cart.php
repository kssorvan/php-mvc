<?php use App\Helpers\Helper; ?>

<div class="bg0 p-t-75 p-b-85">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 col-xl-7 m-lr-auto m-b-50">
                <div class="m-l-25 m-r--38 m-lr-0-xl">
                    <!-- Cart Table - Only show when cart has items -->
                    <?php if (!empty($cart_items)): ?>
                    <div class="wrap-table-shopping-cart">
                        <table class="table-shopping-cart">
                            <tr class="table_head">
                                <th class="column-1">Product</th>
                                <th class="column-2"></th>
                                <th class="column-3">Price</th>
                                <th class="column-4">Quantity</th>
                                <th class="column-5">Total</th>
                                <th class="column-6"></th>
                            </tr>

                            <tbody id="cart-items">
                                <?php foreach ($cart_items as $item): ?>
                                <tr class="table_row" data-cart-id="<?= $item['cartID'] ?>">
                                    <td class="column-1">
                                        <div class="how-itemcart1">
                                            <img src="<?= Helper::upload($item['image_path']) ?>" alt="<?= Helper::sanitize($item['name']) ?>">
                                        </div>
                                    </td>
                                    <td class="column-2"><?= Helper::sanitize($item['name']) ?></td>
                                    <td class="column-3"><?= Helper::formatCurrency($item['price']) ?></td>
                                    <td class="column-4">
                                        <div class="wrap-num-product flex-w m-l-auto m-r-0">
                                            <div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m" onclick="updateQuantity(<?= $item['cartID'] ?>, <?= $item['quantity'] - 1 ?>)">
                                                <i class="fs-16 zmdi zmdi-minus"></i>
                                            </div>

                                            <input class="mtext-104 cl3 txt-center num-product" type="number" 
                                                   name="quantity" value="<?= $item['quantity'] ?>" min="1" 
                                                   onchange="updateQuantity(<?= $item['cartID'] ?>, this.value)">

                                            <div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m" onclick="updateQuantity(<?= $item['cartID'] ?>, <?= $item['quantity'] + 1 ?>)">
                                                <i class="fs-16 zmdi zmdi-plus"></i>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="column-5 item-total"><?= Helper::formatCurrency($item['total']) ?></td>
                                    <td class="column-6">
                                        <button class="btn-remove-item" onclick="removeItem(<?= $item['cartID'] ?>)">
                                            <i class="zmdi zmdi-close"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <!-- Empty Cart State -->
                    <div id="empty-cart-state" class="txt-center p-t-50 p-b-50" style="<?= !empty($cart_items) ? 'display: none;' : '' ?>">
                        <div class="empty-cart-icon">
                            <i class="zmdi zmdi-shopping-cart" style="font-size: 64px; color: #999; margin-bottom: 20px;"></i>
                        </div>
                        <h4 class="mtext-109 cl2 p-b-30">Your cart is empty</h4>
                        <p class="stext-113 cl6 p-b-26">Looks like you haven't added anything to your cart yet</p>
                        <a href="<?= Helper::url('/shop') ?>" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer">
                            CONTINUE SHOPPING
                        </a>
                    </div>

                    <div class="flex-w flex-sb-m bor15 p-t-18 p-b-15 p-lr-40 p-lr-15-sm" id="cart-actions" style="<?= empty($cart_items) ? 'display: none;' : '' ?>">
                        <div class="flex-w flex-m m-r-20 m-tb-5">
                            <input class="stext-104 cl2 plh4 size-117 bor13 p-lr-20 m-r-10 m-tb-5" 
                                   type="text" id="coupon-code" placeholder="Coupon Code">
                                
                            <button class="flex-c-m stext-101 cl2 size-118 bg8 bor13 hov-btn3 p-lr-15 trans-04 pointer m-tb-5" 
                                    onclick="applyCoupon()">
                                Apply coupon
                            </button>
                        </div>

                        <button class="flex-c-m stext-101 cl2 size-119 bg8 bor13 hov-btn3 p-lr-15 trans-04 pointer m-tb-10"
                                onclick="updateCart()">
                            Update Cart
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-sm-10 col-lg-7 col-xl-5 m-lr-auto m-b-50" id="cart-summary" style="<?= empty($cart_items) ? 'display: none;' : '' ?>">
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
                            <span class="mtext-110 cl2" id="subtotal">
                                <?= Helper::formatCurrency($subtotal ?? 0) ?>
                            </span>
                        </div>
                    </div>

                    <div class="flex-w flex-t bor12 p-t-15 p-b-30">
                        <div class="size-208 w-full-ssm">
                            <span class="stext-110 cl2">
                                Shipping:
                            </span>
                        </div>
                        <div class="size-209 p-t-1">
                            <span class="stext-111 cl6">
                                <?= Helper::formatCurrency($shipping ?? 10.00) ?>
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
                            <span class="mtext-110 cl2" id="total">
                                <?= Helper::formatCurrency($total ?? 0) ?>
                            </span>
                        </div>
                    </div>

                    <div class="cart-actions">
                        <a href="<?= Helper::url('/shop') ?>" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer m-tb-5">
                            Continue Shopping
                        </a>
                        
                        <?php if (!empty($cart_items)): ?>
                        <button class="flex-c-m stext-101 cl0 size-116 bg1 bor14 hov-btn3 p-lr-15 trans-04 pointer m-tb-5 w-full"
                                onclick="proceedToCheckout()">
                            Proceed to Checkout
                        </button>
                        
                        <button class="flex-c-m stext-101 cl6 size-116 bg8 bor14 hov-btn3 p-lr-15 trans-04 pointer m-tb-5 w-full"
                                onclick="clearCart()">
                            Clear Cart
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript Configuration
window.OneStoreClient = window.OneStoreClient || {
    baseUrl: '<?= Helper::url() ?>',
    url: (path) => '<?= Helper::url() ?>' + (path || '')
};

// Cart management functions
function updateQuantity(cartID, quantity) {
    if (quantity < 1) {
        removeItem(cartID);
        return;
    }
    
    fetch(window.OneStoreClient.url('/cart/update'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart_id: cartID,
            quantity: quantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to update all totals
        } else {
            alert(data.message || 'Failed to update quantity');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function removeItem(cartID) {
    if (!confirm('Are you sure you want to remove this item?')) {
        return;
    }
    
    fetch(window.OneStoreClient.url('/cart/remove'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart_id: cartID
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the item row
            const row = document.querySelector(`tr[data-cart-id="${cartID}"]`);
            if (row) {
                row.remove();
            }
            
            // Check if cart is now empty
            const remainingItems = document.querySelectorAll('#cart-items .table_row[data-cart-id]');
            if (remainingItems.length === 0) {
                showEmptyCartState();
            } else {
                location.reload(); // Reload to update totals
            }
        } else {
            alert(data.message || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function clearCart() {
    if (!confirm('Are you sure you want to clear your entire cart?')) {
        return;
    }
    
    fetch(window.OneStoreClient.url('/cart/clear'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showEmptyCartState();
        } else {
            alert(data.message || 'Failed to clear cart');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

function showEmptyCartState() {
    // Hide cart table and actions
    const cartTable = document.querySelector('.wrap-table-shopping-cart');
    if (cartTable) cartTable.style.display = 'none';
    
    const cartActions = document.getElementById('cart-actions');
    if (cartActions) cartActions.style.display = 'none';
    
    const cartSummary = document.getElementById('cart-summary');
    if (cartSummary) cartSummary.style.display = 'none';
    
    // Show empty cart state
    const emptyCartState = document.getElementById('empty-cart-state');
    if (emptyCartState) emptyCartState.style.display = 'block';
}

function applyCoupon() {
    const couponCode = document.getElementById('coupon-code').value.trim();
    if (!couponCode) {
        alert('Please enter a coupon code');
        return;
    }
    
    // TODO: Implement coupon functionality
    alert('Coupon functionality coming soon!');
}

function updateCart() {
    location.reload();
}

function proceedToCheckout() {
    window.location.href = window.OneStoreClient.url('/checkout');
}
</script>

<style>
.btn-remove-item {
    background: none;
    border: none;
    color: #999;
    font-size: 16px;
    cursor: pointer;
    padding: 5px;
    transition: color 0.3s;
}

.btn-remove-item:hover {
    color: #e74c3c;
}

.cart-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.w-full {
    width: 100%;
}
</style> 