<?php
require_once 'config.php';

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

// Get cart data from POST
$cart = isset($_POST['cart']) ? json_decode($_POST['cart'], true) : [];
$total = isset($_POST['total']) ? $_POST['total'] : 0;
$special_offer = isset($_POST['special_offer']) ? $_POST['special_offer'] : 'none';

// Parse address data
$address = [];
if (isset($_POST['address'])) {
    $address_data = $_POST['address'];
    if (is_string($address_data)) {
        $decoded = json_decode($address_data, true);
        if (is_array($decoded)) {
            $address = $decoded;
        }
    }
}

// If address is still empty, try individual fields
if (empty($address)) {
    $address = [
        'fullName' => $_POST['fullName'] ?? '',
        'phone' => $_POST['phone'] ?? '',
        'address' => $_POST['address'] ?? '',
        'city' => $_POST['city'] ?? '',
        'state' => $_POST['state'] ?? '',
        'pincode' => $_POST['pincode'] ?? ''
    ];
}

// Parse delivery data
$delivery = isset($_POST['delivery']) && is_string($_POST['delivery'])
    ? json_decode($_POST['delivery'], true)
    : [
        'type' => $_POST['deliveryType'] ?? 'standard',
        'petInstructions' => $_POST['petInstructions'] ?? '',
        'deliveryNotes' => $_POST['deliveryNotes'] ?? ''
    ];

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
    <title>Payment - Pet Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #fff8f5;
            font-family: 'Nunito', sans-serif;
        }

        .breadcrumb-section {
            background: linear-gradient(135deg, #2c1a0e 0%, #8B4513 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .breadcrumb-section h1 {
            margin: 0;
            font-family: 'Playfair Display', serif;
            font-size: 32px;
        }

        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 20px;
        }

        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 2px;
            background: #ddd;
            z-index: 0;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 50px;
            height: 50px;
            background: white;
            border: 3px solid #ddd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: 600;
            color: #666;
            font-size: 18px;
        }

        .step.active .step-number {
            background: #2c1a0e;
            color: white;
            border-color: #2c1a0e;
        }

        .step.completed .step-number {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }

        .step.completed .step-number::after {
            content: '✓';
        }

        .step.completed .step-number {
            font-size: 20px;
        }

        .step-label {
            font-size: 12px;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .step.active .step-label {
            color: #2c1a0e;
            font-weight: 700;
        }

        .payment-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-header h2 {
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .payment-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .payment-options {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
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

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2c1a0e;
            box-shadow: 0 0 0 3px rgba(44, 26, 14, 0.1);
        }

        .order-summary {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .order-summary h3 {
            color: #2c1a0e;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0e68c;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-total {
            font-size: 18px;
            font-weight: 700;
            color: #2c1a0e;
            border-top: 2px solid #eee;
            padding-top: 15px;
            margin-top: 15px;
            text-align: center;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, #2c1a0e 0%, #8B4513 100%);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 26, 14, 0.3);
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

            .order-summary {
                position: static;
            }

            .checkout-steps {
                gap: 10px;
            }

            .step-label {
                font-size: 10px;
            }
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

    <div class="breadcrumb-section">
        <div class="container">
            <h1>💳 Payment</h1>
        </div>
    </div>

    <div class="container">
        <!-- Checkout Steps -->
        <div class="checkout-steps">
            <div class="step completed">
                <div class="step-number">✓</div>
                <div class="step-label">Cart Review</div>
            </div>
            <div class="step completed">
                <div class="step-number">✓</div>
                <div class="step-label">Delivery Address</div>
            </div>
            <div class="step active">
                <div class="step-number">3</div>
                <div class="step-label">Payment</div>
            </div>
        </div>
    </div>

    <div class="ps-wrap">
        <!-- Success Message Section (Initially Hidden) -->
        <div id="success-section" style="display: none;">
            <div class="payment-container">
                <div style="text-align: center; background: #e8f5e9; padding: 40px; border-radius: 12px; border: 2px solid #4caf50;">
                    <h2 style="color: #2e7d32; margin-top: 0;">✅ Payment Successful!</h2>
                    <p style="color: #666; font-size: 16px; margin: 20px 0;">Your order has been confirmed and payment has been successfully processed.</p>

                    <div style="background: white; padding: 30px; border-radius: 8px; margin: 20px 0; text-align: left; max-width: 500px; margin-left: auto; margin-right: auto;">
                        <h3 style="color: #2c1a0e; margin-top: 0; border-bottom: 2px solid #b5860d; padding-bottom: 10px;">Order Details</h3>
                        <p style="margin: 15px 0;"><strong>Order Number:</strong> <span id="success-order-number" style="color: #b5860d; font-weight: bold;">ORD000000000</span></p>
                        <p style="margin: 15px 0;"><strong>Total Amount:</strong> <span id="success-total" style="color: #2c1a0e; font-weight: bold; font-size: 18px;">₹0</span></p>
                        <p style="margin: 15px 0;"><strong>Delivery Address:</strong> <span id="success-address" style="color: #555;">--</span></p>
                        <p style="margin: 15px 0;"><strong>Expected Delivery:</strong> <span id="success-delivery" style="color: #555;">3-5 business days</span></p>
                    </div>

                    <div style="background: #fff3e0; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: left; max-width: 500px; margin-left: auto; margin-right: auto;">
                        <p style="margin: 0; color: #e65100;"><strong>📧 A confirmation email has been sent to your registered email address.</strong></p>
                        <p style="margin: 10px 0 0 0; color: #666; font-size: 14px;">You can track your order status in your Order History page.</p>
                    </div>

                    <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: center;">
                        <a href="order_history.php" style="background: #b5860d; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">📦 View Order History</a>
                        <a href="index.php" style="background: #2c1a0e; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">🏠 Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form Section (Initially Visible) -->
        <div id="payment-form-section">
            <div class="payment-container">
                <div class="payment-header">
                    <h2>Complete Your Payment</h2>
                    <p>Choose your payment method and complete the transaction securely</p>
                </div>

                <form id="payment-form" method="POST" action="process_payment.php" enctype="multipart/form-data">
                    <div class="payment-grid">
                        <div class="payment-options">
                            <h3 style="color: #2c1a0e; margin-top: 0; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #f0e68c;">💳 Secure Payment</h3>
                            <div style="text-align: center;">
                                <p style="color: #666; margin: 15px 0;">Click the button below to open the secure Razorpay checkout gateway.</p>
                                <p style="color: #555; font-size: 14px; margin: 10px 0 20px 0;">You can pay using:</p>
                                <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 20px; font-size: 14px; color: #666;">
                                    <span style="background: #f0f0f0; padding: 6px 12px; border-radius: 6px;">💳 Credit/Debit Cards</span>
                                    <span style="background: #f0f0f0; padding: 6px 12px; border-radius: 6px;">🏦 Netbanking</span>
                                    <span style="background: #f0f0f0; padding: 6px 12px; border-radius: 6px;">📱 UPI</span>
                                    <span style="background: #f0f0f0; padding: 6px 12px; border-radius: 6px;">👛 Wallets</span>
                                </div>
                                <img src="https://upload.wikimedia.org/wikipedia/commons/8/89/Razorpay_logo.svg" alt="Razorpay" style="height: 50px; margin: 20px 0;">
                            </div>
                        </div>

                        <div class="order-summary">
                            <h3>📦 Order Summary</h3>
                            <div id="order-items">
                                <!-- Order items will be populated by JavaScript -->
                            </div>
                            <div class="order-total" id="order-total">
                                Total: ₹<?php echo number_format($total); ?>
                            </div>

                            <input type="hidden" name="cart" value="<?php echo htmlspecialchars(json_encode($cart)); ?>">
                            <input type="hidden" name="address" value="<?php echo htmlspecialchars(json_encode($address)); ?>">
                            <input type="hidden" name="delivery" value="<?php echo htmlspecialchars(json_encode($delivery)); ?>">
                            <input type="hidden" name="special_offer" value="<?php echo htmlspecialchars($special_offer); ?>">

                            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
                            <input type="hidden" name="razorpay_signature" id="razorpay_signature">
                            <input type="hidden" name="payment_method" id="payment_method" value="Razorpay">

                            <button type="button" id="rzp-button1" class="submit-btn">💳 Pay with Razorpay</button>
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
                    if (response.razorpay_payment_id) {
                        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                        document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                        document.getElementById('razorpay_signature').value = response.razorpay_signature;
                        submitPaymentFormAjax();
                    }
                },

                "prefill": {
                    "name": "<?php echo htmlspecialchars($_SESSION['user']['username'] ?? ''); ?>",
                    "email": "<?php echo htmlspecialchars($_SESSION['user']['email'] ?? ''); ?>"
                },

                "theme": {
                    "color": "#b5860d"
                },

                "modal": {
                    "ondismiss": function() {
                        showToast("Payment cancelled", "❌");
                    }
                }
            };

            // AJAX Payment Submission
            function submitPaymentFormAjax() {
                const formData = new FormData(document.getElementById('payment-form'));

                fetch('process_payment.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update success section with order details
                            document.getElementById('success-order-number').textContent = data.order_number;
                            document.getElementById('success-total').textContent = '₹' + new Intl.NumberFormat('en-IN').format(<?php echo $total; ?>);
                            document.getElementById('success-address').textContent = data.address;

                            // Hide payment form and show success section
                            document.getElementById('payment-form-section').style.display = 'none';
                            document.getElementById('success-section').style.display = 'block';

                            // Scroll to top
                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });

                            // Clear cart from localStorage/session
                            if (typeof(Storage) !== 'undefined') {
                                localStorage.removeItem('petStoreCart');
                            }
                        } else {
                            showToast("Payment processing failed: " + (data.error || 'Unknown error'), '❌');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('An error occurred: ' + error.message, '❌');
                    });
            }

            var rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function(response) {
                showToast("Payment Failed: " + response.error.description, '❌');
            });

            document.getElementById('rzp-button1').onclick = function(e) {
                e.preventDefault();
                rzp1.open();
            }
        </script>
</body>

</html>