<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

require_once 'db.php';

$orders = [];
try {
    // Handle order cancellation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
        $cancel_id = $_POST['cancel_order_id'];
        $user_id = $_SESSION['user']['id'];

        // Update the order status to Cancelled
        $update_stmt = $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE id = ? AND user_id = ?");
        $update_stmt->execute([$cancel_id, $user_id]);

        $success_message = "Your order has been successfully cancelled. A refund has been initiated!";
    }

    $stmt = $pdo->prepare("
        SELECT o.*, p.name AS pet_name, p.price AS pet_price, p.image AS pet_image, pm.transaction_id, pm.payment_method
        FROM orders o
        LEFT JOIN pets p ON o.pet_id = p.id
        LEFT JOIN payments pm ON o.id = pm.order_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user']['id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .history-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .history-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .history-header h1 {
            font-family: 'Playfair Display', serif;
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .history-header p {
            color: #666;
            margin: 0;
        }

        .order-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8e0d4;
            padding: 22px;
            margin-bottom: 18px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .order-card h3 {
            margin: 0 0 10px 0;
            font-size: 20px;
            color: #2c1a0e;
        }

        .order-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }

        .order-meta span {
            font-size: 14px;
            color: #555;
            padding: 6px 10px;
            border-radius: 12px;
            background: #f5f2eb;
        }

        .status-badge {
            padding: 4px 10px !important;
            font-weight: 600;
            border-radius: 12px;
        }

        .status-Processing {
            background: #fff3cd !important;
            color: #856404 !important;
        }

        .status-Confirmed {
            background: #d1ecf1 !important;
            color: #0c5460 !important;
        }

        .status-Shipped {
            background: #cce5ff !important;
            color: #004085 !important;
        }

        .status-Delivered {
            background: #d4edda !important;
            color: #155724 !important;
        }

        .status-Cancelled {
            background: #f8d7da !important;
            color: #721c24 !important;
        }

        .order-details {
            border-top: 1px solid #eee;
            padding-top: 16px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row strong {
            color: #2c1a0e;
            font-weight: 700;
        }

        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-orders a {
            color: #b5860d;
            text-decoration: none;
            font-weight: 700;
        }

        .btn-cancel-order {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-cancel-order:hover {
            background-color: #c82333;
        }

        .btn-reorder {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-reorder:hover {
            background-color: #218838;
        }

        .btn-invoice {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
        }

        .btn-invoice:hover {
            background-color: #138496;
        }

        .order-actions {
            margin-top: 15px;
            display: flex;
            justify-content: flex-end;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .refund-notice {
            background: #e0f7fa;
            border: 1px solid #b2ebf2;
            color: #006064;
            padding: 14px 18px;
            border-radius: 8px;
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            line-height: 1.5;
        }

        @media (max-width: 768px) {
            .order-meta {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <nav class="fk-nav-header">
        <div class="fk-nav-top">
            <a href="index.php" class="fk-logo">🐾 Paws Store</a>
            <div class="fk-nav-right" style="margin-left: auto;">
                <a href="index.php" class="fk-cart-btn" style="margin-right: 15px;">
                    <span class="fk-cart-icon">🏠</span> Home
                </a>
                <a href="wishlist.php" class="fk-cart-btn" style="margin-right: 15px;">
                    <span class="fk-cart-icon">🤍</span> Wishlist
                </a>
                <a href="cart.php" class="fk-cart-btn">
                    <span class="fk-cart-icon">🛒</span> Cart
                    <span id="cart-count" class="cart-count">0</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="ps-wrap">
        <div class="history-container">
            <div class="history-header">
                <h1>Your Order History</h1>
                <p>Track your past orders and see their current status.</p>
            </div>

            <?php if (isset($success_message)): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600;">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <h2>No orders yet</h2>
                    <p>You haven’t placed any orders yet. Browse pets and place your first order!</p>
                    <p><a href="index.php">Browse Pets</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <h3>Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>

                        <div class="order-meta">
                            <span><strong>Placed:</strong> <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></span>
                            <span><strong>Status:</strong>
                                <?php if ($order['order_status'] === 'Cancelled'): ?>
                                    <span class="status-badge status-Cancelled">🚫 Order Cancelled</span>
                                <?php else: ?>
                                    <span class="status-badge status-<?php echo htmlspecialchars($order['order_status']); ?>"><?php echo htmlspecialchars($order['order_status']); ?></span>
                                <?php endif; ?>
                            </span>
                            <span><strong>Paid with:</strong> <?php echo htmlspecialchars(ucfirst($order['payment_method'] ?? 'N/A')); ?></span>
                            <span><strong>Txn ID:</strong> <?php echo htmlspecialchars($order['transaction_id'] ?? 'N/A'); ?></span>
                        </div>

                        <div class="order-details">
                            <div class="detail-row">
                                <strong>Pet Ordered:</strong>
                                <span><?php echo htmlspecialchars($order['pet_name'] ?? 'N/A'); ?> (x<?php echo htmlspecialchars($order['quantity']); ?>)</span>
                            </div>
                            <div class="detail-row">
                                <strong>Total Amount:</strong>
                                <span>₹<?php echo number_format($order['total_amount']); ?></span>
                            </div>
                            <div class="detail-row">
                                <strong>Delivery Address:</strong>
                                <span>
                                    <?php
                                    $address = $order['shipping_address'] ?? '';
                                    $decoded = json_decode($address, true);
                                    if (is_string($decoded)) {
                                        $decoded = json_decode($decoded, true);
                                    }
                                    $address_array = is_array($decoded) ? $decoded : [$address];
                                    echo htmlspecialchars(implode(', ', $address_array));
                                    ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($order['order_status'] === 'Cancelled'): ?>
                            <div class="refund-notice">
                                <span style="font-size: 26px;">💸</span>
                                <div>
                                    <strong>Refund Initiated!</strong> Your amount of ₹<?php echo number_format($order['total_amount']); ?> is being securely processed and will reflect in your original payment method within 3-5 business days.
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="order-actions" style="gap: 10px;">
                            <?php if (!in_array($order['order_status'], ['Confirmed', 'Shipped', 'Delivered', 'Cancelled'])): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');" style="margin: 0;">
                                    <input type="hidden" name="cancel_order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="btn-cancel-order">Cancel Order</button>
                                </form>
                            <?php endif; ?>
                            <button type="button" class="btn-reorder" onclick="reorderPet('<?php echo $order['pet_id']; ?>', '<?php echo addslashes(htmlspecialchars($order['pet_name'] ?? 'Pet')); ?>', <?php echo (float)($order['pet_price'] ?? 0); ?>, '<?php echo addslashes(htmlspecialchars($order['pet_image'] ?? '')); ?>', <?php echo (int)$order['quantity']; ?>)">Reorder</button>
                            <?php if ($order['order_status'] !== 'Cancelled'): ?>
                                <a href="invoice.php?order_id=<?php echo urlencode($order['order_number']); ?>" class="btn-invoice">Invoice</a>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>

    <script>
        function reorderPet(id, name, price, image, quantity) {
            let cart = JSON.parse(localStorage.getItem('pawsCart')) || [];
            const existingPet = cart.find(item => item.id == id);

            if (existingPet) {
                existingPet.quantity += quantity;
            } else {
                cart.push({
                    id: id.toString(),
                    name: name,
                    price: parseFloat(price),
                    image: image,
                    quantity: quantity
                });
            }

            localStorage.setItem(cartKey, JSON.stringify(cart));
            alert(name + " added to cart!");
            window.location.href = 'cart.php';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const currentUserId = '<?php echo isset($_SESSION["user"]["id"]) ? $_SESSION["user"]["id"] : "guest"; ?>';
            const cartKey = 'pawsCart_' + currentUserId;

            let cart = JSON.parse(localStorage.getItem(cartKey)) || [];

            function updateCartCount() {
                const cartCountElement = document.getElementById('cart-count');
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                if (cartCountElement) {
                    cartCountElement.textContent = totalItems;
                    cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
                }
            }
            updateCartCount();
        });
    </script>
</body>

</html>