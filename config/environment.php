<?php
/**
 * Environment-aware configuration for OneStore
 * Automatically detects hosting environment and sets appropriate configs
 */

/**
 * Detect current environment based on server characteristics
 */
function detectEnvironment() {
    // Check server IP for EC2
    $serverIP = $_SERVER['SERVER_ADDR'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    
    // EC2 production indicators
    if ($serverIP === '172-31-0-67' || 
        $serverName === '172-31-0-67' || 
        $httpHost === '172-31-0-67' ||
        strpos($httpHost, '172-31-0-67') !== false) {
        return 'production';
    }
    
    // Laragon local development indicators  
    if (strpos($httpHost, 'php-test.test') !== false ||
        strpos($httpHost, 'localhost') !== false) {
        return 'development';
    }
    
    // Default to development for safety
    return 'development';
}

// Detect environment
$environment = detectEnvironment();

// Environment-specific configuration
if ($environment === 'production') {
    // Production (EC2) configuration
    define('APP_ENV', 'production');
    define('APP_DEBUG', false);
    define('BASE_PATH', '/onestore');
    define('APP_URL', 'http://localhost:8000/onestore');
    
    // Production database
    define('DB_USER', 'onestore_user');
    define('DB_PASS', 'OneStore_Secure_2024!@#');
    
    // Production file upload (larger limit)
    define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
    
    // Production PayPal (should use environment variables in real production)
    define('PAYPAL_MODE', 'live');
    // Note: PayPal credentials should be loaded from environment variables in production
    // For now, we'll use sandbox for safety until live credentials are configured
    define('PAYPAL_CLIENT_ID', 'ATdyQLhtH8ByRKGWfrCSVd13AJhyE9RT0oSvF2fn6oo0Zm4LbBLjL-_hha7DqCvN3dNVOJTqw8jhvb3u'); // Set this via environment variables
    define('PAYPAL_CLIENT_SECRET', 'EEMfzSecZwyG7_JU6fR497ZRA4CRcONB1og0ctUTb9Udk5eH1QoqxpjV_M9vBfZCAi0X6vTD1WmWhEof'); // Set this via environment variables
    
} else {
    // Development configuration
    define('APP_ENV', 'development'); 
    define('APP_DEBUG', true);
    define('BASE_PATH', '');
    define('APP_URL', 'http://localhost:8000');
    
    // Development database
    define('DB_USER', 'root');
    define('DB_PASS', '');
    
    // Development file upload (smaller limit)
    define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
    
    // Development PayPal (sandbox)
    define('PAYPAL_MODE', 'sandbox');
    define('PAYPAL_CLIENT_ID', 'ATdyQLhtH8ByRKGWfrCSVd13AJhyE9RT0oSvF2fn6oo0Zm4LbBLjL-_hha7DqCvN3dNVOJTqw8jhvb3u');
    define('PAYPAL_CLIENT_SECRET', 'EEMfzSecZwyG7_JU6fR497ZRA4CRcONB1og0ctUTb9Udk5eH1QoqxpjV_M9vBfZCAi0X6vTD1WmWhEof');
}

// Common settings (same for all environments)
define('APP_NAME', 'OneStore');
define('APP_VERSION', '2.0.0');
define('DB_HOST', 'localhost');
define('DB_NAME', 'temushop_db');
define('DB_CHARSET', 'utf8mb4');
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('SESSION_TIMEOUT', 7200); // 2 hours
define('SESSION_NAME', 'onestore_session');
define('CSRF_TOKEN_NAME', '_token');
define('PASSWORD_HASH_ALGO', PASSWORD_DEFAULT);

// PayPal API URL
define('PAYPAL_API_URL', PAYPAL_MODE === 'live' ? 'https://api.paypal.com' : 'https://api.sandbox.paypal.com');

// Debug mode based on environment
define('DEBUG_MODE', APP_ENV === 'development');

/**
 * Get environment-aware URLs
 */
function getBaseUrl() {
    return defined('BASE_PATH') && !empty(BASE_PATH) ? BASE_PATH : '';
}

function getAssetUrl($path) {
    return getBaseUrl() . '/public/assets/' . ltrim($path, '/');
}

function getUploadUrl($path) {
    return getBaseUrl() . '/public/uploads/' . ltrim($path, '/');
}

?> 