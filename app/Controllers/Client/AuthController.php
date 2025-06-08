<?php
namespace App\Controllers\Client;

use App\Models\Customer;
use App\Models\Cart;
use App\Helpers\Helper;
use App\Helpers\PasswordHelper;
use Exception;

class AuthController extends ClientController {
    private $customerModel;
    private $cartModel;
    
    public function __construct() {
        parent::__construct();
        $this->customerModel = new Customer();
        $this->cartModel = new Cart();
    }
    
    /**
     * Show login form
     */
    public function showLogin() {
        // Redirect if already logged in
        if ($this->getCurrentUser()) {
            $this->redirect('/account');
        }
        
        $this->setData('page_title', 'Login - ' . APP_NAME);
        $this->view('auth.login');
    }
    
    /**
     * Handle login
     */
    public function login() {
        try {
            // Sanitize input
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);
            
            // Validate input
            if (empty($email) || empty($password)) {
                Helper::flash('error', 'Email and password are required');
                $this->redirect('/login');
                return;
            }
            
            // Additional validation
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Helper::flash('error', 'Invalid email format');
                $this->redirect('/login');
                return;
            }
            
            // Rate limiting check (simple implementation)
            $attemptKey = 'login_attempts_' . $email;
            $attempts = $_SESSION[$attemptKey] ?? 0;
            
            if ($attempts >= 5) {
                $lastAttempt = $_SESSION[$attemptKey . '_time'] ?? 0;
                if (time() - $lastAttempt < 900) { // 15 minutes lockout
                    Helper::flash('error', 'Too many failed attempts. Please try again in 15 minutes.');
                    $this->redirect('/login');
                    return;
                } else {
                    // Reset attempts after lockout period
                    unset($_SESSION[$attemptKey]);
                    unset($_SESSION[$attemptKey . '_time']);
                }
            }
            
            // Verify credentials
            $customer = $this->customerModel->verifyLogin($email, $password);
            
            if ($customer) {
                // Reset failed attempts
                unset($_SESSION[$attemptKey]);
                unset($_SESSION[$attemptKey . '_time']);
                
                // Set session
                $_SESSION['customer_id'] = $customer['customerID'];
                $_SESSION['customer_name'] = $customer['firstName'] . ' ' . $customer['lastName'];
                $_SESSION['customer_email'] = $customer['email'];
                
                // Update last login
                $this->customerModel->updateLastLogin($customer['customerID']);
                
                // Transfer session cart to customer account
                $sessionId = session_id();
                $this->cartModel->transferSessionCart($sessionId, $customer['customerID']);
                
                // Set remember me cookie if requested
                if ($rememberMe) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                    // TODO: Store token in database for security
                }
                
                Helper::flash('success', 'Welcome back, ' . $customer['firstName'] . '!');
                
                // Redirect to intended page or account
                $redirectTo = $_SESSION['intended_url'] ?? '/account';
                unset($_SESSION['intended_url']);
                $this->redirect($redirectTo);
            } else {
                // Increment failed attempts
                $_SESSION[$attemptKey] = $attempts + 1;
                $_SESSION[$attemptKey . '_time'] = time();
                
                Helper::flash('error', 'Invalid email or password');
                $this->redirect('/login');
            }
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            Helper::flash('error', 'An error occurred during login');
            $this->redirect('/login');
        }
    }
    
    /**
     * Show registration form
     */
    public function showRegister() {
        // Redirect if already logged in
        if ($this->getCurrentUser()) {
            $this->redirect('/account');
        }
        
        $this->setData('page_title', 'Register - ' . APP_NAME);
        $this->view('auth.register');
    }
    
    /**
     * Handle registration
     */
    public function register() {
        try {
            // Sanitize input
            $firstName = trim($_POST['firstName'] ?? '');
            $lastName = trim($_POST['lastName'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            $phone = trim($_POST['phone'] ?? '');
            $agreeTerms = isset($_POST['agree_terms']);
            
            // Validate input
            $errors = [];
            
            if (empty($firstName)) $errors[] = 'First name is required';
            if (empty($lastName)) $errors[] = 'Last name is required';
            if (empty($email)) $errors[] = 'Email is required';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
            if (empty($password)) $errors[] = 'Password is required';
            if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters';
            if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
            if (!$agreeTerms) $errors[] = 'You must agree to the terms and conditions';
            
            // Additional validation
            if (strlen($firstName) > 100) $errors[] = 'First name too long (max 100 characters)';
            if (strlen($lastName) > 100) $errors[] = 'Last name too long (max 100 characters)';
            if (strlen($email) > 255) $errors[] = 'Email address too long (max 255 characters)';
            if (!empty($phone) && strlen($phone) > 20) $errors[] = 'Phone number too long (max 20 characters)';
            
            // Check password strength
            $passwordValidation = PasswordHelper::validateStrength($password);
            if (!$passwordValidation['is_valid']) {
                $errors = array_merge($errors, $passwordValidation['errors']);
            }
            
            // Check if email already exists
            if ($this->customerModel->emailExists($email)) {
                $errors[] = 'Email address already exists';
            }
            
            if (!empty($errors)) {
                Helper::flash('error', implode('<br>', $errors));
                $this->redirect('/register');
                return;
            }
            
            // Create customer
            $customerData = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'password' => $password, // Will be hashed in model
                'phone' => $phone,
                'status' => 1
            ];
            
            $customerID = $this->customerModel->createCustomer($customerData);
            
            if ($customerID) {
                // Auto-login the user
                $_SESSION['customer_id'] = $customerID;
                $_SESSION['customer_name'] = $firstName . ' ' . $lastName;
                $_SESSION['customer_email'] = $email;
                
                // Transfer session cart to customer account
                $sessionId = session_id();
                $this->cartModel->transferSessionCart($sessionId, $customerID);
                
                Helper::flash('success', 'Registration successful! Welcome to OneStore, ' . $firstName . '!');
                $this->redirect('/account');
            } else {
                Helper::flash('error', 'Registration failed. Please try again.');
                $this->redirect('/register');
            }
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            Helper::flash('error', 'An error occurred during registration');
            $this->redirect('/register');
        }
    }
    
    /**
     * Handle logout
     */
    public function logout() {
        // Clear session
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_name']);
        unset($_SESSION['customer_email']);
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        Helper::flash('success', 'You have been logged out successfully');
        $this->redirect('/');
    }
    
    /**
     * Show forgot password form
     */
    public function showForgotPassword() {
        $this->setData('page_title', 'Forgot Password - ' . APP_NAME);
        $this->render('auth.forgot-password');
    }
    
    /**
     * Handle forgot password
     */
    public function forgotPassword() {
        try {
            $email = $_POST['email'] ?? '';
            
            if (empty($email)) {
                Helper::flash('error', 'Email is required');
                $this->redirect('/forgot-password');
                return;
            }
            
            $customer = $this->customerModel->findByEmail($email);
            
            if ($customer) {
                // TODO: Generate reset token and send email
                // For now, just show success message
                Helper::flash('success', 'If an account with that email exists, we\'ve sent password reset instructions.');
            } else {
                // Don't reveal if email exists or not for security
                Helper::flash('success', 'If an account with that email exists, we\'ve sent password reset instructions.');
            }
            
            $this->redirect('/forgot-password');
            
        } catch (Exception $e) {
            Helper::flash('error', 'An error occurred. Please try again.');
            $this->redirect('/forgot-password');
        }
    }
    
    /**
     * Show customer account page
     */
    public function account() {
        // Require authentication
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $this->setData('page_title', 'My Account - ' . APP_NAME);
        $this->setData('user', $user);
        
        $this->view('pages.account');
    }
} 