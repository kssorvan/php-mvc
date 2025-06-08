<?php
/**
 * OneStore Application Configuration
 * Now with environment-aware settings
 */

// TODO: Remove after complete migration
// Include the old config for backward compatibility during migration
// if (file_exists(__DIR__ . '/../config.php')) {
//     require_once __DIR__ . '/../config.php';
// }

// Load environment-specific configuration first
require_once __DIR__ . '/environment.php';


// Path Constants (environment-aware)
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('CONFIG_PATH', ROOT_PATH . '/config');
require_once ROOT_PATH . '/database.php';

// File Upload Settings (environment-aware paths)
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads/');

// Error Reporting (now based on environment detection)
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Timezone
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

// Initialize session safely
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
    
    // Session security
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id();
        $_SESSION['initiated'] = true;
    }
    
    // Session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}
?> 