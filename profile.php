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

                // Send Profile Update Email
                $to = $new_email;
                $subject = "Profile Updated - Paws Store";
                $message = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Profile Updated</title>
                </head>
                <body style='margin: 0; padding: 0; font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; background-color: #faf7f2; color: #333333;'>
                    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #faf7f2; padding: 20px;'>
                        <tr>
                            <td align='center'>
                                <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                                    <tr>
                                        <td style='background-color: #2c1a0e; padding: 30px; text-align: center;'>
                                            <h1 style='color: #b5860d; margin: 0; font-size: 28px; font-weight: normal;'>🐾 Paws Store</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 40px 30px;'>
                                            <h2 style='color: #2c1a0e; margin-top: 0;'>Profile Updated Successfully</h2>
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Hello " . htmlspecialchars($new_username) . ",</p>
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>This is a quick notification to confirm that your profile details have been successfully updated.</p>
                                            
                                            <div style='background-color: #fdfaf6; border: 1px solid #e8e0d4; border-radius: 8px; padding: 20px; margin: 30px 0; text-align: center;'>
                                                <p style='margin: 0; font-size: 16px; color: #555555;'>If you made this change, no further action is required.</p>
                                            </div>
                                            
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'><strong>Didn't make this change?</strong> Please contact our support team immediately to secure your account.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                                            <p style='margin: 0; color: #888888; font-size: 14px;'>Best Regards,<br><strong style='color: #2c1a0e;'>🐾 Paws Store Team</strong></p>
                                            <p style='margin: 10px 0 0 0; color: #aaaaaa; font-size: 12px;'>© " . date('Y') . " Paws Store. Made with love in India.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>
                ";

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: Paws Store <warishali105@gmail.com>\r\n";

                @mail($to, $subject, $message, $headers);

                // Send Profile Update WhatsApp
                $env = parse_ini_file('.env');
                $instance_id = $env['ULTRAMSG_INSTANCE_ID'] ?? '';
                $token = $env['ULTRAMSG_TOKEN'] ?? '';
                $clean_phone = preg_replace('/[^0-9]/', '', $new_phone);

                if (!empty($instance_id) && !empty($token) && strlen($clean_phone) >= 10) {
                    if (strlen($clean_phone) == 10) {
                        $clean_phone = "91" . $clean_phone;
                    }
                    $wa_body = "🐾 *Paws Store - Profile Update*\n\nHello *" . htmlspecialchars($new_username) . "*,\n\nYour profile details have been successfully updated.\n\nIf you did not make this change, please contact our support team immediately.";
                    
                    $curl = curl_init();
                    curl_setopt_array($curl, [
                        CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => http_build_query(["token" => $token, "to" => "+" . $clean_phone, "body" => $wa_body]),
                        CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
                        CURLOPT_SSL_VERIFYPEER => false, 
                        CURLOPT_SSL_VERIFYHOST => false
                    ]);
                    curl_exec($curl);
                    curl_close($curl);
                }
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

            // Send Security Alert Email using HTML Tables and Inline CSS
            $username = $_SESSION['user']['username'] ?? 'Customer';
            $email = $_SESSION['user']['email'] ?? '';

            if (!empty($email)) {
                $to = $email;
                $subject = "Security Alert: Password Changed - Paws Store";
                $message = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Password Changed Successfully</title>
                </head>
                <body style='margin: 0; padding: 0; font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; background-color: #faf7f2; color: #333333;'>
                    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #faf7f2; padding: 20px;'>
                        <tr>
                            <td align='center'>
                                <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                                    <tr>
                                        <td style='background-color: #2c1a0e; padding: 30px; text-align: center;'>
                                            <h1 style='color: #b5860d; margin: 0; font-size: 28px; font-weight: normal;'>🐾 Paws Store</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 40px 30px;'>
                                            <h2 style='color: #2c1a0e; margin-top: 0;'>Password Changed Successfully</h2>
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Hello " . htmlspecialchars($username) . ",</p>
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>This is a quick notification to confirm that the password for your Paws Store account has been successfully changed from your profile settings.</p>
                                            
                                            <div style='background-color: #fdfaf6; border: 1px solid #e8e0d4; border-radius: 8px; padding: 20px; margin: 30px 0; text-align: center;'>
                                                <p style='margin: 0; font-size: 16px; color: #555555;'>If you made this change, no further action is required.</p>
                                            </div>
                                            
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'><strong>Didn't make this change?</strong> Please contact our support team immediately to secure your account.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                                            <p style='margin: 0; color: #888888; font-size: 14px;'>Best Regards,<br><strong style='color: #2c1a0e;'>🐾 Paws Store Team</strong></p>
                                            <p style='margin: 10px 0 0 0; color: #aaaaaa; font-size: 12px;'>© " . date('Y') . " Paws Store. Made with love in India.</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>
                ";

                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: Paws Store <warishali105@gmail.com>\r\n";

                @mail($to, $subject, $message, $headers);
            }

            // Send Security WhatsApp
            $env = parse_ini_file('.env');
            $instance_id = $env['ULTRAMSG_INSTANCE_ID'] ?? '';
            $token = $env['ULTRAMSG_TOKEN'] ?? '';
            $phone = $_SESSION['user']['phone'] ?? '';
            $clean_phone = preg_replace('/[^0-9]/', '', $phone);

            if (!empty($instance_id) && !empty($token) && strlen($clean_phone) >= 10) {
                if (strlen($clean_phone) == 10) {
                    $clean_phone = "91" . $clean_phone;
                }
                $wa_body = "🐾 *Paws Store - Security Alert*\n\nHello *" . htmlspecialchars($username) . "*,\n\nYour password has been successfully changed from your profile settings. If you made this change, no further action is required.\n\nIf you didn't make this change, please contact our support team immediately.";

                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => http_build_query(["token" => $token, "to" => "+" . $clean_phone, "body" => $wa_body]),
                    CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ]);
                curl_exec($curl);
                curl_close($curl);
            }

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
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'get'
                })
            }).then(r => r.json()).then(d => {
                if (d.status === 'success') updateCartCount(d.cart_count);
            });

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