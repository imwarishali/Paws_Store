<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

$orders = isset($_SESSION['orders']) ? $_SESSION['orders'] : [];

// Show latest orders first
$orders = array_reverse($orders);
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

        .status-processing {
            background: #fff3cd;
            color: #856404;
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

            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <h2>No orders yet</h2>
                    <p>You haven’t placed any orders yet. Browse pets and place your first order!</p>
                    <p><a href="index.php">Browse Pets</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <h3>Order #<?php echo htmlspecialchars($order['id']); ?></h3>

                        <div class="order-meta">
                            <span><strong>Placed:</strong> <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></span>
                            <span><strong>Status:</strong> <span class="status-processing"><?php echo htmlspecialchars($order['status']); ?></span></span>
                            <span><strong>Paid with:</strong> <?php echo htmlspecialchars(ucfirst($order['payment_method'] ?? 'N/A')); ?></span>
                            <span><strong>Txn ID:</strong> <?php echo htmlspecialchars($order['transaction_id']); ?></span>
                        </div>

                        <div class="order-details">
                            <div class="detail-row">
                                <strong>Total Amount:</strong>
                                <span>₹<?php echo number_format($order['total']); ?></span>
                            </div>
                            <div class="detail-row">
                                <strong>Delivery Address:</strong>
                                <span>
                                    <?php
                                    $address = $order['address'] ?? '';
                                    if (is_string($address)) {
                                        $decoded = json_decode($address, true);
                                        $address = is_array($decoded) ? $decoded : $address;
                                    }
                                    echo htmlspecialchars(is_array($address) ? implode(', ', $address) : $address);
                                    ?>
                                </span>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let cart = JSON.parse(localStorage.getItem('pawsCart')) || [];

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