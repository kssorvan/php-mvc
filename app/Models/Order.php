<?php
namespace App\Models;

use PDO;
use Exception;

class Order extends BaseModel {
    protected $table = 'tbl_order';
    protected $primaryKey = 'orderID';
    protected $fillable = [
        'customerID',
        'order_number',
        'order_status',
        'payment_status',
        'payment_method',
        'subtotal',
        'tax_amount',
        'shipping_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'billing_address',
        'shipping_address',
        'notes',
        'shipped_at',
        'delivered_at'
    ];
    
    /**
     * Create a new order
     */
    public function createOrder($data) {
        try {
            // Start transaction
            $this->pdo->beginTransaction();
            
            // Generate order number if not provided
            if (!isset($data['order_number'])) {
                $data['order_number'] = $this->generateOrderNumber();
            }
            
            // Set default status if not provided
            if (!isset($data['order_status'])) {
                $data['order_status'] = 'pending';
            }
            
            // Create order
            $orderID = $this->create($data);
            
            if (!$orderID) {
                $this->pdo->rollBack();
                return false;
            }
            
            $this->pdo->commit();
            return $orderID;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Order createOrder error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add order items
     */
    public function addOrderItems($orderID, $items) {
        try {
            $sql = "INSERT INTO tbl_order_item (orderID, productID, product_name, product_sku, quantity, price, total) 
                    VALUES (:orderID, :productID, :product_name, :product_sku, :quantity, :price, :total)";
            $stmt = $this->pdo->prepare($sql);
            
            foreach ($items as $item) {
                $total = $item['quantity'] * $item['price'];
                $stmt->execute([
                    'orderID' => $orderID,
                    'productID' => $item['productID'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $total
                ]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Order addOrderItems error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get order with items
     */
    public function getOrderWithItems($orderID) {
        try {
            $order = $this->find($orderID);
            if (!$order) {
                return false;
            }
            
            // Get order items
            $sql = "SELECT oi.*, p.image_path 
                    FROM tbl_order_item oi 
                    LEFT JOIN tbl_product p ON oi.productID = p.productID 
                    WHERE oi.orderID = :orderID 
                    ORDER BY oi.created_at ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['orderID' => $orderID]);
            $order['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $order;
        } catch (Exception $e) {
            error_log("Order getOrderWithItems error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get customer orders
     */
    public function getCustomerOrders($customerID, $limit = 10, $offset = 0) {
        try {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE customerID = :customerID 
                    ORDER BY created_at DESC 
                    LIMIT :limit OFFSET :offset";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Order getCustomerOrders error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update order status
     */
    public function updateStatus($orderID, $status) {
        try {
            $sql = "UPDATE {$this->table} SET order_status = :order_status, updated_at = NOW() WHERE orderID = :orderID";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'order_status' => $status,
                'orderID' => $orderID
            ]);
        } catch (Exception $e) {
            error_log("Order updateStatus error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($orderID, $paymentStatus, $transactionId = null) {
        try {
            $sql = "UPDATE {$this->table} SET payment_status = :payment_status, updated_at = NOW() WHERE orderID = :orderID";
            $params = ['payment_status' => $paymentStatus, 'orderID' => $orderID];
            
            // Note: transaction_id field doesn't exist in current schema
            // If needed, add it to the database first
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Order updatePaymentStatus error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate unique order number
     */
    private function generateOrderNumber() {
        return 'ORD-' . date('YmdHis') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get order statistics
     */
    public function getOrderStats() {
        try {
            $stats = [];
            
            // Total orders
            $sql = "SELECT COUNT(*) as total_orders FROM {$this->table}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
            
            // Total revenue
            $sql = "SELECT SUM(total_amount) as total_revenue FROM {$this->table} WHERE payment_status = 'completed'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;
            
            // Orders by status
            $sql = "SELECT order_status, COUNT(*) as count FROM {$this->table} GROUP BY order_status";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        } catch (Exception $e) {
            error_log("Order getOrderStats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get order with details (wrapper for getOrderWithItems for OrderController compatibility)
     */
    public function getOrderWithDetails($orderID) {
        return $this->getOrderWithItems($orderID);
    }
    
    /**
     * Update order status (wrapper for updateStatus for OrderController compatibility)
     */
    public function updateOrderStatus($orderID, $status) {
        return $this->updateStatus($orderID, $status);
    }
} 