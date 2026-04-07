<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

// Get cart data from POST or session
$cart = isset($_POST['cart']) ? json_decode($_POST['cart'], true) : [];
$address = isset($_POST['address']) ? json_decode($_POST['address'], true) : [];
$total = isset($_POST['total']) ? $_POST['total'] : 0;
$special_offer = isset($_POST['special_offer']) ? $_POST['special_offer'] : 'none';

if (empty($cart)) {
    header("Location: cart.php");
    exit();
}

require_once 'db.php';

$env = parse_ini_file('.env');
$keyId = $env['RAZORPAY_KEY_ID'] ?? '';
$keySecret = $env['RAZORPAY_KEY_SECRET'] ?? '';

// Create Razorpay Order
$razorpayOrderId = '';
if ($total > 0 && $keyId && $keySecret) {
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'amount' => round($total * 100),
        'currency' => 'INR',
        'receipt' => 'rcpt_' . time()
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $razorpayOrder = json_decode($response, true);
    $razorpayOrderId = $razorpayOrder['id'] ?? '';
}

$petData = [];
try {
    $pet_ids = array_column($cart, 'id');
    $placeholders = implode(',', array_fill(0, count($pet_ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price FROM pets WHERE id IN ($placeholders)");
    $stmt->execute($pet_ids);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $petData[$row['id']] = ['name' => $row['name'], 'price' => (float)$row['price']];
    }
} catch (PDOException $e) {
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway - Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .payment-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-header h1 {
            font-family: 'Playfair Display', serif;
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .payment-options {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .payment-method {
            margin-bottom: 20px;
            border: 2px solid #e8e0d4;
            border-radius: 8px;
            overflow: hidden;
        }

        .payment-method-header {
            background: #f9f9f9;
            padding: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .payment-method-header:hover {
            background: #f0f0f0;
        }

        .payment-method-content {
            display: none;
            padding: 20px;
        }

        .payment-method.active .payment-method-content {
            display: block;
        }

        .payment-method.active .payment-method-header {
            background: #fef9f0;
            border-bottom: 2px solid #b5860d;
        }

        .qr-section {
            text-align: center;
        }

        .qr-code {
            width: 200px;
            height: 200px;
            background: #f0f0f0;
            border: 2px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px auto;
            border-radius: 8px;
        }

        .bank-details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }

        .bank-details h4 {
            margin: 0 0 10px 0;
            color: #2c1a0e;
        }

        .bank-details p {
            margin: 5px 0;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c1a0e;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Nunito', sans-serif;
        }

        .file-upload {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.3s;
        }

        .file-upload:hover {
            border-color: #b5860d;
        }

        .file-upload input[type="file"] {
            display: none;
        }

        .file-upload-label {
            color: #666;
            font-size: 16px;
        }

        .order-summary {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-total {
            font-size: 18px;
            font-weight: 700;
            color: #b5860d;
            border-top: 2px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
            text-align: center;
        }

        .submit-btn {
            width: 100%;
            background: #b5860d;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #9a7210;
        }

        .upi-id {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 10px 0;
        }

        .upi-id strong {
            color: #2e7d32;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .payment-grid {
                grid-template-columns: 1fr;
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
        <div class="payment-container">
            <div class="payment-header">
                <h1>Complete Your Payment</h1>
                <p>Choose your payment method and complete the transaction</p>
            </div>

            <form id="payment-form" method="POST" action="process_payment.php" enctype="multipart/form-data">
                <div class="payment-grid">
                    <div class="payment-options" style="display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                        <h3>Secure Payment via Razorpay</h3>
                        <p style="color: #666; margin-top: 10px;">Click the button below to open the secure Razorpay checkout gateway. You can pay using Cards, Netbanking, UPI, and Wallets.</p>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/8/89/Razorpay_logo.svg" alt="Razorpay" style="height: 40px; margin-top: 20px;">
                    </div>

                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        <div id="order-items">
                            <!-- Order items will be populated by JavaScript -->
                        </div>
                        <div class="order-total" id="order-total">
                            Total: ₹<?php echo number_format($total); ?>
                        </div>

                        <input type="hidden" name="cart" value="<?php echo htmlspecialchars(json_encode($cart)); ?>">
                        <input type="hidden" name="address" value="<?php echo htmlspecialchars(json_encode($address)); ?>">
                        <input type="hidden" name="special_offer" value="<?php echo htmlspecialchars($special_offer); ?>">

                        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
                        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                        <input type="hidden" name="payment_method" id="payment_method" value="Razorpay">

                        <button type="button" id="rzp-button1" class="submit-btn">Pay with Razorpay</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <script>
        const petData = <?php echo json_encode($petData); ?>;

        const cart = <?php echo json_encode($cart); ?>;

        function renderOrderItems() {
            const orderItems = document.getElementById('order-items');
            let html = '';

            cart.forEach(item => {
                const pet = petData[item.id];
                const itemTotal = pet.price * item.quantity;
                html += `
                    <div class="order-item">
                        <span>${pet.name} (x${item.quantity})</span>
                        <span>₹${itemTotal.toLocaleString()}</span>
                    </div>
                `;
            });

            orderItems.innerHTML = html;
        }

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

        renderOrderItems();

        var options = {
            "key": "<?php echo $keyId; ?>",
            "amount": "<?php echo round($total * 100); ?>",
            "currency": "INR",
            "name": "Paws Store",
            "description": "Pet Purchase",
            "order_id": "<?php echo $razorpayOrderId; ?>",
            "handler": function(response) {
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                document.getElementById('payment-form').submit();
            },
            "prefill": {
                "name": "<?php echo htmlspecialchars($_SESSION['user']['username'] ?? ''); ?>",
                "email": "<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>"
            },
            "theme": {
                "color": "#b5860d"
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function(response) {
            showToast("Payment Failed: " + response.error.description, '❌');
        });
        document.getElementById('rzp-button1').onclick = function(e) {
            rzp1.open();
            e.preventDefault();
        }

        // Update Cart Count
        function updateCartCount(count) {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = count;
                cartCountElement.style.display = count > 0 ? 'flex' : 'none';
            }
        }
        // We can just use the PHP cart array length since it's already on the server
        updateCartCount(<?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>);
    </script>
</body>

</html>