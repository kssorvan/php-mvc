<?php
use App\Helpers\Helper;

$page_title = 'Customer Management - OneStore Admin';
$content = ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-users text-primary me-2"></i>Customer Management
        </h2>
        <p class="text-muted mb-0">Manage customer accounts and information</p>
    </div>
</div>

<!-- Statistics Cards -->
<?php if (!empty($stats)): ?>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total Customers</h6>
                    <h3 class="stats-number mb-0"><?= $stats['total_customers'] ?? 0 ?></h3>
                </div>
                <div class="stats-icon primary">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Verified Customers</h6>
                    <h3 class="stats-number mb-0"><?= $stats['verified_customers'] ?? 0 ?></h3>
                </div>
                <div class="stats-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">New This Month</h6>
                    <h3 class="stats-number mb-0"><?= $stats['new_customers_month'] ?? 0 ?></h3>
                </div>
                <div class="stats-icon info">
                    <i class="fas fa-user-plus"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Active Orders</h6>
                    <h3 class="stats-number mb-0"><?= $stats['active_orders'] ?? 0 ?></h3>
                </div>
                <div class="stats-icon warning">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Search Customers</label>
                <input type="text" class="form-control" name="search" id="search" 
                       placeholder="Search by name or email..." value="<?= $filter['search'] ?? '' ?>">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label">Email Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="">All Customers</option>
                    <option value="verified" <?= ($filter['status'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified</option>
                    <option value="unverified" <?= ($filter['status'] ?? '') === 'unverified' ? 'selected' : '' ?>>Unverified</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="sort" class="form-label">Sort By</label>
                <select class="form-select" name="sort" id="sort">
                    <option value="newest" <?= ($filter['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest First</option>
                    <option value="oldest" <?= ($filter['sort'] ?? '') === 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                    <option value="name" <?= ($filter['sort'] ?? '') === 'name' ? 'selected' : '' ?>>Name A-Z</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
                <a href="/admin/customers" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($customers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No customers found</h5>
                <p class="text-muted">Customer accounts will appear here when users register.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Orders</th>
                            <th>Total Spent</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><strong>#<?= $customer['customerID'] ?></strong></td>
                                <td>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($customer['firstName'] . ' ' . $customer['lastName']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($customer['email']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?php if (!empty($customer['phone'])): ?>
                                            <div><i class="fas fa-phone me-1"></i><?= htmlspecialchars($customer['phone']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($customer['city'])): ?>
                                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($customer['city']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $customer['order_count'] ?? 0 ?> orders</span>
                                </td>
                                <td>
                                    <strong>$<?= number_format($customer['total_spent'] ?? 0, 2) ?></strong>
                                </td>
                                <td>
                                    <?php if ($customer['email_verified']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Verified
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Unverified
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($customer['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <a href="/admin/customers/view?id=<?= $customer['customerID'] ?>" 
                                       class="btn btn-sm btn-outline-primary btn-action" 
                                       title="View Customer">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-success btn-action" 
                                            onclick="editCustomer(<?= $customer['customerID'] ?>)" 
                                            title="Edit Customer">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if (!$customer['email_verified']): ?>
                                        <button class="btn btn-sm btn-outline-info btn-action" 
                                                onclick="verifyEmail(<?= $customer['customerID'] ?>)" 
                                                title="Verify Email">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
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
                        Showing <?= $pagination['start'] ?> to <?= $pagination['end'] ?> of <?= $pagination['total'] ?> customers
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

<!-- Customer Edit Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Edit Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="customerForm" method="POST" action="/admin/customers/update">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="firstName" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="firstName" name="firstName" required>
                    </div>
                    <div class="mb-3">
                        <label for="lastName" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="lastName" name="lastName" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <small class="form-text text-muted">Leave blank to keep current password</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Customers-specific JavaScript
$inline_scripts = '
// Customer Management JavaScript
let editMode = false;
let editId = null;

function editCustomer(customerId) {
    editMode = true;
    editId = customerId;
    document.getElementById("customerModalLabel").textContent = "Edit Customer";
    
    // Fetch customer data
    fetch(window.OneStoreAdmin.adminUrl(`/customers/get?id=${customerId}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const customer = data.customer;
                document.getElementById("firstName").value = customer.firstName || "";
                document.getElementById("lastName").value = customer.lastName || "";
                document.getElementById("email").value = customer.email || "";
                document.getElementById("phone").value = customer.phone || "";
                document.getElementById("password").value = "";
                
                document.getElementById("customerForm").action = window.OneStoreAdmin.adminUrl(`/customers/update?id=${customerId}`);
                new bootstrap.Modal(document.getElementById("customerModal")).show();
            } else {
                alert(data.message || "Error loading customer data");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error loading customer data");
        });
}

function verifyEmail(customerId) {
    if (confirm("Are you sure you want to verify this customer\'s email?")) {
        fetch(window.OneStoreAdmin.adminUrl("/customers/verify-email"), {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `customerID=${customerId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || "Email verified successfully");
                location.reload();
            } else {
                alert(data.message || "An error occurred while verifying email");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while verifying email");
        });
    }
}

// Form submission
document.getElementById("customerForm").addEventListener("submit", function(e) {
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById("customerModal")).hide();
            alert(data.message || "Customer updated successfully");
            location.reload();
        } else {
            alert(data.message || "An error occurred");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while updating the customer");
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

console.log("Customer management loaded successfully");
';

// Include the admin layout
include ROOT_PATH . '/app/Views/Admin/layouts/admin.php';
?> 