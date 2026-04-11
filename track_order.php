<?php
session_start();

require_once 'db.php';

$tracking_data = null;
$error_message = '';
$search_reference = '';
$user_id = $_SESSION['user']['id'] ?? null;

// Auto-fetch if ref parameter is passed from order_history
if (isset($_GET['ref']) && !empty($_GET['ref'])) {
    $search_reference = trim($_GET['ref']);
    $pet_id = isset($_GET['pet_id']) ? (int)$_GET['pet_id'] : null;

    try {
        if ($user_id) {
            // If user is logged in, only show their own order
            if ($pet_id) {
                // Track specific pet in order
                $stmt = $pdo->prepare("
                    SELECT o.*, p.name AS pet_name, p.image AS pet_image, pm.payment_method, pm.payment_status
                    FROM orders o
                    LEFT JOIN pets p ON o.pet_id = p.id
                    LEFT JOIN payments pm ON o.id = pm.order_id
                    WHERE (o.order_number = ? OR CAST(o.id AS CHAR) = ?)
                    AND o.user_id = ?
                    AND o.pet_id = ?
                    LIMIT 1
                ");
                $stmt->execute([$search_reference, $search_reference, $user_id, $pet_id]);
            } else {
                // Track by order number only
                $stmt = $pdo->prepare("
                    SELECT o.*, p.name AS pet_name, p.image AS pet_image, pm.payment_method, pm.payment_status
                    FROM orders o
                    LEFT JOIN pets p ON o.pet_id = p.id
                    LEFT JOIN payments pm ON o.id = pm.order_id
                    WHERE (o.order_number = ? OR CAST(o.id AS CHAR) = ?)
                    AND o.user_id = ?
                    GROUP BY o.id
                    LIMIT 1
                ");
                $stmt->execute([$search_reference, $search_reference, $user_id]);
            }
        } else {
            // If not logged in, allow tracking with order number only
            if ($pet_id) {
                $stmt = $pdo->prepare("
                    SELECT o.*, p.name AS pet_name, p.image AS pet_image, pm.payment_method, pm.payment_status
                    FROM orders o
                    LEFT JOIN pets p ON o.pet_id = p.id
                    LEFT JOIN payments pm ON o.id = pm.order_id
                    WHERE (o.order_number = ? OR CAST(o.id AS CHAR) = ?)
                    AND o.pet_id = ?
                    LIMIT 1
                ");
                $stmt->execute([$search_reference, $search_reference, $pet_id]);
            } else {
                $stmt = $pdo->prepare("
                    SELECT o.*, p.name AS pet_name, p.image AS pet_image, pm.payment_method, pm.payment_status
                    FROM orders o
                    LEFT JOIN pets p ON o.pet_id = p.id
                    LEFT JOIN payments pm ON o.id = pm.order_id
                    WHERE (o.order_number = ? OR CAST(o.id AS CHAR) = ?)
                    GROUP BY o.id
                    LIMIT 1
                ");
                $stmt->execute([$search_reference, $search_reference]);
            }
        }
        $tracking_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tracking_data) {
            $error_message = 'Order not found. Please check your reference number.';
        }
    } catch (PDOException $e) {
        $error_message = 'Database error: ' . $e->getMessage();
    }
}

// Handle order tracking search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reference_number'])) {
    $search_reference = trim($_POST['reference_number']);
    $pet_id = isset($_POST['pet_id']) ? (int)$_POST['pet_id'] : null;

    if (empty($search_reference)) {
        $error_message = 'Please enter an order reference number.';
    } else {
        try {
            if ($user_id) {
                // If user is logged in, only search their own orders
                if ($pet_id) {
                    $stmt = $pdo->prepare("
                        SELECT o.*, p.name AS pet_name, p.image AS pet_image, pm.payment_method, pm.payment_status
                        FROM orders o
                        LEFT JOIN pets p ON o.pet_id = p.id
                        LEFT JOIN payments pm ON o.id = pm.order_id
                        WHERE (o.order_number = ? OR CAST(o.id AS CHAR) = ?)
                        AND o.user_id = ?
                        AND o.pet_id = ?
                        LIMIT 1
                    ");
                    $stmt->execute([$search_reference, $search_reference, $user_id, $pet_id]);
                } else {
                    $stmt = $pdo->prepare("
                        SELECT o.*, p.name AS pet_name, p.image AS pet_image, pm.payment_method, pm.payment_status
                        FROM orders o
                        LEFT JOIN pets p ON o.pet_id = p.id
                        LEFT JOIN payments pm ON o.id = pm.order_id
                        WHERE (o.order_number = ? OR CAST(o.id AS CHAR) = ?)
                        AND o.user_id = ?
                        GROUP BY o.id
                        LIMIT 1
                    ");
                    $stmt->execute([$search_reference, $search_reference, $user_id]);
                }
            } else {
                // If not logged in, allow search with order number
                if ($pet_id) {
                    $stmt = $pdo->prepare("
                        SELECT o.*, p.name AS pet_name, p.image AS pet_image, pm.payment_method, pm.payment_status
                        FROM orders o
                        LEFT JOIN pets p ON o.pet_id = p.id
                        LEFT JOIN payments pm ON o.id = pm.order_id
                        WHERE (o.order_number = ? OR CAST(o.id AS CHAR) = ?)
                        AND o.pet_id = ?
                        LIMIT 1
                    ");
                    $stmt->execute([$search_reference, $search_reference, $pet_id]);
                } else {
                    $stmt = $pdo->prepare("
                        SELECT o.*, p.name AS pet_name, p.image AS pet_image, pm.payment_method, pm.payment_status
                        FROM orders o
                        LEFT JOIN pets p ON o.pet_id = p.id
                        LEFT JOIN payments pm ON o.id = pm.order_id
                        WHERE o.order_number = ? OR CAST(o.id AS CHAR) = ?
                        GROUP BY o.id
                        LIMIT 1
                    ");
                    $stmt->execute([$search_reference, $search_reference]);
                }
            }
            $tracking_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$tracking_data) {
                if ($user_id) {
                    $error_message = 'Order not found. Please check that you have the correct order reference.';
                } else {
                    $error_message = 'Order not found. Please check your reference number.';
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Pet Delivery - Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #faf6f0 0%, #f5ead8 100%);
            color: #333;
            min-height: 100vh;
            padding: 20px;
        }

        .tracking-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px;
        }

        .tracking-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .tracking-header h1 {
            font-family: 'Playfair Display', serif;
            color: #2c1a0e;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .tracking-header p {
            color: #666;
            font-size: 16px;
        }

        .search-section {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .search-form input {
            flex: 1;
            min-width: 250px;
            padding: 14px 18px;
            border: 2px solid #e8e0d4;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
        }

        .search-form input:focus {
            outline: none;
            border-color: #b5860d;
            box-shadow: 0 0 0 3px rgba(181, 134, 13, 0.1);
        }

        .search-form button {
            padding: 14px 40px;
            background: #b5860d;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
        }

        .search-form button:hover {
            background: #9a7210;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.2);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 16px;
            border-radius: 10px;
            border-left: 4px solid #c33;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tracking-result {
            background: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .order-info h2 {
            color: #2c1a0e;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .order-reference {
            color: #999;
            font-size: 14px;
        }

        .status-badge {
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 14px;
            display: inline-block;
        }

        .status-confirmed {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-shipped {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-out-for-delivery {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .status-delivered {
            background: #e8f5e9;
            color: #388e3c;
        }

        .status-cancelled {
            background: #ffebee;
            color: #d32f2f;
        }

        /* Timeline Styles */
        .timeline {
            margin: 30px 0;
        }

        .timeline-item {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            position: relative;
            padding-left: 40px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 20px;
            height: 20px;
            background: #e8e0d4;
            border-radius: 50%;
            border: 3px solid white;
        }

        .timeline-item.active::before {
            background: #b5860d;
            box-shadow: 0 0 0 4px rgba(181, 134, 13, 0.2);
        }

        .timeline-item.completed::before {
            background: #4caf50;
        }

        .timeline-content h4 {
            color: #2c1a0e;
            margin-bottom: 5px;
            font-size: 15px;
        }

        .timeline-content p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .pet-section {
            background: #fafaf8;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            border: 1px solid #e8e0d4;
        }

        .pet-card {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .pet-image {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
        }

        .pet-details h3 {
            color: #2c1a0e;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .pet-info {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .delivery-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 25px 0;
        }

        .detail-box {
            background: #fafaf8;
            padding: 18px;
            border-radius: 10px;
            border-left: 4px solid #b5860d;
        }

        .detail-label {
            color: #999;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .detail-value {
            color: #2c1a0e;
            font-size: 16px;
            font-weight: 600;
        }

        .special-instructions {
            background: #fffbf0;
            border-left: 4px solid #ff9800;
            padding: 18px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .special-instructions h4 {
            color: #ff9800;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .special-instructions p {
            color: #666;
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
        }

        .contact-options {
            background: #e3f2fd;
            border-left: 4px solid #1976d2;
            padding: 18px;
            border-radius: 10px;
            margin: 20px 0;
        }

        .contact-options h4 {
            color: #1976d2;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .contact-option {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #666;
            font-size: 14px;
            margin: 8px 0;
        }

        .contact-option input {
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
            }

            .search-form input,
            .search-form button {
                width: 100%;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .delivery-details {
                grid-template-columns: 1fr;
            }

            .pet-card {
                flex-direction: column;
                text-align: center;
            }

            .tracking-header h1 {
                font-size: 1.8rem;
            }
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #b5860d;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 25px;
            transition: all 0.3s;
        }

        .back-link:hover {
            gap: 12px;
        }
    </style>
</head>

<body>
    <div class="tracking-container">
        <a href="<?php echo isset($_SESSION['user']) ? 'order_history.php' : 'index.php'; ?>" class="back-link">
            ← <?php echo isset($_SESSION['user']) ? 'Back to Orders' : 'Back to Home'; ?>
        </a>

        <div class="tracking-header">
            <h1>🚚 Track Your Pet Delivery</h1>
            <p>Enter your order reference number to track your pet's delivery status</p>
        </div>

        <div class="search-section">
            <form method="POST" class="search-form">
                <input
                    type="text"
                    name="reference_number"
                    placeholder="Enter Order Number (e.g., ORD1712345678123 or Order ID)"
                    value="<?php echo htmlspecialchars($search_reference); ?>"
                    required>
                <button type="submit">🔍 Track Order</button>
            </form>
        </div>

        <?php if ($error_message): ?>
            <div class="error-message">
                ⚠️ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($tracking_data): ?>
            <div class="tracking-result">
                <!-- Order Header -->
                <div class="order-header">
                    <div class="order-info">
                        <h2><?php echo htmlspecialchars($tracking_data['pet_name'] ?? 'Pet'); ?></h2>
                        <p class="order-reference">Order #<?php echo htmlspecialchars($tracking_data['order_number']); ?></p>
                    </div>
                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $tracking_data['order_status'])); ?>">
                        <?php echo htmlspecialchars($tracking_data['order_status']); ?>
                    </span>
                </div>

                <!-- Delivery Timeline -->
                <div class="timeline">
                    <!-- Order Confirmed -->
                    <div class="timeline-item completed">
                        <div class="timeline-content">
                            <h4>✓ Order Confirmed</h4>
                            <p>Your order has been confirmed and payment received.</p>
                        </div>
                    </div>

                    <!-- Shipped -->
                    <div class="timeline-item <?php echo in_array($tracking_data['order_status'], ['Shipped', 'Out for Delivery', 'Delivered']) ? 'completed' : ''; ?>">
                        <div class="timeline-content">
                            <h4><?php echo in_array($tracking_data['order_status'], ['Shipped', 'Out for Delivery', 'Delivered']) ? '✓' : '○'; ?> Picked Up & Shipped</h4>
                            <p>Your pet has been carefully packed and shipped from our facility.</p>
                        </div>
                    </div>

                    <!-- Out for Delivery -->
                    <div class="timeline-item <?php echo $tracking_data['order_status'] === 'Out for Delivery' || $tracking_data['order_status'] === 'Delivered' ? 'active' : '';
                                                echo $tracking_data['order_status'] === 'Delivered' ? ' completed' : ''; ?>">
                        <div class="timeline-content">
                            <h4><?php echo $tracking_data['order_status'] === 'Delivered' ? '✓' : '→'; ?> Out for Delivery</h4>
                            <p>Your pet is on the way to you! 🚗</p>
                            <?php if ($tracking_data['delivery_date']): ?>
                                <p style="margin-top: 8px; font-weight: 600; color: #2c1a0e;">
                                    📅 Expected: <?php echo date('M d, Y', strtotime($tracking_data['delivery_date'])); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($tracking_data['delivery_time']): ?>
                                <p style="font-weight: 600; color: #2c1a0e;">
                                    ⏰ Time Slot: <?php echo htmlspecialchars($tracking_data['delivery_time']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Delivered -->
                    <div class="timeline-item <?php echo $tracking_data['order_status'] === 'Delivered' ? 'active' : '';
                                                echo $tracking_data['order_status'] === 'Delivered' ? ' completed' : ''; ?>">
                        <div class="timeline-content">
                            <h4><?php echo $tracking_data['order_status'] === 'Delivered' ? '✓' : '○'; ?> Delivered</h4>
                            <p>Your pet has arrived at your address. Welcome home! 🏠</p>
                        </div>
                    </div>
                </div>

                <!-- Pet Card -->
                <?php if ($tracking_data['pet_image']): ?>
                    <div class="pet-section">
                        <div class="pet-card">
                            <img src="<?php echo htmlspecialchars($tracking_data['pet_image']); ?>" alt="Pet" class="pet-image">
                            <div class="pet-details">
                                <h3>🐾 <?php echo htmlspecialchars($tracking_data['pet_name']); ?></h3>
                                <div class="pet-info">
                                    <p><strong>Order Total:</strong> ₹<?php echo number_format($tracking_data['total_amount']); ?></p>
                                    <p><strong>Quantity:</strong> <?php echo htmlspecialchars($tracking_data['quantity']); ?></p>
                                    <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($tracking_data['payment_status']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Delivery Details -->
                <div class="delivery-details">
                    <div class="detail-box">
                        <div class="detail-label">📅 Delivery Date</div>
                        <div class="detail-value">
                            <?php echo $tracking_data['delivery_date'] ? date('M d, Y', strtotime($tracking_data['delivery_date'])) : 'Not scheduled'; ?>
                        </div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">⏰ Time Slot</div>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($tracking_data['delivery_time'] ?? 'Not specified'); ?>
                        </div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">🚚 Delivery Type</div>
                        <div class="detail-value">
                            <?php echo ucfirst(htmlspecialchars($tracking_data['delivery_type'] ?? 'Standard')); ?>
                        </div>
                    </div>
                    <div class="detail-box">
                        <div class="detail-label">📍 Delivery Address</div>
                        <div class="detail-value">
                            <?php echo htmlspecialchars(substr($tracking_data['shipping_address'], 0, 30) . (strlen($tracking_data['shipping_address']) > 30 ? '...' : '')); ?>
                        </div>
                    </div>
                </div>

                <!-- Special Instructions -->
                <?php
                $delivery_prefs = json_decode($tracking_data['delivery_preferences'], true);
                if ($delivery_prefs && $delivery_prefs['petInstructions']):
                ?>
                    <div class="special-instructions">
                        <h4>🐾 Special Pet Instructions</h4>
                        <p><?php echo htmlspecialchars($delivery_prefs['petInstructions']); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Contact Preferences -->
                <?php if ($delivery_prefs): ?>
                    <div class="contact-options">
                        <h4>📞 Delivery Preferences</h4>
                        <div class="contact-option">
                            <?php echo $delivery_prefs['callBeforeDelivery'] ? '✓' : '○'; ?>
                            Will call before delivery
                        </div>
                        <div class="contact-option">
                            <?php echo $delivery_prefs['whatsappNotification'] ? '✓' : '○'; ?>
                            WhatsApp updates enabled
                        </div>
                        <?php if ($delivery_prefs['deliveryNotes']): ?>
                            <p style="margin-top: 12px; color: #666; font-size: 13px;">
                                <strong>Delivery Notes:</strong> <?php echo htmlspecialchars($delivery_prefs['deliveryNotes']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Help Section -->
                <div style="background: #f0f0f0; padding: 20px; border-radius: 10px; margin-top: 25px; text-align: center;">
                    <h4 style="color: #2c1a0e; margin-bottom: 10px;">Need Help?</h4>
                    <p style="color: #666; margin: 0;">
                        📞 <strong><a href="tel:+919798889456" style="color: #b5860d; text-decoration: none;">+91 97988 89456</a></strong> |
                        💬 <strong><a href="https://wa.me/919798889456" style="color: #b5860d; text-decoration: none;">WhatsApp</a></strong> |
                        ✉️ <strong><a href="mailto:support@pawsstore.in" style="color: #b5860d; text-decoration: none;">support@pawsstore.in</a></strong>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>