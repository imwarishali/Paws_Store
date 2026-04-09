<?php

/**
 * CSRF Token Protection
 * Prevents Cross-Site Request Forgery attacks
 */

class CSRFToken
{
    private static $token_key = '_csrf_token';
    private static $token_name = 'csrf_token';

    /**
     * Generate a CSRF token
     */
    public static function generate()
    {
        if (!isset($_SESSION[self::$token_key])) {
            $_SESSION[self::$token_key] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$token_key];
    }

    /**
     * Get current token
     */
    public static function getToken()
    {
        return $_SESSION[self::$token_key] ?? '';
    }

    /**
     * Get token field name
     */
    public static function getFieldName()
    {
        return self::$token_name;
    }

    /**
     * Generate hidden input field
     */
    public static function getInputField()
    {
        return '<input type="hidden" name="' . self::$token_name . '" value="' . htmlspecialchars(self::generate(), ENT_QUOTES) . '">';
    }

    /**
     * Verify token from request
     */
    public static function verify($token = null)
    {
        if ($token === null) {
            $token = $_POST[self::$token_name] ?? $_GET[self::$token_name] ?? '';
        }

        if (empty($token) || empty($_SESSION[self::$token_key])) {
            return false;
        }

        // Use constant-time comparison to prevent timing attacks
        return hash_equals($_SESSION[self::$token_key], $token);
    }

    /**
     * Regenerate token (for security after sensitive operations)
     */
    public static function regenerate()
    {
        $_SESSION[self::$token_key] = bin2hex(random_bytes(32));
        return $_SESSION[self::$token_key];
    }

    /**
     * Verify and return error message if invalid
     */
    public static function verifyOrFail($token = null)
    {
        if (!self::verify($token)) {
            http_response_code(403);
            die('CSRF token verification failed. Please try again.');
        }
    }
}
