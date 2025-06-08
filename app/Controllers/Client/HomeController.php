<?php
namespace App\Controllers\Client;

use Exception;

/**
 * Home Controller
 * Handles home page and general client requests
 */
class HomeController extends ClientController {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Connect to database - Use centralized connection
     */
    private function connectDatabase() {
        try {
            
            // Use the centralized database connection function
            return connectToDatabase();
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Display home page
     */
    public function home() {
        $this->setTitle('Welcome to OneStore');
        $this->setMeta('OneStore - Your favorite online shopping destination');
        
        try {
            $pdo = $this->connectDatabase();
            
            // Get featured products
            $productsStmt = $pdo->query("SELECT p.*, c.catName as categoryName, b.brandName 
                                        FROM tbl_product p 
                                        LEFT JOIN tbl_category c ON p.categoryID = c.categoryID 
                                        LEFT JOIN tbl_brand b ON p.brandID = b.brandID 
                                        WHERE p.status = 1 AND p.featured = 1 
                                        ORDER BY p.productID DESC LIMIT 8");
            $featuredProducts = $productsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get active sliders
            $slidersStmt = $pdo->query("SELECT * FROM tbl_slider WHERE status = 1 ORDER BY position ASC LIMIT 3");
            $sliders = $slidersStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get categories for navigation
            $categoriesStmt = $pdo->query("SELECT * FROM tbl_category WHERE status = 1 ORDER BY catName");
            $categories = $categoriesStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->view('pages.home', [
                'featured_products' => $featuredProducts,
                'sliders' => $sliders,
                'categories' => $categories
            ]);
            
        } catch (Exception $e) {
            error_log("Homepage error: " . $e->getMessage());
            $this->view('pages.home', [
                'featured_products' => [],
                'sliders' => [],
                'categories' => [],
                'error' => 'Unable to load homepage content. Please try again later.'
            ]);
        }
    }
    
    /**
     * Display shop page with products and filters
     */
    public function shop() {
        $this->setTitle('Shop');
        $this->setMeta('Browse our collection of products');
        
        // Clear cart-related error messages that might come from checkout redirect
        // The shop page should not show cart error notifications
        if (isset($_SESSION['flash']['error']) && $_SESSION['flash']['error'] === 'Your cart is empty') {
            unset($_SESSION['flash']['error']);
            $this->data['flash_messages'] = [];
        }
        
        try {
            $pdo = $this->connectDatabase();
            
            // Get filter parameters
            $categoryID = $_GET['category'] ?? null;
            $brandID = $_GET['brand'] ?? null;
            $search = $_GET['search'] ?? null;
            $sortBy = $_GET['sort'] ?? 'newest';
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 12;
            $offset = ($page - 1) * $limit;
            
            // Build query
            $where = ["p.status = 1"];
            $params = [];
            
            if ($categoryID) {
                $where[] = "p.categoryID = ?";
                $params[] = $categoryID;
            }
            
            if ($brandID) {
                $where[] = "p.brandID = ?";
                $params[] = $brandID;
            }
            
            if ($search) {
                $where[] = "(p.productName LIKE ? OR p.description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Sort options
            $orderBy = match($sortBy) {
                'price_low' => 'p.price ASC',
                'price_high' => 'p.price DESC',
                'name' => 'p.productName ASC',
                'newest' => 'p.created_at DESC',
                default => 'p.productID DESC'
            };
            
            // Get products
            $sql = "SELECT p.*, c.catName as categoryName, b.brandName 
                    FROM tbl_product p 
                    LEFT JOIN tbl_category c ON p.categoryID = c.categoryID 
                    LEFT JOIN tbl_brand b ON p.brandID = b.brandID 
                    WHERE $whereClause 
                    ORDER BY $orderBy 
                    LIMIT $limit OFFSET $offset";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countSql = "SELECT COUNT(*) FROM tbl_product p WHERE $whereClause";
            $countStmt = $pdo->prepare($countSql);
            $countStmt->execute($params);
            $totalProducts = $countStmt->fetchColumn();
            $totalPages = ceil($totalProducts / $limit);
            
            // Get categories for filter
            $categoriesStmt = $pdo->query("SELECT * FROM tbl_category WHERE status = 1 ORDER BY catName");
            $categories = $categoriesStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get brands for filter
            $brandsStmt = $pdo->query("SELECT * FROM tbl_brand WHERE status = 1 ORDER BY brandName");
            $brands = $brandsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $this->view('pages.shop', [
                'products' => $products,
                'categories' => $categories,
                'brands' => $brands,
                'currentPage' => $page,
                'current_page' => $page,
                'totalPages' => $totalPages,
                'totalProducts' => $totalProducts,
                'per_page' => $limit,
                'tags' => [],
                'filters' => [
                    'category' => $categoryID,
                    'brand' => $brandID,
                    'search' => $search,
                    'sort' => $sortBy
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Shop page error: " . $e->getMessage());
            $this->view('pages.shop', [
                'products' => [],
                'categories' => [],
                'brands' => [],
                'tags' => [],
                'currentPage' => 1,
                'current_page' => 1,
                'totalPages' => 0,
                'totalProducts' => 0,
                'per_page' => 12,
                'filters' => [
                    'category' => null,
                    'brand' => null,
                    'search' => null,
                    'sort' => 'newest'
                ],
                'error' => 'Unable to load shop content. Please try again later.'
            ]);
        }
    }
    
    /**
     * AJAX endpoint for getting products
     */
    public function getProducts() {
        header('Content-Type: application/json');
        
        try {
            $pdo = $this->connectDatabase();
            
            $categoryID = $_GET['category'] ?? null;
            $brandID = $_GET['brand'] ?? null;
            $featured = $_GET['featured'] ?? null;
            $limit = min(50, intval($_GET['limit'] ?? 12));
            
            $where = ["p.status = 1"];
            $params = [];
            
            if ($categoryID) {
                $where[] = "p.categoryID = ?";
                $params[] = $categoryID;
            }
            
            if ($brandID) {
                $where[] = "p.brandID = ?";
                $params[] = $brandID;
            }
            
            if ($featured) {
                $where[] = "p.featured = 1";
            }
            
            $whereClause = implode(' AND ', $where);
            
            $sql = "SELECT p.*, c.catName as categoryName, b.brandName 
                    FROM tbl_product p 
                    LEFT JOIN tbl_category c ON p.categoryID = c.categoryID 
                    LEFT JOIN tbl_brand b ON p.brandID = b.brandID 
                    WHERE $whereClause 
                    ORDER BY p.productID DESC 
                    LIMIT $limit";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'products' => $products,
                'count' => count($products)
            ]);
            
        } catch (Exception $e) {
            error_log("Get products API error: " . $e->getMessage());
            echo json_encode(['error' => 'Unable to load products']);
        }
        exit;
    }
    
    /**
     * AJAX endpoint for getting categories
     */
    public function getCategories() {
        header('Content-Type: application/json');
        
        try {
            $pdo = $this->connectDatabase();
            
            $stmt = $pdo->query("SELECT * FROM tbl_category WHERE status = 1 ORDER BY catName");
            $categories = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'categories' => $categories
            ]);
            
        } catch (Exception $e) {
            error_log("Get categories API error: " . $e->getMessage());
            echo json_encode(['error' => 'Unable to load categories']);
        }
        exit;
    }
    
    /**
     * AJAX endpoint for getting sliders
     */
    public function getSliders() {
        header('Content-Type: application/json');
        
        try {
            $pdo = $this->connectDatabase();
            
            $stmt = $pdo->query("SELECT * FROM tbl_slider WHERE status = 1 ORDER BY position ASC LIMIT 3");
            $sliders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'sliders' => $sliders
            ]);
            
        } catch (Exception $e) {
            error_log("Get sliders API error: " . $e->getMessage());
            echo json_encode(['error' => 'Unable to load sliders']);
        }
        exit;
    }
    
    /**
     * AJAX endpoint for load more products (4 products per request = 1 row)
     */
    public function loadMore() {
        header('Content-Type: application/json');
        
        try {
            $pdo = $this->connectDatabase();
            
            // Get page parameter (default to 2 since first page is already loaded)
            $page = max(2, intval($_GET['page'] ?? 2));
            $limit = 4; // Show 4 products per load more click (1 row)
            $offset = ($page - 1) * $limit;
            
            // Get filter parameters (maintain any existing filters)
            $categoryID = $_GET['category'] ?? null;
            $brandID = $_GET['brand'] ?? null;
            $search = $_GET['search'] ?? null;
            $sortBy = $_GET['sort'] ?? 'newest';
            
            // Build query with same filters as shop page
            $where = ["p.status = 1"];
            $params = [];
            
            if ($categoryID) {
                $where[] = "p.categoryID = ?";
                $params[] = $categoryID;
            }
            
            if ($brandID) {
                $where[] = "p.brandID = ?";
                $params[] = $brandID;
            }
            
            if ($search) {
                $where[] = "(p.productName LIKE ? OR p.description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $whereClause = implode(' AND ', $where);
            
            // Sort options
            $orderBy = match($sortBy) {
                'price_low' => 'p.price ASC',
                'price_high' => 'p.price DESC',
                'name' => 'p.productName ASC',
                'newest' => 'p.created_at DESC',
                default => 'p.productID DESC'
            };
            
            // Get products for this page
            $sql = "SELECT p.*, c.catName as categoryName, b.brandName 
                    FROM tbl_product p 
                    LEFT JOIN tbl_category c ON p.categoryID = c.categoryID 
                    LEFT JOIN tbl_brand b ON p.brandID = b.brandID 
                    WHERE $whereClause 
                    ORDER BY $orderBy 
                    LIMIT $limit OFFSET $offset";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Check if there are more products available
            $nextOffset = $page * $limit;
            $nextSql = "SELECT COUNT(*) FROM tbl_product p WHERE $whereClause";
            $nextStmt = $pdo->prepare($nextSql);
            $nextStmt->execute($params);
            $totalProducts = $nextStmt->fetchColumn();
            $hasMore = $totalProducts > $nextOffset;
            
            // Return response
            if (empty($products)) {
                echo json_encode([
                    'success' => true,
                    'products' => [],
                    'hasMore' => false,
                    'message' => 'No more products to load',
                    'count' => 0
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'products' => $products,
                    'hasMore' => $hasMore,
                    'count' => count($products),
                    'page' => $page
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Load more API error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Unable to load more products',
                'hasMore' => false
            ]);
        }
        exit;
    }
    
    /**
     * Display about page
     */
    public function about() {
        $this->setTitle('About Us');
        $this->setMeta('Learn more about OneStore and our mission');
        $this->addBreadcrumb('About');
        
        $this->view('pages.about');
    }
    
    /**
     * Display checkout page
     */
    public function checkout() {
        $this->setTitle('Shopping Cart');
        $this->setMeta('Review your cart and proceed to checkout');
        $this->addBreadcrumb('Shopping Cart');
        
        // Sample cart items for demonstration
        $cartItems = [
            [
                'id' => 1,
                'name' => 'Fresh Strawberries',
                'image' => 'item-cart-04.jpg',
                'price' => 36.00,
                'quantity' => 1,
                'total' => 36.00
            ],
            [
                'id' => 2,
                'name' => 'Lightweight Jacket',
                'image' => 'item-cart-05.jpg',
                'price' => 16.00,
                'quantity' => 1,
                'total' => 16.00
            ]
        ];
        
        $subtotal = array_sum(array_column($cartItems, 'total'));
        $shipping = 0; // Free shipping
        $total = $subtotal + $shipping;
        
        $this->view('pages.checkout', [
            'cart_items' => $cartItems,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total
        ]);
    }
    
    /**
     * Display blog page
     */
    public function blog() {
        $this->setTitle('Blog');
        $this->setMeta('Read our latest news and updates');
        $this->addBreadcrumb('Blog');
        
        $this->view('pages.blog', [
            'posts' => []
        ]);
    }
    
    /**
     * Display contact page
     */
    public function contact() {
        $this->setTitle('Contact Us');
        $this->setMeta('Get in touch with us');
        $this->addBreadcrumb('Contact');
        
        $this->view('pages.contact');
    }
}
?> 