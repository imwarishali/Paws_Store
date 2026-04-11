<?php
session_start();
require 'config.php';

// Verify user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'] ?? '';
$phone = $_SESSION['user']['phone'] ?? '';

// Get cart data from POST
$cart = isset($_POST['cart']) ? json_decode($_POST['cart'], true) : [];
$addressData = isset($_POST['address']) ? json_decode($_POST['address'], true) : [];
$deliveryData = isset($_POST['delivery']) ? json_decode($_POST['delivery'], true) : [];
$total = isset($_POST['total']) ? floatval($_POST['total']) : 0;
$appliedPromo = isset($_POST['special_offer']) ? $_POST['special_offer'] : 'none';

// Pre-fill address fields with POST data if available
$fullName = $addressData['fullName'] ?? $username;
$postPhone = $addressData['phone'] ?? $phone;
$address = $addressData['address'] ?? '';
$city = $addressData['city'] ?? '';
$state = $addressData['state'] ?? '';
$pincode = $addressData['pincode'] ?? '';

// Pre-fill delivery data
$deliveryType = $deliveryData['type'] ?? 'standard';
$petInstructions = $deliveryData['petInstructions'] ?? '';
$deliveryNotes = $deliveryData['deliveryNotes'] ?? '';

// If no cart data, redirect back
if (empty($cart)) {
    header('Location: cart.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Address - Pet Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
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

        .checkout-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
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

        .address-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .form-section h4 {
            color: #2c1a0e;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0e68c;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2c1a0e;
            box-shadow: 0 0 0 3px rgba(44, 26, 14, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .delivery-type-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .delivery-option {
            padding: 15px;
            border: 2px solid #e8d5a3;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .delivery-option label {
            margin: 0;
            cursor: pointer;
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }

        .delivery-option input[type="radio"] {
            cursor: pointer;
            width: auto;
            margin: 0;
        }

        .delivery-option input[type="radio"]:checked+span {
            font-weight: 700;
        }

        .delivery-option:has(input[type="radio"]:checked) {
            background: #2c1a0e;
            color: white;
            border-color: #2c1a0e;
        }

        .delivery-option:has(input[type="radio"]:checked) label {
            color: white;
        }

        .delivery-option:has(input[type="radio"]:checked) small {
            color: #ffc069;
        }

        .summary-card {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2c1a0e;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .summary-row.total {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 16px;
            font-weight: 700;
            color: #2c1a0e;
        }

        .button-container {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn-back {
            background: #666;
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            font-size: 16px;
        }

        .btn-back:hover {
            background: #555;
            color: white;
            text-decoration: none;
        }

        .btn-continue {
            background: linear-gradient(135deg, #2c1a0e 0%, #8B4513 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
        }

        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 26, 14, 0.3);
            color: white;
        }

        .required-field::after {
            content: ' *';
            color: #dc3545;
        }

        .help-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .checkout-steps {
                gap: 10px;
            }

            .step-label {
                font-size: 10px;
            }

            .button-container {
                flex-direction: column;
            }

            .btn-back,
            .btn-continue {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="breadcrumb-section">
        <div class="container">
            <h1>🛒 Checkout</h1>
        </div>
    </div>

    <div class="container" style="margin-bottom: 40px;">
        <!-- Checkout Steps -->
        <div class="checkout-steps">
            <div class="step completed">
                <div class="step-number">✓</div>
                <div class="step-label">Cart Review</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-label">Delivery Address</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Payment</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="address-container">
                    <form id="deliveryAddressForm" method="POST" action="payment.php">
                        <!-- Delivery Address Section -->
                        <div class="form-section">
                            <h4>🏠 Delivery Address</h4>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="required-field">Full Name</label>
                                    <input type="text" name="fullName" id="fullName" value="<?php echo htmlspecialchars($fullName); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="required-field">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" maxlength="10" value="<?php echo htmlspecialchars($postPhone); ?>" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                    <div class="help-text">10-digit mobile number</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="required-field">Address</label>
                                <textarea name="address" id="address" rows="3" placeholder="Enter street address, building name, etc." required><?php echo htmlspecialchars($address); ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="required-field">City</label>
                                    <input type="text" name="city" id="city" value="<?php echo htmlspecialchars($city); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="required-field">PIN Code</label>
                                    <input type="text" name="pincode" id="pincode" maxlength="6" value="<?php echo htmlspecialchars($pincode); ?>" oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value.length === 6) fetchPincodeDetails(this.value);" required>
                                    <div class="help-text">6-digit postal code</div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="required-field">State/UT</label>
                                <select name="state" id="state" required>
                                    <option value="">Select State/UT</option>
                                    <option value="Andaman and Nicobar Islands" <?php echo ($state === 'Andaman and Nicobar Islands') ? 'selected' : ''; ?>>Andaman and Nicobar Islands</option>
                                    <option value="Andhra Pradesh" <?php echo ($state === 'Andhra Pradesh') ? 'selected' : ''; ?>>Andhra Pradesh</option>
                                    <option value="Arunachal Pradesh" <?php echo ($state === 'Arunachal Pradesh') ? 'selected' : ''; ?>>Arunachal Pradesh</option>
                                    <option value="Assam" <?php echo ($state === 'Assam') ? 'selected' : ''; ?>>Assam</option>
                                    <option value="Bihar" <?php echo ($state === 'Bihar') ? 'selected' : ''; ?>>Bihar</option>
                                    <option value="Chandigarh" <?php echo ($state === 'Chandigarh') ? 'selected' : ''; ?>>Chandigarh</option>
                                    <option value="Chhattisgarh" <?php echo ($state === 'Chhattisgarh') ? 'selected' : ''; ?>>Chhattisgarh</option>
                                    <option value="Dadra and Nagar Haveli and Daman and Diu" <?php echo ($state === 'Dadra and Nagar Haveli and Daman and Diu') ? 'selected' : ''; ?>>Dadra and Nagar Haveli and Daman and Diu</option>
                                    <option value="Delhi" <?php echo ($state === 'Delhi') ? 'selected' : ''; ?>>Delhi</option>
                                    <option value="Goa" <?php echo ($state === 'Goa') ? 'selected' : ''; ?>>Goa</option>
                                    <option value="Gujarat" <?php echo ($state === 'Gujarat') ? 'selected' : ''; ?>>Gujarat</option>
                                    <option value="Haryana" <?php echo ($state === 'Haryana') ? 'selected' : ''; ?>>Haryana</option>
                                    <option value="Himachal Pradesh" <?php echo ($state === 'Himachal Pradesh') ? 'selected' : ''; ?>>Himachal Pradesh</option>
                                    <option value="Jammu and Kashmir" <?php echo ($state === 'Jammu and Kashmir') ? 'selected' : ''; ?>>Jammu and Kashmir</option>
                                    <option value="Jharkhand" <?php echo ($state === 'Jharkhand') ? 'selected' : ''; ?>>Jharkhand</option>
                                    <option value="Karnataka" <?php echo ($state === 'Karnataka') ? 'selected' : ''; ?>>Karnataka</option>
                                    <option value="Kerala" <?php echo ($state === 'Kerala') ? 'selected' : ''; ?>>Kerala</option>
                                    <option value="Ladakh" <?php echo ($state === 'Ladakh') ? 'selected' : ''; ?>>Ladakh</option>
                                    <option value="Lakshadweep" <?php echo ($state === 'Lakshadweep') ? 'selected' : ''; ?>>Lakshadweep</option>
                                    <option value="Madhya Pradesh" <?php echo ($state === 'Madhya Pradesh') ? 'selected' : ''; ?>>Madhya Pradesh</option>
                                    <option value="Maharashtra" <?php echo ($state === 'Maharashtra') ? 'selected' : ''; ?>>Maharashtra</option>
                                    <option value="Manipur" <?php echo ($state === 'Manipur') ? 'selected' : ''; ?>>Manipur</option>
                                    <option value="Meghalaya" <?php echo ($state === 'Meghalaya') ? 'selected' : ''; ?>>Meghalaya</option>
                                    <option value="Mizoram" <?php echo ($state === 'Mizoram') ? 'selected' : ''; ?>>Mizoram</option>
                                    <option value="Nagaland" <?php echo ($state === 'Nagaland') ? 'selected' : ''; ?>>Nagaland</option>
                                    <option value="Odisha" <?php echo ($state === 'Odisha') ? 'selected' : ''; ?>>Odisha</option>
                                    <option value="Puducherry" <?php echo ($state === 'Puducherry') ? 'selected' : ''; ?>>Puducherry</option>
                                    <option value="Punjab" <?php echo ($state === 'Punjab') ? 'selected' : ''; ?>>Punjab</option>
                                    <option value="Rajasthan" <?php echo ($state === 'Rajasthan') ? 'selected' : ''; ?>>Rajasthan</option>
                                    <option value="Sikkim" <?php echo ($state === 'Sikkim') ? 'selected' : ''; ?>>Sikkim</option>
                                    <option value="Tamil Nadu" <?php echo ($state === 'Tamil Nadu') ? 'selected' : ''; ?>>Tamil Nadu</option>
                                    <option value="Telangana" <?php echo ($state === 'Telangana') ? 'selected' : ''; ?>>Telangana</option>
                                    <option value="Tripura" <?php echo ($state === 'Tripura') ? 'selected' : ''; ?>>Tripura</option>
                                    <option value="Uttar Pradesh" <?php echo ($state === 'Uttar Pradesh') ? 'selected' : ''; ?>>Uttar Pradesh</option>
                                    <option value="Uttarakhand" <?php echo ($state === 'Uttarakhand') ? 'selected' : ''; ?>>Uttarakhand</option>
                                    <option value="West Bengal" <?php echo ($state === 'West Bengal') ? 'selected' : ''; ?>>West Bengal</option>
                                </select>
                            </div>
                        </div>

                        <!-- Delivery Preferences Section -->
                        <div class="form-section">
                            <h4>🚚 Delivery Preferences</h4>

                            <label class="required-field" style="font-weight: 600; margin-bottom: 15px; display: block;">Delivery Type</label>
                            <div class="delivery-type-options">
                                <div class="delivery-option">
                                    <label>
                                        <input type="radio" name="deliveryType" value="standard" <?php echo ($deliveryType === 'standard') ? 'checked' : ''; ?>>
                                        <span>
                                            Standard<br>
                                            <small style="font-size: 12px;">3-5 days</small>
                                        </span>
                                    </label>
                                </div>
                                <div class="delivery-option">
                                    <label>
                                        <input type="radio" name="deliveryType" value="express" <?php echo ($deliveryType === 'express') ? 'checked' : ''; ?>>
                                        <span>
                                            Express<br>
                                            <small style="font-size: 12px;">1-2 days</small>
                                        </span>
                                    </label>
                                </div>
                                <div class="delivery-option">
                                    <label>
                                        <input type="radio" name="deliveryType" value="sameday" <?php echo ($deliveryType === 'sameday') ? 'checked' : ''; ?>>
                                        <span>
                                            Same Day<br>
                                            <small style="font-size: 12px;">If available</small>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>🐾 Special Instructions for Pet</label>
                                <textarea name="petInstructions" id="petInstructions" rows="3" placeholder="E.g., Pet is shy, needs gentle handling, has anxiety, medical conditions, etc."><?php echo htmlspecialchars($petInstructions); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>📝 Delivery Notes</label>
                                <textarea name="deliveryNotes" id="deliveryNotes" rows="2" placeholder="E.g., Gate code, Building description, preferred exit point, etc."><?php echo htmlspecialchars($deliveryNotes); ?></textarea>
                            </div>
                        </div>

                        <!-- Hidden fields to pass data forward -->
                        <input type="hidden" name="cart" value="<?php echo htmlspecialchars(json_encode($cart)); ?>">
                        <input type="hidden" name="total" value="<?php echo $total; ?>">
                        <input type="hidden" name="special_offer" value="<?php echo htmlspecialchars($appliedPromo); ?>">

                        <div class="button-container">
                            <button type="button" class="btn-back" onclick="history.back()">← Back to Cart</button>
                            <button type="submit" class="btn-continue">Continue to Payment →</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary Card Sidebar -->
            <div class="col-md-4">
                <div class="summary-card" style="position: sticky; top: 20px;">
                    <h5 style="color: #2c1a0e; font-weight: 700; margin-bottom: 15px;">📦 Order Summary</h5>

                    <?php
                    $subtotal = 0;
                    $itemCount = 0;
                    foreach ($cart as $item) {
                        $itemCount++;
                        $subtotal += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
                    }

                    $shipping = $subtotal > 5000 ? 0 : 500;
                    $discount = 0;

                    if ($appliedPromo === 'firstTime') {
                        $discount = round($subtotal * 0.1);
                    } elseif ($appliedPromo === 'bulkDiscount' && count($cart) >= 2) {
                        $discount = round($subtotal * 0.05);
                    } elseif ($appliedPromo === 'freeVet') {
                        $discount = 500;
                    } elseif ($appliedPromo === 'save20') {
                        $discount = 2000;
                    }

                    $tax = round(($subtotal - $discount) * 0.18);
                    $finalTotal = $subtotal - $discount + $shipping + $tax;
                    ?>

                    <div class="summary-row">
                        <span><?php echo $itemCount; ?> Pet(s)</span>
                        <span>₹<?php echo number_format($subtotal, 0); ?></span>
                    </div>

                    <?php if ($shipping > 0): ?>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>₹<?php echo number_format($shipping, 0); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span style="color: #28a745; font-weight: 600;">FREE</span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-row">
                        <span>Tax (18%)</span>
                        <span>₹<?php echo number_format($tax, 0); ?></span>
                    </div>

                    <?php if ($discount > 0): ?>
                        <div class="summary-row" style="color: #28a745;">
                            <span>Discount</span>
                            <span>-₹<?php echo number_format($discount, 0); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="summary-row total">
                        <span>Total Amount</span>
                        <span>₹<?php echo number_format($finalTotal, 0); ?></span>
                    </div>

                    <?php if ($appliedPromo !== 'none' && $appliedPromo !== ''): ?>
                        <div style="margin-top: 10px; padding: 10px; background: #e8f5e9; border-radius: 6px; font-size: 12px; color: #2e7d32;">
                            ✓ Promo code applied successfully!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showToast(message, icon = '📝') {
            const toast = document.createElement('div');
            toast.textContent = icon + ' ' + message;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #333;
                color: white;
                padding: 15px 20px;
                border-radius: 6px;
                z-index: 9999;
                animation: slideIn 0.3s ease-out;
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('deliveryAddressForm');
            form.addEventListener('submit', function(e) {
                const fullName = document.getElementById('fullName').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const address = document.getElementById('address').value.trim();
                const city = document.getElementById('city').value.trim();
                const state = document.getElementById('state').value.trim();
                const pincode = document.getElementById('pincode').value.trim();

                // Validation
                if (!fullName) {
                    e.preventDefault();
                    showToast('Please enter your full name.', '⚠️');
                    return;
                }

                if (!phone || !/^\d{10}$/.test(phone)) {
                    e.preventDefault();
                    showToast('Please enter a valid 10-digit phone number.', '⚠️');
                    return;
                }

                if (!address) {
                    e.preventDefault();
                    showToast('Please enter your address.', '⚠️');
                    return;
                }

                if (!city) {
                    e.preventDefault();
                    showToast('Please enter your city.', '⚠️');
                    return;
                }

                if (!state) {
                    e.preventDefault();
                    showToast('Please select a state/UT.', '⚠️');
                    return;
                }

                if (!pincode || !/^\d{6}$/.test(pincode)) {
                    e.preventDefault();
                    showToast('Please enter a valid 6-digit PIN code.', '⚠️');
                    return;
                }
            });
        });

        function fetchPincodeDetails(pincode) {
            fetch(`https://api.postalpincode.in/pincode/${pincode}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data[0] && data[0].Status === 'Success' && data[0].PostOffice && data[0].PostOffice.length > 0) {
                        const postOffice = data[0].PostOffice[0];
                        const city = postOffice.District || postOffice.Block || postOffice.Region;
                        const state = postOffice.State;

                        const cityInput = document.getElementById('city');
                        const stateSelect = document.getElementById('state');

                        if (cityInput && !cityInput.value) cityInput.value = city;
                        if (stateSelect) {
                            for (let i = 0; i < stateSelect.options.length; i++) {
                                const optionText = stateSelect.options[i].text.toLowerCase();
                                const apiState = state.toLowerCase();
                                if (optionText === apiState || optionText.includes(apiState) || apiState.includes(optionText)) {
                                    stateSelect.selectedIndex = i;
                                    break;
                                }
                            }
                        }
                        showToast('Location auto-filled successfully!', '📍');
                    } else {
                        showToast('Could not find location for this PIN code.', '⚠️');
                    }
                })
                .catch(error => {
                    console.error('Error fetching pincode details:', error);
                    showToast('Error fetching location details.', '⚠️');
                });
        }
    </script>

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
</body>

</html>