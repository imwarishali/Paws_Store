<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

$order_id = $_GET['order_id'] ?? '';
$order = null;

if ($order_id && isset($_SESSION['orders'])) {
    foreach ($_SESSION['orders'] as $o) {
        if ($o['id'] === $order_id) {
            $order = $o;
            break;
        }
    }
}

if (!$order) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
            text-align: center;
        }

        .success-icon {
            font-size: 80px;
            color: #4caf50;
            margin-bottom: 20px;
        }

        .success-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .success-message {
            font-size: 18px;
            color: #666;
            margin-bottom: 30px;
        }

        .order-details {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            text-align: left;
        }

        .order-details h3 {
            color: #2c1a0e;
            margin-bottom: 20px;
            text-align: center;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .status-processing {
            background: #fff3cd;
            color: #856404;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-primary,
        .btn-secondary {
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #b5860d;
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: #9a7210;
        }

        .btn-secondary {
            background: transparent;
            color: #2c1a0e;
            border: 2px solid #2c1a0e;
        }

        .btn-secondary:hover {
            background: #2c1a0e;
            color: white;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-primary,
            .btn-secondary {
                width: 200px;
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
        <div class="success-container">
            <div class="success-icon">✅</div>
            <h1 class="success-title">Payment Successful!</h1>
            <p class="success-message">
                Thank you for your purchase. Your order is now being processed and will be delivered within 3-5 business days.
            </p>

            <div class="order-details">
                <h3>Order Details</h3>
                <div class="detail-row">
                    <strong>Order ID:</strong>
                    <span><?php echo htmlspecialchars($order['id']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Transaction ID:</strong>
                    <span><?php echo htmlspecialchars($order['transaction_id']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Payment Method:</strong>
                    <span><?php echo htmlspecialchars(ucfirst($order['payment_method'] ?? 'N/A')); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Payment Status:</strong>
                    <span class="status-processing"><?php echo htmlspecialchars($order['payment_status'] ?? 'Processing'); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Payment Date:</strong>
                    <span><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Total Amount:</strong>
                    <span>₹<?php echo number_format($order['total']); ?></span>
                </div>
                <div class="detail-row">
                    <strong>Delivery Address:</strong>
                    <span><?php
                            $address = $order['address'] ?? '';
                            if (is_string($address)) {
                                $decoded = json_decode($address, true);
                                $address = is_array($decoded) ? $decoded : $address;
                            }
                            echo htmlspecialchars(is_array($address) ? implode(', ', $address) : $address);
                            ?></span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="index.php" class="btn-primary">Continue Shopping</a>
                <a href="order_history.php" class="btn-secondary">View Order History</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clear cart from local storage after successful payment
            localStorage.removeItem('pawsCart');
            let cart = [];

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
</content>