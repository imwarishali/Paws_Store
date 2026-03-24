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
$total = $_POST['total'];
$transaction_id = $_POST['transaction_id'];
$payment_method = $_POST['payment_method'] ?? 'qr';

// Handle file upload
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$screenshot_path = '';
if (isset($_FILES['payment_screenshot']) && $_FILES['payment_screenshot']['error'] === UPLOAD_ERR_OK) {
    $file_extension = pathinfo($_FILES['payment_screenshot']['name'], PATHINFO_EXTENSION);
    $file_name = 'payment_' . time() . '_' . uniqid() . '.' . $file_extension;
    $target_path = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $target_path)) {
        $screenshot_path = $target_path;
    }
}

// In a real application, you would save this to a database
// For now, we'll store in session
$order = [
    'id' => 'ORD' . time() . rand(100, 999),
    'user_id' => $_SESSION['user']['id'] ?? 1,
    'cart' => $cart,
    'address' => $address,
    'total' => $total,
    'transaction_id' => $transaction_id,
    'screenshot' => $screenshot_path,
    'payment_method' => $payment_method,
    'payment_status' => 'Completed',
    'status' => 'processing',
    'created_at' => date('Y-m-d H:i:s')
];

// Store order in session (in real app, save to database)
if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = [];
}
$_SESSION['orders'][] = $order;

// Clear cart
unset($_SESSION['cart']); // If using session cart

// Redirect to success page
header("Location: payment_success.php?order_id=" . $order['id']);
exit();
?></content>