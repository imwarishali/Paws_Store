<?php
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

require_once 'db.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
    $stmt->execute([$id]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);

    $suggested_pets = [];
    if ($pet) {
        // Fetch 4 random pets from any category, excluding the current pet
        $suggested_stmt = $pdo->prepare("SELECT id, name, price, image FROM pets WHERE id != ? ORDER BY RAND() LIMIT 4");
        $suggested_stmt->execute([$id]);
        $suggested_pets = $suggested_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if (!$pet) {
    die('<h2>Pet not found.</h2><a href="index.php">Return to Homepage</a>');
}

// Set defaults for missing dynamic properties
if (empty($pet['status'])) {
    $pet['status'] = 'Available for Adoption';
}
$pet['date'] = date('M d, Y', strtotime('-' . rand(1, 14) . ' days'));

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($pet['name']); ?> — Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ps-details-container {
            max-width: 1100px;
            margin: 40px auto 80px;
            display: flex;
            gap: 50px;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #e8e0d4;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .ps-details-left {
            flex: 1.2;
            border-radius: 12px;
            overflow: hidden;
            background: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ps-details-left img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            max-height: 600px;
        }

        .ps-details-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .ps-details-header {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .ps-meta-badge {
            background: #f5ecd8;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            color: #8b6840;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .ps-details-title {
            font-family: "Playfair Display", serif;
            font-size: 38px;
            color: #2c1a0e;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .ps-details-id {
            font-size: 14px;
            color: #888;
            margin-bottom: 24px;
            font-weight: 500;
            display: flex;
            gap: 15px;
        }

        .ps-details-price {
            font-size: 34px;
            font-weight: 700;
            color: #b5860d;
            margin-bottom: 24px;
        }

        .ps-details-desc {
            font-size: 16px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 35px;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
            padding: 24px 0;
        }

        .ps-details-actions {
            display: flex;
            gap: 15px;
        }

        .ps-btn-add {
            flex: 1;
            background: #b5860d;
            color: white;
            border: none;
            padding: 18px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Nunito', sans-serif;
        }

        .ps-btn-add:hover {
            background: #9a7210;
        }

        @keyframes pop {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .ps-btn-add.added-to-cart {
            background: #28a745 !important;
            animation: pop 0.3s ease;
        }

        .ps-btn-buy {
            flex: 1;
            background: #2c1a0e;
            color: white;
            border: none;
            padding: 18px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Nunito', sans-serif;
        }

        .ps-btn-buy:hover {
            background: #4a3020;
        }

        .ps-btn-wish {
            width: 60px;
            background: transparent;
            border: 2px solid #e8e0d4;
            border-radius: 8px;
            font-size: 26px;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .ps-btn-wish:hover {
            background: #fdfaf6;
            border-color: #d8c094;
        }

        .ps-btn-wish.active {
            color: #e74c3c;
            border-color: #e74c3c;
            background: #fdf5f5;
        }

        /* Suggested Pets */
        .related-section {
            margin-top: 60px;
            padding-top: 40px;
            border-top: 1px solid #e8e0d4;
        }

        .related-title {
            font-family: "Playfair Display", serif;
            font-size: 28px;
            color: #2c1a0e;
            text-align: center;
            margin-bottom: 30px;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
        }

        .related-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(92, 64, 51, 0.08);
            overflow: hidden;
            text-decoration: none;
            color: #2d2a26;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid #e8e0d4;
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(92, 64, 51, 0.12);
        }

        .related-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .related-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .related-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #2c1a0e;
            margin-bottom: 8px;
        }

        .related-price {
            color: #b5860d;
            background: #fdfaf6;
            font-size: 14px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 12px;
            align-self: flex-start;
            border: 1px solid #f5ecd8;
        }

        @media (max-width: 768px) {
            .ps-details-container {
                flex-direction: column;
                padding: 24px;
                margin: 20px;
                gap: 30px;
            }
        }
    </style>
</head>

<body>
    <nav class="fk-nav-header">
        <div class="fk-nav-top">
            <a href="index.php" class="fk-logo">🐾 Paws Store</a>
            <div class="fk-nav-right" style="margin-left: auto;">
                <div class="fk-dropdown-wrapper" style="margin-right: 15px;">
                    <button class="fk-login-btn">
                        <span class="fk-icon-user">👤</span>
                        <?php echo isset($_SESSION["user"]) ? "Account" : "Login"; ?>
                        <span class="fk-chevron">⌄</span>
                    </button>
                    <div class="fk-dropdown-menu">
                        <?php if (!isset($_SESSION["user"])): ?>
                            <?php $current_url = urlencode("pet_details.php?id=" . $id); ?>
                            <div class="fk-new-customer">
                                <span>Existing user?</span>
                                <a href="auth/login.php?redirect=<?php echo $current_url; ?>" class="fk-signup-link">Log In</a>
                            </div>
                            <div class="fk-new-customer">
                                <span>New customer?</span>
                                <a href="auth/register.php?redirect=<?php echo $current_url; ?>" class="fk-signup-link">Sign Up</a>
                            </div>
                            <hr class="fk-divider">
                        <?php endif; ?>
                        <a href="profile.php" class="fk-menu-item"><span class="fk-menu-icon">👤</span> My Profile</a>
                        <a href="order_history.php" class="fk-menu-item"><span class="fk-menu-icon">📦</span> Orders</a>
                        <a href="wishlist.php" class="fk-menu-item"><span class="fk-menu-icon">🤍</span> Wishlist</a>
                        <?php if (isset($_SESSION["user"])): ?>
                            <hr class="fk-divider">
                            <a href="auth/logout.php" class="fk-menu-item"><span class="fk-menu-icon">🚪</span> Logout</a>
                        <?php endif; ?>
                    </div>
                </div>
                <a href="cart.php" class="fk-cart-btn">
                    <span class="fk-cart-icon">🛒</span> Cart
                    <span id="cart-count" class="cart-count">0</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="ps-wrap">
        <div class="ps-details-container">
            <div class="ps-details-left">
                <img src="<?php echo htmlspecialchars($pet['image']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
            </div>
            <div class="ps-details-right">
                <div class="ps-details-header">
                    <span class="ps-meta-badge"><?php echo htmlspecialchars($pet['category']); ?></span>
                    <span class="ps-meta-badge status-badge"><?php echo htmlspecialchars($pet['status']); ?></span>
                </div>

                <h1 class="ps-details-title"><?php echo htmlspecialchars($pet['name']); ?></h1>

                <div class="ps-details-id">
                    <span><strong>Pet ID:</strong> #<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></span>
                    <span>|</span>
                    <span><strong>Listed On:</strong> <?php echo $pet['date']; ?></span>
                </div>

                <div class="ps-details-price">₹<?php echo number_format($pet['price']); ?></div>

                <div class="ps-details-desc">
                    <?php echo htmlspecialchars($pet['description']); ?>
                </div>

                <div class="ps-details-actions">
                    <button class="ps-btn-add" id="add-to-cart">Add to Cart</button>
                    <button class="ps-btn-buy" id="buy-now">Buy Now</button>
                    <button class="ps-btn-wish" id="toggle-wishlist">♡</button>
                </div>
            </div>
        </div>

        <!-- SUGGESTED PETS SECTION -->
        <?php if (!empty($suggested_pets)): ?>
            <div class="related-section">
                <h2 class="related-title">Suggested For You</h2>
                <div class="related-grid">
                    <?php foreach ($suggested_pets as $s_pet): ?>
                        <a href="pet_details.php?id=<?php echo $s_pet['id']; ?>" class="related-card">
                            <img src="<?php echo htmlspecialchars($s_pet['image']); ?>" alt="<?php echo htmlspecialchars($s_pet['name']); ?>" class="related-img">
                            <div class="related-content">
                                <div class="related-card-title"><?php echo htmlspecialchars($s_pet['name']); ?></div>
                                <div class="related-price">₹<?php echo number_format($s_pet['price']); ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer id="contact">
        <div class="ps-footer" style="margin-top: 0;">
            <div class="ps-footer-bottom" style="text-align: center; color: white;">
                © 2026 Paws Store. Made with 🐾 in India.
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            let wishlist = JSON.parse(localStorage.getItem(wishKey)) || [];

            const petId = "<?php echo $id; ?>";
            const petName = "<?php echo addslashes($pet['name']); ?>";
            const petPrice = <?php echo floatval($pet['price'] ?? 0); ?>;
            const petImage = "<?php echo addslashes($pet['image']); ?>";

            const cartBtn = document.getElementById('add-to-cart');
            const buyNowBtn = document.getElementById('buy-now');
            const wishBtn = document.getElementById('toggle-wishlist');
            const cartCountElement = document.getElementById('cart-count');

            function updateCartCount(count) {
                if (cartCountElement) {
                    cartCountElement.textContent = count;
                    cartCountElement.style.display = count > 0 ? 'flex' : 'none';
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

            function updateWishlistIcon() {
                if (wishlist.find(item => item.id === petId)) {
                    wishBtn.classList.add('active');
                    wishBtn.innerHTML = '♥';
                } else {
                    wishBtn.classList.remove('active');
                    wishBtn.innerHTML = '♡';
                }
            }

            function addToCart() {
                fetch('cart_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'add',
                            id: petId,
                            quantity: 1
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            updateCartCount(data.cart_count);
                        }
                    });
            }

            cartBtn.addEventListener('click', function() {
                // Check if user is logged in
                if (currentUserId === 'guest') {
                    alert('🔐 Login to add to cart\n\nPlease log in to your account to add items to your cart.');
                    window.location.href = 'auth/login.php?redirect=pet_details.php?id=' + petId;
                    return;
                }

                addToCart();

                this.textContent = 'Added! ✓';
                this.classList.add('added-to-cart');

                clearTimeout(this.addedTimeout);
                this.addedTimeout = setTimeout(() => {
                    this.textContent = 'Add to Cart';
                    this.classList.remove('added-to-cart');
                }, 2000);

                showToast(petName + " added to cart!", '🛒');
            });

            buyNowBtn.addEventListener('click', function() {
                // Check if user is logged in
                if (currentUserId === 'guest') {
                    alert('🔐 Login to add to cart\n\nPlease log in to your account to add items to your cart.');
                    window.location.href = 'auth/login.php?redirect=pet_details.php?id=' + petId;
                    return;
                }

                addToCart();
                window.location.href = 'cart.php';
            });

            wishBtn.addEventListener('click', function() {
                const existingIndex = wishlist.findIndex(item => item.id === petId);
                if (existingIndex > -1) {
                    wishlist.splice(existingIndex, 1);
                    showToast(petName + " removed from wishlist!", '🤍');
                } else {
                    wishlist.push({
                        id: petId,
                        name: petName,
                        price: petPrice, // Store the raw number for consistency
                        image: petImage
                    });
                    showToast(petName + " added to wishlist!", '❤️');
                }
                localStorage.setItem(wishKey, JSON.stringify(wishlist));
                updateWishlistIcon();

                this.classList.add('wish-pop');
                setTimeout(() => this.classList.remove('wish-pop'), 300);
            });

            updateWishlistIcon();
        });
    </script>
</body>

</html>