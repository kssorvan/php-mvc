<?php
namespace App\Controllers\Client;

use App\Controllers\BaseController;
use App\Helpers\Helper;

/**
 * Client Base Controller
 * Base controller for all client-side controllers
 */
abstract class ClientController extends BaseController {
    
    public function __construct() {
        parent::__construct();
        
        // Client-specific initialization
        $this->setData('site_name', APP_NAME);
        $this->setData('page_title', APP_NAME);
        $this->setData('cart_count', $this->getCartCount());
    }
    
    /**
     * Get view path for client views
     */
    protected function getViewPath($view) {
        return ROOT_PATH . '/app/Views/Client/' . str_replace('.', '/', $view) . '.php';
    }
    
    /**
     * Get current customer if logged in
     */
    protected function getCurrentUser() {
        if (isset($_SESSION['customer_id'])) {
            // Load customer data from database
            return [
                'id' => $_SESSION['customer_id'],
                'name' => $_SESSION['customer_name'] ?? '',
                'email' => $_SESSION['customer_email'] ?? ''
            ];
        }
        return null;
    }
    
    /**
     * Check if customer is logged in
     */
    protected function requireAuth() {
        if (!$this->getCurrentUser()) {
            Helper::flash('error', 'Please login to continue');
            $this->redirect('/login');
        }
    }
    
    /**
     * Get cart item count
     */
    protected function getCartCount() {
        // If user is logged in, get from database
        if ($user = $this->getCurrentUser()) {
            // Implement cart count logic from database
            return 0; // Placeholder
        }
        
        // Get from session for guest users
        return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    }
    
    /**
     * Render client view with layout
     */
    protected function view($view, $data = []) {
        // Use default client layout
        $layout = $data['layout'] ?? 'layouts.main';
        unset($data['layout']);
        
        $this->renderWithLayout($view, $layout, $data);
    }
    
    /**
     * Set page title
     */
    protected function setTitle($title) {
        $this->setData('page_title', $title . ' - ' . APP_NAME);
    }
    
    /**
     * Set meta description
     */
    protected function setMeta($description, $keywords = '') {
        $this->setData('meta_description', $description);
        $this->setData('meta_keywords', $keywords);
    }
    
    /**
     * Add breadcrumb
     */
    protected function addBreadcrumb($title, $url = null) {
        $breadcrumbs = $this->getData('breadcrumbs') ?? [];
        $breadcrumbs[] = ['title' => $title, 'url' => $url];
        $this->setData('breadcrumbs', $breadcrumbs);
    }
}
?> 