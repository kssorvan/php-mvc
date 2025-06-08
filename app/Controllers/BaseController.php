<?php
namespace App\Controllers;

use App\Helpers\Helper;

/**
 * Base Controller Class
 * Common functionality for all controllers
 */
abstract class BaseController {
    
    protected $data = [];
    
    public function __construct() {
        // Initialize common data for all controllers
        $this->data['csrf_token'] = Helper::generateCsrfToken();
        $this->data['flash_messages'] = $this->getFlashMessages();
        $this->data['user'] = $this->getCurrentUser();
    }
    
    /**
     * Render a view with data
     */
    protected function render($view, $data = []) {
        // Merge controller data with passed data
        $viewData = array_merge($this->data, $data);
        
        // Extract variables for the view
        extract($viewData);
        
        // Build view path
        $viewPath = $this->getViewPath($view);
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View not found: {$viewPath}");
        }
        
        require $viewPath;
    }
    
    /**
     * Render with layout
     */
    protected function renderWithLayout($view, $layout, $data = []) {
        // Start output buffering for content
        ob_start();
        $this->render($view, $data);
        $content = ob_get_clean();
        
        // Render layout with content
        $this->render($layout, array_merge($data, ['content' => $content]));
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect($url, $statusCode = 302) {
        // Make URL environment-aware if it's a relative path
        if ($url[0] === '/' && !str_starts_with($url, APP_URL)) {
            $url = APP_URL . $url;
        }
        
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Redirect back with message
     */
    protected function redirectBack($message = null, $type = 'info') {
        if ($message) {
            Helper::flash($type, $message);
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    /**
     * Get flash messages
     */
    protected function getFlashMessages() {
        $messages = [];
        $types = ['success', 'error', 'warning', 'info'];
        
        foreach ($types as $type) {
            $message = Helper::flash($type);
            if ($message) {
                $messages[$type] = $message;
            }
        }
        
        return $messages;
    }
    
    /**
     * Get current user (to be implemented by child classes)
     */
    protected function getCurrentUser() {
        return null;
    }
    
    /**
     * Get view path (to be implemented by child classes)
     */
    abstract protected function getViewPath($view);
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrfToken() {
        $token = $_POST['_token'] ?? $_GET['_token'] ?? '';
        
        if (!Helper::verifyCsrfToken($token)) {
            $this->json(['error' => 'Invalid CSRF token'], 403);
        }
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequired($fields, $data) {
        $errors = [];
        
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = ucfirst($field) . ' is required';
            }
        }
        
        return $errors;
    }
    
    /**
     * Sanitize input data
     */
    protected function sanitizeInput($data) {
        return Helper::sanitize($data);
    }
    
    /**
     * Check if request is POST
     */
    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    /**
     * Check if request is GET
     */
    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    /**
     * Check if request is AJAX
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Get request data
     */
    protected function getInput($key = null, $default = null) {
        $input = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $input;
        }
        
        return $input[$key] ?? $default;
    }
    
    /**
     * Handle file upload
     */
    protected function handleFileUpload($fileKey, $uploadDir = 'general') {
        if (!isset($_FILES[$fileKey])) {
            return ['success' => false, 'error' => 'No file uploaded'];
        }
        
        $file = $_FILES[$fileKey];
        $errors = Helper::validateImageUpload($file);
        
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        $filename = Helper::generateUniqueFilename($file['name']);
        $uploadPath = UPLOAD_PATH . $uploadDir . '/';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $fullPath = $uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            return [
                'success' => true, 
                'filename' => $filename,
                'path' => $uploadDir . '/' . $filename,
                'url' => Helper::upload($uploadDir . '/' . $filename)
            ];
        }
        
        return ['success' => false, 'error' => 'Failed to upload file'];
    }
    
    /**
     * Set page data
     */
    protected function setData($key, $value) {
        $this->data[$key] = $value;
    }
    
    /**
     * Get page data
     */
    protected function getData($key = null) {
        if ($key === null) {
            return $this->data;
        }
        
        return $this->data[$key] ?? null;
    }
}
?> 