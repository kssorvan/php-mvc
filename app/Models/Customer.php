<?php
namespace App\Models;

use PDO;
use Exception;
use App\Helpers\PasswordHelper;

class Customer extends BaseModel {
    protected $table = 'tbl_customer';
    protected $primaryKey = 'customerID';
    protected $timestamps = false;
    
    protected $fillable = [
        'firstName',
        'lastName', 
        'email',
        'password',
        'phone',
        'email_verified',
        'status'
    ];
    
    /**
     * Create a new customer
     */
    public function createCustomer($data) {
        try {
            // Hash password
            if (isset($data['password'])) {
                $data['password'] = PasswordHelper::hash($data['password']);
            }
            
            return $this->create($data);
        } catch (Exception $e) {
            error_log("Customer createCustomer error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find customer by email
     */
    public function findByEmail($email) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Customer findByEmail error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify customer login
     */
    public function verifyLogin($email, $password) {
        try {
            $customer = $this->findByEmail($email);
            
            if ($customer && PasswordHelper::verify($password, $customer['password'])) {
                // Remove password from returned data
                unset($customer['password']);
                return $customer;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Customer verifyLogin error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update customer information
     */
    public function updateCustomer($id, $data) {
        try {
            // Hash password if provided
            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = PasswordHelper::hash($data['password']);
            } else {
                // Remove password field if empty
                unset($data['password']);
            }
            
            return $this->update($id, $data);
        } catch (Exception $e) {
            error_log("Customer updateCustomer error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        try {
            $sql = "SELECT customerID FROM {$this->table} WHERE email = :email";
            $params = ['email' => $email];
            
            if ($excludeId) {
                $sql .= " AND customerID != :excludeId";
                $params['excludeId'] = $excludeId;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (Exception $e) {
            error_log("Customer emailExists error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify email
     */
    public function verifyEmail($id) {
        try {
            $sql = "UPDATE {$this->table} SET email_verified = 1 WHERE customerID = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            error_log("Customer verifyEmail error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin($id) {
        try {
            $sql = "UPDATE {$this->table} SET updated_at = NOW() WHERE customerID = :id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (Exception $e) {
            error_log("Customer updateLastLogin error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get customer with addresses
     */
    public function getCustomerWithAddresses($id) {
        try {
            $customer = $this->find($id);
            if ($customer) {
                // Remove password
                unset($customer['password']);
                
                // Get addresses
                $sql = "SELECT * FROM tbl_customer_address WHERE customerID = :id ORDER BY is_default DESC, created_at DESC";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['id' => $id]);
                $customer['addresses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $customer;
        } catch (Exception $e) {
            error_log("Customer getCustomerWithAddresses error: " . $e->getMessage());
            return false;
        }
    }
} 