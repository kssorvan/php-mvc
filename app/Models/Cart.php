<?php
namespace App\Models;

use PDO;
use Exception;

class Cart extends BaseModel {
    protected $table = 'tbl_cart';
    protected $primaryKey = 'cartID';
    
    /**
     * Add item to cart
     */
    public function addItem($customerID, $sessionId, $productID, $quantity = 1) {
        try {
            // Check if item already exists in cart
            $existingItem = $this->findByCustomerAndProduct($customerID, $sessionId, $productID);
            
            if ($existingItem) {
                // Update quantity
                return $this->updateQuantity($existingItem['cartID'], $existingItem['quantity'] + $quantity);
            } else {
                // Add new item
                $data = [
                    'customerID' => $customerID,
                    'session_id' => $sessionId,
                    'productID' => $productID,
                    'quantity' => $quantity
                ];
                return $this->create($data);
            }
        } catch (Exception $e) {
            error_log("Cart addItem error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find cart item by customer and product
     */
    public function findByCustomerAndProduct($customerID, $sessionId, $productID) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE ";
            $params = [];
            
            if ($customerID) {
                $sql .= "customerID = :customerID";
                $params['customerID'] = $customerID;
            } else {
                $sql .= "session_id = :session_id";
                $params['session_id'] = $sessionId;
            }
            
            $sql .= " AND productID = :productID";
            $params['productID'] = $productID;
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Cart findByCustomerAndProduct error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cart items for customer or session
     */
    public function getCartItems($customerID = null, $sessionId = null) {
        try {
            $sql = "SELECT c.*, p.productName as name, p.price, p.image_path, p.stock_quantity,
                           (c.quantity * p.price) as total,
                           c.productID as product_id
                    FROM {$this->table} c
                    JOIN tbl_product p ON c.productID = p.productID 
                    WHERE ";
            $params = [];
            
            if ($customerID) {
                $sql .= "c.customerID = :customerID";
                $params['customerID'] = $customerID;
            } else {
                $sql .= "c.session_id = :session_id";
                $params['session_id'] = $sessionId;
            }
            
            $sql .= " ORDER BY c.created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Cart getCartItems error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update item quantity
     */
    public function updateQuantity($cartID, $quantity) {
        try {
            if ($quantity <= 0) {
                return $this->removeItem($cartID);
            }
            
            $sql = "UPDATE {$this->table} SET quantity = :quantity, updated_at = NOW() WHERE cartID = :cartID";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'quantity' => $quantity,
                'cartID' => $cartID
            ]);
        } catch (Exception $e) {
            error_log("Cart updateQuantity error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update item quantity by product ID
     */
    public function updateByProduct($customerID, $sessionId, $productID, $quantity) {
        try {
            if ($quantity <= 0) {
                return $this->removeByProduct($customerID, $sessionId, $productID);
            }
            
            $sql = "UPDATE {$this->table} SET quantity = :quantity, updated_at = NOW() WHERE productID = :productID AND ";
            $params = [
                'quantity' => $quantity,
                'productID' => $productID
            ];
            
            if ($customerID) {
                $sql .= "customerID = :customerID";
                $params['customerID'] = $customerID;
            } else {
                $sql .= "session_id = :session_id";
                $params['session_id'] = $sessionId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Cart updateByProduct error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem($cartID) {
        try {
            return $this->delete($cartID);
        } catch (Exception $e) {
            error_log("Cart removeItem error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Remove item by product ID (for backward compatibility)
     */
    public function removeByProduct($customerID, $sessionId, $productID) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE productID = :productID AND ";
            $params = ['productID' => $productID];
            
            if ($customerID) {
                $sql .= "customerID = :customerID";
                $params['customerID'] = $customerID;
            } else {
                $sql .= "session_id = :session_id";
                $params['session_id'] = $sessionId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Cart removeByProduct error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear cart for customer or session
     */
    public function clearCart($customerID = null, $sessionId = null) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE ";
            $params = [];
            
            if ($customerID) {
                $sql .= "customerID = :customerID";
                $params['customerID'] = $customerID;
            } else {
                $sql .= "session_id = :session_id";
                $params['session_id'] = $sessionId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Cart clearCart error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get cart total
     */
    public function getCartTotal($customerID = null, $sessionId = null) {
        try {
            $sql = "SELECT SUM(c.quantity * p.price) as total
                    FROM {$this->table} c
                    JOIN tbl_product p ON c.productID = p.productID 
                    WHERE ";
            $params = [];
            
            if ($customerID) {
                $sql .= "c.customerID = :customerID";
                $params['customerID'] = $customerID;
            } else {
                $sql .= "c.session_id = :session_id";
                $params['session_id'] = $sessionId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Cart getCartTotal error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get cart count
     */
    public function getCartCount($customerID = null, $sessionId = null) {
        try {
            $sql = "SELECT SUM(quantity) as count FROM {$this->table} WHERE ";
            $params = [];
            
            if ($customerID) {
                $sql .= "customerID = :customerID";
                $params['customerID'] = $customerID;
            } else {
                $sql .= "session_id = :session_id";
                $params['session_id'] = $sessionId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Cart getCartCount error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Transfer session cart to customer account
     */
    public function transferSessionCart($sessionId, $customerID) {
        try {
            $sql = "UPDATE {$this->table} SET customerID = :customerID, session_id = NULL 
                    WHERE session_id = :session_id AND customerID IS NULL";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                'customerID' => $customerID,
                'session_id' => $sessionId
            ]);
        } catch (Exception $e) {
            error_log("Cart transferSessionCart error: " . $e->getMessage());
            return false;
        }
    }
} 