<?php
/**
 * OneStore Autoloader
 * Automatically loads classes from the app directory
 */

spl_autoload_register(function($class) {
    // Project namespace prefix
    $prefix = 'App\\';
    
    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/';
    
    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Get the relative class name
    $relative_class = substr($class, $len);
    
    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

// Require configuration and bootstrap
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/Helpers/Helper.php';
?> 