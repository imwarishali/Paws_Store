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
            padding: 40px 48px 20px;
            text-align: center;
        }

        .ps-wishlist-header h1 {
            font-family: "Playfair Display", serif;
            font-size: 36px;
            color: #2c1a0e;
        }

        .ps-empty-wishlist {
            text-align: center;
            padding: 60px 20px;
        }

        .ps-empty-wishlist h2 {
            font-family: "Playfair Display", serif;
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .ps-empty-wishlist p {
            color: #8b6840;
            margin-bottom: 20px;
        }

        .ps-empty-wishlist a {
            display: inline-block;
            background: #b5860d;
            color: #fff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 24px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .ps-empty-wishlist a:hover {
            background: #9a7210;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.2);
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
        </div>

        <div class="ps-section ps-featured" style="padding-top: 20px; min-height: 50vh;">
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
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'get'})
            }).then(r => r.json()).then(d => { if(d.status === 'success') updateCartCount(d.cart_count); });

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
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({action: 'add', id: pet.id, quantity: 1})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
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