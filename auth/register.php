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
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST["username"] ?? '');
  $email = trim($_POST["email"] ?? '');
  $phone = trim($_POST["phone"] ?? '');
  $password = $_POST["password"] ?? '';
  $confirm_password = $_POST["confirm_password"] ?? '';

  if (empty($username) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
    $error = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  } elseif (!preg_match(PHONE_REGEX, preg_replace('/[^0-9]/', '', $phone))) {
    $error = "Please enter a valid 10-digit mobile number.";
  } elseif ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
    $error = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long.";
  } elseif (!preg_match('/[0-9]/', $password)) {
    $error = "Password must contain at least one number.";
  } elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
    $error = "Password must contain at least one special character.";
  } else {
    try {
      // Rate Limiting: Check OTP requests
      if (!isset($_SESSION['otp_requests'])) {
        $_SESSION['otp_requests'] = [];
      }
      $_SESSION['otp_requests'] = array_filter($_SESSION['otp_requests'], function ($timestamp) {
        return ($timestamp > time() - 3600);
      });

      if (count($_SESSION['otp_requests']) >= MAX_OTP_REQUESTS_PER_HOUR) {
        $error = "You have exceeded the maximum number of OTP requests. Please try again after an hour.";
      } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);

        if ($stmt->rowCount() > 0) {
          $error = "An account with this email or username already exists.";
        } else {
          $password_hash = password_hash($password, PASSWORD_DEFAULT);
          $otp = rand(100000, 999999);

          $_SESSION['pending_user'] = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => $password_hash,
            'otp' => $otp,
            'otp_time' => time()
          ];

          // Send OTP Email using helper
          sendOTPEmail($email, $username, $otp);

          // Send OTP via WhatsApp
          $wa_body = "🐾 *Paws Store*\n\nHello! 👋\nYour One-Time Password (OTP) for account verification is: *" . $otp . "*\n\n⏳ This code is valid for the next 10 minutes.\nFor your security, please do not share this code with anyone.\n\nThank you for choosing Paws Store!";
          sendWhatsAppMessage($phone, $wa_body);

          $_SESSION['otp_requests'][] = time();
          $success = "OTP sent to your email and WhatsApp! Redirecting to verification...";
          $redirect_url = 'verify_otp.php' . (!empty($redirect) ? '?redirect=' . urlencode($redirect) : '');
          echo "<script>setTimeout(function(){ window.location.href = '" . $redirect_url . "'; }, 2000);</script>";
        }
      }
    } catch (PDOException $e) {
      error_log("Registration error: " . $e->getMessage());
      $error = "Database error: " . $e->getMessage();
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Paws Store</title>
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
    <h1>Create account</h1>
    <p class="sub">Join us and find your new best friend!</p>

    <?php if ($error != ""): ?>
      <p class="message error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success != ""): ?>
      <p class="message success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Your name" required>
      </div>
      <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label for="phone">Phone number</label>
        <input type="tel" id="phone" name="phone" placeholder="98765 43210" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-wrapper">
          <input type="password" id="password" name="password" placeholder="Min 8 chars, 1 number, 1 symbol" required>
          <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password', this)">👁️</button>
        </div>
      </div>
      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <div class="password-wrapper">
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
          <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm_password', this)">👁️</button>
        </div>
      </div>
      <button type="submit" class="btn">Register</button>
    </form>
    <p class="login-link">Already have an account? <a href="login.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>">Login here</a></p>
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