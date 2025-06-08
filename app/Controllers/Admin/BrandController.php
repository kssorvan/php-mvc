<?php
namespace App\Controllers\Admin;

use App\Models\Brand;
use App\Models\Product;
use Exception;

class BrandController extends AdminController {
    private $brandModel;
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->brandModel = new Brand();
        $this->productModel = new Product();
    }
    
    /**
     * Display brands list
     */
    public function index() {
        $this->setAdminTitle('Brand Management');
        $this->requirePermission('manage_brands');
        
        try {
            // Get filter parameters
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            
            // Build query conditions
            $conditions = [];
            $params = [];
            
            if (!empty($search)) {
                $conditions[] = "b.brandName LIKE :search";
                $params['search'] = '%' . $search . '%';
            }
            
            if ($status === 'active') {
                $conditions[] = "b.status = 1";
            } elseif ($status === 'inactive') {
                $conditions[] = "b.status = 0";
            }
            
            $whereClause = '';
            if (!empty($conditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            }
            
            // Get brands with product count
            $sql = "SELECT b.*, 
                           b.brandName as name,
                           COUNT(p.productID) as product_count,
                           COUNT(CASE WHEN p.status = 1 THEN 1 END) as active_product_count
                    FROM tbl_brand b 
                    LEFT JOIN tbl_product p ON b.brandID = p.brandID
                    {$whereClause}
                    GROUP BY b.brandID 
                    ORDER BY b.brandID ASC";
            
            $pdo = $this->connectDatabase();
            $stmt = $pdo->prepare($sql);
            
            // Bind filter parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            $brands = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get brand statistics
            $stats = $this->getBrandStatistics();
            
            $data = [
                'title' => 'Brand Management - OneStore Admin',
                'brands' => $brands,
                'stats' => $stats,
                'filters' => [
                    'search' => $search,
                    'status' => $status
                ],
                'admin_user' => $this->adminUser,
                'success' => $_SESSION['flash_success'] ?? null,
                'error' => $_SESSION['flash_error'] ?? null
            ];
            
            // Clear flash messages
            unset($_SESSION['flash_success'], $_SESSION['flash_error']);
            
            $this->adminView('brands/index', $data);
            
        } catch (Exception $e) {
            error_log("Brand listing error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error loading brands: ' . $e->getMessage();
            header('Location: /admin/dashboard');
            exit;
        }
    }
    
    /**
     * Create new brand
     */
    public function store() {
        $this->requirePermission('manage_brands');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $brandName = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = intval($_POST['status'] ?? 1);
        
        if (empty($brandName)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Brand name is required']);
            exit;
        }
        
        try {
            // Check if brand name already exists
            $pdo = $this->connectDatabase();
            $checkStmt = $pdo->prepare("SELECT brandID FROM tbl_brand WHERE brandName = ?");
            $checkStmt->execute([$brandName]);
            if ($checkStmt->fetch()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Brand name already exists']);
                exit;
            }
            
            // Handle logo upload
            $logoPath = null;
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleLogoUpload($_FILES['logo']);
                if ($uploadResult['success']) {
                    $logoPath = $uploadResult['filename'];
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Logo upload failed: ' . $uploadResult['error']]);
                    exit;
                }
            }
            
            $brandData = [
                'brandName' => $brandName,
                'description' => $description,
                'logo' => $logoPath,
                'status' => $status
            ];
            
            $brandID = $this->brandModel->createBrand($brandData);
            
            if ($brandID) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Brand created successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create brand']);
            }
            
        } catch (Exception $e) {
            error_log("Brand creation error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error creating brand: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Update brand
     */
    public function update() {
        $this->requirePermission('manage_brands');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $brandID = intval($_GET['id'] ?? 0);
        $brandName = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = intval($_POST['status'] ?? 1);
        
        if (!$brandID || empty($brandName)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Brand ID and name are required']);
            exit;
        }
        
        try {
            // Check if brand name already exists for another brand
            $pdo = $this->connectDatabase();
            $checkStmt = $pdo->prepare("SELECT brandID FROM tbl_brand WHERE brandName = ? AND brandID != ?");
            $checkStmt->execute([$brandName, $brandID]);
            if ($checkStmt->fetch()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Brand name already exists']);
                exit;
            }
            
            $updateData = [
                'brandName' => $brandName,
                'description' => $description,
                'status' => $status
            ];
            
            // Handle logo upload
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleLogoUpload($_FILES['logo']);
                if ($uploadResult['success']) {
                    // Delete old logo if exists
                    $existingBrand = $this->brandModel->find($brandID);
                    if ($existingBrand && $existingBrand['logo']) {
                        // Handle both old format (filename only) and new format (brands/filename)
                        $oldLogoName = $existingBrand['logo'];
                        if (strpos($oldLogoName, 'brands/') === 0) {
                            // New format: remove 'brands/' prefix to get just filename
                            $oldLogoName = str_replace('brands/', '', $oldLogoName);
                        }
                        $oldLogoPath = __DIR__ . '/../../../public/uploads/brands/' . $oldLogoName;
                        if (file_exists($oldLogoPath)) {
                            unlink($oldLogoPath);
                        }
                    }
                    $updateData['logo'] = $uploadResult['filename'];
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Logo upload failed: ' . $uploadResult['error']]);
                    exit;
                }
            }
            
            $success = $this->brandModel->updateBrand($brandID, $updateData);
            
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Brand updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update brand']);
            }
            
        } catch (Exception $e) {
            error_log("Brand update error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating brand: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Delete brand (soft delete)
     */
    public function delete() {
        $this->requirePermission('manage_brands');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/brands');
            exit;
        }
        
        $brandID = intval($_POST['brandID'] ?? 0);
        
        if (!$brandID) {
            $_SESSION['flash_error'] = 'Brand ID is required';
            header('Location: /admin/brands');
            exit;
        }
        
        try {
            // Check if brand has products
            $pdo = $this->connectDatabase();
            $productCheckStmt = $pdo->prepare("SELECT COUNT(*) as product_count FROM tbl_product WHERE brandID = ?");
            $productCheckStmt->execute([$brandID]);
            $productCount = $productCheckStmt->fetch(\PDO::FETCH_ASSOC)['product_count'];
            
            if ($productCount > 0) {
                $_SESSION['flash_error'] = 'Cannot delete brand that contains products. Please move or delete products first.';
                header('Location: /admin/brands');
                exit;
            }
            
            // Soft delete the brand
            $success = $this->brandModel->deleteBrand($brandID);
            
            if ($success) {
                $_SESSION['flash_success'] = 'Brand deleted successfully';
            } else {
                $_SESSION['flash_error'] = 'Failed to delete brand';
            }
            
        } catch (Exception $e) {
            error_log("Brand deletion error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error deleting brand: ' . $e->getMessage();
        }
        
        header('Location: /admin/brands');
        exit;
    }
    
    /**
     * Get brand statistics
     */
    private function getBrandStatistics() {
        try {
            $pdo = $this->connectDatabase();
            
            $sql = "SELECT 
                        COUNT(*) as total_brands,
                        COUNT(CASE WHEN status = 1 THEN 1 END) as active_brands,
                        COUNT(CASE WHEN status = 0 THEN 1 END) as inactive_brands
                    FROM tbl_brand";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $brandStats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Get product distribution stats
            $productDistSql = "SELECT 
                                  COUNT(CASE WHEN brandID IS NOT NULL THEN 1 END) as products_with_brands,
                                  COUNT(CASE WHEN brandID IS NULL THEN 1 END) as unbranded_products
                               FROM tbl_product 
                               WHERE status = 1";
            
            $productStmt = $pdo->prepare($productDistSql);
            $productStmt->execute();
            $productStats = $productStmt->fetch(\PDO::FETCH_ASSOC);
            
            return array_merge($brandStats, $productStats);
            
        } catch (Exception $e) {
            error_log("Brand statistics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Handle logo upload with correct subdirectory path
     */
    private function handleLogoUpload($file) {
        try {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            // Validate file type
            if (!in_array($file['type'], $allowedTypes)) {
                return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
            }
            
            // Validate file size
            if ($file['size'] > $maxSize) {
                return ['success' => false, 'error' => 'File size too large. Maximum size is 5MB.'];
            }
            
            // Create upload directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../../public/uploads/brands/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'brand_' . uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Return the full path including subdirectory so URLs work correctly
                return ['success' => true, 'filename' => 'brands/' . $filename];
            } else {
                return ['success' => false, 'error' => 'Failed to move uploaded file.'];
            }
            
        } catch (Exception $e) {
            error_log("Logo upload error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Upload error occurred.'];
        }
    }
    
    /**
     * AJAX endpoint to get brand data
     */
    public function get() {
        header('Content-Type: application/json');
        
        $brandID = intval($_GET['id'] ?? 0);
        
        if (!$brandID) {
            echo json_encode(['success' => false, 'message' => 'Brand ID required']);
            exit;
        }
        
        try {
            $brand = $this->brandModel->find($brandID);
            
            if ($brand) {
                // Add name alias for JavaScript compatibility
                $brand['name'] = $brand['brandName'];
                echo json_encode(['success' => true, 'brand' => $brand]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Brand not found']);
            }
            
        } catch (Exception $e) {
            error_log("Brand get error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading brand']);
        }
        
        exit;
    }
    
    protected function adminView($view, $data = []) {
        extract($data);
        require $this->getViewPath($view);
    }
    
    protected function getViewPath($view) {
        return __DIR__ . '/../../Views/Admin/' . $view . '.php';
    }
}
?> 