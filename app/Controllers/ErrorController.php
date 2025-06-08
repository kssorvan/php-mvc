<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ErrorController extends BaseController {
    
    /**
     * Get view path for error controller
     */
    protected function getViewPath($view) {
        return ROOT_PATH . '/app/Views/Errors/' . $view . '.php';
    }
    
    /**
     * Show 404 error page for client
     */
    public function show404Client() {
        http_response_code(404);
        include ROOT_PATH . '/app/Views/Client/errors/404.php';
    }
    
    /**
     * Show 404 error page for admin
     */
    public function show404Admin() {
        http_response_code(404);
        include ROOT_PATH . '/app/Views/Admin/errors/404.php';
    }
    
    /**
     * Show 500 error page
     */
    public function show500($error = null) {
        http_response_code(500);
        
        if (DEBUG_MODE && $error) {
            echo '<h1>Application Error</h1>';
            echo '<pre>' . htmlspecialchars($error->getMessage()) . '</pre>';
            echo '<pre>' . htmlspecialchars($error->getTraceAsString()) . '</pre>';
        } else {
            echo '<h1>Something went wrong</h1>';
            echo '<p>We are working to fix this issue. Please try again later.</p>';
            
            if ($error) {
                error_log($error->getMessage());
            }
        }
    }
    
    /**
     * Show maintenance page
     */
    public function showMaintenance() {
        http_response_code(503);
        
        echo '<!DOCTYPE html>';
        echo '<html><head><title>Under Maintenance</title></head>';
        echo '<body style="font-family: Arial, sans-serif; text-align: center; padding: 50px;">';
        echo '<h1>We\'ll be back soon!</h1>';
        echo '<p>OneStore is currently under maintenance. We\'ll be back online shortly.</p>';
        echo '</body></html>';
    }
} 