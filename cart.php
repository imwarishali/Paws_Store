<?php
session_start();

require_once 'db.php';

$petData = [];
try {
    $stmt = $pdo->query("SELECT * FROM pets");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $petData[$row['id']] = ['name' => $row['name'], 'price' => (float)$row['price'], 'image' => $row['image']];
    }
} catch (PDOException $e) {
}

$isFirstTime = true;
if (isset($_SESSION["user"])) {
    try {
        $order_check_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $order_check_stmt->execute([$_SESSION["user"]["id"]]);
        if ($order_check_stmt->fetchColumn() > 0) {
            $isFirstTime = false;
        }
    } catch (PDOException $e) {
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .cart-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .cart-header h1 {
            font-family: 'Playfair Display', serif;
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .cart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .cart-items {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            margin-right: 15px;
            object-fit: cover;
        }

        .cart-item-details h4 {
            margin: 0 0 5px 0;
            color: #2c1a0e;
        }

        .cart-item-price {
            color: #b5860d;
            font-weight: 600;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        .quantity-btn {
            background: #f0f0f0;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 16px;
        }

        .quantity {
            margin: 0 10px;
            font-weight: 600;
        }

        .remove-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }

        .cart-summary {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .summary-section {
            margin-bottom: 20px;
        }

        .summary-section h3 {
            margin: 0 0 15px 0;
            color: #2c1a0e;
            font-size: 18px;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Nunito', sans-serif;
        }

        .offers {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .offer {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 2px solid #e8e0d4;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .offer:hover {
            border-color: #b5860d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.1);
        }

        .offer input[type="radio"] {
            display: none;
        }

        .offer.selected {
            border-color: #b5860d;
            background: #fef9f0;
        }

        .offer.selected .offer-title {
            color: #b5860d;
        }

        .offer.selected .offer-desc {
            color: #8b5d2a;
        }

        .offer.selected .offer-radio {
            background: #b5860d;
            border-color: #b5860d;
        }

        .offer.selected .offer-radio::after {
            opacity: 1;
            transform: scale(1);
        }

        .offer:last-child {
            margin-bottom: 0;
        }

        .offer-content {
            flex: 1;
            cursor: pointer;
        }

        .offer-title {
            font-weight: 700;
            color: #2c1a0e;
            margin-bottom: 4px;
            font-size: 15px;
            transition: color 0.3s ease;
        }

        .offer input[type="radio"]:checked+.offer-content .offer-title {
            color: #b5860d;
        }

        .offer-desc {
            color: #6b4c2a;
            font-size: 13px;
            transition: color 0.3s ease;
        }

        .offer input[type="radio"]:checked+.offer-content .offer-desc {
            color: #8b5d2a;
        }

        .offer-radio {
            width: 20px;
            height: 20px;
            border: 2px solid #ddd;
            border-radius: 50%;
            background: white;
            position: relative;
            transition: all 0.3s ease;
            margin-left: 15px;
        }

        .offer-radio::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 8px;
            height: 8px;
            background: white;
            border-radius: 50%;
            transform: translate(-50%, -50%) scale(0);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .price-breakdown {
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .total {
            font-size: 18px;
            font-weight: 700;
            color: #b5860d;
            border-top: 2px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }

        .checkout-btn {
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

        .checkout-btn:hover {
            background: #9a7210;
        }

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .empty-cart h2 {
            color: #2c1a0e;
            margin-bottom: 20px;
        }

        .empty-cart a {
            color: #b5860d;
            text-decoration: none;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .cart-grid {
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
        <div class="cart-container">
            <div class="cart-header">
                <h1>Shopping Cart</h1>
                <p>Review your items and complete your purchase</p>
            </div>

            <div id="cart-content">
                <!-- Cart content will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cartContent = document.getElementById('cart-content');

            const petData = <?php echo json_encode($petData); ?>;

            const currentUserId = '<?php echo isset($_SESSION["user"]["id"]) ? $_SESSION["user"]["id"] : "guest"; ?>';
            const cartKey = 'pawsCart_' + currentUserId;

            // TRANSFER GUEST CART TO LOGGED-IN USER
            if (currentUserId !== 'guest') {
                let guestCart = JSON.parse(localStorage.getItem('pawsCart_guest'));
                if (guestCart && guestCart.length > 0) {
                    let userCart = JSON.parse(localStorage.getItem(cartKey)) || [];
                    guestCart.forEach(guestItem => {
                        let existing = userCart.find(item => item.id === guestItem.id);
                        if (existing) existing.quantity += guestItem.quantity;
                        else userCart.push(guestItem);
                    });
                    localStorage.setItem(cartKey, JSON.stringify(userCart));
                    localStorage.removeItem('pawsCart_guest');
                }
            }

            let cart = JSON.parse(localStorage.getItem(cartKey)) || [];

            window.appliedPromoType = 'none';
            window.appliedPromoCode = '';

            const isFirstTime = <?php echo $isFirstTime ? 'true' : 'false'; ?>;

            function renderCart() {
                if (cart.length === 0) {
                    cartContent.innerHTML = `
            <div class="empty-cart">
              <h2>Your cart is empty</h2>
              <p>Add some pets to get started!</p>
              <a href="index.php">Browse Pets</a>
            </div>
          `;
                    return;
                }

                let subtotal = 0;
                const cartItemsHtml = cart.map(item => {
                    const pet = petData[item.id];
                    const itemTotal = pet.price * item.quantity;
                    subtotal += itemTotal;

                    return `
            <div class="cart-item" data-id="${item.id}">
              <img src="${item.image}" alt="${item.name}">
              <div class="cart-item-details">
                <h4>${pet.name}</h4>
                <div class="cart-item-price">₹${pet.price.toLocaleString()}</div>
              </div>
              <div class="quantity-controls">
                <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">-</button>
                <span class="quantity">${item.quantity}</span>
                <button class="quantity-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                <button class="remove-btn" onclick="removeItem(${item.id})">Remove</button>
              </div>
            </div>
          `;
                }).join('');

                const shipping = subtotal > 5000 ? 0 : 500;
                const tax = Math.round(subtotal * 0.18);
                const total = subtotal + shipping + tax;

                cartContent.innerHTML = `
          <div class="cart-grid">
            <div class="cart-items">
              <h3>Your Items</h3>
              ${cartItemsHtml}
            </div>

            <div class="cart-summary">
              <div class="summary-section">
                <h3>Delivery Address</h3>
                <div class="form-group">
                  <label>Full Name</label>
                  <input type="text" id="fullName" placeholder="Enter your full name">
                </div>
                <div class="form-group">
                  <label>Phone Number</label>
                  <input type="tel" id="phone" placeholder="Enter your 10-digit phone number" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
                <div class="form-group">
                  <label>Address</label>
                  <textarea id="address" rows="3" placeholder="Enter your complete address"></textarea>
                </div>
                <div class="form-group">
                  <label>City</label>
                  <input type="text" id="city" placeholder="Enter your city">
                </div>
                <div class="form-group">
                  <label>State</label>
                  <select id="state">
                    <option value="">Select State</option>
                    <option value="Maharashtra">Maharashtra</option>
                    <option value="Delhi">Delhi</option>
                    <option value="Karnataka">Karnataka</option>
                    <option value="Tamil Nadu">Tamil Nadu</option>
                    <option value="Uttar Pradesh">Uttar Pradesh</option>
                    <option value="Gujarat">Gujarat</option>
                    <option value="Rajasthan">Rajasthan</option>
                    <option value="Punjab">Punjab</option>
                    <option value="Haryana">Haryana</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>PIN Code</label>
                  <input type="text" id="pincode" placeholder="Enter 6-digit PIN code" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>
              </div>

              <div class="summary-section">
                <h3>Promo Code</h3>
                <div style="display: flex; gap: 10px;">
                  <input type="text" id="promoCodeInput" placeholder="Enter promo code" value="${appliedPromoCode}" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Nunito', sans-serif;">
                  <button type="button" onclick="applyPromoCode()" style="padding: 10px 20px; background: #2c1a0e; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">Apply</button>
                </div>
                <div style="margin-top: 8px; font-size: 13px; color: #666;">Try codes: <strong>FIRST10</strong>, <strong>BULK5</strong>, <strong>VET500</strong>, <strong>SAVE20</strong></div>
                <div id="promoMessage" style="margin-top: 10px; font-size: 14px; font-weight: 600;"></div>
              </div>

              <div class="summary-section">
                <h3>Payment Summary</h3>
                <div class="price-breakdown">
                  <div class="price-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">₹${subtotal.toLocaleString()}</span>
                  </div>
                  <div class="price-row">
                    <span>Shipping:</span>
                    <span id="shipping">₹${shipping.toLocaleString()}</span>
                  </div>
                  <div class="price-row">
                    <span>Tax (18%):</span>
                    <span id="tax">₹${tax.toLocaleString()}</span>
                  </div>
                  <div class="price-row">
                    <span>Discount:</span>
                    <span id="discount">-₹0</span>
                  </div>
                  <div class="price-row total">
                    <span>Total:</span>
                    <span id="total">₹${total.toLocaleString()}</span>
                  </div>
                </div>
              </div>

              <button class="checkout-btn" onclick="processPayment()">Complete Purchase</button>
            </div>
          </div>
        `;
            }

            window.updateQuantity = function(id, newQuantity) {
                if (newQuantity <= 0) {
                    removeItem(id);
                    return;
                }

                const item = cart.find(item => item.id == id);
                if (item) {
                    item.quantity = newQuantity;
                    localStorage.setItem(cartKey, JSON.stringify(cart));
                    renderCart();
                    applyPromoCode();
                    updateCartCount();
                }
            };

            window.removeItem = function(id) {
                cart = cart.filter(item => item.id != id);
                localStorage.setItem(cartKey, JSON.stringify(cart));
                renderCart();
                applyPromoCode();
                updateCartCount();
            };

            window.applyPromoCode = function() {
                if (cart.length === 0) {
                    alert('Please add items to your cart before entering a promo code.');
                    appliedPromoType = 'none';
                    return;
                }

                const subtotal = cart.reduce((sum, item) => sum + (petData[item.id].price * item.quantity), 0);
                const inputElement = document.getElementById('promoCodeInput');
                if (!inputElement) return;

                appliedPromoCode = inputElement.value.trim().toUpperCase();
                const msg = document.getElementById('promoMessage');

                if (appliedPromoCode === 'FIRST10') {
                    if (isFirstTime) {
                        appliedPromoType = 'firstTime';
                        msg.textContent = 'Promo code applied! 10% off on your first purchase.';
                        msg.style.color = '#28a745';
                    } else {
                        appliedPromoType = 'none';
                        msg.textContent = 'FIRST10 is only valid for your first purchase.';
                        msg.style.color = '#dc3545';
                    }
                } else if (appliedPromoCode === 'BULK5') {
                    if (cart.length >= 2) {
                        appliedPromoType = 'bulkDiscount';
                        msg.textContent = 'Promo code applied! 5% off for 2+ pets.';
                        msg.style.color = '#28a745';
                    } else {
                        appliedPromoType = 'none';
                        msg.textContent = 'BULK5 requires 2 or more pets in cart.';
                        msg.style.color = '#dc3545';
                    }
                } else if (appliedPromoCode === 'VET500') {
                    appliedPromoType = 'freeVet';
                    msg.textContent = 'Promo code applied! ₹500 off for free vet consultation.';
                    msg.style.color = '#28a745';
                } else if (appliedPromoCode === 'SAVE20') {
                    if (subtotal > 10000) {
                        appliedPromoType = 'save20';
                        msg.textContent = 'Promo code applied! Flat ₹2000 discount.';
                        msg.style.color = '#28a745';
                    } else {
                        appliedPromoType = 'none';
                        msg.textContent = 'SAVE20 requires an order total over ₹10,000.';
                        msg.style.color = '#dc3545';
                    }
                } else if (appliedPromoCode === '') {
                    appliedPromoType = 'none';
                    msg.textContent = '';
                } else {
                    appliedPromoType = 'none';
                    msg.textContent = 'Invalid promo code.';
                    msg.style.color = '#dc3545';
                }

                calculateTotals();
            };

            window.calculateTotals = function() {
                const subtotal = cart.reduce((sum, item) => sum + (petData[item.id].price * item.quantity), 0);
                let discount = 0;

                if (appliedPromoType === 'firstTime') {
                    discount += Math.round(subtotal * 0.1);
                } else if (appliedPromoType === 'bulkDiscount' && cart.length >= 2) {
                    discount += Math.round(subtotal * 0.05);
                } else if (appliedPromoType === 'freeVet') {
                    discount += 500;
                } else if (appliedPromoType === 'save20') {
                    discount += 2000;
                }

                const shipping = subtotal > 5000 ? 0 : 500;
                const tax = Math.round((subtotal - discount) * 0.18);
                const total = subtotal - discount + shipping + tax;

                document.getElementById('discount').textContent = `-₹${discount.toLocaleString()}`;
                document.getElementById('tax').textContent = `₹${tax.toLocaleString()}`;
                document.getElementById('total').textContent = `₹${total.toLocaleString()}`;
            };

            window.processPayment = function() {
                if (currentUserId === 'guest') {
                    alert('Please log in or sign up to complete your purchase.');
                    window.location.href = 'auth/login.php?redirect=cart.php';
                    return;
                }

                const fullName = document.getElementById('fullName').value;
                const phone = document.getElementById('phone').value;
                const address = document.getElementById('address').value;
                const city = document.getElementById('city').value;
                const state = document.getElementById('state').value;
                const pincode = document.getElementById('pincode').value;

                if (!fullName || !phone || !address || !city || !state || !pincode) {
                    alert('Please fill in all address fields');
                    return;
                }

                if (!/^\d{10}$/.test(phone)) {
                    alert('Please enter a valid 10-digit phone number');
                    return;
                }

                if (!/^\d{6}$/.test(pincode)) {
                    alert('Please enter a valid 6-digit PIN code');
                    return;
                }

                // Calculate final total
                const subtotal = cart.reduce((sum, item) => sum + (petData[item.id].price * item.quantity), 0);
                let discount = 0;

                if (appliedPromoType === 'firstTime') {
                    discount += Math.round(subtotal * 0.1);
                } else if (appliedPromoType === 'bulkDiscount' && cart.length >= 2) {
                    discount += Math.round(subtotal * 0.05);
                } else if (appliedPromoType === 'freeVet') {
                    discount += 500;
                } else if (appliedPromoType === 'save20') {
                    discount += 2000;
                }

                const shipping = subtotal > 5000 ? 0 : 500;
                const tax = Math.round((subtotal - discount) * 0.18);
                const total = subtotal - discount + shipping + tax;

                // Create form data
                const addressData = {
                    fullName,
                    phone,
                    address,
                    city,
                    state,
                    pincode
                };

                // Create and submit form to payment.php
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'payment.php';

                // Add cart data
                const cartInput = document.createElement('input');
                cartInput.type = 'hidden';
                cartInput.name = 'cart';
                cartInput.value = JSON.stringify(cart);
                form.appendChild(cartInput);

                // Add address data
                const addressInput = document.createElement('input');
                addressInput.type = 'hidden';
                addressInput.name = 'address';
                addressInput.value = JSON.stringify(addressData);
                form.appendChild(addressInput);

                // Add total
                const totalInput = document.createElement('input');
                totalInput.type = 'hidden';
                totalInput.name = 'total';
                totalInput.value = total;
                form.appendChild(totalInput);

                // Add special offer
                const offerInput = document.createElement('input');
                offerInput.type = 'hidden';
                offerInput.name = 'special_offer';
                offerInput.value = appliedPromoType;
                form.appendChild(offerInput);

                document.body.appendChild(form);
                form.submit();
            };

            // Function to update cart count
            function updateCartCount() {
                const cartCountElement = document.getElementById('cart-count');
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                cartCountElement.textContent = totalItems;
                cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
            }

            renderCart();
            updateCartCount();
            applyPromoCode();
        });
    </script>
</body>

</html>
</content>