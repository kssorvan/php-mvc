<?php
use App\Helpers\Helper;

$page_title = 'Product Management - OneStore Admin';
$content = ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-box text-primary me-2"></i>Product Management
        </h2>
        <p class="text-muted mb-0">Manage your store products</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openCreateModal()">
        <i class="fas fa-plus me-2"></i>Add New Product
    </button>
</div>

<!-- Products Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No products found</h5>
                <p class="text-muted">Start by adding your first product!</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openCreateModal()">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><strong>#<?= $product['productID'] ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($product['image_path'])): ?>
                                            <img src="<?= Helper::upload($product['image_path']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name'] ?? 'Product') ?>" 
                                                 class="product-image me-2">
                                        <?php else: ?>
                                            <div class="product-image me-2 bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($product['name'] ?? 'Unnamed Product') ?></div>
                                            <small class="text-muted"><?= htmlspecialchars(substr($product['description'] ?? '', 0, 50)) ?>...</small>
                                        </div>
                                    </div>
                                </td>
                                <td><strong>$<?= number_format($product['price'] ?? 0, 2) ?></strong></td>
                                <td>
                                    <span class="badge bg-info"><?= htmlspecialchars($product['category_name'] ?? 'No Category') ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($product['brand_name'] ?? 'No Brand') ?></span>
                                </td>
                                <td>
                                    <span class="<?= ($product['stock'] ?? 0) < 10 ? 'text-danger' : 'text-success' ?>">
                                        <?= $product['stock'] ?? 0 ?> units
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-status bg-<?= ($product['status'] ?? 0) == 1 ? 'success' : 'secondary' ?>">
                                        <?= ($product['status'] ?? 0) == 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($product['created_at'] ?? 'now')) ?>
                                    </small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" 
                                            onclick="editProduct(<?= $product['productID'] ?>)" 
                                            title="Edit Product">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-action" 
                                            onclick="deleteProduct(<?= $product['productID'] ?>)" 
                                            title="Delete Product">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Product Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Category</label>
                            <select class="form-select" id="category_id" name="category_id">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['categoryID'] ?>"><?= htmlspecialchars($category['name'] ?? 'Unnamed Category') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="brand_id" class="form-label">Brand</label>
                            <select class="form-select" id="brand_id" name="brand_id">
                                <option value="">Select Brand</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?= $brand['brandID'] ?>"><?= htmlspecialchars($brand['name'] ?? 'Unnamed Brand') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="stock" class="form-label">Stock Quantity *</label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="featured" class="form-label">Featured Product</label>
                            <select class="form-select" id="featured" name="featured">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                            <small class="form-text text-muted">Featured products appear prominently on homepage</small>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</small>
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <img id="preview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Products-specific JavaScript
$inline_scripts = '
// Product Management JavaScript
let editMode = false;
let editId = null;

function openCreateModal() {
    editMode = false;
    editId = null;
    document.getElementById("productModalLabel").textContent = "Add New Product";
    document.getElementById("productForm").reset();
    document.getElementById("imagePreview").style.display = "none";
    document.getElementById("productForm").action = window.OneStoreAdmin.adminUrl("products/store");
}

function editProduct(id) {
    editMode = true;
    editId = id;
    document.getElementById("productModalLabel").textContent = "Edit Product";
    
    // Fetch product data
    fetch(window.OneStoreAdmin.adminUrl("products/get?id=" + id))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const product = data.product;
                document.getElementById("name").value = product.name || product.productName || "";
                document.getElementById("price").value = product.price || "";
                document.getElementById("category_id").value = product.categoryID || "";
                document.getElementById("brand_id").value = product.brandID || "";
                document.getElementById("stock").value = product.stock || product.stock_quantity || "";
                // In the editProduct function, add this line after the status line:
document.getElementById("featured").value = product.featured || "0";
                document.getElementById("status").value = product.status || "";
                document.getElementById("description").value = product.description || "";
                
                // Show existing image if available
                if (product.image_path) {
                    document.getElementById("preview").src = window.OneStoreAdmin.baseUrl + "/uploads/" + product.image_path;
                    document.getElementById("imagePreview").style.display = "block";
                }
                
                document.getElementById("productForm").action = window.OneStoreAdmin.adminUrl("products/update?id=" + id);
                new bootstrap.Modal(document.getElementById("productModal")).show();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error loading product data");
        });
}

function deleteProduct(id) {
    if (confirm("Are you sure you want to delete this product?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = window.OneStoreAdmin.adminUrl("products/delete?id=" + id);
        document.body.appendChild(form);
        form.submit();
    }
}

// Image preview functionality
document.getElementById("image").addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("preview").src = e.target.result;
            document.getElementById("imagePreview").style.display = "block";
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById("imagePreview").style.display = "none";
    }
});

// Form submission
document.getElementById("productForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector("button[type=submit]");
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = "Saving...";
    
    fetch(this.action, {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById("productModal")).hide();
            location.reload();
        } else {
            alert(data.message || "An error occurred");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while saving the product");
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

console.log("Product management loaded successfully");
';

// Include the admin layout
include ROOT_PATH . '/app/Views/Admin/layouts/admin.php';
?> 