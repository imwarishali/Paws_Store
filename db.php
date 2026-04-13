<?php

/**
 * Database Connection Handler
 * Establishes PDO connection to MySQL database
 * Uses environment variables from .env file
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');

// Load environment variables from .env file
$env = parse_ini_file(__DIR__ . '/.env');

// Get database credentials from .env or use defaults
$host = $env['DB_HOST'] ?? 'localhost';
$dbname = $env['DB_NAME'] ?? 'pet_store';
$username = $env['DB_USER'] ?? 'root';
$password = $env['DB_PASS'] ?? '';

try {
    // Create PDO connection with UTF-8 charset
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Set error mode to throw exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set fetch mode to associative array by default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database Connection Failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}
