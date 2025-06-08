<?php use App\Helpers\Helper; ?>

<!-- Order Confirmation -->
<div class="bg0 p-t-75 p-b-85">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-lg-9 p-b-80">
                <div class="p-r-45 p-r-0-lg">
                    <!-- Success Message -->
                    <div class="text-center p-b-50">
                        <div class="p-b-30">
                            <i class="zmdi zmdi-check-circle" style="font-size: 80px; color: #28a745;"></i>
                        </div>
                        <h3 class="mtext-111 cl2 p-b-16">Order Confirmed!</h3>
                        <p class="stext-113 cl6 p-b-20">
                            Thank you for your order. We've received your order and will begin processing it soon.
                        </p>
                        <div class="bor10 p-lr-40 p-t-20 p-b-20" style="background-color: #f8f9fa;">
                            <p class="stext-113 cl2">
                                <strong>Order Number:</strong> <?= htmlspecialchars($order['order_number']) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="bor10 p-lr-40 p-t-30 p-b-40 m-b-40">
                        <h4 class="mtext-109 cl2 p-b-30">Order Details</h4>
                        
                        <div class="row p-b-25">
                            <div class="col-md-6">
                                <div class="p-b-20">
                                    <h6 class="stext-102 cl3 p-b-10">Order Information</h6>
                                    <p class="stext-104 cl6 p-b-5">
                                        <strong>Order Number:</strong> <?= htmlspecialchars($order['order_number']) ?>
                                    </p>
                                    <p class="stext-104 cl6 p-b-5">
                                        <strong>Order Date:</strong> <?= date('F j, Y', strtotime($order['created_at'])) ?>
                                    </p>
                                    <p class="stext-104 cl6 p-b-5">
                                        <strong>Payment Method:</strong> 
                                        <?php if ($order['payment_method'] === 'cod'): ?>
                                            Cash on Delivery
                                        <?php else: ?>
                                            PayPal
                                        <?php endif; ?>
                                    </p>
                                    <p class="stext-104 cl6">
                                        <strong>Status:</strong> 
                                        <span class="cl1"><?= ucfirst($order['order_status']) ?></span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-b-20">
                                    <h6 class="stext-102 cl3 p-b-10">Billing Address</h6>
                                    <p class="stext-104 cl6">
                                        <?= nl2br(htmlspecialchars($order['billing_address'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($order['shipping_address']) && $order['shipping_address'] !== $order['billing_address']): ?>
                        <div class="row p-b-25">
                            <div class="col-md-6">
                                <div class="p-b-20">
                                    <h6 class="stext-102 cl3 p-b-10">Shipping Address</h6>
                                    <p class="stext-104 cl6">
                                        <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Order Items -->
                    <div class="bor10 p-lr-40 p-t-30 p-b-40 m-b-40">
                        <h4 class="mtext-109 cl2 p-b-30">Items Ordered</h4>
                        
                        <div class="wrap-table-shopping-cart">
                            <table class="table-shopping-cart">
                                <tr class="table_head">
                                    <th class="column-1">Product</th>
                                    <th class="column-2"></th>
                                    <th class="column-3">Price</th>
                                    <th class="column-4">Quantity</th>
                                    <th class="column-5">Total</th>
                                </tr>
                                
                                <?php foreach ($order['items'] as $item): ?>
                                <tr class="table_row">
                                    <td class="column-1">
                                        <div class="how-itemcart1">
                                        <img src="<?= Helper::url('/public/uploads/' . ($item['image_path'] ?? 'placeholder.jpg')) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                        </div>
                                    </td>
                                    <td class="column-2"><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td class="column-3">$<?= number_format($item['price'], 2) ?></td>
                                    <td class="column-4"><?= $item['quantity'] ?></td>
                                    <td class="column-5">$<?= number_format($item['total'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center p-t-20">
                        <a href="/onestore/shop" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 m-r-10 m-b-10">
                            Continue Shopping
                        </a>
                        <?php $user = $this->getCurrentUser(); ?>
                        <?php if ($user): ?>
                        <a href="/account/orders" class="flex-c-m stext-101 cl2 size-116 bg8 bor14 hov-btn4 p-lr-15 trans-04 m-b-10">
                            View All Orders
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-md-4 col-lg-3 p-b-80">
                <div class="bor10 p-lr-40 p-t-30 p-b-40">
                    <h4 class="mtext-109 cl2 p-b-30">Order Summary</h4>

                    <div class="flex-w flex-t bor12 p-b-13">
                        <div class="size-208">
                            <span class="stext-110 cl2">Subtotal:</span>
                        </div>
                        <div class="size-209">
                            <span class="mtext-110 cl2">$<?= number_format($order['subtotal'], 2) ?></span>
                        </div>
                    </div>

                    <div class="flex-w flex-t bor12 p-b-13">
                        <div class="size-208">
                            <span class="stext-110 cl2">Shipping:</span>
                        </div>
                        <div class="size-209">
                            <span class="mtext-110 cl2">$<?= number_format($order['shipping_amount'], 2) ?></span>
                        </div>
                    </div>

                    <div class="flex-w flex-t bor12 p-b-13">
                        <div class="size-208">
                            <span class="stext-110 cl2">Tax:</span>
                        </div>
                        <div class="size-209">
                            <span class="mtext-110 cl2">$<?= number_format($order['tax_amount'], 2) ?></span>
                        </div>
                    </div>

                    <div class="flex-w flex-t p-t-27 p-b-33">
                        <div class="size-208">
                            <span class="mtext-101 cl2">Total:</span>
                        </div>
                        <div class="size-209 p-t-1">
                            <span class="mtext-110 cl2">$<?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                    </div>

                    <?php if ($order['payment_method'] === 'cod'): ?>
                    <div class="p-t-20 p-b-20" style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px;">
                        <div class="text-center">
                            <i class="fa fa-money cl1 m-b-10" style="font-size: 24px;"></i>
                            <p class="stext-113 cl6 m-b-5">
                                <strong>Cash on Delivery</strong>
                            </p>
                            <p class="stext-114 cl6">
                                Pay $<?= number_format($order['total_amount'], 2) ?> when your order arrives
                            </p>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="p-t-20 p-b-20" style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 15px;">
                        <div class="text-center">
                            <i class="fa fa-paypal cl1 m-b-10" style="font-size: 24px;"></i>
                            <p class="stext-113 cl6 m-b-5">
                                <strong>Payment Status</strong>
                            </p>
                            <p class="stext-114 cl6">
                                <?= ucfirst($order['payment_status']) ?>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($order['notes'])): ?>
                    <div class="p-t-20">
                        <h6 class="stext-102 cl3 p-b-10">Order Notes</h6>
                        <p class="stext-104 cl6">
                            <?= nl2br(htmlspecialchars($order['notes'])) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Order confirmation specific styles */
.how-itemcart1 {
    width: 80px;
    height: 80px;
    overflow: hidden;
    border-radius: 8px;
}

.how-itemcart1 img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@media (max-width: 991px) {
    .table-shopping-cart {
        min-width: 600px;
    }
    
    .table-shopping-cart .column-1 {
        width: 100px;
        padding-left: 20px;
    }
    
    .table-shopping-cart .column-5 {
        padding-right: 20px;
    }
}
</style> 