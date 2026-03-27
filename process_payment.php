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

// Connect to database (update credentials as needed)
$host = 'localhost';
$dbname = 'pet_store'; // Replace with your actual database name
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Insert into orders table
    $order_number = 'ORD' . time() . rand(100, 999);
    $user_id = $_SESSION['user']['id'] ?? 1;
    $shipping_address = json_encode($address); // Store JSON array as string

    $stmt = $pdo->prepare("
        INSERT INTO orders (order_number, user_id, total_amount, shipping_address, transaction_id, payment_screenshot, payment_method, payment_status, order_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Completed', 'Processing')
    ");

    $stmt->execute([
        $order_number,
        $user_id,
        $total,
        $shipping_address,
        $transaction_id,
        $screenshot_path,
        $payment_method
    ]);

    // Fetch the auto-incremented primary key of the new order
    $order_id = $pdo->lastInsertId();

    // 2. Insert into order_items table
    if (!empty($cart)) {
        $item_stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, pet_id, quantity, price_at_purchase) 
            VALUES (?, ?, ?, ?)
        ");

        foreach ($cart as $item) {
            // The price from the cart will now always be a clean number.
            $price = $item['price'];
            $quantity = $item['quantity'] ?? 1;

            $item_stmt->execute([
                $order_id,
                $item['id'],
                $quantity,
                $price
            ]);
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Clear cart
unset($_SESSION['cart']); // If using session cart

// Redirect to success page
header("Location: payment_success.php?order_id=" . $order_number);
exit();
?>