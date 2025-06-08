<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Helpers\Helper;
use App\Helpers\PasswordHelper;

class AuthController extends BaseController {
    
    public function __construct() {
        // Don't call parent::__construct() to skip admin auth check
        // Initialize database and other base functionality manually
        $this->initializeBase();
    }
    
    private function initializeBase() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    protected function getViewPath($view) {
        return __DIR__ . '/../../Views/Admin/' . $view . '.php';
    }
    
    /**
     * Show login form
     */
    public function showLogin() {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
            header('Location: ' . Helper::adminUrl('dashboard'));
            exit;
        }
        
        $data = [
            'title' => 'Admin Login - OneStore',
            'error' => $_SESSION['flash_error'] ?? null,
            'success' => $_SESSION['flash_success'] ?? null
        ];
        
        // Clear flash messages
        unset($_SESSION['flash_error']);
        unset($_SESSION['flash_success']);
        
        $this->adminView('auth/login', $data);
    }
    
    /**
     * Process login
     */
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . Helper::adminUrl('login'));
            exit;
        }
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = 'Please enter both username and password';
            header('Location: ' . Helper::adminUrl('login'));
            exit;
        }
        
        try {
            // Create database connection with auto-setup
            $pdo = $this->setupDatabase();
            
            if (!$pdo) {
                throw new \Exception('Database connection failed');
            }
            
            $stmt = $pdo->prepare("SELECT * FROM tbl_admin WHERE username = ? AND status = 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($admin && PasswordHelper::verify($password, $admin['password'])) {
                // Check if password needs rehashing (for security upgrades)
                if (PasswordHelper::needsRehash($admin['password'])) {
                    $newHash = PasswordHelper::hash($password);
                    $updateStmt = $pdo->prepare("UPDATE tbl_admin SET password = ? WHERE adminID = ?");
                    $updateStmt->execute([$newHash, $admin['adminID']]);
                }
                
                // Login successful
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['adminID'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_last_activity'] = time();
                
                // Update last login
                $stmt = $pdo->prepare("UPDATE tbl_admin SET last_login = NOW() WHERE adminID = ?");
                $stmt->execute([$admin['adminID']]);
                
                $_SESSION['flash_success'] = 'Welcome back, ' . ($admin['firstName'] ?? 'Administrator');
                header('Location: ' . Helper::adminUrl('dashboard'));
                exit;
                
            } else {
                $_SESSION['flash_error'] = 'Invalid username or password';
                header('Location: ' . Helper::adminUrl('login'));
                exit;
            }
            
        } catch (\Exception $e) {
            error_log("Admin login error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
            header('Location: ' . Helper::adminUrl('login'));
            exit;
        }
    }
    
    /**
     * Setup database connection and create database if needed
     */
    private function setupDatabase() {
        try {
            // Use centralized connection for normal operations
            
            $pdo = connectToDatabase();
            return $pdo;
            
        } catch (\Exception $e) {
            // If centralized connection fails, try to create database
            try {
                $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
                $tempPdo = new \PDO($dsn, DB_USER, DB_PASS, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
                ]);
                
                // Create database if it doesn't exist
                $tempPdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                $tempPdo->exec("USE " . DB_NAME);
                
                return $tempPdo;
                
            } catch (\PDOException $e) {
                error_log("Database setup error: " . $e->getMessage());
                return null;
            }
        }
    }
    
    /**
     * Logout admin
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear admin session data
        unset($_SESSION['admin_logged_in']);
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_last_activity']);
        
        $_SESSION['flash_success'] = 'You have been logged out successfully';
        
        // Use APP_URL to ensure correct environment-aware redirect
        $loginUrl = APP_URL . '/admin/login';
        header('Location: ' . $loginUrl);
        exit;
    }
    
    /**
     * Create admin_users table if it doesn't exist
     */
    private function createAdminTable($pdo) {
        $sql = "CREATE TABLE IF NOT EXISTS admin_users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            role ENUM('admin', 'manager') DEFAULT 'admin',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL
        )";
        
        $pdo->exec($sql);
        
        // Check if default admin exists, if not create one
        $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
        $count = $stmt->fetchColumn();
        
        if ($count == 0) {
            // Create default admin (username: admin, password: admin123)
            $defaultPassword = PasswordHelper::hash('admin123');
            $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, name, email, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['admin', $defaultPassword, 'Administrator', 'admin@onestore.com', 'admin']);
        }
    }
    
    /**
     * Change password functionality
     */
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request method';
            header('Location: ' . Helper::adminUrl('profile'));
            exit;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['flash_error'] = 'All password fields are required';
            header('Location: ' . Helper::adminUrl('profile'));
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error'] = 'New passwords do not match';
            header('Location: ' . Helper::adminUrl('profile'));
            exit;
        }
        
        // Validate password strength
        $strengthCheck = PasswordHelper::validateStrength($newPassword);
        if (!$strengthCheck['is_valid']) {
            $_SESSION['flash_error'] = 'Password not strong enough: ' . implode(', ', $strengthCheck['errors']);
            header('Location: ' . Helper::adminUrl('profile'));
            exit;
        }
        
        try {
            $pdo = $this->setupDatabase();
            
            // Get current admin
            $stmt = $pdo->prepare("SELECT * FROM tbl_admin WHERE adminID = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$admin || !PasswordHelper::verify($currentPassword, $admin['password'])) {
                $_SESSION['flash_error'] = 'Current password is incorrect';
                header('Location: ' . Helper::adminUrl('profile'));
                exit;
            }
            
            // Update password
            $hashedPassword = PasswordHelper::hash($newPassword);
            $updateStmt = $pdo->prepare("UPDATE tbl_admin SET password = ? WHERE adminID = ?");
            $success = $updateStmt->execute([$hashedPassword, $_SESSION['admin_id']]);
            
            if ($success) {
                $_SESSION['flash_success'] = 'Password changed successfully';
            } else {
                $_SESSION['flash_error'] = 'Failed to update password';
            }
            
        } catch (\Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'An error occurred while changing password';
        }
        
        header('Location: ' . Helper::adminUrl('profile'));
        exit;
    }
    
    /**
     * Render admin view without authentication check
     */
    private function adminView($view, $data = []) {
        extract($data);
        
        $viewPath = __DIR__ . '/../../Views/Admin/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            echo "Admin view not found: $view";
        }
    }
} 