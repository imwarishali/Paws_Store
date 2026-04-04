<?php
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

require_once 'db.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
    $stmt->execute([$id]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);
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

            let cart = JSON.parse(localStorage.getItem(cartKey)) || [];
            let wishlist = JSON.parse(localStorage.getItem(wishKey)) || [];

            const petId = "<?php echo $id; ?>";
            const petName = "<?php echo addslashes($pet['name']); ?>";
            const petPrice = <?php echo floatval($pet['price'] ?? 0); ?>;
            const petImage = "<?php echo addslashes($pet['image']); ?>";

            const cartBtn = document.getElementById('add-to-cart');
            const buyNowBtn = document.getElementById('buy-now');
            const wishBtn = document.getElementById('toggle-wishlist');
            const cartCountElement = document.getElementById('cart-count');

            function updateCartCount() {
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                if (cartCountElement) {
                    cartCountElement.textContent = totalItems;
                    cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
                }
            }

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
                const existingPet = cart.find(item => item.id === petId);
                if (existingPet) {
                    existingPet.quantity += 1;
                } else {
                    cart.push({
                        id: petId,
                        name: petName,
                        price: petPrice,
                        image: petImage,
                        quantity: 1
                    });
                }
                localStorage.setItem(cartKey, JSON.stringify(cart));
                updateCartCount();
            }

            cartBtn.addEventListener('click', function() {
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

            updateCartCount();
            updateWishlistIcon();
        });
    </script>
</body>

</html>