<?php
require_once 'config.php';
require_once 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

$success_message = null;
$error_message = null;
$show_otp_form = false;
$pending_changes = null;

// Check if user is in OTP verification mode
if (isset($_SESSION['pending_profile_changes'])) {
    $show_otp_form = true;
    $pending_changes = $_SESSION['pending_profile_changes'];
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_otp'])) {
    if (!isset($_SESSION['pending_profile_changes'])) {
        $error_message = "No pending changes found. Please try again.";
    } else {
        $entered_otp = trim($_POST['otp'] ?? '');

        $remaining_cooldown = 0;
        $time_elapsed = 0;
        if (isset($_SESSION['pending_profile_changes']['otp_time'])) {
            $time_elapsed = time() - $_SESSION['pending_profile_changes']['otp_time'];
            $remaining_cooldown = max(0, 600 - $time_elapsed); // 10 minutes
        }

        if (empty($entered_otp)) {
            $error_message = "Please enter the OTP.";
            $show_otp_form = true;
        } elseif ($time_elapsed > 600) {
            $error_message = "OTP has expired. Please try updating your profile again.";
            unset($_SESSION['pending_profile_changes']);
            $show_otp_form = false;
        } elseif ($entered_otp != $_SESSION['pending_profile_changes']['otp']) {
            if (!isset($_SESSION['pending_profile_changes']['attempts'])) {
                $_SESSION['pending_profile_changes']['attempts'] = 0;
            }
            $_SESSION['pending_profile_changes']['attempts']++;

            if ($_SESSION['pending_profile_changes']['attempts'] >= 6) {
                $error_message = "Too many failed OTP attempts. Please try updating your profile again.";
                unset($_SESSION['pending_profile_changes']);
                $show_otp_form = false;
            } else {
                $remaining = 6 - $_SESSION['pending_profile_changes']['attempts'];
                $error_message = "Invalid OTP. You have {$remaining} attempt(s) left.";
                $show_otp_form = true;
            }
        } else {
            // OTP is valid, apply the profile changes
            try {
                $new_username = $_SESSION['pending_profile_changes']['username'];
                $new_email = $_SESSION['pending_profile_changes']['email'];
                $new_phone = $_SESSION['pending_profile_changes']['phone'];
                $user_id = $_SESSION['user']['id'];

                // Double-check email is still not taken
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$new_email, $user_id]);
                if ($stmt->fetch()) {
                    $error_message = "This email address is now in use by another account.";
                } else {
                    // Update user in the database
                    $update_stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
                    $update_stmt->execute([$new_username, $new_email, $new_phone, $user_id]);

                    // Update session data
                    $_SESSION['user']['username'] = $new_username;
                    $_SESSION['user']['email'] = $new_email;
                    $_SESSION['user']['phone'] = $new_phone;

                    $success_message = "Profile updated successfully!";
                    unset($_SESSION['pending_profile_changes']);
                    $show_otp_form = false;

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
}

// Handle profile update form submission (save_changes)
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
            // Check if email is valid format
            if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                $error_message = "Please enter a valid email address.";
            } else {
                // Check if email is already taken by another user
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$new_email, $user_id]);
                if ($stmt->fetch()) {
                    $error_message = "This email address is already in use by another account.";
                } else {
                    // Check if email or phone has changed
                    $email_changed = ($new_email !== $_SESSION['user']['email']);
                    $phone_changed = ($new_phone !== $_SESSION['user']['phone']);

                    if ($email_changed || $phone_changed) {
                        // Generate OTP
                        $otp = rand(100000, 999999);

                        // Store pending changes in session
                        $_SESSION['pending_profile_changes'] = [
                            'username' => $new_username,
                            'email' => $new_email,
                            'phone' => $new_phone,
                            'otp' => $otp,
                            'otp_time' => time(),
                            'attempts' => 0
                        ];

                        // Send OTP Email
                        $to = $email_changed ? $new_email : $_SESSION['user']['email'];
                        $subject = "Verify Your Profile Changes - Paws Store";
                        $message = "
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset='UTF-8'>
                            <title>Email Verification</title>
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
                                                    <h2 style='color: #2c1a0e; margin-top: 0;'>Verify Your Profile Changes</h2>
                                                    <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Hello " . htmlspecialchars($new_username) . ",</p>
                                                    <p style='font-size: 16px; line-height: 1.5; color: #555555;'>We received a request to update your profile. Please use the following One-Time Password (OTP) to verify your changes:</p>
                                                    
                                                    <div style='background-color: #fdfaf6; border: 1px solid #e8e0d4; border-radius: 8px; padding: 20px; margin: 30px 0; text-align: center;'>
                                                        <h1 style='margin: 0; font-size: 36px; color: #b5860d; letter-spacing: 5px;'>" . $otp . "</h1>
                                                    </div>
                                                    
                                                    <p style='font-size: 16px; line-height: 1.5; color: #555555;'>This OTP is valid for 10 minutes. If you did not request this change, please do not share this code with anyone.</p>
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

                        // Send OTP via WhatsApp
                        $env = parse_ini_file('.env');
                        $instance_id = $env['ULTRAMSG_INSTANCE_ID'] ?? '';
                        $token = $env['ULTRAMSG_TOKEN'] ?? '';
                        $phone = $phone_changed ? $new_phone : $_SESSION['user']['phone'];
                        $clean_phone = preg_replace('/[^0-9]/', '', $phone);

                        if (!empty($instance_id) && !empty($token) && strlen($clean_phone) >= 10) {
                            if (strlen($clean_phone) == 10) {
                                $clean_phone = "91" . $clean_phone;
                            }

                            $curl = curl_init();
                            curl_setopt_array($curl, [
                                CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_POST => true,
                                CURLOPT_POSTFIELDS => http_build_query([
                                    "token" => $token,
                                    "to" => "+" . $clean_phone,
                                    "body" => "🐾 *Paws Store - Verify Profile Changes*\n\nHello! 👋\nYour One-Time Password (OTP) for profile verification is: *" . $otp . "*\n\n⏳ This code is valid for the next 10 minutes.\nFor your security, please do not share this code with anyone.\n\nThank you for choosing Paws Store!"
                                ]),
                                CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
                                CURLOPT_SSL_VERIFYPEER => false,
                                CURLOPT_SSL_VERIFYHOST => false
                            ]);
                            curl_exec($curl);
                            curl_close($curl);
                        }

                        $success_message = "OTP has been sent to your email and WhatsApp. Please verify to complete your profile update.";
                        $show_otp_form = true;
                    } else {
                        // No changes made
                        $error_message = "No changes detected. Please modify at least one field.";
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Resend OTP handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_otp'])) {
    if (isset($_SESSION['pending_profile_changes'])) {
        $remaining_cooldown = 0;
        if (isset($_SESSION['pending_profile_changes']['otp_time'])) {
            $time_elapsed = time() - $_SESSION['pending_profile_changes']['otp_time'];
            $remaining_cooldown = max(0, 60 - $time_elapsed);
        }

        if ($remaining_cooldown > 0) {
            $error_message = "Please wait " . $remaining_cooldown . " seconds before requesting a new OTP.";
        } else {
            $otp = rand(100000, 999999);
            $_SESSION['pending_profile_changes']['otp'] = $otp;
            $_SESSION['pending_profile_changes']['otp_time'] = time();
            $_SESSION['pending_profile_changes']['attempts'] = 0;

            $changes = $_SESSION['pending_profile_changes'];
            $to = $changes['email'];
            $subject = "Resend: Verify Your Profile Changes - Paws Store";
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Email Verification</title>
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
                                        <h2 style='color: #2c1a0e; margin-top: 0;'>Verify Your Profile Changes (Resend)</h2>
                                        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Hello " . htmlspecialchars($changes['username']) . ",</p>
                                        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Here is your new One-Time Password (OTP):</p>
                                        
                                        <div style='background-color: #fdfaf6; border: 1px solid #e8e0d4; border-radius: 8px; padding: 20px; margin: 30px 0; text-align: center;'>
                                            <h1 style='margin: 0; font-size: 36px; color: #b5860d; letter-spacing: 5px;'>" . $otp . "</h1>
                                        </div>
                                        
                                        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>This OTP is valid for 10 minutes.</p>
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

            // Resend via WhatsApp
            $env = parse_ini_file('.env');
            $instance_id = $env['ULTRAMSG_INSTANCE_ID'] ?? '';
            $token = $env['ULTRAMSG_TOKEN'] ?? '';
            $clean_phone = preg_replace('/[^0-9]/', '', $changes['phone']);

            if (!empty($instance_id) && !empty($token) && strlen($clean_phone) >= 10) {
                if (strlen($clean_phone) == 10) {
                    $clean_phone = "91" . $clean_phone;
                }

                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => http_build_query([
                        "token" => $token,
                        "to" => "+" . $clean_phone,
                        "body" => "🐾 *Paws Store*\n\nHello! 👋\nYour new One-Time Password (OTP) for profile verification is: *" . $otp . "*\n\n⏳ This code is valid for the next 10 minutes.\n\nThank you!"
                    ]),
                    CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ]);
                curl_exec($curl);
                curl_close($curl);
            }

            $success_message = "A new OTP has been sent to your email and WhatsApp.";
            $show_otp_form = true;
        }
    } else {
        $error_message = "No pending profile update found.";
    }
}

// User data - show pending changes if in OTP form, otherwise show current profile
if ($show_otp_form && isset($_SESSION['pending_profile_changes'])) {
    $userEmail = $_SESSION['pending_profile_changes']['email'];
    $userName = $_SESSION['pending_profile_changes']['username'];
    $userPhone = $_SESSION['pending_profile_changes']['phone'];
} else {
    $user = $_SESSION["user"];
    $userEmail = $user["email"] ?? "";
    $userName = $user["username"] ?? "";
    $userPhone = $user["phone"] ?? "";
}
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


            <?php if (!$show_otp_form): ?>
                <form method="POST" action="profile.php">
                    <div class="ps-form-group">
                        <label for="fullName">Full Name</label>
                        <input type="text" id="fullName" name="username" value="<?php echo htmlspecialchars($userName); ?>" required>
                    </div>

                    <div class="ps-form-group">
                        <label for="emailId">Email ID</label>
                        <input type="email" id="emailId" name="email" value="<?php echo htmlspecialchars($userEmail); ?>" required>
                    </div>

                    <div class="ps-form-group">
                        <label for="mobileNo">Mobile Number</label>
                        <input type="tel" id="mobileNo" name="phone" value="<?php echo htmlspecialchars($userPhone); ?>" required>
                    </div>

                    <div class="ps-form-group" style="background-color: #fdfaf6; padding: 15px; border-radius: 8px; border: 1px solid #e8e0d4;">
                        <label style="display: flex; align-items: center; font-weight: 500; cursor: pointer; margin-bottom: 0;">
                            <input type="checkbox" id="newsletter" name="newsletter" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
                            <span>Subscribe to our newsletter for exclusive offers and updates</span>
                        </label>
                        <small style="color: #999; font-size: 12px; margin-top: 8px; display: block;">
                            📧 Get the latest news about new pets, special promotions, and more!
                        </small>
                    </div>

                    <div class="ps-profile-actions">
                        <a href="index.php" class="ps-btn-cancel">Cancel</a>
                        <button type="submit" name="save_changes" class="ps-btn-save">Save Changes</button>
                    </div>
                </form>
            <?php else: ?>
                <!-- OTP Verification Form -->
                <div style="text-align: center; margin-bottom: 30px;">
                    <p style="color: #666; font-size: 16px; margin-bottom: 5px;">
                        Verification code has been sent to:<br>
                        <strong style="color: #2c1a0e;"><?php echo htmlspecialchars($userEmail); ?></strong>
                    </p>
                    <p style="color: #999; font-size: 14px;">Valid for 10 minutes</p>
                </div>

                <form method="POST" action="profile.php">
                    <div class="ps-form-group">
                        <label for="otp">Enter OTP</label>
                        <input type="text" id="otp" name="otp" placeholder="000000" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" required autofocus>
                        <small style="color: #999; font-size: 13px; display: block; margin-top: 5px;">
                            Enter the 6-digit code sent to your email and WhatsApp
                        </small>
                    </div>

                    <div class="ps-profile-actions">
                        <button type="button" onclick="location.href='profile.php'" class="ps-btn-cancel">Cancel</button>
                        <button type="submit" name="verify_otp" class="ps-btn-save">Verify & Update</button>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" name="resend_otp" style="background: none; border: none; color: #b5860d; text-decoration: underline; cursor: pointer; font-size: 14px; font-weight: 600;">
                            Didn't receive OTP? Resend
                        </button>
                    </div>
                </form>
            <?php endif; ?>
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