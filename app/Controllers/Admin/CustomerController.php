<?php
namespace App\Controllers\Admin;

use App\Models\Customer;
use App\Models\Order;
use Exception;

class CustomerController extends AdminController {
    private $customerModel;
    private $orderModel;
    
    public function __construct() {
        parent::__construct();
        $this->customerModel = new Customer();
        $this->orderModel = new Order();
    }
    
    /**
     * Display customers list
     */
    public function index() {
        $this->setAdminTitle('Customer Management');
        $this->requirePermission('manage_customers');
        
        try {
            // Get filter parameters
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            // Build query conditions
            $conditions = [];
            $params = [];
            
            if (!empty($search)) {
                $conditions[] = "(CONCAT(c.firstName, ' ', c.lastName) LIKE :search OR c.email LIKE :search)";
                $params['search'] = '%' . $search . '%';
            }
            
            if ($status === 'verified') {
                $conditions[] = "c.email_verified = 1";
            } elseif ($status === 'unverified') {
                $conditions[] = "c.email_verified = 0";
            }
            
            $whereClause = '';
            if (!empty($conditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            }
            
            // Get customers with order statistics
            $sql = "SELECT c.*,
                           COUNT(o.orderID) as order_count,
                           SUM(CASE WHEN o.payment_status = 'paid' THEN o.total_amount ELSE 0 END) as total_spent,
                           MAX(o.created_at) as last_order_date
                    FROM tbl_customer c
                    LEFT JOIN tbl_order o ON c.customerID = o.customerID
                    {$whereClause}
                    GROUP BY c.customerID
                    ORDER BY c.customerID ASC
                    LIMIT :limit OFFSET :offset";
            
            $pdo = $this->connectDatabase();
            $stmt = $pdo->prepare($sql);
            
            // Bind filter parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            
            $stmt->execute();
            $customers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countSql = "SELECT COUNT(*) as total 
                        FROM tbl_customer c
                        {$whereClause}";
            $countStmt = $pdo->prepare($countSql);
            foreach ($params as $key => $value) {
                $countStmt->bindValue(':' . $key, $value);
            }
            $countStmt->execute();
            $totalCustomers = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            // Calculate pagination
            $totalPages = ceil($totalCustomers / $limit);
            
            // Get customer statistics
            $stats = $this->getCustomerStatistics();
            
            $data = [
                'title' => 'Customer Management - OneStore Admin',
                'customers' => $customers,
                'stats' => $stats,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_customers' => $totalCustomers,
                'filters' => [
                    'search' => $search,
                    'status' => $status
                ],
                'admin_user' => $this->adminUser,
                'success' => $_SESSION['flash_success'] ?? null,
                'error' => $_SESSION['flash_error'] ?? null
            ];
            
            // Clear flash messages
            unset($_SESSION['flash_success'], $_SESSION['flash_error']);
            
            $this->adminView('customers/index', $data);
            
        } catch (Exception $e) {
            error_log("Customer listing error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error loading customers: ' . $e->getMessage();
            header('Location: /admin/dashboard');
            exit;
        }
    }
    
    /**
     * View customer details
     */
    public function view() {
        $this->setAdminTitle('Customer Details');
        $this->requirePermission('manage_customers');
        
        $customerID = intval($_GET['id'] ?? 0);
        
        if (!$customerID) {
            $_SESSION['flash_error'] = 'Customer not found';
            header('Location: /admin/customers');
            exit;
        }
        
        try {
            // Get customer with addresses
            $customer = $this->customerModel->getCustomerWithAddresses($customerID);
            
            if (!$customer) {
                $_SESSION['flash_error'] = 'Customer not found';
                header('Location: /admin/customers');
                exit;
            }
            
            // Get customer orders
            $orders = $this->orderModel->getCustomerOrders($customerID, 10, 0);
            
            // Get customer statistics
            $pdo = $this->connectDatabase();
            $statsQuery = "SELECT 
                              COUNT(o.orderID) as total_orders,
                              SUM(CASE WHEN o.payment_status = 'paid' THEN o.total_amount ELSE 0 END) as total_spent,
                              AVG(CASE WHEN o.payment_status = 'paid' THEN o.total_amount ELSE NULL END) as avg_order_value,
                              MAX(o.created_at) as last_order_date
                           FROM tbl_order o 
                           WHERE o.customerID = :customerID";
            $statsStmt = $pdo->prepare($statsQuery);
            $statsStmt->execute(['customerID' => $customerID]);
            $customerStats = $statsStmt->fetch(\PDO::FETCH_ASSOC);
            
            $data = [
                'title' => $customer['firstName'] . ' ' . $customer['lastName'] . ' - Customer Details',
                'customer' => $customer,
                'orders' => $orders,
                'customer_stats' => $customerStats,
                'admin_user' => $this->adminUser,
                'success' => $_SESSION['flash_success'] ?? null,
                'error' => $_SESSION['flash_error'] ?? null
            ];
            
            // Clear flash messages
            unset($_SESSION['flash_success'], $_SESSION['flash_error']);
            
            $this->adminView('customers/view', $data);
            
        } catch (Exception $e) {
            error_log("Customer view error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error loading customer details';
            header('Location: /admin/customers');
            exit;
        }
    }
    
    /**
     * Update customer status (verify email, etc.)
     */
    public function updateStatus() {
        $this->requirePermission('manage_customers');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/customers');
            exit;
        }
        
        $customerID = intval($_POST['customerID'] ?? 0);
        $action = trim($_POST['action'] ?? '');
        
        if (!$customerID || empty($action)) {
            $_SESSION['flash_error'] = 'Invalid customer or action';
            header('Location: /admin/customers');
            exit;
        }
        
        try {
            $success = false;
            $message = '';
            
            switch ($action) {
                case 'verify_email':
                    $success = $this->customerModel->verifyEmail($customerID);
                    $message = $success ? 'Customer email verified successfully' : 'Failed to verify customer email';
                    break;
                    
                case 'unverify_email':
                    $pdo = $this->connectDatabase();
                    $stmt = $pdo->prepare("UPDATE tbl_customer SET email_verified = 0 WHERE customerID = ?");
                    $success = $stmt->execute([$customerID]);
                    $message = $success ? 'Customer email unverified successfully' : 'Failed to unverify customer email';
                    break;
                    
                default:
                    $_SESSION['flash_error'] = 'Invalid action';
                    header('Location: /admin/customers');
                    exit;
            }
            
            if ($success) {
                $_SESSION['flash_success'] = $message;
            } else {
                $_SESSION['flash_error'] = $message;
            }
            
        } catch (Exception $e) {
            error_log("Customer status update error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error updating customer status';
        }
        
        $redirectUrl = $_POST['redirect'] ?? '/admin/customers';
        header('Location: ' . $redirectUrl);
        exit;
    }
    
    // Edit method removed - using modal-based editing instead
    
    /**
     * Update customer information (AJAX)
     */
    public function update() {
        $this->requirePermission('manage_customers');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $customerID = intval($_GET['id'] ?? 0);
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if (!$customerID || empty($firstName) || empty($lastName) || empty($email)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Customer ID, first name, last name, and email are required']);
            exit;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }
        
        try {
            // Check if email already exists for another customer
            if ($this->customerModel->emailExists($email, $customerID)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Email already exists for another customer']);
                exit;
            }
            
            $updateData = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'phone' => $phone
            ];
            
            // Add password only if provided
            if (!empty($password)) {
                $updateData['password'] = $password;
            }
            
            $success = $this->customerModel->updateCustomer($customerID, $updateData);
            
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Customer updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update customer']);
            }
            
        } catch (Exception $e) {
            error_log("Customer update error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating customer: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Verify customer email (AJAX)
     */
    public function verifyEmail() {
        $this->requirePermission('manage_customers');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        $customerID = intval($_POST['customerID'] ?? 0);
        
        if (!$customerID) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
            exit;
        }
        
        try {
            $success = $this->customerModel->verifyEmail($customerID);
            
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Email verified successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to verify email']);
            }
            
        } catch (Exception $e) {
            error_log("Email verification error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error verifying email: ' . $e->getMessage()]);
        }
        
        exit;
    }
    
    /**
     * Get customer statistics
     */
    private function getCustomerStatistics() {
        try {
            $pdo = $this->connectDatabase();
            
            $sql = "SELECT 
                        COUNT(*) as total_customers,
                        COUNT(CASE WHEN email_verified = 1 THEN 1 END) as verified_customers,
                        COUNT(CASE WHEN email_verified = 0 THEN 1 END) as unverified_customers,
                        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as new_today,
                        COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as new_this_week,
                        COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as new_this_month
                    FROM tbl_customer";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Customer statistics error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * AJAX endpoint to get customer data
     */
    public function get() {
        header('Content-Type: application/json');
        
        $customerID = intval($_GET['id'] ?? 0);
        
        if (!$customerID) {
            echo json_encode(['success' => false, 'message' => 'Customer ID required']);
            exit;
        }
        
        try {
            $customer = $this->customerModel->getCustomerWithAddresses($customerID);
            
            if ($customer) {
                echo json_encode(['success' => true, 'customer' => $customer]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Customer not found']);
            }
            
        } catch (Exception $e) {
            error_log("Customer get error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading customer']);
        }
        
        exit;
    }
    
    protected function adminView($view, $data = []) {
        extract($data);
        require $this->getViewPath($view);
    }
    
    protected function getViewPath($view) {
        return __DIR__ . '/../../Views/Admin/' . $view . '.php';
    }
} 