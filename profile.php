<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

// User data is stored in the session as an array
$user = $_SESSION["user"];
$userEmail = $user["email"] ?? "";
$userName = $user["username"] ?? "";
$userPhone = $user["phone"] ?? "";
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Profile — Paws Store</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ps-profile-container {
            max-width: 600px;
            margin: 40px auto 80px;
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e8e0d4;
        }

        .ps-profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .ps-profile-header h1 {
            font-family: "Playfair Display", serif;
            font-size: 32px;
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .ps-profile-avatar {
            width: 100px;
            height: 100px;
            background: #f5ecd8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
            color: #b5860d;
            border: 2px solid #b5860d;
        }

        .ps-form-group {
            margin-bottom: 20px;
        }

        .ps-form-group label {
            display: block;
            font-weight: 600;
            color: #2c1a0e;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .ps-form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            color: #333;
            transition: border-color 0.2s;
        }

        .ps-form-group input:focus {
            outline: none;
            border-color: #b5860d;
            box-shadow: 0 0 0 2px rgba(181, 134, 13, 0.1);
        }

        .ps-profile-actions {
            margin-top: 30px;
            display: flex;
            gap: 15px;
        }

        .ps-btn-save {
            flex: 1;
            background: #b5860d;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            font-family: 'Nunito', sans-serif;
        }

        .ps-btn-save:hover {
            background: #9a7210;
        }

        .ps-btn-cancel {
            flex: 1;
            background: transparent;
            color: #2c1a0e;
            border: 1px solid #ddd;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Nunito', sans-serif;
            text-align: center;
            text-decoration: none;
        }

        .ps-btn-cancel:hover {
            background: #f9f9f9;
            border-color: #ccc;
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
        <div class="ps-profile-container">
            <div class="ps-profile-header">
                <div class="ps-profile-avatar">👤</div>
                <h1>My Profile</h1>
                <p style="color: #666;">Update your personal details below.</p>
            </div>

            <form onsubmit="event.preventDefault(); alert('Profile updated successfully!');">
                <div class="ps-form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" value="<?php echo htmlspecialchars($userName); ?>" placeholder="Enter your full name" required>
                </div>

                <div class="ps-form-group">
                    <label for="emailId">Email ID</label>
                    <input type="email" id="emailId" value="<?php echo htmlspecialchars($userEmail); ?>" placeholder="Enter your email address" required>
                </div>

                <div class="ps-form-group">
                    <label for="mobileNo">Mobile Number</label>
                    <input type="tel" id="mobileNo" value="<?php echo htmlspecialchars($userPhone); ?>" placeholder="Enter your 10-digit mobile number" required>
                </div>

                <div class="ps-profile-actions">
                    <a href="index.php" class="ps-btn-cancel">Cancel</a>
                    <button type="submit" class="ps-btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <footer id="contact">
        <div class="ps-footer">
            <div class="ps-footer-bottom" style="text-align: center; color: white;">
                © 2026 Paws Store. Made with 🐾 in India.
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let cart = JSON.parse(localStorage.getItem('pawsCart')) || [];

            function updateCartCount() {
                const cartCountElement = document.getElementById('cart-count');
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

                if (cartCountElement) {
                    cartCountElement.textContent = totalItems;
                    cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
                }
            }

            updateCartCount();
        });
    </script>
</body>

</html>