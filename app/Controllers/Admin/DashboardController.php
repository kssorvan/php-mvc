<?php
namespace App\Controllers\Admin;

class DashboardController extends AdminController {
    
    protected function getViewPath($view) {
        return __DIR__ . '/../../Views/Admin/' . $view . '.php';
    }
    
    public function index() {
        $this->setAdminTitle('Dashboard');
        
        // Get admin statistics
        $stats = $this->getAdminStats();
        
        $data = [
            'title' => 'Admin Dashboard - OneStore',
            'stats' => $stats,
            'recent_orders' => $stats['recent_orders'] ?? [],
            'admin_user' => $this->adminUser,
            'success' => $_SESSION['flash_success'] ?? null,
            'error' => $_SESSION['flash_error'] ?? null
        ];
        
        // Clear flash messages
        unset($_SESSION['flash_success']);
        unset($_SESSION['flash_error']);
        
        $this->adminView('dashboard/index', $data);
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