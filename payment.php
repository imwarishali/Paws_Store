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

$petData = [];
try {
    $stmt = $pdo->query("SELECT * FROM pets");
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
                    <div class="payment-options">
                        <h3>Select Payment Method</h3>

                        <!-- QR Code Payment -->
                        <div class="payment-method" id="qr-method">
                            <div class="payment-method-header" onclick="togglePaymentMethod('qr')">
                                <span>📱 Pay via QR Code</span>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="payment-method-content">
                                <div class="qr-section">
                                    <h4>Scan QR Code to Pay</h4>
                                    <div class="qr-code">
                                        <div style="font-size: 14px; color: #666;">
                                            <div>QR Code Placeholder</div>
                                            <div>UPI ID: pawsstore@upi</div>
                                            <div>Amount: ₹<?php echo number_format($total); ?></div>
                                        </div>
                                    </div>
                                    <p>Scan this QR code with your UPI app or banking app</p>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Transfer -->
                        <div class="payment-method" id="bank-method">
                            <div class="payment-method-header" onclick="togglePaymentMethod('bank')">
                                <span>🏦 Bank Transfer</span>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="payment-method-content">
                                <div class="bank-details">
                                    <h4>Bank Account Details</h4>
                                    <p><strong>Account Name:</strong> Paws Store Pvt Ltd</p>
                                    <p><strong>Account Number:</strong> 123456789012</p>
                                    <p><strong>IFSC Code:</strong> PAWS0001234</p>
                                    <p><strong>Bank Name:</strong> Paws Bank</p>
                                    <p><strong>Branch:</strong> Mumbai Main Branch</p>
                                </div>
                                <p>Transfer the exact amount to the above account</p>
                            </div>
                        </div>

                        <!-- UPI ID -->
                        <div class="payment-method" id="upi-method">
                            <div class="payment-method-header" onclick="togglePaymentMethod('upi')">
                                <span>💳 UPI Transfer</span>
                                <span class="toggle-icon">▼</span>
                            </div>
                            <div class="payment-method-content">
                                <div class="upi-id">
                                    <p><strong>UPI ID:</strong> pawsstore@upi</p>
                                </div>
                                <p>Use this UPI ID in your banking app or UPI app</p>
                            </div>
                        </div>
                    </div>

                    <div class="order-summary">
                        <h3>Order Summary</h3>
                        <div id="order-items">
                            <!-- Order items will be populated by JavaScript -->
                        </div>
                        <div class="order-total" id="order-total">
                            Total: ₹<?php echo number_format($total); ?>
                        </div>

                        <div class="form-group">
                            <label>Transaction ID / Reference Number</label>
                            <input type="text" id="transaction_id" name="transaction_id" required pattern="^[Tt].*" title="Transaction ID must start with 'T'" placeholder="Enter transaction ID (Must start with 'T')">
                        </div>

                        <div class="form-group">
                            <label>Payment Screenshot</label>
                            <div class="file-upload" onclick="document.getElementById('payment-screenshot').click()">
                                <input type="file" id="payment-screenshot" name="payment_screenshot" accept="image/*" required>
                                <div class="file-upload-label">
                                    📎 Click to upload payment screenshot
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="cart" value="<?php echo htmlspecialchars(json_encode($cart)); ?>">
                        <input type="hidden" name="address" value="<?php echo htmlspecialchars(json_encode($address)); ?>">
                        <input type="hidden" name="special_offer" value="<?php echo htmlspecialchars($special_offer); ?>">
                        <input type="hidden" name="payment_method" id="payment_method" value="qr">

                        <button type="submit" class="submit-btn">Confirm Payment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

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

        function togglePaymentMethod(method) {
            // Remove active class from all methods
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('active');
            });

            // Add active class to selected method
            document.getElementById(method + '-method').classList.add('active');

            // Update hidden payment_method field
            const methodInput = document.getElementById('payment_method');
            if (methodInput) {
                methodInput.value = method;
            }
        }

        // File upload preview
        document.getElementById('payment-screenshot').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const label = document.querySelector('.file-upload-label');
                label.textContent = '📎 ' + file.name;
            }
        });

        document.getElementById('payment-form').addEventListener('submit', function(e) {
            const txnId = document.getElementById('transaction_id').value.trim();
            if (!/^[Tt]/.test(txnId)) {
                e.preventDefault();
                alert('Please enter a valid Transaction ID starting with the letter "T".');
            }
        });

        renderOrderItems();

        // Update Cart Count
            const currentUserId = '<?php echo isset($_SESSION["user"]["id"]) ? $_SESSION["user"]["id"] : "guest"; ?>';
            const cartKey = 'pawsCart_' + currentUserId;

            let localCart = JSON.parse(localStorage.getItem(cartKey)) || [];

        function updateCartCount() {
            const cartCountElement = document.getElementById('cart-count');
            const totalItems = localCart.reduce((sum, item) => sum + item.quantity, 0);
            cartCountElement.textContent = totalItems;
            cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
        }
        updateCartCount();
    </script>
</body>

</html>