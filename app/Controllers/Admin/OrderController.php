<?php
namespace App\Controllers\Admin;

use App\Models\Order;
use App\Models\Customer;
use Exception;

class OrderController extends AdminController {
    private $orderModel;
    private $customerModel;
    
    public function __construct() {
        parent::__construct();
        $this->orderModel = new Order();
        $this->customerModel = new Customer();
    }
    
    /**
     * Display orders list
     */
    public function index() {
        $this->setAdminTitle('Order Management');
        $this->requirePermission('manage_orders');
        
        try {
            // Get filter parameters
            $status = $_GET['status'] ?? '';
            $payment_status = $_GET['payment_status'] ?? '';
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            // Build query conditions
            $conditions = [];
            $params = [];
            
            if (!empty($status)) {
                $conditions[] = "o.order_status = :status";
                $params['status'] = $status;
            }
            
            if (!empty($payment_status)) {
                $conditions[] = "o.payment_status = :payment_status";
                $params['payment_status'] = $payment_status;
            }
            
            $whereClause = '';
            if (!empty($conditions)) {
                $whereClause = 'WHERE ' . implode(' AND ', $conditions);
            }
            
            // Get orders with customer information
            $sql = "SELECT o.*, 
                           CONCAT(c.firstName, ' ', c.lastName) as customer_name,
                           c.email as customer_email,
                           COUNT(oi.orderItemID) as item_count
                    FROM tbl_order o
                    LEFT JOIN tbl_customer c ON o.customerID = c.customerID
                    LEFT JOIN tbl_order_item oi ON o.orderID = oi.orderID
                    {$whereClause}
                    GROUP BY o.orderID
                    ORDER BY o.orderID ASC
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
            $orders = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countSql = "SELECT COUNT(*) as total 
                        FROM tbl_order o
                        {$whereClause}";
            $countStmt = $pdo->prepare($countSql);
            foreach ($params as $key => $value) {
                $countStmt->bindValue(':' . $key, $value);
            }
            $countStmt->execute();
            $totalOrders = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            // Calculate pagination
            $totalPages = ceil($totalOrders / $limit);
            
            // Get order statistics
            $stats = $this->getOrderStatistics();
            
            $data = [
                'title' => 'Order Management - OneStore Admin',
                'orders' => $orders,
                'stats' => $stats,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_orders' => $totalOrders,
                'filters' => [
                    'status' => $status,
                    'payment_status' => $payment_status
                ],
                'admin_user' => $this->adminUser,
                'success' => $_SESSION['flash_success'] ?? null,
                'error' => $_SESSION['flash_error'] ?? null
            ];
            
            // Clear flash messages
            unset($_SESSION['flash_success'], $_SESSION['flash_error']);
            
            $this->adminView('orders/index', $data);
            
        } catch (Exception $e) {
            error_log("Order listing error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error loading orders: ' . $e->getMessage();
            header('Location: /admin/dashboard');
            exit;
        }
    }
    
    /**
     * View order details
     */
    public function view() {
        $this->setAdminTitle('Order Details');
        $this->requirePermission('manage_orders');
        
        $orderID = intval($_GET['id'] ?? 0);
        
        if (!$orderID) {
            $_SESSION['flash_error'] = 'Order not found';
            header('Location: /admin/orders');
            exit;
        }
        
        try {
            // Get order details
            $order = $this->orderModel->getOrderWithDetails($orderID);
            
            if (!$order) {
                $_SESSION['flash_error'] = 'Order not found';
                header('Location: /admin/orders');
                exit;
            }
            
            // Get customer information
            $customer = null;
            if ($order['customerID']) {
                $customer = $this->customerModel->find($order['customerID']);
            }
            
            // Get order items with product details
            $pdo = $this->connectDatabase();
            $itemsSql = "SELECT oi.*, p.productName as product_name, p.productSKU as product_sku, 
                                pi.imagePath as image_path
                         FROM tbl_order_item oi
                         LEFT JOIN tbl_product p ON oi.productID = p.productID
                         LEFT JOIN tbl_product_image pi ON p.productID = pi.productID AND pi.isPrimary = 1
                         WHERE oi.orderID = :orderID
                         ORDER BY oi.orderItemID";
            $itemsStmt = $pdo->prepare($itemsSql);
            $itemsStmt->execute(['orderID' => $orderID]);
            $order['items'] = $itemsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $data = [
                'title' => 'Order #' . $order['order_number'] . ' - Order Details',
                'order' => $order,
                'customer' => $customer,
                'admin_user' => $this->adminUser,
                'success' => $_SESSION['flash_success'] ?? null,
                'error' => $_SESSION['flash_error'] ?? null
            ];
            
            // Clear flash messages
            unset($_SESSION['flash_success'], $_SESSION['flash_error']);
            
            $this->adminView('orders/view', $data);
            
        } catch (Exception $e) {
            error_log("Order view error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Error loading order details';
            header('Location: /admin/orders');
            exit;
        }
    }
    
    /**
     * Update order status
     */
    public function updateStatus() {
        $this->requirePermission('manage_orders');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        // Get orderID from URL parameter (sent by JavaScript)
        $orderID = intval($_GET['id'] ?? 0);
        $order_status = trim($_POST['status'] ?? '');
        $note = trim($_POST['note'] ?? '');
        
        if (!$orderID || empty($order_status)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order ID and status are required']);
            exit;
        }
        
        // Validate status
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($order_status, $validStatuses)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid order status']);
            exit;
        }
        
        try {
            $success = $this->orderModel->updateOrderStatus($orderID, $order_status);
            
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
            }
            
        } catch (Exception $e) {
            error_log("Order status update error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating order status']);
        }
        
        exit;
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus() {
        $this->requirePermission('manage_orders');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }
        
        // Get orderID from URL parameter (sent by JavaScript)
        $orderID = intval($_GET['id'] ?? 0);
        $payment_status = trim($_POST['payment_status'] ?? '');
        $note = trim($_POST['note'] ?? '');
        
        if (!$orderID || empty($payment_status)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order ID and payment status are required']);
            exit;
        }
        
        // Validate payment status
        $validStatuses = ['pending', 'paid', 'failed', 'refunded'];
        if (!in_array($payment_status, $validStatuses)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid payment status']);
            exit;
        }
        
        try {
            $success = $this->orderModel->updatePaymentStatus($orderID, $payment_status);
            
            if ($success) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Payment status updated successfully']);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update payment status']);
            }
            
        } catch (Exception $e) {
            error_log("Payment status update error: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error updating payment status']);
        }
        
        exit;
    }
    
    /**
     * Get order statistics
     */
    private function getOrderStatistics() {
        try {
            $pdo = $this->connectDatabase();
            
            $sql = "SELECT 
                        COUNT(*) as total_orders,
                        COUNT(CASE WHEN order_status = 'pending' THEN 1 END) as pending_orders,
                        COUNT(CASE WHEN order_status = 'delivered' THEN 1 END) as delivered_orders,
                        SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as total_revenue,
                        AVG(CASE WHEN payment_status = 'paid' THEN total_amount ELSE NULL END) as avg_order,
                        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as orders_today,
                        COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as orders_this_week,
                        COUNT(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as orders_this_month
                    FROM tbl_order";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Ensure avg_order is not null
            $result['avg_order'] = $result['avg_order'] ?: 0;
            $result['total_revenue'] = $result['total_revenue'] ?: 0;
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Order statistics error: " . $e->getMessage());
            return [
                'total_orders' => 0,
                'pending_orders' => 0,
                'delivered_orders' => 0,
                'total_revenue' => 0,
                'avg_order' => 0,
                'orders_today' => 0,
                'orders_this_week' => 0,
                'orders_this_month' => 0
            ];
        }
    }
    
    /**
     * AJAX endpoint to get order data
     */
    public function get() {
        header('Content-Type: application/json');
        
        $orderID = intval($_GET['id'] ?? 0);
        
        if (!$orderID) {
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            exit;
        }
        
        try {
            $order = $this->orderModel->getOrderWithDetails($orderID);
            
            if ($order) {
                echo json_encode(['success' => true, 'order' => $order]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Order not found']);
            }
            
        } catch (Exception $e) {
            error_log("Order get error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error loading order']);
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