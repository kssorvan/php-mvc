<?php
use App\Helpers\Helper;

$page_title = 'Brand Management';
$content = ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tags me-2"></i>Brand Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#brandModal" onclick="openCreateModal()">
            <i class="fas fa-plus me-1"></i>Add New Brand
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title text-muted">Total Brands</h5>
                        <h2 class="text-primary"><?= $stats['total_brands'] ?? 0 ?></h2>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title text-muted">Active Brands</h5>
                        <h2 class="text-success"><?= $stats['active_brands'] ?? 0 ?></h2>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title text-muted">Branded Products</h5>
                        <h2 class="text-info"><?= $stats['products_with_brands'] ?? 0 ?></h2>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title text-muted">Unbranded Products</h5>
                        <h2 class="text-warning"><?= $stats['unbranded_products'] ?? 0 ?></h2>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Brands List -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Brands List</h5>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="Search brands..." 
                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="fas fa-search me-1"></i>Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="/admin/brands" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>Clear
                </a>
            </div>
        </form>

        <?php if (empty($brands)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No brands found</h5>
                <p class="text-muted">Start by adding your first brand to organize your products.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#brandModal" onclick="openCreateModal()">
                    <i class="fas fa-plus me-1"></i>Add First Brand
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
                        <?php foreach ($brands as $brand): ?>
                            <tr>
                                <td><strong>#<?= $brand['brandID'] ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($brand['logo'])): ?>
                                            <img src="<?= Helper::upload($brand['logo']) ?>" 
                                                 alt="<?= htmlspecialchars($brand['name'] ?? 'Unnamed Brand') ?>" 
                                                 class="product-image me-2">
                                        <?php else: ?>
                                            <div class="product-image me-2 bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= htmlspecialchars($brand['name'] ?? 'Unnamed Brand') ?></strong>
                                            <?php if (!empty($brand['description'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars(substr($brand['description'], 0, 50)) ?><?= strlen($brand['description']) > 50 ? '...' : '' ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $brand['product_count'] ?> Products</span>
                                    <?php if ($brand['active_product_count'] != $brand['product_count']): ?>
                                        <br><small class="text-muted"><?= $brand['active_product_count'] ?> active</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($brand['status'] == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M j, Y', strtotime($brand['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" onclick="editBrand(<?= $brand['brandID'] ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="deleteBrand(<?= $brand['brandID'] ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Brand Modal -->
<div class="modal fade" id="brandModal" tabindex="-1" aria-labelledby="brandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="brandModalLabel">Add New Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="brandForm" method="POST" enctype="multipart/form-data" action="<?= Helper::adminUrl('brands/store') ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Brand Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief brand description..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="logo" class="form-label">Brand Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                        <small class="form-text text-muted">Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</small>
                        <div id="logoPreview" class="mt-2" style="display: none;">
                            <img id="preview" src="" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Brands-specific JavaScript
$inline_scripts = '
// Brand Management JavaScript
let editMode = false;
let editId = null;

function openCreateModal() {
    editMode = false;
    editId = null;
    document.getElementById("brandModalLabel").textContent = "Add New Brand";
    document.getElementById("brandForm").reset();
    document.getElementById("logoPreview").style.display = "none";
    document.getElementById("brandForm").action = window.OneStoreAdmin.adminUrl("/brands/store");
}

function editBrand(id) {
    editMode = true;
    editId = id;
    document.getElementById("brandModalLabel").textContent = "Edit Brand";
    
    // Fetch brand data
    fetch(window.OneStoreAdmin.adminUrl(`/brands/get?id=${id}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const brand = data.brand;
                document.getElementById("name").value = brand.name;
                document.getElementById("description").value = brand.description || "";
                document.getElementById("status").value = brand.status;
                
                // Show existing logo if available
                if (brand.logo) {
                    document.getElementById("preview").src = window.OneStoreAdmin.baseUrl + "/uploads/" + brand.logo;
                    document.getElementById("logoPreview").style.display = "block";
                }
                
                document.getElementById("brandForm").action = window.OneStoreAdmin.adminUrl(`/brands/update?id=${id}`);
                new bootstrap.Modal(document.getElementById("brandModal")).show();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error loading brand data");
        });
}

function deleteBrand(id) {
    if (confirm("Are you sure you want to delete this brand?")) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = window.OneStoreAdmin.adminUrl(`/brands/delete?id=${id}`);
        
        // Add brandID as hidden field
        const brandIDInput = document.createElement("input");
        brandIDInput.type = "hidden";
        brandIDInput.name = "brandID";
        brandIDInput.value = id;
        form.appendChild(brandIDInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Logo preview functionality
document.getElementById("logo").addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("preview").src = e.target.result;
            document.getElementById("logoPreview").style.display = "block";
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById("logoPreview").style.display = "none";
    }
});

// Form submission
document.getElementById("brandForm").addEventListener("submit", function(e) {
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
            bootstrap.Modal.getInstance(document.getElementById("brandModal")).hide();
            location.reload();
        } else {
            alert(data.message || "An error occurred");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while saving the brand");
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

console.log("Brand management loaded successfully");
';

// Include the admin layout
include ROOT_PATH . '/app/Views/Admin/layouts/admin.php';
?> 