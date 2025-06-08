<?php
/**
 * OneStore Application Entry Point
 */

// Load configuration
require_once 'config/app.php';

// Load autoloader
require_once 'app/autoload.php';

// Import required classes
use App\Controllers\Client\HomeController;
use App\Controllers\Client\CartController;
use App\Controllers\Client\AuthController;
use App\Controllers\Client\CheckoutController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\ProductController;
use App\Controllers\Admin\SliderController;
use App\Controllers\Admin\OrderController;
use App\Controllers\Admin\CustomerController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\BrandController;
use App\Controllers\ErrorController;
use App\Helpers\Helper;

try {
    // Initialize the application - Environment aware path cleaning
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);
    
    // Remove base path based on environment
    if (defined('BASE_PATH') && !empty(BASE_PATH)) {
        $path = str_replace(BASE_PATH, '', $path);
    } else {
        $path = str_replace('/php-test', '', $path); // Local development
    }
    
    // Admin Routes
    if (strpos($path, '/admin') === 0) {
        
        // Remove /admin prefix for admin routing
        $adminPath = substr($path, 6);
        
        switch ($adminPath) {
            case '':
            case '/':
            case '/dashboard':
                $controller = new DashboardController();
                $controller->index();
                break;
                
            case '/login':
                $controller = new AdminAuthController();
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->login();
                } else {
                    $controller->showLogin();
                }
                break;
                
            case '/logout':
                $controller = new AdminAuthController();
                $controller->logout();
                break;
                
            case '/products':
                $controller = new ProductController();
                $controller->index();
                break;
                
            case '/products/create':
                $controller = new ProductController();
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->store();
                } else {
                    $controller->create();
                }
                break;
                
            case '/products/store':
                $controller = new ProductController();
                $controller->store();
                break;
                
            case '/products/edit':
                $controller = new ProductController();
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->update();
                } else {
                    $controller->edit();
                }
                break;
                
            case '/products/update':
                $controller = new ProductController();
                $controller->update();
                break;
                
            case '/products/delete':
                $controller = new ProductController();
                $controller->delete();
                break;
                
            case '/products/get':
                $controller = new ProductController();
                $controller->get();
                break;
                
            // Slider routes
            case '/slider':
                $controller = new SliderController();
                $controller->index();
                break;
                
            case '/slider/store':
                $controller = new SliderController();
                $controller->store();
                break;
                
            case '/slider/update':
                $controller = new SliderController();
                $controller->update();
                break;
                
            case '/slider/delete':
                $controller = new SliderController();
                $controller->delete();
                break;
                
            case '/slider/get':
                $controller = new SliderController();
                $controller->get();
                break;
                
            // Order routes
            case '/orders':
                $controller = new OrderController();
                $controller->index();
                break;
                
            case '/orders/view':
                $controller = new OrderController();
                $controller->view();
                break;
                
            case '/orders/update-status':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller = new OrderController();
                    $controller->updateStatus();
                } else {
                    http_response_code(405);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                }
                break;
                
            case '/orders/update-payment-status':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller = new OrderController();
                    $controller->updatePaymentStatus();
                } else {
                    http_response_code(405);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                }
                break;
                
            case '/orders/get':
                $controller = new OrderController();
                $controller->get();
                break;
                
            // Customer routes
            case '/customers':
                $controller = new CustomerController();
                $controller->index();
                break;
                
            case '/customers/view':
                $controller = new CustomerController();
                $controller->view();
                break;
                
            case '/customers/update':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller = new CustomerController();
                    $controller->update();
                } else {
                    http_response_code(405);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                }
                break;
                
            case '/customers/update-status':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller = new CustomerController();
                    $controller->updateStatus();
                } else {
                    http_response_code(405);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                }
                break;
                
            case '/customers/verify-email':
                $controller = new CustomerController();
                $controller->verifyEmail();
                break;
                
            case '/customers/get':
                $controller = new CustomerController();
                $controller->get();
                break;
                
            // Category routes
            case '/categories':
                $controller = new CategoryController();
                $controller->index();
                break;
                
            case '/categories/store':
                $controller = new CategoryController();
                $controller->store();
                break;
                
            case '/categories/update':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller = new CategoryController();
                    $controller->update();
                } else {
                    http_response_code(405);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                }
                break;
                
            case '/categories/delete':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller = new CategoryController();
                    $controller->delete();
                } else {
                    http_response_code(405);
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
                }
                break;
                
            case '/categories/get':
                $controller = new CategoryController();
                $controller->get();
                break;
                
            // Brand routes
            case '/brands':
                $controller = new BrandController();
                $controller->index();
                break;
                
            case '/brands/store':
                $controller = new BrandController();
                $controller->store();
                break;
                
            case '/brands/update':
                $controller = new BrandController();
                $controller->update();
                break;
                
            case '/brands/delete':
                $controller = new BrandController();
                $controller->delete();
                break;
                
            case '/brands/get':
                $controller = new BrandController();
                $controller->get();
                break;
                
            default:
                $errorController = new ErrorController();
                $errorController->show404Admin();
                break;
        }
        
    } else {
        // Client Routes
        
        // Authentication routes
        if ($path === '/login') {
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->login();
            } else {
                $controller->showLogin();
            }
        } elseif ($path === '/register') {
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->register();
            } else {
                $controller->showRegister();
            }
        } elseif ($path === '/logout') {
            $controller = new AuthController();
            $controller->logout();
        } elseif ($path === '/forgot-password') {
            $controller = new AuthController();
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->forgotPassword();
            } else {
                $controller->showForgotPassword();
            }
        } elseif ($path === '/account') {
            $controller = new AuthController();
            $controller->account();
            
        // Cart routes
        } elseif ($path === '/cart') {
            $controller = new CartController();
            $controller->index();
        } elseif ($path === '/cart/add') {
            $controller = new CartController();
            $controller->add();
        } elseif ($path === '/cart/update') {
            $controller = new CartController();
            $controller->update();
        } elseif ($path === '/cart/remove') {
            $controller = new CartController();
            $controller->remove();
        } elseif ($path === '/cart/clear') {
            $controller = new CartController();
            $controller->clear();
        } elseif ($path === '/cart/get') {
            $controller = new CartController();
            $controller->get();
            
        // Checkout routes
        } elseif ($path === '/checkout') {
            $controller = new CheckoutController();
            $controller->index();
        } elseif ($path === '/checkout/debug') {
            $controller = new CheckoutController();
            $controller->debugCustomer();
        } elseif ($path === '/checkout/process') {
            $controller = new CheckoutController();
            $controller->process();
        } elseif ($path === '/checkout/paypal') {
            $controller = new CheckoutController();
            $controller->showPayPal();
        } elseif ($path === '/checkout/paypal/success') {
            $controller = new CheckoutController();
            $controller->paypalSuccess();
        } elseif (preg_match('/^\/order-confirmation\/(\d+)$/', $path, $matches)) {
            $controller = new CheckoutController();
            $controller->orderConfirmation($matches[1]);
            
        // Main site routes
        } else {
            $controller = new HomeController();
            
            switch ($path) {
                case '/':
                case '/home':
                    $controller->home();
                    break;
                    
                case '/about':
                    $controller->about();
                    break;
                    
                case '/shop':
                    $controller->shop();
                    break;
                    
                case '/blog':
                    $controller->blog();
                    break;
                    
                case '/contact':
                    $controller->contact();
                    break;
                    
                // AJAX API endpoints
                case '/api/products':
                    $controller->getProducts();
                    break;
                    
                case '/api/categories':
                    $controller->getCategories();
                    break;
                    
                case '/api/sliders':
                    $controller->getSliders();
                    break;
                    
                case '/shop/load-more':
                    $controller->loadMore();
                    break;
                    
                default:
                    $errorController = new ErrorController();
                    $errorController->show404Client();
                    break;
            }
        }
    }
    
} catch (Exception $e) {
    // Handle errors gracefully using ErrorController
    $errorController = new ErrorController();
    $errorController->show500($e);
}
?>
