<?php
namespace App\Controllers\Client;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Helpers\Helper;
use Exception;

class CheckoutController extends ClientController {
    private $cartModel;
    private $orderModel;
    private $customerModel;
    private $productModel;
    
    public function __construct() {
        parent::__construct();
        $this->cartModel = new Cart();
        $this->orderModel = new Order();
        $this->customerModel = new Customer();
        $this->productModel = new Product();
    }
    
    /**
     * Show checkout page
     */
    public function index() {
        $cartItems = $this->getCartItems();
        
        if (empty($cartItems)) {
            // Instead of showing an error, redirect to cart page which will show nice empty state
            $this->redirect('/cart');
            return;
        }
        
        $subtotal = $this->calculateSubtotal($cartItems);
        $shipping = 10.00; // Fixed shipping for now
        $tax = $subtotal * 0.1; // 10% tax
        $total = $subtotal + $shipping + $tax;
        
        // Get customer info if logged in
        $customer = null;
        $user = $this->getCurrentUser();
        
        if ($user) {
            // Try to get full customer data with addresses
            $customer = $this->customerModel->getCustomerWithAddresses($user['id']);
            
            // If that fails, create customer data from session
            if (!$customer) {
                $customer = [
                    'customerID' => $user['id'],
                    'firstName' => explode(' ', $user['name'])[0] ?? '',
                    'lastName' => explode(' ', $user['name'])[1] ?? '',
                    'email' => $user['email'],
                    'phone' => '',
                    'addresses' => []
                ];
            }
        }
        
        // Set page data
        $this->setData('cart_items', $cartItems);
        $this->setData('subtotal', $subtotal);
        $this->setData('shipping', $shipping);
        $this->setData('tax', $tax);
        $this->setData('total', $total);
        $this->setData('customer', $customer);
        $this->setData('paypal_client_id', $this->getPayPalClientId());
        
        // Set page metadata
        $this->setTitle('Checkout');
        $this->setMeta('Complete your order checkout', 'checkout, order, payment');
        
        // Add breadcrumbs
        $this->addBreadcrumb('Shop', '/shop');
        $this->addBreadcrumb('Checkout');
        
        $this->view('pages.checkout');
    }
    
    /**
     * Process checkout
     */
    public function process() {
        try {
            $cartItems = $this->getCartItems();
            
            if (empty($cartItems)) {
                echo json_encode(['success' => false, 'message' => 'Cart is empty']);
                return;
            }
            
            // Validate stock availability
            foreach ($cartItems as $item) {
                $product = $this->productModel->find($item['productID']);
                if (!$product || $product['stock_quantity'] < $item['quantity']) {
                    echo json_encode(['success' => false, 'message' => 'Some items are out of stock']);
                    return;
                }
            }
            
            // Get form data
            $billingData = [
                'firstName' => $_POST['billing_first_name'] ?? '',
                'lastName' => $_POST['billing_last_name'] ?? '',
                'email' => $_POST['billing_email'] ?? '',
                'phone' => $_POST['billing_phone'] ?? '',
                'address1' => $_POST['billing_address1'] ?? '',
                'address2' => $_POST['billing_address2'] ?? '',
                'city' => $_POST['billing_city'] ?? '',
                'state' => $_POST['billing_state'] ?? '',
                'postal_code' => $_POST['billing_postal_code'] ?? '',
                'country' => $_POST['billing_country'] ?? 'US'
            ];
            
            // Get payment method
            $paymentMethod = $_POST['payment_method'] ?? 'paypal';
            
            $shippingData = $billingData; // Default to billing
            if (isset($_POST['different_shipping']) && $_POST['different_shipping']) {
                $shippingData = [
                    'firstName' => $_POST['shipping_first_name'] ?? '',
                    'lastName' => $_POST['shipping_last_name'] ?? '',
                    'address1' => $_POST['shipping_address1'] ?? '',
                    'address2' => $_POST['shipping_address2'] ?? '',
                    'city' => $_POST['shipping_city'] ?? '',
                    'state' => $_POST['shipping_state'] ?? '',
                    'postal_code' => $_POST['shipping_postal_code'] ?? '',
                    'country' => $_POST['shipping_country'] ?? 'US'
                ];
            }
            
            // Calculate totals
            $subtotal = $this->calculateSubtotal($cartItems);
            $shipping = 10.00;
            $tax = $subtotal * 0.1;
            $total = $subtotal + $shipping + $tax;
            
            // Create order
            $user = $this->getCurrentUser();
            $orderData = [
                'customerID' => $user ? $user['id'] : null,
                'order_number' => $this->generateOrderNumber(),
                'billing_address' => $this->formatAddress($billingData),
                'shipping_address' => $this->formatAddress($shippingData),
                'subtotal' => $subtotal,
                'shipping_amount' => $shipping,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'currency' => 'USD',
                'order_status' => 'pending',
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'notes' => $_POST['order_notes'] ?? null
            ];
            
            $orderID = $this->orderModel->createOrder($orderData);
            
            if (!$orderID) {
                echo json_encode(['success' => false, 'message' => 'Failed to create order']);
                return;
            }
            
            // Add order items
            $orderItems = [];
            foreach ($cartItems as $item) {
                $orderItems[] = [
                    'productID' => $item['productID'],
                    'product_name' => $item['name'],
                    'product_sku' => '', // TODO: Add SKU to products
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total']
                ];
            }
            
            $this->orderModel->addOrderItems($orderID, $orderItems);
            
            // Handle different payment methods
            if ($paymentMethod === 'cod') {
                // For Cash on Delivery, mark as confirmed and redirect to confirmation
                $this->orderModel->updateStatus($orderID, 'confirmed');
                
                // Clear cart
                $user = $this->getCurrentUser();
                $customerID = $user ? $user['id'] : null;
                $sessionId = $customerID ? null : session_id();
                $this->cartModel->clearCart($customerID, $sessionId);
                
                echo json_encode([
                    'success' => true,
                    'order_id' => $orderID,
                    'payment_method' => 'cod',
                    'message' => 'Order placed successfully! You will pay upon delivery.'
                ]);
            } elseif ($paymentMethod === 'paypal') {
                // For PayPal, mark as processing (payment already captured)
                $this->orderModel->updateStatus($orderID, 'processing');
                $this->orderModel->updatePaymentStatus($orderID, 'paid');
                
                // Update product stock
                foreach ($cartItems as $item) {
                    $product = $this->productModel->find($item['productID']);
                    if ($product) {
                        $newStock = $product['stock_quantity'] - $item['quantity'];
                        $this->productModel->update($item['productID'], ['stock_quantity' => max(0, $newStock)]);
                    }
                }
                
                // Clear cart
                $user = $this->getCurrentUser();
                $customerID = $user ? $user['id'] : null;
                $sessionId = $customerID ? null : session_id();
                $this->cartModel->clearCart($customerID, $sessionId);
                
                echo json_encode([
                    'success' => true,
                    'order_id' => $orderID,
                    'payment_method' => 'paypal',
                    'message' => 'PayPal payment successful!'
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'An error occurred during checkout']);
        }
    }
    
    /**
     * Show PayPal payment page
     */
    public function showPayPal() {
        $orderID = $_SESSION['checkout_order_id'] ?? null;
        
        if (!$orderID) {
            Helper::flash('error', 'No active order found');
            $this->redirect('/cart');
            return;
        }
        
        $order = $this->orderModel->getOrderWithItems($orderID);
        if (!$order) {
            Helper::flash('error', 'Order not found');
            $this->redirect('/cart');
            return;
        }
        
        $this->setData('order', $order);
        $this->setData('paypal_client_id', $this->getPayPalClientId());
        $this->setData('page_title', 'PayPal Payment - ' . APP_NAME);
        
        $this->render('pages.paypal-checkout');
    }
    
    /**
     * Handle PayPal success
     */
    public function paypalSuccess() {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $paymentID = $input['paymentID'] ?? null;
            $payerID = $input['payerID'] ?? null;
            $orderID = $_SESSION['checkout_order_id'] ?? null;
            
            if (!$paymentID || !$payerID || !$orderID) {
                echo json_encode(['success' => false, 'message' => 'Missing payment information']);
                return;
            }
            
            // Verify payment with PayPal
            $paymentDetails = $this->verifyPayPalPayment($paymentID, $payerID);
            
            if ($paymentDetails && $paymentDetails['state'] === 'approved') {
                // Update order status
                $this->orderModel->updatePaymentStatus($orderID, 'completed', $paymentID);
                $this->orderModel->updateStatus($orderID, 'processing');
                
                // Update product stock
                $order = $this->orderModel->getOrderWithItems($orderID);
                foreach ($order['items'] as $item) {
                    $product = $this->productModel->find($item['productID']);
                    if ($product) {
                        $newStock = $product['stock_quantity'] - $item['quantity'];
                        $this->productModel->update($item['productID'], ['stock_quantity' => max(0, $newStock)]);
                    }
                }
                
                // Clear cart
                $user = $this->getCurrentUser();
                $customerID = $user ? $user['id'] : null;
                $sessionId = $customerID ? null : session_id();
                $this->cartModel->clearCart($customerID, $sessionId);
                
                // Clear session
                unset($_SESSION['checkout_order_id']);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment successful',
                    'redirect_url' => '/order-confirmation/' . $orderID
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Payment verification failed']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Payment processing error']);
        }
    }
    
    /**
     * Show order confirmation
     */
    public function orderConfirmation($orderID) {
        $order = $this->orderModel->getOrderWithItems($orderID);
        
        if (!$order) {
            Helper::flash('error', 'Order not found');
            $this->redirect('/');
            return;
        }
        
        // Check if user can view this order
        $user = $this->getCurrentUser();
        if ($order['customerID'] && (!$user || $user['id'] != $order['customerID'])) {
            Helper::flash('error', 'You do not have permission to view this order');
            $this->redirect('/');
            return;
        }
        
        // Set page data
        $this->setData('order', $order);
        
        // Set page metadata
        $this->setTitle('Order Confirmation - Order #' . $order['order_number']);
        $this->setMeta('Your order has been confirmed. Thank you for shopping with us!', 'order, confirmation, receipt');
        
        // Add breadcrumbs
        $this->addBreadcrumb('Shop', '/shop');
        $this->addBreadcrumb('Order Confirmation');
        
        $this->view('pages.order-confirmation');
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
    
    /**
     * Generate order number
     */
    private function generateOrderNumber() {
        return 'ORD-' . date('YmdHis') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get PayPal client ID
     */
    private function getPayPalClientId() {
        return defined('PAYPAL_CLIENT_ID') ? PAYPAL_CLIENT_ID : '';
    }
    
    /**
     * Verify PayPal payment
     */
    private function verifyPayPalPayment($paymentID, $payerID) {
        // TODO: Implement actual PayPal API verification
        // For demo purposes, return approved
        return [
            'id' => $paymentID,
            'state' => 'approved',
            'payer' => ['payer_info' => ['payer_id' => $payerID]]
        ];
    }
    
    /**
     * Format address
     */
    private function formatAddress($addressData) {
        return trim(implode(', ', array_filter([
            $addressData['firstName'] . ' ' . $addressData['lastName'],
            $addressData['address1'],
            $addressData['address2'] ?? '',
            $addressData['city'],
            $addressData['state'],
            $addressData['postal_code'],
            $addressData['country'],
            !empty($addressData['phone']) ? 'Phone: ' . $addressData['phone'] : '',
            !empty($addressData['email']) ? 'Email: ' . $addressData['email'] : ''
        ])));
    }
    
    /**
     * Debug customer data - TEMPORARY
     */
    public function debugCustomer() {
        header('Content-Type: application/json');
        
        $user = $this->getCurrentUser();
        $customer = null;
        
        if ($user) {
            $customer = $this->customerModel->getCustomerWithAddresses($user['id']);
        }
        
        $debug = [
            'session_data' => [
                'customer_id' => $_SESSION['customer_id'] ?? 'not set',
                'customer_name' => $_SESSION['customer_name'] ?? 'not set', 
                'customer_email' => $_SESSION['customer_email'] ?? 'not set'
            ],
            'getCurrentUser_result' => $user,
            'customer_from_db' => $customer,
            'session_id' => session_id(),
            'all_session' => $_SESSION
        ];
        
        echo json_encode($debug, JSON_PRETTY_PRINT);
        exit;
    }
} 