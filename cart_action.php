<?php
session_start();
header('Content-Type: application/json');

// Initialize the session cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Read POST data (Supports both JSON fetch payloads and standard form data)
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $data['action'] ?? '';
$id = $data['id'] ?? '';
$qty = isset($data['quantity']) ? (int)$data['quantity'] : 1;

if ($action === 'add' && !empty($id)) {
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $qty;
    } else {
        $_SESSION['cart'][$id] = $qty;
    }
} elseif ($action === 'update' && !empty($id)) {
    if ($qty > 0) {
        $_SESSION['cart'][$id] = $qty;
    } else {
        unset($_SESSION['cart'][$id]);
    }
} elseif ($action === 'remove' && !empty($id)) {
    unset($_SESSION['cart'][$id]);
} elseif ($action === 'clear') {
    $_SESSION['cart'] = [];
}

// Return the total number of items currently in the cart
$total_items = array_sum($_SESSION['cart']);

echo json_encode(['status' => 'success', 'cart_count' => $total_items]);
?>