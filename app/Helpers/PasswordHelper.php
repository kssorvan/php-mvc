<?php
namespace App\Helpers;

/**
 * Password Helper Class
 * Provides secure password hashing and verification methods
 */
class PasswordHelper
{
    /**
     * Hash a password securely
     * 
     * @param string $password The plaintext password
     * @return string The hashed password
     */
    public static function hash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify a password against its hash
     * 
     * @param string $password The plaintext password
     * @param string $hash The hashed password from database
     * @return bool True if password matches, false otherwise
     */
    public static function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Check if a password needs to be rehashed
     * (useful when updating password hashing algorithms)
     * 
     * @param string $hash The current hash
     * @return bool True if rehashing is needed
     */
    public static function needsRehash($hash)
    {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password The password to validate
     * @return array Array of validation results
     */
    public static function validateStrength($password)
    {
        $errors = [];
        $requirements = [];
        
        // Minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        } else {
            $requirements[] = 'At least 8 characters ✓';
        }
        
        // Contains uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        } else {
            $requirements[] = 'Contains uppercase letter ✓';
        }
        
        // Contains lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        } else {
            $requirements[] = 'Contains lowercase letter ✓';
        }
        
        // Contains number
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        } else {
            $requirements[] = 'Contains number ✓';
        }
        
        // Contains special character (optional but recommended)
        if (preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
            $requirements[] = 'Contains special character ✓';
        }
        
        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'requirements_met' => $requirements,
            'strength_score' => count($requirements)
        ];
    }
    
    /**
     * Generate a secure random password
     * 
     * @param int $length Password length (default: 12)
     * @param bool $includeSymbols Include special characters
     * @return string Generated password
     */
    public static function generate($length = 12, $includeSymbols = true)
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        
        $chars = $lowercase . $uppercase . $numbers;
        if ($includeSymbols) {
            $chars .= $symbols;
        }
        
        $password = '';
        
        // Ensure at least one character from each required set
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        
        if ($includeSymbols) {
            $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        }
        
        // Fill the rest randomly
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        // Shuffle the password
        return str_shuffle($password);
    }
    
    /**
     * Check if a hash appears to be a bcrypt hash
     * 
     * @param string $hash The hash to check
     * @return bool True if it's a bcrypt hash
     */
    public static function isBcryptHash($hash)
    {
        return strlen($hash) === 60 && str_starts_with($hash, '$2y$');
    }
    
    /**
     * Securely compare two strings (timing attack safe)
     * 
     * @param string $known The known string
     * @param string $user The user-provided string
     * @return bool True if strings match
     */
    public static function secureCompare($known, $user)
    {
        return hash_equals($known, $user);
    }
    
    /**
     * Create a secure password reset token
     * 
     * @return array Contains token and expiry timestamp
     */
    public static function createResetToken()
    {
        $token = bin2hex(random_bytes(32)); // 64 character token
        $expiry = time() + (24 * 60 * 60); // 24 hours from now
        
        return [
            'token' => $token,
            'expiry' => $expiry,
            'expiry_formatted' => date('Y-m-d H:i:s', $expiry)
        ];
    }
    
    /**
     * Verify a password reset token
     * 
     * @param string $token The token to verify
     * @param string $storedToken The token from database
     * @param int $expiry The expiry timestamp from database
     * @return bool True if token is valid and not expired
     */
    public static function verifyResetToken($token, $storedToken, $expiry)
    {
        // Check if token matches and hasn't expired
        return self::secureCompare($token, $storedToken) && time() < $expiry;
    }
} 