<?php
require_once 'config.php';
require_once 'db.php';

// Redirect guests to login if they try to access the cart
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php?redirect=cart.php");
    exit();
}

$cart_for_js = [];
try {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Only fetch database prices for items ACTUALLY in the cart
    if (!empty($_SESSION['cart'])) {
        $ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, name, price, image FROM pets WHERE id IN ($placeholders)");
        $stmt->execute($ids);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['quantity'] = $_SESSION['cart'][$row['id']];
            $row['price'] = (float)$row['price'];
            $cart_for_js[] = $row;
        }
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
            margin-bottom: 40px;
            position: relative;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            padding: 0 20px;
        }

        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 26px;
            left: 5%;
            right: 5%;
            height: 3px;
            background: linear-gradient(90deg, #b5860d 0%, #ddd 100%);
            z-index: 0;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-number {
            width: 56px;
            height: 56px;
            background: white;
            border: 3px solid #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-weight: 700;
            color: #999;
            font-size: 20px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .step.active .step-number {
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: white;
            border-color: #b5860d;
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.3);
        }

        .step.completed .step-number {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
            border-color: #4caf50;
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.2);
        }

        .step-label {
            font-size: 13px;
            color: #888;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 8px;
            letter-spacing: 0.5px;
        }

        .step.active .step-label {
            color: #2c1a0e;
            font-weight: 700;
        }

        .step.completed .step-label {
            color: #4caf50;
            font-weight: 700;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .cart-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .cart-header h2 {
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
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #e0e0e0;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item:hover {
            background: rgba(181, 134, 13, 0.02);
            padding-left: 8px;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            margin-right: 20px;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .cart-item-details h4 {
            margin: 0 0 6px 0;
            color: #2c1a0e;
            font-size: 16px;
            font-weight: 700;
            font-family: 'Playfair Display', serif;
        }

        .cart-item-price {
            color: #b5860d;
            font-weight: 700;
            font-size: 16px;
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
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .summary-section {
            margin-bottom: 20px;
        }

        .summary-section h3 {
            margin: 0 0 15px 0;
            color: #2c1a0e;
            font-size: 16px;
            font-weight: 700;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0e68c;
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

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 26, 14, 0.3);
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

        .empty-cart-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-cart a {
            display: inline-block;
            background: #b5860d;
            color: #fff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 24px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .empty-cart a:hover {
            background: #9a7210;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.2);
        }

        /* Delivery Options Styles */
        input[type="radio"]:checked+span,
        input[type="checkbox"]:checked+span {
            font-weight: 600;
            color: #2c1a0e;
        }

        label[style*="border"] input[type="radio"]:checked {
            border: 2px solid #b5860d;
            accent-color: #b5860d;
        }

        input[type="date"],
        input[type="text"],
        select,
        textarea {
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .cart-grid {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }

            .checkout-steps {
                gap: 10px;
                padding: 0 10px;
            }

            .step-label {
                font-size: 10px;
            }

            .breadcrumb-section h1 {
                font-size: 24px;
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
            <h1>🛒 Shopping Cart</h1>
        </div>
    </div>

    <div class="container">
        <!-- Checkout Steps -->
        <div class="checkout-steps">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Cart Review</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Delivery Address</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Payment</div>
            </div>
        </div>
    </div>

    <div class="ps-wrap">
        <div class="cart-container">
            <div class="cart-header">
                <h2>Review your items and proceed to checkout</h2>
            </div>

            <div id="cart-content">
                <!-- Cart content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Mobile App-like Bottom Navigation -->
    <div class="mobile-bottom-nav">
        <a href="index.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <span class="mobile-nav-icon">🏠</span>
            <span>Home</span>
        </a>
        <a href="index.php#categories" class="mobile-nav-item">
            <span class="mobile-nav-icon">🔍</span>
            <span>Shop</span>
        </a>
        <a href="wishlist.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'wishlist.php' ? 'active' : ''; ?>">
            <span class="mobile-nav-icon">🤍</span>
            <span>Wishlist</span>
        </a>
        <a href="cart.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">
            <span class="mobile-nav-icon">🛒</span>
            <span>Cart</span>
            <span id="mobile-cart-count" class="mobile-cart-badge" style="display: none;">0</span>
        </a>
        <a href="profile.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <span class="mobile-nav-icon">👤</span>
            <span>Profile</span>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cartContent = document.getElementById('cart-content');

            // Load the securely compiled cart directly from PHP!
            let cart = <?php echo json_encode($cart_for_js); ?>;

            window.appliedPromoType = 'none';
            window.appliedPromoCode = '';

            const isFirstTime = <?php echo $isFirstTime ? 'true' : 'false'; ?>;

            // Auto-fill coupon from marquee if copied
            const copiedCoupon = localStorage.getItem('copiedCoupon');
            if (copiedCoupon) {
                const promoInput = document.getElementById('promoCodeInput');
                if (promoInput) {
                    promoInput.value = copiedCoupon;
                    // Clear localStorage after use
                    localStorage.removeItem('copiedCoupon');
                    // Auto-apply if cart has items
                    setTimeout(() => {
                        if (cart.length > 0) {
                            applyPromoCode(false);
                        }
                    }, 100);
                }
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

            function renderCart() {
                if (cart.length === 0) {
                    cartContent.innerHTML = `
            <div class="empty-cart">
              <div class="empty-cart-icon">🛒</div>
              <h2>Your cart is empty</h2>
              <p>Add some pets to get started!</p>
              <a href="index.php">Browse Pets</a>
            </div>
          `;
                    return;
                }

                let subtotal = 0;
                const cartItemsHtml = cart.map(item => {
                    const itemTotal = item.price * item.quantity;
                    subtotal += itemTotal;

                    return `
            <div class="cart-item" data-id="${item.id}">
              <img src="${item.image}" alt="${item.name}">
              <div class="cart-item-details">
                <h4>${item.name}</h4>
                <div class="cart-item-price">₹${item.price.toLocaleString()}</div>
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




              <div class="summary-section" style="background: linear-gradient(135deg, #faf7f2 0%, #f5f1ec 100%); border: 2px solid #e8d9cc; border-radius: 8px; padding: 16px; box-shadow: 0 2px 8px rgba(44, 26, 14, 0.08);">
                <h3 style="color: #2c1a0e; margin-bottom: 12px; font-size: 16px; display: flex; align-items: center; gap: 8px;">🎁 Promo Code</h3>
                <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                  <input type="text" id="promoCodeInput" value="${appliedPromoCode}" placeholder="Enter promo code" style="flex: 1; padding: 12px 14px; border: 2px solid #ddd; border-radius: 6px; font-family: 'Nunito', sans-serif; font-size: 14px; transition: all 0.3s ease; background: white;" onmouseover="this.style.borderColor='#c9a876'; this.style.boxShadow='0 2px 6px rgba(201, 168, 118, 0.2)';" onmouseout="this.style.borderColor='#ddd'; this.style.boxShadow='none';" onfocus="this.style.borderColor='#c9a876'; this.style.boxShadow='0 0 0 3px rgba(201, 168, 118, 0.1)';" onblur="this.style.borderColor='#ddd'; this.style.boxShadow='none';">
                  <button type="button" onclick="applyPromoCode(true)" style="padding: 12px 24px; background: linear-gradient(135deg, #2c1a0e 0%, #1f1107 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(44, 26, 14, 0.2);" onmouseover="this.style.boxShadow='0 4px 8px rgba(44, 26, 14, 0.3)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow='0 2px 4px rgba(44, 26, 14, 0.2)'; this.style.transform='translateY(0)';" onmousedown="this.style.transform='translateY(0)';">✓ Apply</button>
                </div>
                <div style="margin-bottom: 10px; padding: 10px; background: rgba(201, 168, 118, 0.1); border-left: 4px solid #c9a876; border-radius: 4px; font-size: 13px; color: #555;">
                  <strong style="color: #2c1a0e;">💡 Try codes:</strong> 
                  <span style="display: inline-flex; gap: 8px; margin-left: 6px; flex-wrap: wrap;">
                    <?php if ($isFirstTime): ?>
                      <span style="background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd; color: #2c1a0e; font-weight: 600;">🎉 FIRST10</span>
                    <?php endif; ?>
                    <span style="background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd; color: #2c1a0e; font-weight: 600;">BULK5</span>
                    <span style="background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd; color: #2c1a0e; font-weight: 600;">VET500</span>
                    <span style="background: white; padding: 4px 8px; border-radius: 4px; border: 1px solid #ddd; color: #2c1a0e; font-weight: 600;">SAVE20</span>
                  </span>
                </div>
                <div id="promoMessage" style="margin-top: 10px; font-size: 14px; font-weight: 600; min-height: 20px; transition: all 0.3s ease;"></div>
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

              <button class="checkout-btn" onclick="continueToDeliveryAddress()">Continue to Delivery Address →</button>
            </div>
          </div>
        `;
            }

            window.fetchPincodeDetails = function(pincode) {
                fetch(`https://api.postalpincode.in/pincode/${pincode}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data[0] && data[0].Status === 'Success' && data[0].PostOffice && data[0].PostOffice.length > 0) {
                            const postOffice = data[0].PostOffice[0];
                            const city = postOffice.District || postOffice.Block || postOffice.Region;
                            const state = postOffice.State;

                            const cityInput = document.getElementById('city');
                            const stateSelect = document.getElementById('state');

                            if (cityInput) cityInput.value = city;
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
                    .catch(error => console.error('Error fetching pincode details:', error));
            };

            window.updateQuantity = function(id, newQuantity) {
                if (newQuantity <= 0) {
                    removeItem(id);
                    return;
                }

                const item = cart.find(item => item.id == id);
                if (item) {
                    item.quantity = newQuantity;

                    // Securely update session cart via AJAX
                    fetch('cart_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'update',
                            id: id,
                            quantity: newQuantity
                        })
                    }).then(() => {
                        renderCart();
                        applyPromoCode(false);
                        updateCartCount();
                    });
                }
            };

            window.removeItem = function(id) {
                const itemToRemove = cart.find(item => item.id == id);
                if (itemToRemove) {
                    showToast((itemToRemove.name) + " removed from cart!", "🗑️");
                }
                cart = cart.filter(item => item.id != id);

                // Securely remove from session cart via AJAX
                fetch('cart_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'remove',
                        id: id
                    })
                }).then(() => {
                    renderCart();
                    applyPromoCode(false);
                    updateCartCount();
                });
            };

            window.applyPromoCode = function(isManual = false) {
                if (cart.length === 0) {
                    if (isManual) {
                        showToast('Please add items to your cart before entering a promo code.', '⚠️');
                    }
                    appliedPromoType = 'none';
                    return;
                }

                const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
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
                } else if (appliedPromoCode === 'CARD15') {
                    appliedPromoType = 'card15';
                    msg.textContent = 'Promo code applied! 15% off with card payment.';
                    msg.style.color = '#28a745';
                } else if (appliedPromoCode === 'SUMMER25') {
                    if (subtotal >= 3000) {
                        appliedPromoType = 'summer25';
                        msg.textContent = 'Promo code applied! 25% off summer special collection.';
                        msg.style.color = '#28a745';
                    } else {
                        appliedPromoType = 'none';
                        msg.textContent = 'SUMMER25 requires minimum purchase of ₹3,000.';
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
                const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                let discount = 0;

                if (appliedPromoType === 'firstTime') {
                    discount += Math.round(subtotal * 0.1);
                } else if (appliedPromoType === 'bulkDiscount' && cart.length >= 2) {
                    discount += Math.round(subtotal * 0.05);
                } else if (appliedPromoType === 'freeVet') {
                    discount += 500;
                } else if (appliedPromoType === 'save20') {
                    discount += 2000;
                } else if (appliedPromoType === 'card15') {
                    discount += Math.round(subtotal * 0.15);
                } else if (appliedPromoType === 'summer25') {
                    discount += Math.round(subtotal * 0.25);
                }

                const shipping = subtotal > 5000 ? 0 : 500;
                const tax = Math.round((subtotal - discount) * 0.18);
                const total = subtotal - discount + shipping + tax;

                document.getElementById('discount').textContent = `-₹${discount.toLocaleString()}`;
                document.getElementById('tax').textContent = `₹${tax.toLocaleString()}`;
                document.getElementById('total').textContent = `₹${total.toLocaleString()}`;
            };

            window.continueToDeliveryAddress = function() {
                if (cart.length === 0) {
                    showToast('Please add items to your cart.', '⚠️');
                    return;
                }

                // Calculate final total
                const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
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

                // Create and submit form to delivery_address.php
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delivery_address.php';

                // Add cart data
                const cartInput = document.createElement('input');
                cartInput.type = 'hidden';
                cartInput.name = 'cart';
                cartInput.value = JSON.stringify(cart);
                form.appendChild(cartInput);

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
                const mobileCartCount = document.getElementById('mobile-cart-count');
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

                if (cartCountElement) {
                    cartCountElement.textContent = totalItems;
                    cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
                }
                if (mobileCartCount) {
                    mobileCartCount.textContent = totalItems;
                    mobileCartCount.style.display = totalItems > 0 ? 'flex' : 'none';
                }
            }

            renderCart();
            updateCartCount();
            applyPromoCode();
        });
    </script>
</body>

</html>
</content>