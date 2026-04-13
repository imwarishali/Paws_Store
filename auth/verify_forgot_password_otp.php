<?php

require_once '../config.php';
require_once '../db.php';
require_once '../helpers/email_helper.php';

if (isset($_SESSION["user"])) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_SESSION['pending_reset'])) {
    header("Location: forgot_password.php");
    exit();
}

$error = "";
$success = "";

$remaining_cooldown = 0;
if (isset($_SESSION['pending_reset']['otp_time'])) {
    $time_elapsed = time() - $_SESSION['pending_reset']['otp_time'];
    $remaining_cooldown = max(0, 60 - $time_elapsed);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['resend'])) {
        if ($remaining_cooldown > 0) {
            $error = "Please wait before requesting a new OTP.";
        } else {
            $otp = rand(100000, 999999);
            $_SESSION['pending_reset']['otp'] = $otp;
            $_SESSION['pending_reset']['otp_time'] = time();
            $_SESSION['pending_reset']['attempts'] = 0; // Reset attempts for new OTP
            $remaining_cooldown = 60;

            $username = $_SESSION['pending_reset']['username'];
            $email = $_SESSION['pending_reset']['email'];
            $phone = $_SESSION['pending_reset']['phone'] ?? '';
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
                                    <td style='background-color: #5c4033; padding: 30px; text-align: center;'>
                                        <h1 style='color: #c9a227; margin: 0; font-size: 28px; font-weight: normal;'>🐾 Paws Store</h1>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='padding: 40px 30px;'>
                                        <h2 style='color: #5c4033; margin-top: 0;'>Password Reset OTP</h2>
                                        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Hello " . htmlspecialchars($username) . ",</p>
                                        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>We received a request to resend the password reset code for your account. Please use the following One-Time Password (OTP) to proceed:</p>
                                        
                                        <div style='background-color: #fdfaf6; border: 1px solid #e8dcc4; border-radius: 8px; padding: 20px; margin: 30px 0; text-align: center;'>
                                            <h1 style='margin: 0; font-size: 36px; color: #c9a227; letter-spacing: 5px;'>" . $otp . "</h1>
                                        </div>
                                        
                                        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>This OTP is valid for 10 minutes. If you did not request a password reset, please ignore this email or contact support immediately.</p>
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
            $headers .= "From: Paws Store <" . $system_email . ">\r\n";

            @mail($to, $subject, $message, $headers);

            // Resend OTP via WhatsApp (Using UltraMsg API)
            $env = parse_ini_file('../.env');
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

            $success = "A new OTP has been sent to your email and WhatsApp.";
        }
    } else {
        $entered_otp = trim($_POST['otp']);

        if (empty($entered_otp)) {
            $error = "Please enter the OTP.";
        } elseif (time() - $_SESSION['pending_reset']['otp_time'] > 600) { // 10 minutes expiry
            $error = "OTP has expired. Please request a new password reset.";
            unset($_SESSION['pending_reset']);
        } elseif ($entered_otp != $_SESSION['pending_reset']['otp']) {
            if (!isset($_SESSION['pending_reset']['attempts'])) {
                $_SESSION['pending_reset']['attempts'] = 0;
            }
            $_SESSION['pending_reset']['attempts']++;

            if ($_SESSION['pending_reset']['attempts'] >= 6) {
                $error = "Too many failed attempts. Please request a new password reset.";
                unset($_SESSION['pending_reset']);
            } else {
                $remaining = 6 - $_SESSION['pending_reset']['attempts'];
                $error = "Invalid OTP. You have {$remaining} attempt(s) left.";
            }
        } else {
            // OTP is valid, proceed to password reset page
            $_SESSION['pending_reset']['step'] = 'password_reset';
            $success = "OTP verified successfully! Redirecting to password reset...";
            echo "<script>setTimeout(function(){ window.location.href = 'set_new_password.php'; }, 1500);</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Password Reset - Paws Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --cream: #faf6f0;
            --brown: #5c4033;
            --accent: #c9a227;
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
            background: linear-gradient(135deg, #faf6f0 0%, #e8dcc4 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow-x: hidden;
        }

        .register-wrap {
            max-width: 440px;
            width: 100%;
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: 0 20px 60px rgba(92, 64, 51, 0.15);
            padding: 2.5rem;
            border: 2px solid #e8dcc4;
            animation: slideInUp 0.8s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            border: 2px solid #e8dcc4;
            border-radius: 10px;
            font-size: 1.2rem;
            font-family: inherit;
            letter-spacing: 2px;
            text-align: center;
            background-color: #faf6f0;
            color: #2d2a26;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent);
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(201, 162, 39, 0.15);
        }

        .btn {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, var(--brown) 0%, #3d2a21 100%);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            font-family: inherit;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(92, 64, 51, 0.2);
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

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background: linear-gradient(135deg, #9b7958 0%, #7a5c47 100%) !important;
        }
    </style>
</head>

<body>
    <div class="register-wrap">
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 40px; margin-bottom: 15px;">🐾</div>
            <h1>Verify OTP</h1>
            <p class="sub">Step 2 of 3</p>
        </div>

        <!-- Step Indicator -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; position: relative;">
            <div style="position: absolute; top: 20px; left: 0; right: 0; height: 2px; background: #e0d5c4; z-index: 0;"></div>
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #5c4033; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; position: relative; z-index: 1;">✓</div>
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #c9a227; color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; position: relative; z-index: 1; box-shadow: 0 4px 12px rgba(201, 162, 39, 0.3);">2</div>
            <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0d5c4; color: #8b7355; display: flex; align-items: center; justify-content: center; font-weight: 600; position: relative; z-index: 1;">3</div>
        </div>

        <p style="text-align: center; color: #5c4033; font-weight: 500; margin-bottom: 20px;">We have sent a 6-digit OTP to <strong><?php echo htmlspecialchars($_SESSION['pending_reset']['email'] ?? ''); ?></strong> and your WhatsApp.</p>

        <?php if ($error != ""): ?>
            <p class="message error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success != ""): ?>
            <p class="message success"><?php echo $success; ?></p>
        <?php endif; ?>

        <form method="POST" action="verify_forgot_password_otp.php">
            <div class="form-group">
                <label for="otp">Enter 6-Digit OTP</label>
                <input type="text" id="otp" name="otp" maxlength="6" pattern="\d{6}" placeholder="000000" required autocomplete="one-time-code" style="text-align: center; font-size: 24px; letter-spacing: 5px; font-weight: bold;">
            </div>
            <button type="submit" class="btn">Verify OTP</button>
        </form>

        <p class="login-link"><a href="forgot_password.php">← Back</a></p>

        <form method="POST" action="verify_forgot_password_otp.php" style="margin-top: 10px;">
            <input type="hidden" name="resend" value="1">
            <button type="submit" id="resend-btn" class="btn" disabled style="background: white; color: #5c4033; border: 2px solid #c9a227;">Resend OTP</button>
        </form>
    </div>

    <script>
        let cooldown = <?php echo $remaining_cooldown; ?>;
        const resendBtn = document.getElementById('resend-btn');

        if (cooldown > 0) {
            resendBtn.disabled = true;
            const timer = setInterval(() => {
                cooldown--;
                resendBtn.textContent = `Resend OTP in ${cooldown}s`;
                if (cooldown <= 0) {
                    clearInterval(timer);
                    resendBtn.disabled = false;
                    resendBtn.textContent = 'Resend OTP';
                }
            }, 1000);
        } else {
            resendBtn.disabled = false;
            resendBtn.textContent = 'Resend OTP';
        }
    </script>
</body>

</html>