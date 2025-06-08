<?php
/**
 * Database Configuration and Connection
 * Legacy compatibility file for existing code
 */

// Load new configuration if available
if (file_exists('config/app.php')) {
    require_once 'config/app.php';
} else {
    // Fallback configuration - only define if not already defined
    if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', 'temushop_db');
    if (!defined('DB_USER')) define('DB_USER', 'root');
    if (!defined('DB_PASS')) define('DB_PASS', '');
    if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');
    if (!defined('DEBUG_MODE')) define('DEBUG_MODE', true);
}

/**
 * Database connection function
 */
function connectToDatabase() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            throw new Exception("Database Connection Error: " . $e->getMessage());
        } else {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please check your configuration.");
        }
    }
}

/**
 * Global database connection for legacy code
 */
try {
    $pdo = connectToDatabase();
} catch (Exception $e) {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        die("Database Error: " . $e->getMessage());
    } else {
        die("Database connection failed. Please contact the administrator.");
    }
}
?> 