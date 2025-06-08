<?php
use App\Helpers\Helper;

$page_title = 'Order Management - OneStore Admin';
$content = ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-shopping-cart text-primary me-2"></i>Order Management
        </h2>
        <p class="text-muted mb-0">Manage customer orders and track status</p>
    </div>
</div>

<!-- Statistics Cards -->
<?php if (!empty($stats)): ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total Orders</h6>
                    <h3 class="stats-number mb-0"><?= number_format($stats['total_orders'] ?? 0) ?></h3>
                </div>
                <div class="stats-icon primary">
                    <i class="fas fa-shopping-bag"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Pending Orders</h6>
                    <h3 class="stats-number mb-0"><?= number_format($stats['pending_orders'] ?? 0) ?></h3>
                </div>
                <div class="stats-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Revenue</h6>
                    <h3 class="stats-number mb-0">$<?= number_format($stats['total_revenue'] ?? 0, 2) ?></h3>
                </div>
                <div class="stats-icon success">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Avg. Order</h6>
                    <h3 class="stats-number mb-0">$<?= number_format($stats['avg_order'] ?? 0, 2) ?></h3>
                </div>
                <div class="stats-icon info">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Order Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">All Statuses</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="processing" <?= ($filters['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="shipped" <?= ($filters['status'] ?? '') === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= ($filters['status'] ?? '') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="payment_status" class="form-label">Payment Status</label>
                <select class="form-select" name="payment_status" id="payment_status">
                    <option value="">All Payment Status</option>
                    <option value="pending" <?= ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="paid" <?= ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="failed" <?= ($filters['payment_status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="refunded" <?= ($filters['payment_status'] ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control" name="date_from" id="date_from" value="<?= $filters['date_from'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control" name="date_to" id="date_to" value="<?= $filters['date_to'] ?? '' ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Filter Orders
                </button>
                <a href="/admin/orders" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No orders found</h5>
                <p class="text-muted">Orders will appear here when customers make purchases.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?= $order['orderID'] ?></strong></td>
                                <td>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($order['customer_name'] ?? 'Unknown Customer') ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($order['customer_email'] ?? 'No email') ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $order['item_count'] ?? 0 ?> items</span>
                                </td>
                                <td><strong>$<?= number_format($order['total_amount'], 2) ?></strong></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        ($order['order_status'] ?? '') === 'delivered' ? 'success' : 
                                        (($order['order_status'] ?? '') === 'shipped' ? 'info' : 
                                        (($order['order_status'] ?? '') === 'processing' ? 'warning' : 
                                        (($order['order_status'] ?? '') === 'cancelled' ? 'danger' : 'secondary'))) ?>">
                                        <?= ucfirst($order['order_status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= 
                                        ($order['payment_status'] ?? '') === 'paid' ? 'success' : 
                                        (($order['payment_status'] ?? '') === 'failed' ? 'danger' : 
                                        (($order['payment_status'] ?? '') === 'refunded' ? 'warning' : 'secondary')) ?>">
                                        <?= ucfirst($order['payment_status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($order['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="/admin/orders/view?id=<?= $order['orderID'] ?>" 
                                       class="btn btn-sm btn-outline-primary btn-action" 
                                       title="View Order">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-success btn-action" 
                                            onclick="updateOrderStatus(<?= $order['orderID'] ?>)" 
                                            title="Update Status">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning btn-action" 
                                            onclick="updatePaymentStatus(<?= $order['orderID'] ?>)" 
                                            title="Update Payment">
                                        <i class="fas fa-credit-card"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (!empty($pagination)): ?>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing <?= $pagination['start'] ?> to <?= $pagination['end'] ?> of <?= $pagination['total'] ?> orders
                    </div>
                    <nav>
                        <ul class="pagination mb-0">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] - 1 ?><?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $pagination['current_page'] + 1 ?><?= !empty($_GET) ? '&' . http_build_query(array_diff_key($_GET, ['page' => ''])) : '' ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Order Status Modal -->
<div class="modal fade" id="orderStatusModal" tabindex="-1" aria-labelledby="orderStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderStatusModalLabel">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="orderStatusForm" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="order_status" class="form-label">Order Status</label>
                        <select class="form-select" id="order_status" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="status_note" class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="status_note" name="note" rows="3" placeholder="Add a note about this status change..."></textarea>
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

<!-- Payment Status Modal -->
<div class="modal fade" id="paymentStatusModal" tabindex="-1" aria-labelledby="paymentStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentStatusModalLabel">Update Payment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentStatusForm" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_status_select" class="form-label">Payment Status</label>
                        <select class="form-select" id="payment_status_select" name="payment_status" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_note" class="form-label">Note (Optional)</label>
                        <textarea class="form-control" id="payment_note" name="note" rows="3" placeholder="Add a note about this payment change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Orders-specific JavaScript
$inline_scripts = '
let currentOrderId = null;

function updateOrderStatus(orderId) {
    currentOrderId = orderId;
    const actionUrl = window.OneStoreAdmin.adminUrl("orders/update-status?id=" + orderId);
    
    document.getElementById("orderStatusForm").action = actionUrl;
    
    // Reset form
    document.getElementById("order_status").value = "pending";
    document.getElementById("status_note").value = "";
    
    new bootstrap.Modal(document.getElementById("orderStatusModal")).show();
}

function updatePaymentStatus(orderId) {
    currentOrderId = orderId;
    const actionUrl = window.OneStoreAdmin.adminUrl("orders/update-payment-status?id=" + orderId);
    
    document.getElementById("paymentStatusForm").action = actionUrl;
    
    // Reset form
    document.getElementById("payment_status_select").value = "pending";
    document.getElementById("payment_note").value = "";
    
    new bootstrap.Modal(document.getElementById("paymentStatusModal")).show();
}

// Order status form submission
document.getElementById("orderStatusForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector("button[type=submit]");
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = "Updating...";
    
    fetch(this.action, {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("HTTP error! status: " + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById("orderStatusModal"));
            
            // Update button to show success
            submitBtn.textContent = "✓ Updated!";
            submitBtn.className = "btn btn-success";
            
            // Close modal and reload after brief delay
            setTimeout(() => {
                if (modal) {
                    modal.hide();
                }
                
                if (typeof swal !== "undefined") {
                    swal("Success!", "Order status updated successfully", "success").then(() => {
                        window.location.reload();
                    });
                } else {
                    alert("Order status updated successfully!");
                    window.location.reload();
                }
            }, 500);
            
        } else {
            alert(data.message || "An error occurred");
        }
    })
    .catch(error => {
        alert("An error occurred while updating the order status: " + error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Payment status form submission
document.getElementById("paymentStatusForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector("button[type=submit]");
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = "Updating...";
    
    fetch(this.action, {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("HTTP error! status: " + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById("paymentStatusModal"));
            
            // Update button to show success
            submitBtn.textContent = "✓ Updated!";
            submitBtn.className = "btn btn-success";
            
            // Close modal and reload after brief delay
            setTimeout(() => {
                if (modal) {
                    modal.hide();
                }
                
                if (typeof swal !== "undefined") {
                    swal("Success!", "Payment status updated successfully", "success").then(() => {
                        window.location.reload();
                    });
                } else {
                    alert("Payment status updated successfully!");
                    window.location.reload();
                }
            }, 500);
            
        } else {
            alert(data.message || "An error occurred");
        }
    })
    .catch(error => {
        alert("An error occurred while updating the payment status: " + error.message);
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});
';

// Include the admin layout
include ROOT_PATH . '/app/Views/Admin/layouts/admin.php';
?> 