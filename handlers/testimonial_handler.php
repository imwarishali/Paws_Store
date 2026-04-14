<?php

/**
 * Testimonial Submission Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $rating = intval($_POST['rating'] ?? 5);
    $testimonial = trim($_POST['testimonial'] ?? '');

    // Validation
    $errors = [];

    if (empty($name) || strlen($name) < 2) {
        $errors[] = "Name is required";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }

    if (empty($testimonial) || strlen($testimonial) < 10) {
        $errors[] = "Testimonial must be at least 10 characters";
    }

    if ($rating < 1 || $rating > 5) {
        $errors[] = "Invalid rating";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO testimonials (name, role, testimonial_text, rating)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                htmlspecialchars($name),
                htmlspecialchars($role),
                htmlspecialchars($testimonial),
                $rating
            ]);

            $_SESSION['success_message'] = "Thank you! Your testimonial will be reviewed and displayed soon.";
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error submitting testimonial. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }

    header("Location: ../testimonials.php");
    exit;
}
