<?php

/**
 * Global Configuration File
 * Store all constants and configurations here
 */

// Security & Authentication
define('OTP_TIMEOUT', 600);           // 10 minutes in seconds
define('OTP_MAX_ATTEMPTS', 6);        // Max failed OTP attempts
define('SESSION_TIMEOUT', 1800);      // 30 minutes in seconds
define('PASSWORD_MIN_LENGTH', 8);     // Minimum password length

// Pagination
define('ITEMS_PER_PAGE', 12);         // Items displayed per page
define('CACHE_DURATION', 300);        // 5 minutes in seconds

// Rate Limiting
define('MAX_OTP_REQUESTS_PER_HOUR', 6);
define('OTP_REQUEST_COOLDOWN', 60);   // Seconds between OTP requests

// Email Configuration
define('SYSTEM_EMAIL_SENDER', getenv('SYSTEM_EMAIL') ?: 'noreply@paws-store.com');

// Payment Gateway
define('PAYMENT_TIMEOUT', 60);

// Error Handling
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', __DIR__ . '/logs/error.log');
define('DISPLAY_ERRORS', false);      // Don't show errors to users in production

// Security Headers
define('SECURE_HEADERS', [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
]);

// Validation Rules
define('PHONE_REGEX', '/^[0-9]{10}$/');  // 10 digits for India
define('VALID_ORDER_STATUSES', ['Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled']);
define('VALID_PAYMENT_STATUSES', ['Pending', 'Completed', 'Failed', 'Refunded']);

// Database
define('DB_CHARSET', 'utf8mb4');

// Session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0);  // Set to 1 in production with HTTPS
ini_set('session.use_strict_mode', 1);

// Create logs directory if not exists
if (LOG_ERRORS && !is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// Set error handling
if (LOG_ERRORS) {
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', ERROR_LOG_PATH);
}

ini_set('display_errors', DISPLAY_ERRORS ? 1 : 0);

// Start session (must be after ini_set calls)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
