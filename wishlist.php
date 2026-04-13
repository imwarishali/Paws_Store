<?php
session_start();

// Redirect guests to login if they try to access the wishlist
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php?redirect=wishlist.php");
    exit();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Wishlist — Paws Store</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ps-wishlist-header {
            padding: 50px 30px;
            text-align: center;
            background: linear-gradient(135deg, #2c1a0e 0%, #5c3d2e 100%);
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .ps-wishlist-header h1 {
            font-family: "Playfair Display", serif;
            font-size: 42px;
            color: #ffffff;
            margin: 0 0 10px 0;
            letter-spacing: 0.5px;
        }

        .ps-wishlist-header p {
            color: #f0f0f0;
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .ps-empty-wishlist {
            text-align: center;
            padding: 80px 40px;
            background: linear-gradient(135deg, #fafafa 0%, #f5f2ed 100%);
            border-radius: 20px;
            border: 2px dashed #e8e0d4;
            margin: 40px 20px;
        }

        .ps-empty-wishlist h2 {
            font-family: "Playfair Display", serif;
            color: #2c1a0e;
            margin-bottom: 15px;
            font-size: 32px;
            letter-spacing: 0.3px;
        }

        .ps-empty-wishlist p {
            color: #666;
            margin-bottom: 25px;
            font-size: 16px;
            line-height: 1.6;
        }

        .ps-empty-wishlist a {
            display: inline-block;
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: #fff;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 28px;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.25);
        }

        .ps-empty-wishlist a:hover {
            background: linear-gradient(135deg, #9a7210 0%, #c4a528 100%);
            transform: translateY(-3px);
            box-shadow: 0 8px 28px rgba(181, 134, 13, 0.35);
        }

        /* Wishlist wrapper styling */
        .ps-section.ps-featured {
            padding: 40px 20px !important;
        }

        .ps-pets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 24px;
            margin-top: 20px;
        }

        .ps-pet-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e0e0e0;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .ps-pet-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #b5860d, #d4af37);
            transform: translateX(-100%);
            transition: transform 0.4s ease;
            z-index: 1;
        }

        .ps-pet-card:hover {
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            transform: translateY(-6px);
            border-color: #b5860d;
        }

        .ps-pet-card:hover::before {
            transform: translateX(0);
        }

        .ps-pet-photo {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: #fceee0;
            border-radius: 16px 16px 0 0;
        }

        .ps-pet-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .ps-pet-card:hover .ps-pet-photo img {
            transform: scale(1.08);
        }

        .ps-pet-wish {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            cursor: pointer;
            font-size: 24px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 2px solid #f5f2eb;
        }

        .ps-pet-wish:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .ps-pet-wish.active {
            color: #e74c3c;
            animation: heartBeat 0.4s ease;
        }

        @keyframes heartBeat {

            0%,
            100% {
                transform: scale(1);
            }

            25% {
                transform: scale(1.2);
            }

            50% {
                transform: scale(1);
            }
        }

        .ps-pet-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            background: #ffffff;
        }

        .ps-pet-name {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: #2c1a0e;
            margin-bottom: 12px;
            letter-spacing: 0.3px;
        }

        .ps-pet-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: auto;
        }

        .ps-pet-price {
            font-size: 18px;
            font-weight: 700;
            color: #b5860d;
        }

        .ps-pet-add {
            flex-grow: 1;
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.25);
        }

        .ps-pet-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.35);
        }

        .ps-pet-add.added-to-cart {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.25);
        }

        @media (max-width: 1024px) {
            .ps-pets-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .ps-wishlist-header {
                padding: 35px 20px;
                margin-bottom: 25px;
                border-radius: 16px;
            }

            .ps-wishlist-header h1 {
                font-size: 32px;
                margin-bottom: 8px;
            }

            .ps-wishlist-header p {
                font-size: 14px;
            }

            .ps-section.ps-featured {
                padding: 20px 10px !important;
            }

            .ps-pets-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 16px;
            }

            .ps-pet-card {
                border-radius: 12px;
            }

            .ps-pet-photo {
                height: 150px;
                border-radius: 12px 12px 0 0;
            }

            .ps-pet-body {
                padding: 16px;
            }

            .ps-pet-name {
                font-size: 16px;
                margin-bottom: 10px;
            }

            .ps-pet-price {
                font-size: 16px;
            }

            .ps-pet-add {
                font-size: 12px;
                padding: 8px 12px;
            }

            .ps-empty-wishlist {
                padding: 60px 30px;
                margin: 30px 15px;
            }

            .ps-empty-wishlist h2 {
                font-size: 26px;
            }

            .ps-empty-wishlist p {
                font-size: 14px;
            }

            .ps-empty-wishlist a {
                padding: 12px 28px;
                font-size: 14px;
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
                <a href="cart.php" class="fk-cart-btn">
                    <span class="fk-cart-icon">🛒</span> Cart
                    <span id="cart-count" class="cart-count">0</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="ps-wrap">
        <div class="ps-wishlist-header">
            <h1>My Wishlist</h1>
            <p>Your favourite furry friends saved and ready to adopt</p>
        </div>

        <div class="ps-section ps-featured" style="min-height: 50vh;">
            <div class="ps-pets-grid" id="wishlist-grid">
                <!-- Wishlist items will be injected here by JavaScript -->
            </div>
            <div id="empty-wishlist-msg" class="ps-empty-wishlist" style="display: none;">
                <h2>Your wishlist is empty</h2>
                <p>Looks like you haven't added any furry friends to your wishlist yet.</p>
                <a href="index.php">Browse Pets</a>
            </div>
        </div>
    </div>

    <footer id="contact">
        <div class="ps-footer">
            <div class="ps-footer-bottom" style="text-align: center; color: white;">
                © 2026 Paws Store. Made with 🐾 in India.
            </div>
        </div>
    </footer>

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
            const wishlistGrid = document.getElementById('wishlist-grid');
            const emptyMsg = document.getElementById('empty-wishlist-msg');

            const currentUserId = '<?php echo isset($_SESSION["user"]["id"]) ? $_SESSION["user"]["id"] : "guest"; ?>';
            const cartKey = 'pawsCart_' + currentUserId;
            const wishKey = 'pawsWishlist_' + currentUserId;

            // TRANSFER GUEST DATA TO LOGGED-IN USER
            if (currentUserId !== 'guest') {
                let guestWish = JSON.parse(localStorage.getItem('pawsWishlist_guest'));
                if (guestWish && guestWish.length > 0) {
                    let userWish = JSON.parse(localStorage.getItem(wishKey)) || [];
                    guestWish.forEach(guestItem => {
                        if (!userWish.find(item => item.id === guestItem.id)) userWish.push(guestItem);
                    });
                    localStorage.setItem(wishKey, JSON.stringify(userWish));
                    localStorage.removeItem('pawsWishlist_guest');
                }
            }

            let wishlist = JSON.parse(localStorage.getItem(wishKey)) || [];

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

            function updateCartCount(count) {
                const cartCountElement = document.getElementById('cart-count');
                const mobileCartCount = document.getElementById('mobile-cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = count;
                    cartCountElement.style.display = count > 0 ? 'flex' : 'none';
                }
                if (mobileCartCount) {
                    mobileCartCount.textContent = count;
                    mobileCartCount.style.display = count > 0 ? 'flex' : 'none';
                }
            }
            fetch('cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'get'
                })
            }).then(r => r.json()).then(d => {
                if (d.status === 'success') updateCartCount(d.cart_count);
            });

            function renderWishlist() {
                wishlistGrid.innerHTML = '';

                if (wishlist.length === 0) {
                    emptyMsg.style.display = 'block';
                    return;
                } else {
                    emptyMsg.style.display = 'none';
                }

                wishlist.forEach((pet, index) => {
                    const card = document.createElement('div');
                    card.className = 'ps-pet-card';
                    card.innerHTML = `
                <div class="ps-pet-photo" style="background: #fceee0">
                  <img src="${pet.image}" alt="${pet.name}" style="width: 100%; height: 100%; object-fit: cover;">
                  <div class="ps-pet-wish active" data-index="${index}" style="color: #e74c3c;">♥</div>
                </div>
                <div class="ps-pet-body">
                  <div class="ps-pet-name">${pet.name}</div>
                  <div class="ps-pet-row" style="margin-top: 15px;">
                    <span class="ps-pet-price">₹${pet.price.toLocaleString('en-IN')}</span>
                    <button class="ps-pet-add" data-index="${index}">Add to Cart</button>
                  </div>
                </div>
            `;
                    wishlistGrid.appendChild(card);
                });

                // Remove from wishlist
                document.querySelectorAll('.ps-pet-wish.active').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = this.getAttribute('data-index');
                        const removedPet = wishlist.splice(idx, 1)[0];
                        localStorage.setItem(wishKey, JSON.stringify(wishlist));
                        renderWishlist();
                        showToast(removedPet.name + " removed from wishlist!", '🤍');
                    });
                });

                // Add to Cart from wishlist
                document.querySelectorAll('.ps-pet-add').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const idx = this.getAttribute('data-index');
                        const pet = wishlist[idx];

                        fetch('cart_action.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    action: 'add',
                                    id: pet.id,
                                    quantity: 1
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.status === 'success') {
                                    updateCartCount(data.cart_count);
                                }
                            });

                        this.textContent = 'Added! ✓';
                        this.classList.add('added-to-cart');

                        clearTimeout(this.addedTimeout);
                        this.addedTimeout = setTimeout(() => {
                            this.textContent = 'Add to Cart';
                            this.classList.remove('added-to-cart');
                        }, 2000);

                        showToast(pet.name + " added to cart!", '🛒');
                    });
                });
            }

            renderWishlist();
        });
    </script>
</body>

</html>