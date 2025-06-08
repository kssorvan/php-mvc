<?php
namespace App\Controllers\Admin;

use App\Models\Category;
use App\Models\Product;
use Exception;

class CategoryController extends AdminController {
    private $categoryModel;
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->categoryModel = new Category();
        $this->productModel = new Product();
    }
    
    /**
     * Display categories list
     */
    public function index() {
        $this->setAdminTitle('Category Management');
        $this->requirePermission('manage_categories');
        
        try {
            // Get filter parameters
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            
            // Build query conditions
            $conditions = [];
            $params = [];
            
            if (!empty($search)) {
                $conditions[] = "c.catName LIKE :search";
                $params['search'] = '%' . $search . '%';
            }
            
            if ($status === 'active') {
                $conditions[] = "c.status = 1";
            } elseif ($status === 'inactive') {
                $conditions[] = "c.status = 0";
            }
            
            $whereClause = '';
            if (!empty($conditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            }
            
            // Get categories with product count
            $sql = "SELECT c.*, 
                           c.catName as name,
                           COUNT(p.productID) as product_count,
                           COUNT(CASE WHEN p.status = 1 THEN 1 END) as active_product_count
                    FROM tbl_category c 
                    LEFT JOIN tbl_product p ON c.categoryID = p.categoryID
                    {$whereClause}
                    GROUP BY c.categoryID 
                    ORDER BY c.categoryID ASC";
            
            $pdo = $this->connectDatabase();
            $stmt = $pdo->prepare($sql);
            
            // Bind filter parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            
            $stmt->execute();
            $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get category statistics
            $stats = $this->getCategoryStatistics();
            
            $data = [
                'title' => 'Category Management - OneStore Admin',
                'categories' => $categories,
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
            
            $this->adminView('categories/index', $data);
            
        } catch (Exception $e) {
            error_log("Category listing error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error loading categories: ' . $e->getMessage();
            header('Location: /admin/dashboard');
            exit;
        }
    }
    
    /**
     * Create new category
     */
    public function store() {
        $this->requirePermission('manage_categories');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $catName = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = intval($_POST['status'] ?? 1);
        
        if (empty($catName)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Category name is required']);
            exit;
        }
        
        try {
            // Check if category name already exists
            $pdo = $this->connectDatabase();
            $checkStmt = $pdo->prepare("SELECT categoryID FROM tbl_category WHERE catName = ?");
            $checkStmt->execute([$catName]);
            if ($checkStmt->fetch()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Category name already exists']);
                exit;
            }
            
            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleImageUpload($_FILES['image']);
                if ($uploadResult['success']) {
                    $imagePath = $uploadResult['filename'];
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Image upload failed: ' . $uploadResult['error']]);
                    exit;
                }
            }
            
            $categoryData = [
                'catName' => $catName,
                'description' => $description,
                'image' => $imagePath,
                'status' => $status
            ];
            
            $categoryID = $this->categoryModel->createCategory($categoryData);
            
            if ($categoryID) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Category created successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to create category']);
            }
            
        } catch (Exception $e) {
            error_log("Category creation error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error creating category: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Update category
     */
    public function update() {
        $this->requirePermission('manage_categories');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $categoryID = intval($_GET['id'] ?? 0);
        $catName = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = intval($_POST['status'] ?? 1);
        
        if (!$categoryID || empty($catName)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Category ID and name are required']);
            exit;
        }
        
        try {
            // Check if category name already exists for another category
            $pdo = $this->connectDatabase();
            $checkStmt = $pdo->prepare("SELECT categoryID FROM tbl_category WHERE catName = ? AND categoryID != ?");
            $checkStmt->execute([$catName, $categoryID]);
            if ($checkStmt->fetch()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Category name already exists']);
                exit;
            }
            
            $updateData = [
                'catName' => $catName,
                'description' => $description,
                'status' => $status
            ];
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleImageUpload($_FILES['image']);
                if ($uploadResult['success']) {
                    // Delete old image if exists
                    $existingCategory = $this->categoryModel->find($categoryID);
                    if ($existingCategory && $existingCategory['image']) {
                        // Handle both old format (filename only) and new format (categories/filename)
                        $oldImageName = $existingCategory['image'];
                        if (strpos($oldImageName, 'categories/') === 0) {
                            // New format: remove 'categories/' prefix to get just filename
                            $oldImageName = str_replace('categories/', '', $oldImageName);
                        }
                        $oldImagePath = __DIR__ . '/../../../public/uploads/categories/' . $oldImageName;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    $updateData['image'] = $uploadResult['filename'];
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Image upload failed: ' . $uploadResult['error']]);
                    exit;
                }
            }
            
            $success = $this->categoryModel->updateCategory($categoryID, $updateData);
            
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Category updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update category']);
            }
            
        } catch (Exception $e) {
            error_log("Category update error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating category: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Delete category (soft delete)
     */
    public function delete() {
        $this->requirePermission('manage_categories');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/categories');
            exit;
        }
        
        $categoryID = intval($_POST['categoryID'] ?? 0);
        
        if (!$categoryID) {
            $_SESSION['flash_error'] = 'Category ID is required';
            header('Location: /admin/categories');
            exit;
        }
        
        try {
            // Check if category has products
            $pdo = $this->connectDatabase();
            $productCheckStmt = $pdo->prepare("SELECT COUNT(*) as product_count FROM tbl_product WHERE categoryID = ?");
            $productCheckStmt->execute([$categoryID]);
            $productCount = $productCheckStmt->fetch(\PDO::FETCH_ASSOC)['product_count'];
            
            if ($productCount > 0) {
                $_SESSION['flash_error'] = 'Cannot delete category that contains products. Please move or delete products first.';
                header('Location: /admin/categories');
                exit;
            }
            
            // Soft delete the category
            $success = $this->categoryModel->deleteCategory($categoryID);
            
            if ($success) {
                $_SESSION['flash_success'] = 'Category deleted successfully';
            } else {
                $_SESSION['flash_error'] = 'Failed to delete category';
            }
            
        } catch (Exception $e) {
            error_log("Category deletion error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error deleting category: ' . $e->getMessage();
        }
        
        header('Location: /admin/categories');
        exit;
    }
    
    /**
     * Get category statistics
     */
    private function getCategoryStatistics() {
        try {
            $pdo = $this->connectDatabase();
            
            $sql = "SELECT 
                        COUNT(*) as total_categories,
                        COUNT(CASE WHEN status = 1 THEN 1 END) as active_categories,
                        COUNT(CASE WHEN status = 0 THEN 1 END) as inactive_categories
                    FROM tbl_category";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $categoryStats = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Get product distribution stats
            $productDistSql = "SELECT 
                                  COUNT(CASE WHEN categoryID IS NOT NULL THEN 1 END) as products_in_categories,
                                  COUNT(CASE WHEN categoryID IS NULL THEN 1 END) as uncategorized_products
                               FROM tbl_product 
                               WHERE status = 1";
            
            $productStmt = $pdo->prepare($productDistSql);
            $productStmt->execute();
            $productStats = $productStmt->fetch(\PDO::FETCH_ASSOC);
            
            return array_merge($categoryStats, $productStats);
            
        } catch (Exception $e) {
            error_log("Category statistics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload($file) {
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
            $uploadDir = __DIR__ . '/../../../public/uploads/categories/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'category_' . uniqid() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Return the full path including subdirectory so URLs work correctly
                return ['success' => true, 'filename' => 'categories/' . $filename];
            } else {
                return ['success' => false, 'error' => 'Failed to move uploaded file.'];
            }
            
        } catch (Exception $e) {
            error_log("Image upload error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Upload error occurred.'];
        }
    }
    
    /**
     * AJAX endpoint to get category data
     */
    public function get() {
        header('Content-Type: application/json');
        
        $categoryID = intval($_GET['id'] ?? 0);
        
        if (!$categoryID) {
            echo json_encode(['success' => false, 'message' => 'Category ID required']);
            exit;
        }
        
        try {
            $category = $this->categoryModel->find($categoryID);
            
            if ($category) {
                // Add name alias for JavaScript compatibility
                $category['name'] = $category['catName'];
                echo json_encode(['success' => true, 'category' => $category]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Category not found']);
            }
            
        } catch (Exception $e) {
            error_log("Category get error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading category']);
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