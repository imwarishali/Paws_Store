<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php?redirect=order_history.php");
    exit();
}

require_once 'db.php';

$orders = [];
try {
    // Handle order cancellation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_number'])) {
        $cancel_num = $_POST['cancel_order_number'];
        $user_id = $_SESSION['user']['id'];

        // Update the order status to Cancelled
        $update_stmt = $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE order_number = ? AND user_id = ?");
        $update_stmt->execute([$cancel_num, $user_id]);

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
    $raw_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $orders = $raw_orders;
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

        .orders-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .order-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e8e0d4;
            padding: 22px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
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

        .order-search-input {
            width: 100%;
            padding: 12px 20px;
            border-radius: 24px;
            border: 1px solid #d4b87a;
            outline: none;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .order-search-input:focus {
            border-color: #b5860d;
            box-shadow: 0 0 0 2px rgba(181, 134, 13, 0.1);
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
            display: inline-block;
            background: #b5860d;
            color: #fff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 24px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .no-orders a:hover {
            background: #9a7210;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.2);
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
            background-color: #2c1a0e;
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
            background-color: #4a3020;
        }

        .order-actions {
            margin-top: auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .star-rating {
            display: inline-flex;
            gap: 4px;
            font-size: 22px;
            margin-top: 8px;
        }

        .star {
            color: #e8e0d4;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .star.hover,
        .star.active {
            color: #b5860d;
        }

        .star:hover {
            transform: scale(1.15);
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
                <?php if (!empty($orders)): ?>
                    <div style="margin-top: 20px; max-width: 400px; margin-left: auto; margin-right: auto; position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 16px; color: #888;">🔍</span>
                        <input type="text" id="order-search" class="order-search-input" placeholder="Search by Order ID, Pet Name or Status..." style="padding-left: 45px;">
                    </div>
                <?php endif; ?>
            </div>


            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <h2>No orders yet</h2>
                    <p>You haven’t placed any orders yet. Browse pets and place your first order!</p>
                    <a href="index.php" style="margin-top: 10px;">Browse Pets</a>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
                                <?php if (!empty($order['pet_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($order['pet_image']); ?>" style="width: 60px; height: 60px; border-radius: 10px; object-fit: cover;">
                                <?php endif; ?>
                                <div>
                                    <h3 style="margin-bottom: 5px; font-size: 18px; color: #2c1a0e;"><?php echo htmlspecialchars($order['pet_name'] ?? 'Pet'); ?></h3>
                                    <div style="color: #666; font-size: 13px; font-weight: 600;">Qty: <?php echo htmlspecialchars($order['quantity']); ?></div>
                                    <?php if ($order['order_status'] === 'Delivered'): ?>
                                        <div class="star-rating" data-id="<?php echo htmlspecialchars($order['order_number'] . '_' . $order['pet_id']); ?>" data-name="<?php echo htmlspecialchars($order['pet_name'] ?? 'Pet'); ?>" title="Rate this pet">
                                            <span class="star" data-val="1">★</span>
                                            <span class="star" data-val="2">★</span>
                                            <span class="star" data-val="3">★</span>
                                            <span class="star" data-val="4">★</span>
                                            <span class="star" data-val="5">★</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="order-meta">
                                <span><strong>Order:</strong> #<?php echo htmlspecialchars($order['order_number']); ?></span>
                                <span><strong>Date:</strong> <?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                                <span><strong>Status:</strong>
                                    <?php if ($order['order_status'] === 'Cancelled'): ?>
                                        <span class="status-badge status-Cancelled">🚫 Cancelled</span>
                                    <?php else: ?>
                                        <span class="status-badge status-<?php echo htmlspecialchars($order['order_status']); ?>"><?php echo htmlspecialchars($order['order_status']); ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>

                            <div class="order-details">
                                <div class="detail-row">
                                    <strong>Amount Paid:</strong>
                                    <span style="color: #b5860d; font-weight: 700; font-size: 16px;">₹<?php echo number_format($order['total_amount']); ?></span>
                                </div>
                                <div class="detail-row" style="flex-direction: column; gap: 5px;">
                                    <strong>Delivery Address:</strong>
                                    <span style="font-size: 14px; color: #555; line-height: 1.4;">
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
                                <div class="refund-notice" style="padding: 10px; margin-top: 10px;">
                                    <span style="font-size: 20px;">💸</span>
                                    <div style="font-size: 13px;">
                                        <strong>Refund Initiated!</strong> ₹<?php echo number_format($order['total_amount']); ?> will reflect in your account within 3-5 days.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="order-actions" style="gap: 10px; margin-top: auto; border-top: 1px solid #eee; padding-top: 15px;">
                                <?php if (!in_array($order['order_status'], ['Confirmed', 'Shipped', 'Delivered', 'Cancelled'])): ?>
                                    <form method="POST" style="margin: 0; flex: 1; min-width: 150px;">
                                        <input type="hidden" name="cancel_order_number" value="<?php echo $order['order_number']; ?>">
                                        <button type="submit" class="btn-cancel-order" style="width: 100%;" onclick="if(this.innerText === 'Cancel Order') { this.innerText = 'Confirm Cancel'; this.style.backgroundColor = '#852029'; setTimeout(() => { this.innerText = 'Cancel Order'; this.style.backgroundColor = ''; }, 3000); return false; }">Cancel Order</button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" class="btn-reorder" style="flex: 1; min-width: 150px; padding: 8px;" onclick="reorderPet('<?php echo $order['pet_id']; ?>', '<?php echo addslashes(htmlspecialchars($order['pet_name'] ?? 'Pet')); ?>', <?php echo (float)($order['pet_price'] ?? 0); ?>, '<?php echo addslashes(htmlspecialchars($order['pet_image'] ?? '')); ?>', <?php echo (int)$order['quantity']; ?>)">Reorder</button>
                                <?php if ($order['order_status'] !== 'Cancelled'): ?>
                                    <a href="invoice.php?order_id=<?php echo urlencode($order['order_number']); ?>" class="btn-invoice" style="flex: 1; min-width: 150px; text-align: center; padding: 8px; box-sizing: border-box;">Invoice</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="no-matching-orders" style="display: none; text-align: center; padding: 40px; background: #fff; border-radius: 14px; border: 1px solid #e8e0d4; margin-top: 20px;">
                    <h3 style="color: #2c1a0e; margin-bottom: 10px;">No matching orders found</h3>
                    <p style="color: #666;">Try adjusting your search criteria.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        // TOAST NOTIFICATION FUNCTION
        function showToast(message, icon = '✅') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = 'toast-msg';
            toast.innerHTML = `<span class="toast-icon">${icon}</span> <span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        }

        function reorderPet(id, name, price, image, quantity) {
            fetch('cart_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'add', id: id, quantity: quantity})
            }).then(response => response.json()).then(data => {
                showToast(name + " added to cart!", '🛒');
                setTimeout(() => {
                    window.location.href = 'cart.php';
                }, 1200);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($success_message)): ?>
                showToast("<?php echo addslashes($success_message); ?>", "🚫");
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                showToast("<?php echo addslashes($error_message); ?>", "⚠️");
            <?php endif; ?>

            const currentUserId = '<?php echo isset($_SESSION["user"]["id"]) ? $_SESSION["user"]["id"] : "guest"; ?>';

            function updateCartCount(count) {
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = count;
                    cartCountElement.style.display = count > 0 ? 'flex' : 'none';
                }
            }
            fetch('cart_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'get'})
            }).then(r => r.json()).then(d => { if(d.status === 'success') updateCartCount(d.cart_count); });

            // ORDER SEARCH FUNCTIONALITY
            const searchInput = document.getElementById('order-search');
            const orderCards = document.querySelectorAll('.order-card');
            const noOrdersMsg = document.getElementById('no-matching-orders');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    let visibleCount = 0;

                    orderCards.forEach(card => {
                        const orderText = card.textContent.toLowerCase();
                        if (orderText.includes(searchTerm)) {
                            card.style.display = 'flex';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (noOrdersMsg) {
                        noOrdersMsg.style.display = visibleCount === 0 ? 'block' : 'none';
                    }
                });
            }

            // STAR RATING SYSTEM
            const ratingKey = 'pawsRatings_' + currentUserId;
            let ratings = JSON.parse(localStorage.getItem(ratingKey)) || {};

            document.querySelectorAll('.star-rating').forEach(container => {
                const stars = container.querySelectorAll('.star');
                const uniqueId = container.getAttribute('data-id');
                const petName = container.getAttribute('data-name');

                // Load existing rating
                if (ratings[uniqueId]) {
                    stars.forEach(s => {
                        if (s.getAttribute('data-val') <= ratings[uniqueId]) {
                            s.classList.add('active');
                        }
                    });
                }

                stars.forEach(star => {
                    star.addEventListener('mouseover', function() {
                        const val = this.getAttribute('data-val');
                        stars.forEach(s => {
                            s.classList.toggle('hover', s.getAttribute('data-val') <= val);
                        });
                    });

                    star.addEventListener('mouseout', function() {
                        stars.forEach(s => s.classList.remove('hover'));
                    });

                    star.addEventListener('click', function() {
                        const val = this.getAttribute('data-val');
                        ratings[uniqueId] = val;
                        localStorage.setItem(ratingKey, JSON.stringify(ratings));

                        stars.forEach(s => {
                            s.classList.toggle('active', s.getAttribute('data-val') <= val);
                        });
                        showToast(`You rated ${petName} ${val} stars!`, "⭐");
                    });
                });
            });
        });
    </script>
</body>

</html>