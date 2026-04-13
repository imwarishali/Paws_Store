<?php

/**
 * Forgot Password - Step 1 of 3
 * Handles user password reset initiation via email or mobile number
 * Sends OTP via email and WhatsApp for verification
 */

require_once '../config.php';
require_once '../db.php';
require_once '../helpers/email_helper.php';

// Redirect if already logged in
if (isset($_SESSION["user"])) {
    header("Location: ../index.php");
    exit();
}

// Initialize variables
$error = "";
$success = "";

// Process password reset request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $email_or_mobile = trim($_POST["email_or_mobile"] ?? '');

        // Validation: Check required field
        if (empty($email_or_mobile)) {
            $error = "Please enter your email or mobile number.";
        } else {
            // Check if input is email or phone
            $is_email = filter_var($email_or_mobile, FILTER_VALIDATE_EMAIL);
            $is_phone = preg_match('/^[6-9]\d{9}$/', preg_replace('/[^0-9]/', '', $email_or_mobile));

            // Validation: Check valid email or phone format
            if (!$is_email && !$is_phone) {
                $error = "Please enter a valid email or 10-digit mobile number.";
            } else {
                // Rate limiting: Check OTP requests per hour
                if (!isset($_SESSION['otp_requests'])) {
                    $_SESSION['otp_requests'] = [];
                }
                $_SESSION['otp_requests'] = array_filter($_SESSION['otp_requests'], function ($timestamp) {
                    return ($timestamp > time() - 3600);
                });

                if (count($_SESSION['otp_requests']) >= MAX_OTP_REQUESTS_PER_HOUR) {
                    $error = "You have exceeded the maximum OTP requests. Please try again after an hour.";
                } else {
                    // Query: Search user by email or phone
                    if ($is_email) {
                        $stmt = $pdo->prepare("SELECT id, username, email, phone FROM users WHERE email = ?");
                        $stmt->execute([$email_or_mobile]);
                    } else {
                        $clean_phone = '91' . preg_replace('/[^0-9]/', '', $email_or_mobile);
                        $stmt = $pdo->prepare("SELECT id, username, email, phone FROM users WHERE phone = ? OR phone = ?");
                        $stmt->execute([$clean_phone, preg_replace('/[^0-9]/', '', $email_or_mobile)]);
                    }

                    if ($stmt->rowCount() > 0) {
                        // User found - prepare reset data
                        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        $otp = rand(100000, 999999);

                        $_SESSION['pending_reset'] = [
                            'user_id' => $user_data['id'],
                            'username' => $user_data['username'],
                            'email' => $user_data['email'],
                            'phone' => $user_data['phone'],
                            'otp' => $otp,
                            'otp_time' => time(),
                            'attempts' => 0,
                            'step' => 'otp_verification'
                        ];

                        // Send OTP via email
                        sendOTPEmail($user_data['email'], $user_data['username'], $otp);

                        // Send OTP via WhatsApp (if configured)
                        if (!empty($user_data['phone'])) {
                            sendWhatsAppMessage(
                                $user_data['phone'],
                                "🐾 *Paws Store*\n\nHello! 👋\nYour One-Time Password (OTP) for password reset is: *" . $otp . "*\n\n⏳ This code is valid for the next 10 minutes.\nFor your security, please do not share this code with anyone.\n\nThank you for choosing Paws Store!"
                            );
                        }

                        // Track OTP request and redirect
                        $_SESSION['otp_requests'][] = time();
                        $success = "OTP sent successfully! Redirecting to verification...";
                        echo "<script>setTimeout(function(){ window.location.href = 'verify_forgot_password_otp.php'; }, 2000);</script>";
                    } else {
                        $error = "No account found with this email or mobile number.";
                    }
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Password reset error: " . $e->getMessage());
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Paws Store</title>
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

        input[type="email"],
        input[type="tel"],
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0d5c4;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
            color: #2d2a26;
        }

        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #c9a227;
            box-shadow: 0 0 0 3px rgba(201, 162, 39, 0.1);
        }

        .input-hint {
            font-size: 12px;
            color: #8b7355;
            margin-top: 5px;
        }

        .form-group-note {
            background: #fdfaf6;
            border: 1px solid #e8dcc4;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 13px;
            color: #6b5d52;
            margin-bottom: 20px;
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
                font-size: 12px;
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
                <h1 class="title">Reset Password</h1>
                <p class="subtitle">Step 1 of 3</p>
            </div>

            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active">1</div>
                <div class="step">2</div>
                <div class="step">3</div>
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
                <div class="form-group-note">
                    📧 Enter the email or mobile number associated with your Paws Store account
                </div>

                <div class="form-group">
                    <label for="email_or_mobile">Email or Mobile Number</label>
                    <input
                        type="text"
                        id="email_or_mobile"
                        name="email_or_mobile"
                        placeholder="Enter email or 10-digit mobile"
                        required
                        autocomplete="off">

                </div>

                <button type="submit" name="submit">Send OTP</button>
            </form>

            <!-- Loading State -->
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Sending OTP...</p>
            </div>

            <!-- Footer -->
            <div class="form-footer">
                <p>
                    Remembered your password? <a href="login.php">Log in</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const input = document.getElementById('email_or_mobile').value.trim();

            if (!input) {
                e.preventDefault();
                alert('Please enter your email or mobile number');
                return;
            }

            // Show loading state
            document.getElementById('loading').style.display = 'block';
            document.querySelector('button[type="submit"]').disabled = true;
        });

        // Input validation helper
        document.getElementById('email_or_mobile').addEventListener('input', function(e) {
            let value = e.target.value;
            // Allow only email-like or phone-like characters
            if (value && !value.includes('@')) {
                value = value.replace(/[^0-9\s\-+()\[\]]/g, '');
                e.target.value = value;
            }
        });
    </script>
</body>

</html>