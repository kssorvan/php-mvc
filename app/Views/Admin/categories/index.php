<?php
use App\Helpers\Helper;

$page_title = 'Category Management - OneStore Admin';
$content = ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-tags text-primary me-2"></i>Category Management
        </h2>
        <p class="text-muted mb-0">Organize your product categories</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openCreateModal()">
        <i class="fas fa-plus me-2"></i>Add New Category
    </button>
</div>

<!-- Statistics Cards -->
<?php if (!empty($stats)): ?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total Categories</h6>
                    <h3 class="stats-number mb-0"><?= $stats['total_categories'] ?? 0 ?></h3>
                </div>
                <div class="stats-icon primary">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Active Categories</h6>
                    <h3 class="stats-number mb-0"><?= $stats['active_categories'] ?? 0 ?></h3>
                </div>
                <div class="stats-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total Products</h6>
                    <h3 class="stats-number mb-0"><?= $stats['products_in_categories'] ?? 0 ?></h3>
                </div>
                <div class="stats-icon info">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Categories Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($categories)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No categories found</h5>
                <p class="text-muted">Start by creating your first product category!</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openCreateModal()">
                    <i class="fas fa-plus me-2"></i>Add New Category
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><strong>#<?= $category['categoryID'] ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($category['image'])): ?>
                                            <img src="<?= Helper::upload($category['image']) ?>" 
                                                 alt="<?= htmlspecialchars($category['name'] ?? 'Unnamed Category') ?>" 
                                                 class="product-image me-2">
                                        <?php else: ?>
                                            <div class="product-image me-2 bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($category['name'] ?? 'Unnamed Category') ?></div>
                                            <?php if (!empty($category['description'])): ?>
                                                <small class="text-muted"><?= htmlspecialchars(substr($category['description'], 0, 50)) ?>...</small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $category['product_count'] ?? 0 ?> products</span>
                                </td>
                                <td>
                                    <span class="badge badge-status bg-<?= $category['status'] == 1 ? 'success' : 'secondary' ?>">
                                        <?= $category['status'] == 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($category['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" 
                                            onclick="editCategory(<?= $category['categoryID'] ?>)" 
                                            title="Edit Category">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-action" 
                                            onclick="deleteCategory(<?= $category['categoryID'] ?>)" 
                                            title="Delete Category"
                                            <?= ($category['product_count'] ?? 0) > 0 ? 'disabled' : '' ?>>
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

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Category Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Category Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</small>
                        <div id="imagePreview" class="mt-2" style="display: none;">
                            <img id="preview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Categories-specific JavaScript
$inline_scripts = '
// Category Management JavaScript
let editMode = false;
let editId = null;

function openCreateModal() {
    editMode = false;
    editId = null;
    document.getElementById("categoryModalLabel").textContent = "Add New Category";
    document.getElementById("categoryForm").reset();
    document.getElementById("imagePreview").style.display = "none";
    document.getElementById("categoryForm").action = window.OneStoreAdmin.adminUrl("/categories/store");
}

function editCategory(id) {
    editMode = true;
    editId = id;
    document.getElementById("categoryModalLabel").textContent = "Edit Category";
    
    // Fetch category data
    fetch(window.OneStoreAdmin.adminUrl(`/categories/get?id=${id}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const category = data.category;
                document.getElementById("name").value = category.name;
                document.getElementById("description").value = category.description || "";
                document.getElementById("status").value = category.status;
                
                // Show existing image if available
                if (category.image) {
                    document.getElementById("preview").src = window.OneStoreAdmin.baseUrl + "/uploads/" + category.image;
                    document.getElementById("imagePreview").style.display = "block";
                }
                
                document.getElementById("categoryForm").action = window.OneStoreAdmin.adminUrl(`/categories/update?id=${id}`);
                new bootstrap.Modal(document.getElementById("categoryModal")).show();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error loading category data");
        });
}

function deleteCategory(id) {
    if (confirm("Are you sure you want to delete this category?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = window.OneStoreAdmin.adminUrl(`/categories/delete?id=${id}`);
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
document.getElementById("categoryForm").addEventListener("submit", function(e) {
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
            bootstrap.Modal.getInstance(document.getElementById("categoryModal")).hide();
            location.reload();
        } else {
            alert(data.message || "An error occurred");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while saving the category");
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

console.log("Category management loaded successfully");
';

// Include the admin layout
include ROOT_PATH . '/app/Views/Admin/layouts/admin.php';
?> 