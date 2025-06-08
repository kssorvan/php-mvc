<?php
use App\Helpers\Helper;

$page_title = 'Order Details - OneStore Admin';
$content = ob_start();
?>

<!-- Breadcrumb and Header -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/orders">Orders</a></li>
        <li class="breadcrumb-item active">Order #<?= htmlspecialchars($order['order_number'] ?? $order['orderID']) ?></li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-receipt text-primary me-2"></i>Order #<?= htmlspecialchars($order['order_number'] ?? $order['orderID']) ?>
        </h2>
        <p class="text-muted mb-0">Order placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
    </div>
    <div>
        <a href="/admin/orders" class="btn btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i>Back to Orders
        </a>
        <button type="button" class="btn btn-primary" onclick="updateOrderStatus(<?= $order['orderID'] ?>, '<?= htmlspecialchars($order['order_status'] ?? $order['status'] ?? 'pending') ?>')">
            <i class="fas fa-edit me-1"></i>Update Status
        </button>
    </div>
</div>

<!-- Order Status and Quick Info -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body" style="background: #f8f9fa; border-radius: 10px;">
                <div class="row">
                    <div class="col-md-3">
                        <span class="form-label">Order Status</span>
                        <br>
                        <?php
                        $statusColors = [
                            'pending' => 'warning',
                            'confirmed' => 'info',
                            'processing' => 'primary',
                            'shipped' => 'success',
                            'delivered' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $orderStatus = $order['order_status'] ?? $order['status'] ?? 'pending';
                        $color = $statusColors[$orderStatus] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?> badge-status" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                            <?= ucfirst(htmlspecialchars($orderStatus)) ?>
                        </span>
                    </div>
                    <div class="col-md-3">
                        <span class="form-label">Payment Status</span>
                        <br>
                        <?php
                        $paymentColors = [
                            'pending' => 'warning',
                            'paid' => 'success',
                            'failed' => 'danger',
                            'refunded' => 'secondary'
                        ];
                        $paymentStatus = $order['payment_status'] ?? 'pending';
                        $paymentColor = $paymentColors[$paymentStatus] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $paymentColor ?> badge-status" style="font-size: 0.875rem; padding: 0.5rem 1rem;">
                            <?= ucfirst(htmlspecialchars($paymentStatus)) ?>
                        </span>
                    </div>
                    <div class="col-md-3">
                        <span class="form-label">Payment Method</span>
                        <br>
                        <strong><?= ucfirst(htmlspecialchars($order['payment_method'] ?? 'N/A')) ?></strong>
                    </div>
                    <div class="col-md-3">
                        <span class="form-label">Total Amount</span>
                        <br>
                        <strong class="h5 text-success">$<?= number_format($order['total_amount'], 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted">Quick Actions</h6>
                <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="updateOrderStatus(<?= $order['orderID'] ?>, '<?= htmlspecialchars($orderStatus) ?>')">
                    <i class="fas fa-edit"></i> Update Status
                </button>
                <button type="button" class="btn btn-outline-info btn-sm" onclick="updatePaymentStatus(<?= $order['orderID'] ?>, '<?= htmlspecialchars($paymentStatus) ?>')">
                    <i class="fas fa-credit-card"></i> Update Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Order Details -->
<div class="row">
    <div class="col-md-8">
        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-shopping-bag me-2"></i>Order Items
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($order['items'])): ?>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['items'] as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($item['image_path'])): ?>
                                                    <img src="<?= Helper::upload($item['image_path']) ?>" 
                                                         alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" class="me-3">
                                                <?php else: ?>
                                                    <div class="me-3 bg-light d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 8px;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                                    <?php if (!empty($item['product_sku'])): ?>
                                                        <br><small class="text-muted">SKU: <?= htmlspecialchars($item['product_sku']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark"><?= $item['quantity'] ?></span>
                                        </td>
                                        <td>
                                            $<?= number_format($item['price'], 2) ?>
                                        </td>
                                        <td>
                                            <strong>$<?= number_format($item['total'] ?? ($item['price'] * $item['quantity']), 2) ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Order Totals -->
                    <div class="border-top pt-3">
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td>Subtotal:</td>
                                        <td class="text-end">$<?= number_format($order['subtotal'] ?? $order['total_amount'], 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Shipping:</td>
                                        <td class="text-end">$<?= number_format($order['shipping_amount'] ?? 0, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tax:</td>
                                        <td class="text-end">$<?= number_format($order['tax_amount'] ?? 0, 2) ?></td>
                                    </tr>
                                    <?php if (($order['discount_amount'] ?? 0) > 0): ?>
                                    <tr class="text-success">
                                        <td>Discount:</td>
                                        <td class="text-end">-$<?= number_format($order['discount_amount'], 2) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr class="border-top">
                                        <td><strong>Total:</strong></td>
                                        <td class="text-end"><strong>$<?= number_format($order['total_amount'], 2) ?></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No items found for this order.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Order Notes -->
        <?php if (!empty($order['notes'])): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-sticky-note me-2"></i>Order Notes
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <!-- Customer Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Customer Information
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($customer)): ?>
                    <p class="mb-2">
                        <strong><?= htmlspecialchars(($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '')) ?></strong>
                    </p>
                    <p class="mb-2">
                        <i class="fas fa-envelope me-2 text-muted"></i>
                        <a href="mailto:<?= htmlspecialchars($customer['email'] ?? '') ?>"><?= htmlspecialchars($customer['email'] ?? '') ?></a>
                    </p>
                    <?php if (!empty($customer['phone'])): ?>
                    <p class="mb-2">
                        <i class="fas fa-phone me-2 text-muted"></i>
                        <a href="tel:<?= htmlspecialchars($customer['phone']) ?>"><?= htmlspecialchars($customer['phone']) ?></a>
                    </p>
                    <?php endif; ?>
                    <p class="mb-0">
                        <small class="text-muted">Customer since <?= date('M Y', strtotime($customer['created_at'] ?? 'now')) ?></small>
                    </p>
                    <div class="mt-3">
                        <a href="/admin/customers/view?id=<?= $customer['customerID'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-user-circle me-1"></i>View Customer
                        </a>
                    </div>
                <?php else: ?>
                    <p class="mb-0 text-muted">Guest Customer</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Billing Address -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice me-2"></i>Billing Address
                </h5>
            </div>
            <div class="card-body">
                <address class="mb-0">
                    <?= nl2br(htmlspecialchars($order['billing_address'] ?? 'No billing address provided')) ?>
                </address>
            </div>
        </div>
        
        <!-- Shipping Address -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-shipping-fast me-2"></i>Shipping Address
                </h5>
            </div>
            <div class="card-body">
                <address class="mb-0">
                    <?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'No shipping address provided')) ?>
                </address>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
                            <form method="POST" action="<?= Helper::adminUrl('orders/update-status') ?>">
                <div class="modal-body">
                    <input type="hidden" name="orderID" id="statusOrderID">
                    <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Order Status</label>
                        <select name="order_status" id="orderStatus" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payment Status Update Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Payment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="/admin/orders/update-payment-status">
                <div class="modal-body">
                    <input type="hidden" name="orderID" id="paymentOrderID">
                    <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" id="paymentStatus" class="form-select" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Payment Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Order view-specific JavaScript
$inline_scripts = '
function updateOrderStatus(orderID, currentStatus) {
    document.getElementById("statusOrderID").value = orderID;
    document.getElementById("orderStatus").value = currentStatus;
    
    new bootstrap.Modal(document.getElementById("statusModal")).show();
}

function updatePaymentStatus(orderID, currentPaymentStatus) {
    document.getElementById("paymentOrderID").value = orderID;
    document.getElementById("paymentStatus").value = currentPaymentStatus;
    
    new bootstrap.Modal(document.getElementById("paymentModal")).show();
}

console.log("Order view loaded successfully");
';

// Include the admin layout
include ROOT_PATH . '/app/Views/Admin/layouts/admin.php';
?> 