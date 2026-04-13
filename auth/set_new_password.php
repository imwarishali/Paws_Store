<?php

require_once '../config.php';
require_once '../db.php';
require_once '../helpers/email_helper.php';

if (isset($_SESSION["user"])) {
    header("Location: ../index.php");
    exit();
}

// Check if user came from OTP verification (Step 2)
if (!isset($_SESSION['pending_reset']) || $_SESSION['pending_reset']['step'] !== 'password_reset') {
    header("Location: forgot_password.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $new_password = $_POST["new_password"] ?? '';
        $confirm_password = $_POST["confirm_password"] ?? '';

        // Validation
        if (empty($new_password) || empty($confirm_password)) {
            $error = "All fields are required.";
        } elseif (strlen($new_password) < 8) {
            $error = "Password must be at least 8 characters long.";
        } elseif (!preg_match('/[0-9]/', $new_password)) {
            $error = "Password must contain at least one number.";
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
            $error = "Password must contain at least one special character.";
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            // Hash the password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // Update password in database
            $user_id = $_SESSION['pending_reset']['user_id'];
            $username = $_SESSION['pending_reset']['username'];
            $email = $_SESSION['pending_reset']['email'];
            $phone = $_SESSION['pending_reset']['phone'] ?? '';

            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update_stmt->execute([$password_hash, $user_id])) {

                // Send Confirmation Email
                $to = $email;
                $subject = "Security Alert: Password Reset - Paws Store";
                $message = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Password Reset Successful</title>
                </head>
                <body style='margin: 0; padding: 0; font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; background-color: #faf7f2; color: #333333;'>
                    <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #faf7f2; padding: 20px;'>
                        <tr>
                            <td align='center'>
                                <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                                    <tr>
                                        <td style='background-color: #5c4033; padding: 30px; text-align: center;'>
                                            <h1 style='color: #c9a227; margin: 0; font-size: 28px; font-weight: normal;'>🐾 Paws Store</h1>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 40px 30px;'>
                                            <h2 style='color: #5c4033; margin-top: 0;'>Password Reset Successful ✓</h2>
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Hello " . htmlspecialchars($username) . ",</p>
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>This is a confirmation that the password for your Paws Store account has been successfully changed.</p>
                                            
                                            <div style='background-color: #fdfaf6; border: 1px solid #e8dcc4; border-radius: 8px; padding: 20px; margin: 30px 0; text-align: center;'>
                                                <p style='margin: 0; font-size: 16px; color: #555555;'>✓ If you made this change, no further action is required.</p>
                                            </div>
                                            
                                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'><strong>Didn't make this change?</strong> Please contact our support team immediately.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                                            <p style='margin: 0; color: #888888; font-size: 14px;'>Best Regards,<br><strong style='color: #5c4033;'>🐾 Paws Store Team</strong></p>
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

                // Send WhatsApp Notification
                if (!empty($phone)) {
                    $env = parse_ini_file('../.env');
                    $instance_id = $env['ULTRAMSG_INSTANCE_ID'] ?? '';
                    $token = $env['ULTRAMSG_TOKEN'] ?? '';
                    $clean_phone = preg_replace('/[^0-9]/', '', $phone);

                    if (!empty($instance_id) && !empty($token) && strlen($clean_phone) >= 10) {
                        if (strlen($clean_phone) == 10) {
                            $clean_phone = "91" . $clean_phone;
                        }

                        $wa_body = "🐾 *Paws Store - Security Alert*\n\nHello *" . htmlspecialchars($username) . "*,\n\n✓ Your password has been successfully reset.\n\nIf you made this change, no further action is required.\n\nIf you didn't make this change, please contact our support team immediately.";

                        $curl = curl_init();
                        curl_setopt_array($curl, [
                            CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => http_build_query([
                                "token" => $token,
                                "to" => "+" . $clean_phone,
                                "body" => $wa_body
                            ]),
                            CURLOPT_HTTPHEADER => [
                                "Content-Type: application/x-www-form-urlencoded"
                            ],
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false
                        ]);
                        $response = curl_exec($curl);
                        curl_close($curl);
                    }
                }

                // Clear session and redirect
                unset($_SESSION['pending_reset']);
                $success = "Password reset successfully! Redirecting to login...";
                echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
            } else {
                $error = "Failed to update password. Please try again.";
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
    <title>Set New Password - Paws Store</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #5c4033 0%, #8b6f47 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 450px;
        }

        .form-wrapper {
            background: #faf6f0;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 40px;
            margin-bottom: 15px;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .title {
            color: #5c4033;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #8b7355;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0d5c4;
            z-index: 0;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0d5c4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #8b7355;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
            font-size: 12px;
        }

        .step.active {
            background: #c9a227;
            color: white;
            box-shadow: 0 4px 12px rgba(201, 162, 39, 0.3);
        }

        .step.completed {
            background: #5c4033;
            color: white;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-error {
            background: #ffe0e0;
            color: #c81e1e;
            border-left: 4px solid #c81e1e;
        }

        .alert-success {
            background: #e0f7e0;
            color: #1e7e1e;
            border-left: 4px solid #1e7e1e;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #5c4033;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0d5c4;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            color: #2d2a26;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: #c9a227;
            box-shadow: 0 0 0 3px rgba(201, 162, 39, 0.1);
        }

        .password-hint {
            background: #fdfaf6;
            border: 1px solid #e8dcc4;
            border-radius: 8px;
            padding: 12px;
            font-size: 12px;
            color: #6b5d52;
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .password-hint ul {
            margin: 0;
            padding-left: 20px;
        }

        .password-hint li {
            margin: 4px 0;
        }

        .hint-satisfied {
            color: #1e7e1e;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #c9a227 0%, #a67e1f 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(201, 162, 39, 0.3);
        }

        button:active:not(:disabled) {
            transform: translateY(0);
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .secondary-btn {
            background: white;
            color: #5c4033;
            border: 2px solid #c9a227;
            margin-top: 10px;
        }

        .secondary-btn:hover {
            background: #fdfaf6;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
        }

        .form-footer p {
            color: #8b7355;
            font-size: 14px;
        }

        .form-footer a {
            color: #c9a227;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: #a67e1f;
        }

        .loading {
            display: none;
            text-align: center;
            color: #5c4033;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e0d5c4;
            border-top-color: #c9a227;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (max-width: 480px) {
            .form-wrapper {
                padding: 25px;
            }

            .title {
                font-size: 24px;
            }

            .step-indicator {
                margin-bottom: 25px;
            }

            .step {
                width: 35px;
                height: 35px;
                font-size: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-wrapper">
            <!-- Header -->
            <div class="header">
                <div class="logo">🐾</div>
                <h1 class="title">Create New Password</h1>
                <p class="subtitle">Step 3 of 3</p>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step completed">✓</div>
                <div class="step completed">✓</div>
                <div class="step active">3</div>
            </div>

            <!-- Error/Success Messages -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <strong>Success!</strong> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input
                        type="password"
                        id="new_password"
                        name="new_password"
                        placeholder="Enter new password"
                        required
                        autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        placeholder="Confirm password"
                        required
                        autocomplete="new-password">
                </div>

                <div class="password-hint">
                    <strong>Password Requirements:</strong>
                    <ul>
                        <li id="hint-length">❌ At least 8 characters</li>
                        <li id="hint-number">❌ At least one number (0-9)</li>
                        <li id="hint-special">❌ At least one special character (!@#$%^&*)</li>
                        <li id="hint-match">❌ Passwords must match</li>
                    </ul>
                </div>

                <button type="submit" name="submit" id="submit-btn" disabled>Reset Password</button>
            </form>

            <!-- Loading State -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Resetting your password...</p>
            </div>

            <!-- Footer -->
            <div class="form-footer">
                <p>
                    Changed your mind? <a href="login.php">Back to Login</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        const passwordInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('submit-btn');

        function validatePassword() {
            const password = passwordInput.value;
            const confirm = confirmInput.value;

            const lengthOk = password.length >= 8;
            const numberOk = /[0-9]/.test(password);
            const specialOk = /[^a-zA-Z0-9]/.test(password);
            const matchOk = password === confirm && password.length > 0;

            // Update hints
            updateHint('hint-length', lengthOk);
            updateHint('hint-number', numberOk);
            updateHint('hint-special', specialOk);
            updateHint('hint-match', matchOk);

            // Enable/disable submit button
            submitBtn.disabled = !(lengthOk && numberOk && specialOk && matchOk);
        }

        function updateHint(id, isValid) {
            const element = document.getElementById(id);
            if (isValid) {
                element.innerHTML = element.innerHTML.replace('❌', '✓');
                element.classList.add('hint-satisfied');
            } else {
                element.innerHTML = element.innerHTML.replace('✓', '❌');
                element.classList.remove('hint-satisfied');
            }
        }

        passwordInput.addEventListener('input', validatePassword);
        confirmInput.addEventListener('input', validatePassword);

        // Form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }
            document.getElementById('loading').style.display = 'block';
            submitBtn.disabled = true;
        });
    </script>
</body>

</html>