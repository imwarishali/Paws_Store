<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

require_once 'db.php';

$success_message = null;
$error_message = null;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    $new_username = $_POST['username'] ?? '';
    $new_email = $_POST['email'] ?? '';
    $new_phone = $_POST['phone'] ?? '';
    $user_id = $_SESSION['user']['id'];

    // Basic validation
    if (empty($new_username) || empty($new_email)) {
        $error_message = "Username and Email cannot be empty.";
    } else {
        try {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$new_email, $user_id]);
            if ($stmt->fetch()) {
                $error_message = "This email address is already in use by another account.";
            } else {
                // Update user in the database
                $update_stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
                $update_stmt->execute([$new_username, $new_email, $new_phone, $user_id]);

                // Update session data
                $_SESSION['user']['username'] = $new_username;
                $_SESSION['user']['email'] = $new_email;
                $_SESSION['user']['phone'] = $new_phone;

                $success_message = "Profile updated successfully!";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
// Handle password change
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_id = $_SESSION['user']['id'];

    try {
        // Fetch current user's hashed password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify current password, and validate new password
        if (!$user_data || !password_verify($current_password, $user_data['password'])) {
            $error_message = "Your current password is not correct.";
        } elseif (empty($new_password) || strlen($new_password) < 8) {
            $error_message = "New password must be at least 8 characters long.";
        } elseif (!preg_match('/[0-9]/', $new_password)) {
            $error_message = "Password must contain at least one number.";
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
            $error_message = "Password must contain at least one special character.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New password and confirmation do not match.";
        } else {
            // Hash and update new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update_stmt->execute([$hashed_password, $user_id]);
            $success_message = "Your password has been updated successfully!";
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
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

        .ps-password-wrapper {
            position: relative;
            display: block;
        }

        .ps-password-wrapper input {
            padding-right: 40px;
        }

        .ps-password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
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


            <form method="POST" action="profile.php">
                <div class="ps-form-group">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="username" value="<?php echo htmlspecialchars($userName); ?>" placeholder="Enter your full name" required>
                </div>

                <div class="ps-form-group">
                    <label for="emailId">Email ID</label>
                    <input type="email" id="emailId" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" placeholder="Enter your email address" required>
                </div>

                <div class="ps-form-group">
                    <label for="mobileNo">Mobile Number</label>
                    <input type="tel" id="mobileNo" name="phone" value="<?php echo htmlspecialchars($userPhone); ?>" placeholder="Enter your 10-digit mobile number" required>
                </div>

                <div class="ps-profile-actions">
                    <a href="index.php" class="ps-btn-cancel">Cancel</a>
                    <button type="submit" name="save_changes" class="ps-btn-save">Save Changes</button>
                </div>
            </form>
        </div>

        <div class="ps-profile-container" style="margin-top: 30px;">
            <div class="ps-profile-header" style="margin-bottom: 20px;">
                <h2 style="font-family: 'Playfair Display', serif; font-size: 24px; color: #2c1a0e;">Change Password</h2>
            </div>

            <form method="POST" action="profile.php">
                <div class="ps-form-group">
                    <label for="current_password">Current Password</label>
                    <div class="ps-password-wrapper">
                        <input type="password" id="current_password" name="current_password" placeholder="Enter your current password" required>
                        <button type="button" class="ps-password-toggle" onclick="togglePasswordVisibility('current_password', this)">👁️</button>
                    </div>
                </div>
                <div class="ps-form-group">
                    <label for="new_password">New Password</label>
                    <div class="ps-password-wrapper">
                        <input type="password" id="new_password" name="new_password" placeholder="Min 8 chars, 1 number, 1 symbol" required>
                        <button type="button" class="ps-password-toggle" onclick="togglePasswordVisibility('new_password', this)">👁️</button>
                    </div>
                </div>
                <div class="ps-form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="ps-password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password" required>
                        <button type="button" class="ps-password-toggle" onclick="togglePasswordVisibility('confirm_password', this)">👁️</button>
                    </div>
                </div>
                <div class="ps-profile-actions">
                    <button type="submit" name="change_password" class="ps-btn-save" style="flex: none; width: 100%;">Update Password</button>
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

        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($success_message)): ?>
                showToast("<?php echo addslashes($success_message); ?>", "✅");
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                showToast("<?php echo addslashes($error_message); ?>", "⚠️");
            <?php endif; ?>

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

            window.togglePasswordVisibility = function(inputId, button) {
                const input = document.getElementById(inputId);
                if (input.type === 'password') {
                    input.type = 'text';
                    button.textContent = '🙈';
                } else {
                    input.type = 'password';
                    button.textContent = '👁️';
                }
            };
        });
    </script>
</body>

</html>