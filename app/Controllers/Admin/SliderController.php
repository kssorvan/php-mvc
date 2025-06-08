<?php
namespace App\Controllers\Admin;

use Exception;

class SliderController extends AdminController {
    
    protected function getViewPath($view) {
        return __DIR__ . '/../../Views/Admin/' . $view . '.php';
    }
    
    public function index() {
        $this->setAdminTitle('Slider Management');
        $this->requirePermission('manage_products');
        
        try {
            $pdo = $this->connectDatabase();
            
            if (!$pdo) {
                throw new Exception('Database connection failed');
            }
            
            // Get all sliders sorted by position
            $sql = "SELECT * FROM tbl_slider ORDER BY position ASC, sliderID ASC";
            $stmt = $pdo->query($sql);
            $sliders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $data = [
                'title' => 'Slider Management - OneStore Admin',
                'sliders' => $sliders,
                'admin_user' => $this->adminUser,
                'success' => $_SESSION['flash_success'] ?? null,
                'error' => $_SESSION['flash_error'] ?? null
            ];
            
            // Clear flash messages
            unset($_SESSION['flash_success']);
            unset($_SESSION['flash_error']);
            
            $this->adminView('slider/index', $data);
            
        } catch (\Exception $e) {
            error_log("Slider listing error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error loading sliders: ' . $e->getMessage();
            header('Location: /admin/dashboard');
            exit;
        }
    }
    
    public function store() {
        $this->requirePermission('manage_products');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $linkUrl = trim($_POST['link_url'] ?? '');
        $buttonText = trim($_POST['button_text'] ?? '');
        $position = intval($_POST['position'] ?? 1);
        $status = intval($_POST['status'] ?? 1);
        
        if (empty($title)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Slider title is required']);
            exit;
        }
        
        // Validate image upload - required for new sliders
        if (!isset($_FILES['slider_image']) || $_FILES['slider_image']['error'] !== UPLOAD_ERR_OK) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Slider image is required']);
            exit;
        }
        
        try {
            $pdo = $this->connectDatabase();
            
            if (!$pdo) {
                throw new Exception('Database connection failed');
            }
            
            // Handle image upload
            $uploadResult = $this->handleImageUpload($_FILES['slider_image']);
            if (!$uploadResult['success']) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Image upload failed: ' . $uploadResult['error']]);
                exit;
            }
            
            $imagePath = $uploadResult['filename'];
            
            $stmt = $pdo->prepare("INSERT INTO tbl_slider (title, subtitle, description, image, link_url, button_text, position, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subtitle, $description, $imagePath, $linkUrl, $buttonText, $position, $status]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Slider created successfully']);
            exit;
            
        } catch (\Exception $e) {
            error_log("Slider creation error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error creating slider: ' . $e->getMessage()]);
            exit;
        }
    }
    
    public function update() {
        $this->requirePermission('manage_products');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $sliderID = intval($_GET['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $linkUrl = trim($_POST['link_url'] ?? '');
        $buttonText = trim($_POST['button_text'] ?? '');
        $position = intval($_POST['position'] ?? 1);
        $status = intval($_POST['status'] ?? 1);
        
        if (!$sliderID || empty($title)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Slider ID and title are required']);
            exit;
        }
        
        try {
            $pdo = $this->connectDatabase();
            
            // Get current slider data
            $stmt = $pdo->prepare("SELECT image FROM tbl_slider WHERE sliderID = ?");
            $stmt->execute([$sliderID]);
            $currentSlider = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $imagePath = $currentSlider['image'] ?? null;
            
            // Handle image upload
            if (isset($_FILES['slider_image']) && $_FILES['slider_image']['error'] === UPLOAD_ERR_OK) {
                // Delete old image if it exists
                if ($imagePath) {
                    // Handle both old format (filename only) and new format (slider/filename)
                    $oldImageName = $imagePath;
                    if (strpos($oldImageName, 'slider/') === 0) {
                        // New format: remove 'slider/' prefix to get just filename
                        $oldImageName = str_replace('slider/', '', $oldImageName);
                    }
                    $oldImagePath = __DIR__ . '/../../../public/uploads/slider/' . $oldImageName;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                
                $uploadResult = $this->handleImageUpload($_FILES['slider_image']);
                if ($uploadResult['success']) {
                    $imagePath = $uploadResult['filename'];
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Image upload failed: ' . $uploadResult['error']]);
                    exit;
                }
            }
            
            $stmt = $pdo->prepare("UPDATE tbl_slider SET title = ?, subtitle = ?, description = ?, image = ?, link_url = ?, button_text = ?, position = ?, status = ? WHERE sliderID = ?");
            $stmt->execute([$title, $subtitle, $description, $imagePath, $linkUrl, $buttonText, $position, $status, $sliderID]);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Slider updated successfully']);
            exit;
            
        } catch (\Exception $e) {
            error_log("Slider update error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating slider: ' . $e->getMessage()]);
            exit;
        }
    }
    
    public function delete() {
        $this->requirePermission('manage_products');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $sliderID = intval($_GET['id'] ?? $_POST['sliderID'] ?? 0);
        
        if (!$sliderID) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Slider ID required']);
            exit;
        }
        
        try {
            $pdo = $this->connectDatabase();
            
            // Get slider image to delete file
            $stmt = $pdo->prepare("SELECT image FROM tbl_slider WHERE sliderID = ?");
            $stmt->execute([$sliderID]);
            $slider = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$slider) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Slider not found']);
                exit;
            }
            
            // Delete image file if exists
            if ($slider['image']) {
                // Handle both old format (filename only) and new format (slider/filename)
                $imageName = $slider['image'];
                if (strpos($imageName, 'slider/') === 0) {
                    // New format: remove 'slider/' prefix to get just filename
                    $imageName = str_replace('slider/', '', $imageName);
                }
                $imagePath = __DIR__ . '/../../../public/uploads/slider/' . $imageName;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Delete slider from database
            $stmt = $pdo->prepare("DELETE FROM tbl_slider WHERE sliderID = ?");
            $success = $stmt->execute([$sliderID]);
            
            if ($success && $stmt->rowCount() > 0) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Slider deleted successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to delete slider or slider not found']);
            }
            exit;
            
        } catch (\Exception $e) {
            error_log("Slider deletion error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error deleting slider: ' . $e->getMessage()]);
            exit;
        }
    }
    
    public function get() {
        $this->requirePermission('manage_products');
        
        $sliderID = intval($_GET['id'] ?? 0);
        
        if (!$sliderID) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Slider ID required']);
            exit;
        }
        
        try {
            $pdo = $this->connectDatabase();
            
            $stmt = $pdo->prepare("SELECT * FROM tbl_slider WHERE sliderID = ?");
            $stmt->execute([$sliderID]);
            $slider = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$slider) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Slider not found']);
                exit;
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'slider' => $slider]);
            exit;
            
        } catch (\Exception $e) {
            error_log("Slider get error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error loading slider']);
            exit;
        }
    }
    
    /**
     * Handle image upload for sliders
     */
    private function handleImageUpload($file) {
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Log upload attempt
        error_log("Slider upload attempt - File: " . print_r($file, true));
        
        // Check if file was uploaded
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = "Slider file upload error code: " . $file['error'];
            error_log($errorMsg);
            return ['success' => false, 'error' => $errorMsg];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errorMsg = "Slider file size too large: {$file['size']} bytes (max 5MB)";
            error_log($errorMsg);
            return ['success' => false, 'error' => 'File size too large (max 5MB)'];
        }
        
        // Check file type
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedTypes)) {
            $errorMsg = "Slider invalid file type: $fileExtension";
            error_log($errorMsg);
            return ['success' => false, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)];
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../../public/uploads/slider/';
        if (!is_dir($uploadDir)) {
            error_log("Creating slider upload directory: $uploadDir");
            mkdir($uploadDir, 0755, true);
        }
        
        // Check directory permissions
        if (!is_writable($uploadDir)) {
            $errorMsg = "Slider upload directory not writable: $uploadDir";
            error_log($errorMsg);
            return ['success' => false, 'error' => 'Upload directory not writable'];
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $filename;
        
        error_log("Attempting to move slider file from {$file['tmp_name']} to $uploadPath");
        
        // Check if tmp file exists
        if (!file_exists($file['tmp_name'])) {
            $errorMsg = "Slider temporary file does not exist: {$file['tmp_name']}";
            error_log($errorMsg);
            return ['success' => false, 'error' => 'Temporary file not found'];
        }
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            error_log("Slider file upload success: $uploadPath");
            // Verify file was actually created and has content
            if (file_exists($uploadPath) && filesize($uploadPath) > 0) {
                error_log("Slider file verified: " . filesize($uploadPath) . " bytes");
                // Return the full path including subdirectory so URLs work correctly
                return ['success' => true, 'filename' => 'slider/' . $filename];
            } else {
                $errorMsg = "Slider file moved but verification failed: $uploadPath";
                error_log($errorMsg);
                return ['success' => false, 'error' => 'File upload verification failed'];
            }
        } else {
            $errorMsg = "Failed to move slider uploaded file from {$file['tmp_name']} to $uploadPath";
            error_log($errorMsg);
            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }
    }
} 