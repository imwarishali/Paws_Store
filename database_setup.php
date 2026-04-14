<?php

/**
 * Database Setup - Create tables for new features
 * Run this file once to create necessary tables
 */

require_once 'db.php';

try {
    // Table: pet_reviews
    $pdo->exec("CREATE TABLE IF NOT EXISTS pet_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pet_id INT NOT NULL,
        user_id INT NOT NULL,
        username VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5) NOT NULL,
        review_text TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
    )");

    // Table: testimonials
    $pdo->exec("CREATE TABLE IF NOT EXISTS testimonials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        role VARCHAR(50),
        avatar VARCHAR(255),
        testimonial_text TEXT NOT NULL,
        rating INT CHECK (rating >= 1 AND rating <= 5),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Table: analytics_events
    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_type VARCHAR(50) NOT NULL,
        page_url VARCHAR(255),
        user_ip VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Table: system_settings
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value LONGTEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    echo "✅ All database tables created successfully!<br>";
    echo "Tables created:<br>";
    echo "- pet_reviews<br>";
    echo "- testimonials<br>";
    echo "- analytics_events<br>";
    echo "- system_settings<br>";
} catch (PDOException $e) {
    die("❌ Database Setup Error: " . $e->getMessage());
}
