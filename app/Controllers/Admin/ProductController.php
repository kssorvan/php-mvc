<?php
namespace App\Controllers\Admin;

use Exception;

class ProductController extends AdminController {
    
    protected function getViewPath($view) {
        return __DIR__ . '/../../Views/Admin/' . $view . '.php';
    }
    
    public function index() {
        $this->setAdminTitle('Product Management');
        $this->requirePermission('manage_products');
        
        try {
            $pdo = $this->connectDatabase();
            
            if (!$pdo) {
                throw new Exception('Database connection failed');
            }
            
            // Get all products with category and brand information - sorted by ID
            $sql = "SELECT p.*, 
                           p.productName as name,
                           p.stock_quantity as stock,
                           c.catName as category_name, 
                           b.brandName as brand_name 
                    FROM tbl_product p 
                    LEFT JOIN tbl_category c ON p.categoryID = c.categoryID 
                    LEFT JOIN tbl_brand b ON p.brandID = b.brandID 
                    ORDER BY p.productID ASC";
            $stmt = $pdo->query($sql);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get categories for the modal
            $categoriesStmt = $pdo->query("SELECT categoryID, catName as name FROM tbl_category WHERE status = 1 ORDER BY catName");
            $categories = $categoriesStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get brands for the modal
            $brandsStmt = $pdo->query("SELECT brandID, brandName as name FROM tbl_brand WHERE status = 1 ORDER BY brandName");
            $brands = $brandsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $data = [
                'title' => 'Product Management - OneStore Admin',
                'products' => $products,
                'categories' => $categories,
                'brands' => $brands,
                'admin_user' => $this->adminUser,
                'success' => $_SESSION['flash_success'] ?? null,
                'error' => $_SESSION['flash_error'] ?? null
            ];
            
            // Clear flash messages
            unset($_SESSION['flash_success']);
            unset($_SESSION['flash_error']);
            
            $this->adminView('products/index', $data);
            
        } catch (\Exception $e) {
            error_log("Product listing error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error loading products: ' . $e->getMessage();
            header('Location: /admin/dashboard');
            exit;
        }
    }
    
    public function create() {
        $this->setAdminTitle('Add New Product');
        $this->requirePermission('manage_products');
        
        $data = [
            'title' => 'Add New Product - OneStore Admin',
            'admin_user' => $this->adminUser,
            'error' => $_SESSION['flash_error'] ?? null
        ];
        
        unset($_SESSION['flash_error']);
        
        $this->adminView('products/create', $data);
    }
    
    public function store() {
        $this->requirePermission('manage_products');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/products');
            exit;
        }
        
        $productName = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $shortDescription = trim($_POST['short_description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $salePrice = floatval($_POST['sale_price'] ?? 0);
        $categoryID = intval($_POST['category_id'] ?? 0);
        $brandID = intval($_POST['brand_id'] ?? 0);
        $sku = trim($_POST['sku'] ?? '');
        $stockQuantity = intval($_POST['stock'] ?? 0);
        $status = intval($_POST['status'] ?? 1);
        $featured = intval($_POST['featured'] ?? 0);
        
        if (empty($productName) || $price <= 0 || !$categoryID) {
            $_SESSION['flash_error'] = 'Product name, valid price, and category are required';
            header('Location: /admin/products');
            exit;
        }
        
        try {
            $pdo = $this->connectDatabase();
            
            if (!$pdo) {
                throw new Exception('Database connection failed');
            }
            
            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleImageUpload($_FILES['image']);
                if ($uploadResult['success']) {
                    $imagePath = $uploadResult['filename'];
                } else {
                    $_SESSION['flash_error'] = 'Image upload failed: ' . $uploadResult['error'];
                    header('Location: /admin/products');
                    exit;
                }
            }
            
            // Generate slug from product name
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $productName)));
            
            $stmt = $pdo->prepare("INSERT INTO tbl_product (categoryID, brandID, productName, slug, description, short_description, price, sale_price, sku, stock_quantity, image_path, status, featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$categoryID, $brandID ?: null, $productName, $slug, $description, $shortDescription, $price, $salePrice ?: null, $sku, $stockQuantity, $imagePath, $status, $featured]);
            
            $_SESSION['flash_success'] = 'Product created successfully';
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Product created successfully']);
            exit;
            
        } catch (\Exception $e) {
            error_log("Product creation error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error creating product: ' . $e->getMessage()]);
            exit;
        }
    }
    
    public function edit() {
        $this->setAdminTitle('Edit Product');
        $this->requirePermission('manage_products');
        
        $id = intval($_GET['id'] ?? 0);
        
        if (!$id) {
            $_SESSION['flash_error'] = 'Product not found';
            header('Location: /admin/products');
            exit;
        }
        
        try {
            $pdo = $this->connectDatabase();
            
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$product) {
                $_SESSION['flash_error'] = 'Product not found';
                header('Location: /admin/products');
                exit;
            }
            
            $data = [
                'title' => 'Edit Product - OneStore Admin',
                'product' => $product,
                'admin_user' => $this->adminUser,
                'error' => $_SESSION['flash_error'] ?? null
            ];
            
            unset($_SESSION['flash_error']);
            
            $this->adminView('products/edit', $data);
            
        } catch (\Exception $e) {
            error_log("Product edit error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error loading product';
            header('Location: /admin/products');
            exit;
        }
    }
    
    public function update() {
        $this->requirePermission('manage_products');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/products');
            exit;
        }
        
        // Get productID from URL parameter
        $productID = intval($_GET['id'] ?? 0);
        $productName = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $shortDescription = trim($_POST['short_description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $salePrice = floatval($_POST['sale_price'] ?? 0);
        $categoryID = intval($_POST['category_id'] ?? 0);
        $brandID = intval($_POST['brand_id'] ?? 0);
        $sku = trim($_POST['sku'] ?? '');
        $stockQuantity = intval($_POST['stock'] ?? 0);
        $status = intval($_POST['status'] ?? 1);
        $featured = intval($_POST['featured'] ?? 0);
        
        if (!$productID || empty($productName) || $price <= 0 || !$categoryID) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid product data']);
            exit;
        }
        
        try {
            $pdo = $this->connectDatabase();
            
            // Get current product data
            $stmt = $pdo->prepare("SELECT image_path FROM tbl_product WHERE productID = ?");
            $stmt->execute([$productID]);
            $currentProduct = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $imagePath = $currentProduct['image_path'] ?? null;
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // Delete old image if it exists
                if ($imagePath && file_exists(__DIR__ . "/../../../public/uploads/$imagePath")) {
                    unlink(__DIR__ . "/../../../public/uploads/$imagePath");
                }
                
                $uploadResult = $this->handleImageUpload($_FILES['image']);
                if ($uploadResult['success']) {
                    $imagePath = $uploadResult['filename'];
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Image upload failed: ' . $uploadResult['error']]);
                    exit;
                }
            }
            
            // Generate slug from product name
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $productName)));
            
            $stmt = $pdo->prepare("UPDATE tbl_product SET categoryID = ?, brandID = ?, productName = ?, slug = ?, description = ?, short_description = ?, price = ?, sale_price = ?, sku = ?, stock_quantity = ?, image_path = ?, status = ?, featured = ?, updated_at = NOW() WHERE productID = ?");
            $stmt->execute([$categoryID, $brandID ?: null, $productName, $slug, $description, $shortDescription, $price, $salePrice ?: null, $sku, $stockQuantity, $imagePath, $status, $featured, $productID]);
            
            $_SESSION['flash_success'] = 'Product updated successfully';
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
            exit;
            
        } catch (\Exception $e) {
            error_log("Product update error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()]);
            exit;
        }
    }
    
    public function delete() {
        $this->requirePermission('manage_products');
        
        $productID = intval($_GET['id'] ?? 0);
        
        if (!$productID) {
            $_SESSION['flash_error'] = 'Product not found';
            header('Location: /admin/products');
            exit;
        }
        
        try {
            $pdo = $this->connectDatabase();
            
            $stmt = $pdo->prepare("DELETE FROM tbl_product WHERE productID = ?");
            $stmt->execute([$productID]);
            
            $_SESSION['flash_success'] = 'Product deleted successfully';
            header('Location: /admin/products');
            exit;
            
        } catch (\Exception $e) {
            error_log("Product deletion error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error deleting product: ' . $e->getMessage();
            header('Location: /admin/products');
            exit;
        }
    }
    
    public function get() {
        $this->requirePermission('manage_products');
        
        $productID = intval($_GET['id'] ?? 0);
        
        if (!$productID) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            exit;
        }
        
        try {
            $pdo = $this->connectDatabase();
            
            $stmt = $pdo->prepare("SELECT *, 
                                          productName as name, 
                                          stock_quantity as stock 
                                   FROM tbl_product 
                                   WHERE productID = ?");
            $stmt->execute([$productID]);
            $product = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$product) {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found']);
                exit;
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'product' => $product]);
            exit;
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
            exit;
        }
    }
    
    /**
     * Handle image upload
     */
    private function handleImageUpload($file) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Log upload attempt
        error_log("Upload attempt - File: " . print_r($file, true));
        
        // Check if file was uploaded
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = "File upload error code: " . $file['error'];
            error_log($errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errorMsg = "File size too large: {$file['size']} bytes (max 5MB)";
            error_log($errorMsg);
            return ['success' => false, 'error' => 'File size too large (max 5MB)'];
        }
        
        // Check file type
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedTypes)) {
            $errorMsg = "Invalid file type: $fileExtension";
            error_log($errorMsg);
            return ['success' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)];
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../../public/uploads/products/';
        if (!is_dir($uploadDir)) {
            error_log("Creating upload directory: $uploadDir");
            mkdir($uploadDir, 0755, true);
        }
        
        // Check directory permissions
        if (!is_writable($uploadDir)) {
            $errorMsg = "Upload directory not writable: $uploadDir";
            error_log($errorMsg);
            return ['success' => false, 'error' => 'Upload directory not writable'];
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $filename;
        
        error_log("Attempting to move file from {$file['tmp_name']} to $uploadPath");
        
        // Check if tmp file exists
        if (!file_exists($file['tmp_name'])) {
            $errorMsg = "Temporary file does not exist: {$file['tmp_name']}";
            error_log($errorMsg);
            return ['success' => false, 'error' => 'Temporary file not found'];
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            error_log("File upload success: $uploadPath");
            // Verify file was actually created and has content
            if (file_exists($uploadPath) && filesize($uploadPath) > 0) {
                error_log("File verified: " . filesize($uploadPath) . " bytes");
                // Return the full path including subdirectory so URLs work correctly
                return ['success' => true, 'filename' => 'products/' . $filename];
            } else {
                $errorMsg = "File moved but verification failed: $uploadPath";
                error_log($errorMsg);
                return ['success' => false, 'error' => 'File upload verification failed'];
            }
        } else {
            $errorMsg = "Failed to move uploaded file from {$file['tmp_name']} to $uploadPath";
            error_log($errorMsg);
            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }
    }
    
    /**
     * Render admin view
     */
    protected function adminView($view, $data = []) {
        extract($data);
        
        $viewPath = $this->getViewPath($view);
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "Admin view not found: $view";
        }
    }
} 