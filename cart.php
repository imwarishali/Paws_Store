<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
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

            // Sample pet data (in a real app, this would come from a database)
            const petData = {
                1: {
                    name: 'Max — Labrador',
                    price: 15000,
                    image: '🐶'
                },
                2: {
                    name: 'Luna — British Shorthair',
                    price: 18500,
                    image: '🐱'
                },
                3: {
                    name: 'Buddy — Beagle',
                    price: 12000,
                    image: '🐶'
                },
                4: {
                    name: 'Charlie — Pug',
                    price: 10000,
                    image: '🐶'
                },
                5: {
                    name: 'Bella — Golden Retriever',
                    price: 20000,
                    image: '🐶'
                },
                6: {
                    name: 'Rocky — German Shepherd',
                    price: 18000,
                    image: '🐶'
                },
                7: {
                    name: 'Daisy — Bulldog',
                    price: 16000,
                    image: '🐶'
                },
                8: {
                    name: 'Teddy — Shih Tzu',
                    price: 14000,
                    image: '🐶'
                },
                9: {
                    name: 'Coco — Pomeranian',
                    price: 22000,
                    image: '🐶'
                },
                10: {
                    name: 'Bruno — Rottweiler',
                    price: 19000,
                    image: '🐶'
                },
                11: {
                    name: 'Milo — Husky',
                    price: 25000,
                    image: '🐶'
                },
                12: {
                    name: 'Luna — British Shorthair',
                    price: 18500,
                    image: '🐱'
                },
                13: {
                    name: 'Whiskers — Persian',
                    price: 22000,
                    image: '🐱'
                },
                14: {
                    name: 'Shadow — Maine Coon',
                    price: 20000,
                    image: '🐱'
                },
                15: {
                    name: 'Misty — Ragdoll',
                    price: 24000,
                    image: '🐱'
                },
                16: {
                    name: 'Tiger — Bengal',
                    price: 19000,
                    image: '🐱'
                },
                17: {
                    name: 'Smudge — Siamese',
                    price: 17000,
                    image: '🐱'
                },
                18: {
                    name: 'Nala — Abyssinian',
                    price: 21000,
                    image: '🐱'
                },
                19: {
                    name: 'Goldie — Goldfish',
                    price: 500,
                    image: '🐠'
                },
                20: {
                    name: 'Nemo — Clownfish',
                    price: 800,
                    image: '🐠'
                },
                21: {
                    name: 'Bubbles — Betta',
                    price: 600,
                    image: '🐠'
                },
                22: {
                    name: 'Finley — Guppy',
                    price: 400,
                    image: '🐠'
                },
                23: {
                    name: 'Coral — Angelfish',
                    price: 1200,
                    image: '🐠'
                },
                24: {
                    name: 'Splash — Tetra',
                    price: 300,
                    image: '🐠'
                },
                25: {
                    name: 'Pearl — Molly',
                    price: 450,
                    image: '🐠'
                },
                26: {
                    name: 'Rio — African Grey',
                    price: 45000,
                    image: '🐦'
                },
                27: {
                    name: 'Sunny — Macaw',
                    price: 55000,
                    image: '🐦'
                },
                28: {
                    name: 'Tweety — Canary',
                    price: 8000,
                    image: '🐦'
                },
                29: {
                    name: 'Coco — Cockatiel',
                    price: 12000,
                    image: '🐦'
                },
                30: {
                    name: 'Phoenix — Lovebird',
                    price: 6000,
                    image: '🐦'
                },
                31: {
                    name: 'Zeus — Eagle',
                    price: 75000,
                    image: '🐦'
                },
                32: {
                    name: 'Sky — Swan',
                    price: 35000,
                    image: '🐦'
                }
            };

            let cart = JSON.parse(localStorage.getItem('pawsCart')) || [];

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
                  <input type="tel" id="phone" placeholder="Enter your phone number">
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
                  <input type="text" id="pincode" placeholder="Enter PIN code">
                </div>
              </div>

              <div class="summary-section">
                <h3>Special Offers</h3>
                <div class="offers">
                  <label class="offer" for="firstTime">
                    <input type="radio" name="specialOffer" id="firstTime" value="firstTime" onchange="applyOffers()">
                    <div class="offer-content">
                      <div class="offer-title">First-time buyer discount</div>
                      <div class="offer-desc">10% off on your first purchase</div>
                    </div>
                    <div class="offer-radio"></div>
                  </label>
                  <label class="offer" for="bulkDiscount">
                    <input type="radio" name="specialOffer" id="bulkDiscount" value="bulkDiscount" onchange="applyOffers()">
                    <div class="offer-content">
                      <div class="offer-title">Bulk purchase discount</div>
                      <div class="offer-desc">5% off for 2+ pets</div>
                    </div>
                    <div class="offer-radio"></div>
                  </label>
                  <label class="offer" for="freeVet">
                    <input type="radio" name="specialOffer" id="freeVet" value="freeVet" onchange="applyOffers()">
                    <div class="offer-content">
                      <div class="offer-title">Free vet consultation</div>
                      <div class="offer-desc">₹500 value included</div>
                    </div>
                    <div class="offer-radio"></div>
                  </label>
                  <label class="offer" for="noOffer">
                    <input type="radio" name="specialOffer" id="noOffer" value="none" onchange="applyOffers()" checked>
                    <div class="offer-content">
                      <div class="offer-title">No special offer</div>
                      <div class="offer-desc">Continue without discount</div>
                    </div>
                    <div class="offer-radio"></div>
                  </label>
                </div>
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
                    localStorage.setItem('pawsCart', JSON.stringify(cart));
                    renderCart();
                    updateCartCount();
                }
            };

            window.removeItem = function(id) {
                cart = cart.filter(item => item.id != id);
                localStorage.setItem('pawsCart', JSON.stringify(cart));
                renderCart();
                updateCartCount();
            };

            window.toggleOffer = function(offerType) {
                const offerElement = document.querySelector(`[data-offer="${offerType}"]`);
                offerElement.classList.toggle('active');
                applyOffers();
            };

            window.applyOffers = function() {
                // Remove selected class from all offers
                document.querySelectorAll('.offer').forEach(offer => {
                    offer.classList.remove('selected');
                });

                // Add selected class to the checked offer
                const selectedRadio = document.querySelector('input[name="specialOffer"]:checked');
                if (selectedRadio) {
                    selectedRadio.closest('.offer').classList.add('selected');
                }

                const subtotal = cart.reduce((sum, item) => sum + (petData[item.id].price * item.quantity), 0);
                let discount = 0;

                const selectedOffer = selectedRadio ? selectedRadio.value : 'none';

                if (selectedOffer === 'firstTime') {
                    discount += Math.round(subtotal * 0.1);
                } else if (selectedOffer === 'bulkDiscount' && cart.length >= 2) {
                    discount += Math.round(subtotal * 0.05);
                } else if (selectedOffer === 'freeVet') {
                    discount += 500;
                }
                // No discount for 'none' option

                const shipping = subtotal > 5000 ? 0 : 500;
                const tax = Math.round((subtotal - discount) * 0.18);
                const total = subtotal - discount + shipping + tax;

                document.getElementById('discount').textContent = `-₹${discount.toLocaleString()}`;
                document.getElementById('tax').textContent = `₹${tax.toLocaleString()}`;
                document.getElementById('total').textContent = `₹${total.toLocaleString()}`;
            };

            window.processPayment = function() {
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

                // Calculate final total
                const subtotal = cart.reduce((sum, item) => sum + (petData[item.id].price * item.quantity), 0);
                let discount = 0;
                const selectedOffer = document.querySelector('input[name="specialOffer"]:checked');
                if (selectedOffer) {
                    if (selectedOffer.value === 'firstTime') {
                        discount += Math.round(subtotal * 0.1);
                    } else if (selectedOffer.value === 'bulkDiscount' && cart.length >= 2) {
                        discount += Math.round(subtotal * 0.05);
                    } else if (selectedOffer.value === 'freeVet') {
                        discount += 500;
                    }
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
            applyOffers();
        });
    </script>
</body>

</html>
</content>