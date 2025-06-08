<?php
namespace App\Helpers;

/**
 * Global Helper Functions
 * Centralized utility functions for the application
 */
class Helper {
    
    /**
     * Debug and die function
     */
    public static function dd($value) {
        echo "<pre>";
        var_dump($value);
        echo "</pre>";
        die();
    }
    
    /**
     * Check if current page matches given path
     */
    public static function urlIs($path) {
        $currentPage = $_GET['page'] ?? 'index';
        $path = str_replace('.php', '', $path);
        return $currentPage === $path;
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitize($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        // Handle null values to prevent PHP 8+ deprecation warnings
        if ($input === null) {
            return '';
        }
        
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Redirect to URL
     */
    public static function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    /**
     * Format currency
     */
    public static function formatCurrency($amount, $currency = 'USD') {
        return '$' . number_format($amount, 2);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken($token) {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && 
               hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Flash message system
     */
    public static function flash($key, $message = null) {
        if ($message === null) {
            $message = $_SESSION['flash'][$key] ?? null;
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        $_SESSION['flash'][$key] = $message;
    }
    
    /**
     * Check if flash message exists
     */
    public static function hasFlash($key) {
        return isset($_SESSION['flash'][$key]);
    }
    
    /**
     * Get flash message without removing it
     */
    public static function getFlash($key) {
        return $_SESSION['flash'][$key] ?? null;
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
    }
    
    /**
     * Check if admin is logged in
     */
    public static function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']);
    }
    
    /**
     * Get current user ID
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current admin ID
     */
    public static function getCurrentAdminId() {
        return $_SESSION['admin_id'] ?? null;
    }
    
    /**
     * Validate file upload
     */
    public static function validateImageUpload($file) {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error';
            return $errors;
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File size too large. Maximum size is ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB';
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_IMAGE_TYPES)) {
            $errors[] = 'Invalid file type. Allowed types: ' . implode(', ', ALLOWED_IMAGE_TYPES);
        }
        
        return $errors;
    }
    
    /**
     * Generate unique filename
     */
    public static function generateUniqueFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return uniqid() . '_' . time() . '.' . $extension;
    }
    
    /**
     * Create slug from string
     */
    public static function createSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }
    
    /**
     * Truncate text
     */
    public static function truncate($text, $length = 100, $append = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $append;
    }
    
    /**
     * Format date
     */
    public static function formatDate($date, $format = 'Y-m-d H:i:s') {
        return date($format, strtotime($date));
    }
    
    /**
     * Get asset URL - Environment aware ****modify path
     */
    public static function asset($path) {
        $cleanPath = ltrim($path, '/');
        
        if (php_sapi_name() === 'cli-server') {
            // When using PHP built-in server with public as document root
            return '/assets/' . $cleanPath;
        }
        
        // Use BASE_PATH for subdirectory hosting
        $basePath = defined('BASE_PATH') && !empty(BASE_PATH) ? BASE_PATH : '';
        return $basePath . '/assets/' . $cleanPath;
    }
    
    /**
     * Get upload URL - Environment aware
     */
    public static function upload($path) {
        if (empty($path)) {
            return self::asset('images/no-image.png');
        }
        
        $cleanPath = ltrim($path, '/');
        
        if (php_sapi_name() === 'cli-server') {
            return '/uploads/' . $cleanPath;
        }

        // Use APP_URL for consistent environment-aware URLs
        if (defined('APP_URL') && !empty(APP_URL)) {
            return APP_URL . '/uploads/' . $cleanPath;
        }
        
        $basePath = defined('BASE_PATH') && !empty(BASE_PATH) ? BASE_PATH : '';
        return $basePath . '/uploads/' . $cleanPath;
    }
    
    /**
     * Generate URL for routes - Environment aware
     */
    public static function url($path = '') {
        $cleanPath = ltrim($path, '/');
        $basePath = defined('BASE_PATH') && !empty(BASE_PATH) ? BASE_PATH : '';
        return $basePath . '/' . $cleanPath;
    }
    
    /**
     * Generate admin URL - Environment aware
     */
    public static function adminUrl($path = '') {
        $cleanPath = ltrim($path, '/');
        $basePath = defined('BASE_PATH') && !empty(BASE_PATH) ? BASE_PATH : '';
        return $basePath . '/admin/' . $cleanPath;
    }
}
?> 