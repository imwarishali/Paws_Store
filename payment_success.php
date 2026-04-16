<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

// Get order details from URL or session
$order_number = $_GET['order'] ?? $_SESSION['last_order_number'] ?? 'ORD-' . time();
$total = $_GET['total'] ?? $_SESSION['last_order_total'] ?? '0';
$address = $_GET['address'] ?? $_SESSION['last_order_address'] ?? 'Your delivery address';
$delivery_type = $_SESSION['last_delivery_type'] ?? 'standard';

// Store in session for reference
$_SESSION['last_order_number'] = $order_number;
$_SESSION['last_order_total'] = $total;
$_SESSION['last_order_address'] = $address;
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-wrapper {
            width: 100%;
            max-width: 600px;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(44, 26, 14, 0.15);
            overflow: hidden;
            border-top: 6px solid #4caf50;
        }

        .success-header {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            padding: 50px 30px 30px;
            text-align: center;
            color: white;
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 15px;
            animation: bounce 0.8s ease-out;
        }

        @keyframes bounce {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .success-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .success-header p {
            font-size: 16px;
            opacity: 0.95;
            margin: 0;
        }

        .success-content {
            padding: 40px 30px;
        }

        .order-info {
            background: #f5f5f5;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #2c1a0e;
            font-size: 14px;
        }

        .info-value {
            color: #b5860d;
            font-weight: 700;
            font-size: 15px;
        }

        .notification-box {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border-left: 4px solid #ff9800;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .notification-box p {
            color: #e65100;
            font-weight: 600;
            margin: 0;
            font-size: 15px;
        }

        .notification-box .subtitle {
            color: #666;
            font-size: 13px;
            font-weight: 400;
            margin-top: 8px;
        }

        .success-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 14px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 15px;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(181, 134, 13, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #2c1a0e 0%, #5c3d2e 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(44, 26, 14, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(44, 26, 14, 0.4);
        }

        .delivery-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .delivery-info h4 {
            color: #1565c0;
            margin-bottom: 10px;
            font-size: 15px;
        }

        .delivery-info p {
            color: #0d47a1;
            font-size: 14px;
            margin: 0;
            line-height: 1.6;
        }

        @media (max-width: 600px) {
            .success-header h1 {
                font-size: 28px;
            }

            .success-icon {
                font-size: 60px;
            }

            .success-actions {
                grid-template-columns: 1fr;
            }

            .success-content {
                padding: 25px 20px;
            }

            .order-info {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="success-wrapper">
        <div class="success-container">
            <!-- Success Header -->
            <div class="success-header">
                <div class="success-icon">✅</div>
                <h1>Payment Successful!</h1>
                <p>Your order has been confirmed</p>
            </div>

            <!-- Success Content -->
            <div class="success-content">
                <!-- Order Information -->
                <div class="order-info">
                    <div class="info-row">
                        <span class="info-label">📋 Order Number</span>
                        <span class="info-value"><?php echo htmlspecialchars($order_number); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">💰 Total Amount Paid</span>
                        <span class="info-value">₹<?php echo htmlspecialchars($total); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">📍 Delivery Address</span>
                        <span class="info-value" style="text-align: right;"><?php echo htmlspecialchars(substr($address, 0, 40)); ?><?php echo strlen($address) > 40 ? '...' : ''; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">🚚 Delivery Type</span>
                        <span class="info-value"><?php echo ucfirst(htmlspecialchars($delivery_type)); ?></span>
                    </div>
                </div>

                <!-- Email Notification -->
                <div class="notification-box">
                    <p>📧 Confirmation Email Sent</p>
                    <div class="subtitle">A detailed confirmation email with your order receipt has been sent to your registered email address.</div>
                </div>

                <!-- Delivery Information -->
                <div class="delivery-info">
                    <h4>🚚 Expected Delivery</h4>
                    <p>Your pet will be carefully transported and delivered to you within <strong>3-5 business days</strong>. You can track your order status in real-time.</p>
                </div>

                <!-- Action Buttons -->
                <div class="success-actions">
                    <a href="order_history.php" class="btn btn-primary">📦 Track Order</a>
                    <a href="index.php" class="btn btn-secondary">🏠 Back Home</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-play page load animation
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Add any additional tracking or analytics here
            console.log('Payment success page loaded');
        });
    </script>
</body>

</html>