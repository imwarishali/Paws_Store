<?php

require_once '../config.php';
require_once '../db.php';
require_once '../helpers/email_helper.php';

if (isset($_SESSION["user"])) {
    header("Location: ../index.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email = trim($_POST["email"]);
        $username = trim($_POST["username"]);
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];

        if (empty($email) || empty($username) || empty($new_password) || empty($confirm_password)) {
            $error = "All fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif (!preg_match('/[0-9]/', $new_password)) {
            $error = "Password must contain at least one number.";
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
            $error = "Password must contain at least one special character.";
        } else {
            // Rate Limiting: Max 3 OTP requests per hour
            if (!isset($_SESSION['otp_requests'])) {
                $_SESSION['otp_requests'] = [];
            }
            $_SESSION['otp_requests'] = array_filter($_SESSION['otp_requests'], function ($timestamp) {
                return ($timestamp > time() - 3600);
            });

            if (count($_SESSION['otp_requests']) >= 6) {
                $error = "You have exceeded the maximum number of OTP requests. Please try again after an hour.";
            } else {
                // Verify user exists with given email and username
                $stmt = $pdo->prepare("SELECT id, phone FROM users WHERE email = ? AND username = ?");
                $stmt->execute([$email, $username]);

                if ($stmt->rowCount() > 0) {
                    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $user_id = $user_data['id'];
                    $phone = $user_data['phone'];
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $otp = rand(100000, 999999);

                    $_SESSION['pending_reset'] = [
                        'user_id' => $user_id,
                        'username' => $username,
                        'email' => $email,
                        'phone' => $phone,
                        'password_hash' => $password_hash,
                        'otp' => $otp,
                        'otp_time' => time()
                    ];

                    // Send OTP Email
                    $to = $email;
                    $subject = "Password Reset OTP - Paws Store";
                    $message = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Password Reset OTP</title>
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
                                            <h2 style='color: #2c1a0e; margin-top: 0;'>Password Reset OTP</h2>
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Hello " . htmlspecialchars($username) . ",</p>
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>We received a request to reset the password for your account. Please use the following One-Time Password (OTP) to proceed:</p>
                                            
                                            <div style='background-color: #fdfaf6; border: 1px solid #e8e0d4; border-radius: 8px; padding: 20px; margin: 30px 0; text-align: center;'>
                                                <h1 style='margin: 0; font-size: 36px; color: #b5860d; letter-spacing: 5px;'>" . $otp . "</h1>
                                            </div>
                                            
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>This OTP is valid for 10 minutes. If you did not request a password reset, please ignore this email or contact support immediately.</p>
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

                    $env = parse_ini_file('../.env');
                    $system_email = $env['SYSTEM_EMAIL'] ?? 'noreply@localhost';
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                    $headers .= "From: Paws Store <" . $system_email . ">\r\n";

                    @mail($to, $subject, $message, $headers);

                    // Send OTP via WhatsApp (Using UltraMsg API)
                    $instance_id = $env['ULTRAMSG_INSTANCE_ID'] ?? '';
                    $token = $env['ULTRAMSG_TOKEN'] ?? '';
                    $clean_phone = preg_replace('/[^0-9]/', '', $phone);

                    if (!empty($instance_id) && !empty($token) && strlen($clean_phone) >= 10) {
                        if (strlen($clean_phone) == 10) {
                            $clean_phone = "91" . $clean_phone; // Add India country code if 10 digits
                        }

                        $curl = curl_init();
                        curl_setopt_array($curl, [
                            CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => http_build_query([
                                "token" => $token,
                                "to" => "+" . $clean_phone,
                                "body" => "🐾 *Paws Store*\n\nHello! 👋\nYour One-Time Password (OTP) for password reset is: *" . $otp . "*\n\n⏳ This code is valid for the next 10 minutes.\nFor your security, please do not share this code with anyone.\n\nThank you for choosing Paws Store!"
                            ]),
                            CURLOPT_HTTPHEADER => [
                                "Content-Type: application/x-www-form-urlencoded"
                            ],
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false
                        ]);
                        $response = curl_exec($curl);
                        $err = curl_error($curl);
                        curl_close($curl);

                        if ($err) {
                            error_log("cURL Error (WhatsApp): " . $err);
                        } else {
                            error_log("WhatsApp API Response: " . $response);
                        }
                    }

                    $_SESSION['otp_requests'][] = time(); // Log the successful request

                    $success = "OTP sent to your email and WhatsApp! Redirecting to verification...";
                    echo "<script>setTimeout(function(){ window.location.href = 'verify_forgot_password_otp.php'; }, 2000);</script>";
                } else {
                    $error = "No account found with this Email and Username combination.";
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Paws Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --cream: #faf6f0;
            --brown: #5c4033;
            --accent: #c9a227;
            --accent-soft: #e8d5a3;
            --text: #2d2a26;
            --text-muted: #6b6560;
            --white: #ffffff;
            --shadow: 0 4px 20px rgba(92, 64, 51, 0.08);
            --radius: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--cream);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .register-wrap {
            max-width: 440px;
            width: 100%;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 2.5rem;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--brown);
            margin-bottom: 0.5rem;
        }

        .sub {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            border: 1px solid rgba(92, 64, 51, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            font-family: inherit;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
        }

        .btn {
            width: 100%;
            padding: 0.9rem;
            background: var(--accent);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            font-family: inherit;
            margin-top: 0.5rem;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .login-link a {
            color: var(--accent);
            font-weight: 500;
            text-decoration: none;
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }

        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: var(--text-muted);
        }
    </style>
</head>

<body>
    <div class="register-wrap">
        <h1>Reset Password</h1>
        <p class="sub">Verify your identity to create a new password.</p>

        <?php if ($error != ""): ?>
            <p class="message error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success != ""): ?>
            <p class="message success"><?php echo $success; ?></p>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
            <div class="form-group">
                <label for="email">Registered Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('new_password', this)">👁️</button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm_password', this)">👁️</button>
                </div>
            </div>
            <button type="submit" class="btn">Reset Password</button>
        </form>
        <p class="login-link">Remembered your password? <a href="login.php">Login here</a></p>
    </div>

    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '🙈';
            } else {
                input.type = 'password';
                button.textContent = '👁️';
            }
        }
    </script>
</body>

</html>