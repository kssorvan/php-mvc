<?php
namespace App\Controllers\Client;

use App\Models\Cart;
use App\Models\Product;
use App\Helpers\Helper;
use Exception;

class CartController extends ClientController {
    private $cartModel;
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }
    
    /**
     * Display cart page
     */
    public function index() {
        $cartItems = $this->getCartItems();
        $subtotal = $this->calculateSubtotal($cartItems);
        $shipping = 10.00; // Fixed shipping for now
        $total = $subtotal + $shipping;
        
        // If cart is empty, clear any existing error flash messages
        // We want to show our nice empty cart state instead of flash errors
        if (empty($cartItems)) {
            unset($_SESSION['flash']['error']);
            // Also clear any specific cart empty messages
            $this->data['flash_messages'] = [];
        }
        
        $this->setData('cart_items', $cartItems);
        $this->setData('subtotal', $subtotal);
        $this->setData('shipping', $shipping);
        $this->setData('total', $total);
        $this->setData('page_title', 'Shopping Cart - ' . APP_NAME);
        
        $this->view('pages.cart');
    }
    
    /**
     * Add item to cart (AJAX)
     */
    public function add() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $productID = $input['product_id'] ?? null;
            $quantity = $input['quantity'] ?? 1;
            
            if (!$productID) {
                echo json_encode(['success' => false, 'message' => 'Product ID is required']);
                return;
            }
            
            // Validate product exists and has stock
            $product = $this->productModel->find($productID);
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                return;
            }
            
            if ($product['stock_quantity'] < $quantity) {
                echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                return;
            }
            
            // Get customer ID or session ID
            $user = $this->getCurrentUser();
            $customerID = $user ? $user['id'] : null;
            $sessionId = $customerID ? null : session_id();
            
            // Add to cart
            $result = $this->cartModel->addItem($customerID, $sessionId, $productID, $quantity);
            
            if ($result) {
                $cartItems = $this->getCartItems();
                $cartCount = array_reduce($cartItems, function($sum, $item) {
                    return $sum + $item['quantity'];
                }, 0);
                $cartTotal = $this->calculateSubtotal($cartItems);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Item added to cart',
                    'cart_count' => $cartCount,
                    'cart_total' => Helper::formatCurrency($cartTotal)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add item to cart']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Update cart item quantity (AJAX)
     */
    public function update() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $cartID = $input['cart_id'] ?? null;
            $productID = $input['productID'] ?? $input['product_id'] ?? null;
            $quantity = intval($input['quantity'] ?? 1);
            
            // Handle update by product ID or cart ID
            if ($productID && !$cartID) {
                // Find cart item by product ID
                $user = $this->getCurrentUser();
                $customerID = $user ? $user['id'] : null;
                $sessionId = $customerID ? null : session_id();
                
                // If quantity is 0, remove the item
                if ($quantity <= 0) {
                    $result = $this->cartModel->removeByProduct($customerID, $sessionId, $productID);
                    if ($result) {
                        $cartItems = $this->getCartItems();
                        $cartCount = array_reduce($cartItems, function($sum, $item) {
                            return $sum + $item['quantity'];
                        }, 0);
                        $cartTotal = $this->calculateSubtotal($cartItems);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Item removed from cart',
                            'action' => 'removed',
                            'cart_count' => $cartCount,
                            'cart_total' => Helper::formatCurrency($cartTotal),
                            'cart_items' => $cartItems
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
                    }
                    return;
                }
                
                // Update by product ID
                $result = $this->cartModel->updateByProduct($customerID, $sessionId, $productID, $quantity);
            } else if ($cartID) {
                // If quantity is 0, remove the item
                if ($quantity <= 0) {
                    $result = $this->cartModel->removeItem($cartID);
                    if ($result) {
                        $cartItems = $this->getCartItems();
                        $cartCount = array_reduce($cartItems, function($sum, $item) {
                            return $sum + $item['quantity'];
                        }, 0);
                        $cartTotal = $this->calculateSubtotal($cartItems);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Item removed from cart',
                            'action' => 'removed',
                            'cart_count' => $cartCount,
                            'cart_total' => Helper::formatCurrency($cartTotal),
                            'cart_items' => $cartItems
                        ]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to remove item']);
                    }
                    return;
                }
                
                // Update by cart ID
                $result = $this->cartModel->updateQuantity($cartID, $quantity);
            } else {
                echo json_encode(['success' => false, 'message' => 'Cart ID or Product ID is required']);
                return;
            }
            
            if ($result) {
                $user = $this->getCurrentUser();
                $customerID = $user ? $user['id'] : null;
                $sessionId = $customerID ? null : session_id();
                
                $cartItems = $this->getCartItems();
                $cartCount = array_reduce($cartItems, function($sum, $item) {
                    return $sum + $item['quantity'];
                }, 0);
                $cartTotal = $this->calculateSubtotal($cartItems);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Cart updated',
                    'action' => 'updated',
                    'cart_count' => $cartCount,
                    'cart_total' => Helper::formatCurrency($cartTotal),
                    'cart_items' => $cartItems
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Remove item from cart (AJAX)
     */
    public function remove() {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $cartID = $input['cart_id'] ?? null;
            $productID = $input['product_id'] ?? null;
            
            if (!$cartID && !$productID) {
                echo json_encode(['success' => false, 'message' => 'Cart ID or Product ID is required']);
                return;
            }
            
            $result = false;
            
            if ($cartID) {
                // Remove by cart ID (more specific)
                $result = $this->cartModel->removeItem($cartID);
                if (!$result) {
                    echo json_encode(['success' => false, 'message' => 'Failed to remove item by cart ID: ' . $cartID]);
                    return;
                }
            } else {
                // Remove by product ID (for backward compatibility)
                $user = $this->getCurrentUser();
                $customerID = $user ? $user['id'] : null;
                $sessionId = $customerID ? null : session_id();
                $result = $this->cartModel->removeByProduct($customerID, $sessionId, $productID);
                if (!$result) {
                    echo json_encode(['success' => false, 'message' => 'Failed to remove item by product ID: ' . $productID]);
                    return;
                }
            }
            
            // Get updated cart data
            $user = $this->getCurrentUser();
            $customerID = $user ? $user['id'] : null;
            $sessionId = $customerID ? null : session_id();
            
            $cartItems = $this->getCartItems();
            $cartCount = array_reduce($cartItems, function($sum, $item) {
                return $sum + $item['quantity'];
            }, 0);
            $cartTotal = $this->calculateSubtotal($cartItems);
            
            echo json_encode([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart_count' => $cartCount,
                'cart_total' => Helper::formatCurrency($cartTotal)
            ]);
            
        } catch (Exception $e) {
            error_log("Cart remove error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Clear entire cart
     */
    public function clear() {
        header('Content-Type: application/json');
        
        try {
            $user = $this->getCurrentUser();
            $customerID = $user ? $user['id'] : null;
            $sessionId = $customerID ? null : session_id();
            
            $result = $this->cartModel->clearCart($customerID, $sessionId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cart cleared',
                    'cart_count' => 0,
                    'cart_total' => Helper::formatCurrency(0)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to clear cart']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred']);
        }
    }
    
    /**
     * Get cart data (AJAX)
     */
    public function get() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        header('Content-Type: application/json');
        
        try {
            $cartItems = $this->getCartItems();
            $subtotal = $this->calculateSubtotal($cartItems);
            $shipping = $subtotal > 100 ? 0 : 10.00; // Free shipping over $100
            $total = $subtotal + $shipping;
            
            // Use reduce-style calculation for total items
            $totalItems = array_reduce($cartItems, function($sum, $item) {
                return $sum + $item['quantity'];
            }, 0);
            
            echo json_encode([
                'success' => true,
                'cart_items' => $cartItems,
                'cart_totals' => [
                    'subtotal' => $subtotal,
                    'shipping' => $shipping,
                    'total' => $total,
                    'total_items' => $totalItems
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Cart get error: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Error loading cart',
                'cart_items' => [],
                'cart_totals' => ['subtotal' => 0, 'shipping' => 0, 'total' => 0, 'total_items' => 0]
            ]);
        }
        
        exit;
    }
    
    /**
     * Get cart items for current user/session
     */
    private function getCartItems() {
        $user = $this->getCurrentUser();
        $customerID = $user ? $user['id'] : null;
        $sessionId = $customerID ? null : session_id();
        
        return $this->cartModel->getCartItems($customerID, $sessionId);
    }
    
    /**
     * Calculate subtotal
     */
    private function calculateSubtotal($cartItems) {
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['total'];
        }
        return $subtotal;
    }
} 