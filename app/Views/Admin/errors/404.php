<?php
use App\Helpers\Helper;

$page_title = '404 - Admin Page Not Found | OneStore Admin';
$content = ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card">
            <div class="card-body text-center p-5">
                <!-- Error Icon -->
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle" style="font-size: 80px; color: #fd7e14;"></i>
                </div>
                
                <!-- Error Title -->
                <h1 class="display-4 fw-bold text-danger mb-3">404</h1>
                <h2 class="h4 mb-3">Admin Page Not Found</h2>
                
                <!-- Error Message -->
                <p class="text-muted mb-4">
                    The admin page you are looking for could not be found. 
                    It might have been moved, deleted, or you don't have permission to access it.
                </p>
                
                <!-- Action Buttons -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                    <a href="<?= Helper::adminUrl('dashboard') ?>" class="btn btn-primary me-md-2">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a href="<?= Helper::adminUrl('products') ?>" class="btn btn-outline-primary">
                        <i class="fas fa-box me-2"></i>Products
                    </a>
                </div>
                
                <!-- Additional Links -->
                <div class="mt-4">
                    <p class="small text-muted">
                        <a href="<?= Helper::url('') ?>" target="_blank" class="text-decoration-none">
                            <i class="fas fa-external-link-alt me-1"></i>View Site
                        </a>
                        |
                        <a href="<?= Helper::adminUrl('logout') ?>" class="text-decoration-none">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Include the admin layout
include ROOT_PATH . '/app/Views/Admin/layouts/admin.php';
?> 