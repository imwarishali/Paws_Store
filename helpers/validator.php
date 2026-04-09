<?php

/**
 * Input Validation & Sanitization Helper
 * Provides centralized validation for all user inputs
 */

require_once __DIR__ . '/../config.php';

class Validator
{
    /**
     * Validate email address
     */
    public static function validateEmail($email)
    {
        $email = trim($email);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
    }

    /**
     * Validate phone number (10 digits for India)
     */
    public static function validatePhone($phone)
    {
        $phone = trim($phone);
        $clean = preg_replace('/[^0-9]/', '', $phone);
        return preg_match(PHONE_REGEX, $clean) ? $clean : false;
    }

    /**
     * Validate password strength
     */
    public static function validatePassword($password)
    {
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return ['valid' => false, 'error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }
        if (!preg_match('/[0-9]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one number'];
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            return ['valid' => false, 'error' => 'Password must contain at least one special character'];
        }
        return ['valid' => true];
    }

    /**
     * Validate username (alphanumeric + underscore, 3-20 chars)
     */
    public static function validateUsername($username)
    {
        $username = trim($username);
        if (strlen($username) < 3 || strlen($username) > 20) {
            return false;
        }
        return preg_match('/^[a-zA-Z0-9_]+$/', $username) ? $username : false;
    }

    /**
     * Validate order status
     */
    public static function validateOrderStatus($status)
    {
        return in_array($status, VALID_ORDER_STATUSES) ? $status : false;
    }

    /**
     * Validate payment status
     */
    public static function validatePaymentStatus($status)
    {
        return in_array($status, VALID_PAYMENT_STATUSES) ? $status : false;
    }

    /**
     * Validate price (positive number)
     */
    public static function validatePrice($price)
    {
        $price = trim($price);
        if (!is_numeric($price) || $price <= 0) {
            return false;
        }
        return (float)$price;
    }

    /**
     * Validate pet category
     */
    public static function validateCategory($category)
    {
        $valid_categories = ['dogs', 'cats', 'fish', 'birds'];
        $category = strtolower(trim($category));
        return in_array($category, $valid_categories) ? $category : false;
    }

    /**
     * Sanitize string input (remove dangerous characters)
     */
    public static function sanitizeString($input)
    {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }

    /**
     * Sanitize multiple inputs at once
     */
    public static function sanitizeArray($data)
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitized[$key] = is_string($value) ? self::sanitizeString($value) : $value;
        }
        return $sanitized;
    }

    /**
     * Validate pet name
     */
    public static function validatePetName($name)
    {
        $name = trim($name);
        if (strlen($name) < 2 || strlen($name) > 100) {
            return false;
        }
        return preg_match('/^[a-zA-Z0-9\s\-()]+$/', $name) ? $name : false;
    }

    /**
     * Validate file upload (image)
     */
    public static function validateImageUpload($file)
    {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!isset($file['type']) || !isset($file['size'])) {
            return ['valid' => false, 'error' => 'Invalid file'];
        }

        if (!in_array($file['type'], $allowed_types)) {
            return ['valid' => false, 'error' => 'Only JPG, PNG, GIF, and WebP images are allowed'];
        }

        if ($file['size'] > $max_size) {
            return ['valid' => false, 'error' => 'Image size must be less than 5MB'];
        }

        return ['valid' => true, 'file' => $file];
    }

    /**
     * Validate OTP (6 digits)
     */
    public static function validateOTP($otp)
    {
        $otp = trim($otp);
        return (preg_match('/^\d{6}$/', $otp) && strlen($otp) === 6) ? $otp : false;
    }

    /**
     * Get safe integer value
     */
    public static function getIntValue($value, $default = 0)
    {
        return is_numeric($value) ? intval($value) : $default;
    }

    /**
     * Get safe boolean value from request
     */
    public static function getBoolValue($value)
    {
        return in_array($value, [1, '1', 'on', 'true', true], true);
    }
}
