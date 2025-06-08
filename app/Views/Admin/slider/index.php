<?php
use App\Helpers\Helper;

$page_title = 'Slider Management - OneStore Admin';
$content = ob_start();
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">
            <i class="fas fa-images text-primary me-2"></i>Slider Management
        </h2>
        <p class="text-muted mb-0">Manage homepage slider images and content</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sliderModal" onclick="openCreateModal()">
        <i class="fas fa-plus me-2"></i>Add New Slide
    </button>
</div>

<!-- Statistics Cards -->
<?php if (!empty($stats)): ?>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Total Slides</h6>
                    <h3 class="stats-number mb-0"><?= $stats['total_slides'] ?? 0 ?></h3>
                </div>
                <div class="stats-icon primary">
                    <i class="fas fa-images"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-muted mb-1">Active Slides</h6>
                    <h3 class="stats-number mb-0"><?= $stats['active_slides'] ?? 0 ?></h3>
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
                    <h6 class="text-muted mb-1">Display Limit</h6>
                    <h3 class="stats-number mb-0">3 max</h3>
                </div>
                <div class="stats-icon info">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Sliders Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($sliders)): ?>
            <div class="text-center py-5">
                <i class="fas fa-images fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No slides found</h5>
                <p class="text-muted">Start by adding your first slider image!</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sliderModal" onclick="openCreateModal()">
                    <i class="fas fa-plus me-2"></i>Add New Slide
                </button>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Display Limit:</strong> Only the first 3 active slides will be displayed on the homepage, ordered by creation date.
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Display Order</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sliders as $index => $slider): ?>
                            <tr>
                                <td><strong>#<?= $slider['sliderID'] ?></strong></td>
                                <td>
                                    <div class="position-relative">
                                        <img src="<?= Helper::upload($slider['image']) ?>" 
                                             alt="<?= htmlspecialchars($slider['title']) ?>" 
                                             style="width: 100px; height: 60px; object-fit: cover; border-radius: 8px;">
                                        <?php if ($slider['status'] == 1 && $index < 3): ?>
                                            <span class="position-absolute top-0 start-0 badge bg-success">Live</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($slider['title']) ?></div>
                                        <?php if (!empty($slider['subtitle'])): ?>
                                            <small class="text-muted"><?= htmlspecialchars($slider['subtitle']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-status bg-<?= $slider['status'] == 1 ? 'success' : 'secondary' ?>">
                                        <?= $slider['status'] == 1 ? 'Active' : 'Inactive' ?>
                                    </span>
                                    <?php if ($slider['status'] == 1 && $index >= 3): ?>
                                        <br><small class="text-warning">Hidden (over limit)</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($slider['status'] == 1): ?>
                                        <span class="badge bg-info"><?= $index + 1 ?></span>
                                        <?php if ($index < 3): ?>
                                            <small class="text-success d-block">Visible</small>
                                        <?php else: ?>
                                            <small class="text-muted d-block">Queued</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($slider['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-action" 
                                            onclick="editSlider(<?= $slider['sliderID'] ?>)" 
                                            title="Edit Slider">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger btn-action" 
                                            onclick="deleteSlider(<?= $slider['sliderID'] ?>)" 
                                            title="Delete Slider">
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

<!-- Slider Modal -->
<div class="modal fade" id="sliderModal" tabindex="-1" aria-labelledby="sliderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sliderModalLabel">Add New Slide</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sliderForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="title" class="form-label">Slide Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="col-12">
                            <label for="subtitle" class="form-label">Subtitle</label>
                            <input type="text" class="form-control" id="subtitle" name="subtitle">
                        </div>
                        <div class="col-md-6">
                            <label for="button_text" class="form-label">Button Text</label>
                            <input type="text" class="form-control" id="button_text" name="button_text" placeholder="e.g., Shop Now">
                        </div>
                        <div class="col-md-6">
                            <label for="link_url" class="form-label">Button Link</label>
                            <input type="text" class="form-control" id="link_url" name="link_url" placeholder="/shop, /products, or https://...">
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="position" class="form-label">Position</label>
                            <input type="number" class="form-control" id="position" name="position" value="0" min="0">
                            <small class="form-text text-muted">Lower numbers appear first</small>
                        </div>
                        <div class="col-12">
                            <label for="slider_image" class="form-label">Slider Image *</label>
                            <input type="file" class="form-control" id="slider_image" name="slider_image" accept="image/*" required>
                            <small class="form-text text-muted">Recommended size: 1920x800px. Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</small>
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <img id="preview" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Slide</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Slider-specific JavaScript
$inline_scripts = '
// Slider Management JavaScript
let editMode = false;
let editId = null;

function openCreateModal() {
    editMode = false;
    editId = null;
    document.getElementById("sliderModalLabel").textContent = "Add New Slide";
    document.getElementById("sliderForm").reset();
    document.getElementById("imagePreview").style.display = "none";
    document.getElementById("sliderForm").action = window.OneStoreAdmin.adminUrl("/slider/store");
    document.getElementById("slider_image").required = true;
}

function editSlider(id) {
    editMode = true;
    editId = id;
    document.getElementById("sliderModalLabel").textContent = "Edit Slide";
    
    // Fetch slider data
    fetch(window.OneStoreAdmin.adminUrl(`/slider/get?id=${id}`))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const slider = data.slider;
                document.getElementById("title").value = slider.title;
                document.getElementById("subtitle").value = slider.subtitle || "";
                document.getElementById("button_text").value = slider.button_text || "";
                document.getElementById("link_url").value = slider.link_url || "";
                document.getElementById("status").value = slider.status;
                document.getElementById("position").value = slider.position || 0;
                
                // Show existing image if available
                if (slider.image) {
                    document.getElementById("preview").src = window.OneStoreAdmin.baseUrl + "/uploads/" + slider.image;
                    document.getElementById("imagePreview").style.display = "block";
                }
                
                document.getElementById("sliderForm").action = window.OneStoreAdmin.adminUrl(`/slider/update?id=${id}`);
                document.getElementById("slider_image").required = false; // Don\'t require new image for edit
                new bootstrap.Modal(document.getElementById("sliderModal")).show();
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error loading slider data");
        });
}

function deleteSlider(id) {
    if (confirm("Are you sure you want to delete this slide?")) {
        fetch(window.OneStoreAdmin.adminUrl(`/slider/delete?id=${id}`), {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `sliderID=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || "Slider deleted successfully");
                location.reload();
            } else {
                alert(data.message || "An error occurred while deleting the slider");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred while deleting the slider");
        });
    }
}

// Image preview functionality
document.getElementById("slider_image").addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("preview").src = e.target.result;
            document.getElementById("imagePreview").style.display = "block";
        };
        reader.readAsDataURL(file);
    } else if (!editMode) {
        document.getElementById("imagePreview").style.display = "none";
    }
});

// Form submission
document.getElementById("sliderForm").addEventListener("submit", function(e) {
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
            bootstrap.Modal.getInstance(document.getElementById("sliderModal")).hide();
            location.reload();
        } else {
            alert(data.message || "An error occurred");
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("An error occurred while saving the slide");
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

console.log("Slider management loaded successfully");
';

// Include the admin layout
include ROOT_PATH . '/app/Views/Admin/layouts/admin.php';
?> 