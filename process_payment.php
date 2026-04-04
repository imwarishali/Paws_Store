<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit();
}

// Get form data
$cart = json_decode($_POST['cart'], true);
$address = json_decode($_POST['address'], true);
$transaction_id = trim($_POST['transaction_id']);
$special_offer = $_POST['special_offer'] ?? 'none';

if (stripos($transaction_id, 'T') !== 0) {
    die("Invalid Transaction ID. It must start with the letter 'T'.");
}

$payment_method = $_POST['payment_method'] ?? 'qr';

// Handle file upload
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$screenshot_path = '';
if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['payment_screenshot']['tmp_name'];

    // Verify that the uploaded file is a valid image
    if (getimagesize($tmp_name) === false) {
        die("Invalid file uploaded. Please upload a valid image file.");
    }

    $file_extension = strtolower(pathinfo($_FILES['payment_screenshot']['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($file_extension, $allowed_extensions)) {
        die("Invalid file extension. Please upload a JPG, JPEG, PNG, GIF, or WEBP image.");
    }

    $file_name = 'payment_' . time() . '_' . uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $file_name;

    if (move_uploaded_file($tmp_name, $target_path)) {
        $screenshot_path = $target_path;
    }
}

// Connect to database
require_once 'db.php';

$user_id = $_SESSION['user']['id'] ?? 1;

// Calculate total securely on the server
$subtotal = 0;
if (!empty($cart)) {
    $pet_ids = array_column($cart, 'id');
    $placeholders = implode(',', array_fill(0, count($pet_ids), '?'));

    $price_stmt = $pdo->prepare("SELECT id, price FROM pets WHERE id IN ($placeholders)");
    $price_stmt->execute($pet_ids);
    $db_prices = [];
    while ($row = $price_stmt->fetch(PDO::FETCH_ASSOC)) {
        $db_prices[$row['id']] = $row['price'];
    }

    foreach ($cart as $item) {
        if (isset($db_prices[$item['id']])) {
            $subtotal += $db_prices[$item['id']] * ($item['quantity'] ?? 1);
        }
    }
}

$discount = 0;
if ($special_offer === 'firstTime') {
    $order_check_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $order_check_stmt->execute([$user_id]);
    $previous_orders = $order_check_stmt->fetchColumn();

    if ($previous_orders == 0) {
        $discount = round($subtotal * 0.1);
    }
} else if ($special_offer === 'bulkDiscount' && count($cart) >= 2) {
    $discount = round($subtotal * 0.05);
} else if ($special_offer === 'freeVet') {
    $discount = 500;
} else if ($special_offer === 'save20' && $subtotal > 10000) {
    $discount = 2000;
}

$shipping = $subtotal > 5000 ? 0 : 500;
$tax = round(($subtotal - $discount) * 0.18);
$total = $subtotal - $discount + $shipping + $tax;

try {
    // 1. Insert into orders table
    $order_number = 'ORD' . time() . rand(100, 999);
    $shipping_address = is_array($address) ? implode(', ', $address) : (string)$address; // Store as a clean, comma-separated string

    $remaining_total = $total;
    $items_count = count($cart);
    $current_item = 0;

    foreach ($cart as $item) {
        $current_item++;
        $pet_id = $item['id'];
        $quantity = $item['quantity'] ?? 1;
        $price = $db_prices[$pet_id] ?? 0;

        $item_subtotal = $price * $quantity;
        if ($subtotal > 0) {
            $item_proportion = $item_subtotal / $subtotal;
            $item_total = round($total * $item_proportion);
        } else {
            $item_total = 0;
        }

        if ($current_item === $items_count) {
            $item_total = $remaining_total;
        }
        $remaining_total -= $item_total;

        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, user_id, pet_id, quantity, total_amount, shipping_address, payment_screenshot, order_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Processing')
        ");

        $stmt->execute([
            $order_number,
            $user_id,
            $pet_id,
            $quantity,
            $item_total,
            $shipping_address,
            $screenshot_path,
        ]);

        $order_id = $pdo->lastInsertId();

        $payment_stmt = $pdo->prepare("
            INSERT INTO payments (order_id, transaction_id, payment_method, payment_status) 
            VALUES (?, ?, ?, 'Completed')
        ");
        $payment_stmt->execute([
            $order_id,
            $transaction_id,
            $payment_method
        ]);
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Clear cart
unset($_SESSION['cart']); // If using session cart

// Redirect to success page
header("Location: payment_success.php?order_id=" . $order_number);
exit();
