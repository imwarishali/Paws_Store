<?php

/**
 * User Registration Page
 * Handles user registration with OTP verification via email and WhatsApp
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
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';

// Process registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Get and trim user input
  $username = trim($_POST["username"] ?? '');
  $email = trim($_POST["email"] ?? '');
  $phone = trim($_POST["phone"] ?? '');
  $password = $_POST["password"] ?? '';
  $confirm_password = $_POST["confirm_password"] ?? '';

  // Validation: Check required fields
  if (empty($username) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
    $error = "All fields are required.";
  }
  // Validation: Check email format
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  }
  // Validation: Check phone number
  elseif (!preg_match(PHONE_REGEX, preg_replace('/[^0-9]/', '', $phone))) {
    $error = "Please enter a valid 10-digit mobile number.";
  }
  // Validation: Check passwords match
  elseif ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  }
  // Validation: Check password length
  elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
    $error = "Password must be at least " . PASSWORD_MIN_LENGTH . " characters long.";
  }
  // Validation: Check password contains number
  elseif (!preg_match('/[0-9]/', $password)) {
    $error = "Password must contain at least one number.";
  }
  // Validation: Check password contains special character
  elseif (!preg_match('/[^a-zA-Z0-9]/', $password)) {
    $error = "Password must contain at least one special character.";
  }
  // All validations passed - process registration
  else {
    try {
      // Rate limiting: Initialize OTP request tracking
      if (!isset($_SESSION['otp_requests'])) {
        $_SESSION['otp_requests'] = [];
      }

      // Filter out requests older than 1 hour
      $_SESSION['otp_requests'] = array_filter($_SESSION['otp_requests'], function ($timestamp) {
        return ($timestamp > time() - 3600);
      });

      // Check if user exceeded OTP request limit
      if (count($_SESSION['otp_requests']) >= MAX_OTP_REQUESTS_PER_HOUR) {
        $error = "You have exceeded the maximum number of OTP requests. Please try again after an hour.";
      } else {
        // Check if email or username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);

        if ($stmt->rowCount() > 0) {
          $error = "An account with this email or username already exists.";
        } else {
          // Generate password hash and OTP
          $password_hash = password_hash($password, PASSWORD_DEFAULT);
          $otp = rand(100000, 999999);

          // Store pending user data in session
          $_SESSION['pending_user'] = [
            'username' => $username,
            'email' => $email,
            'phone' => $phone,
            'password' => $password_hash,
            'otp' => $otp,
            'otp_time' => time()
          ];

          // Send OTP via email
          sendOTPEmail($email, $username, $otp);

          // Send OTP via WhatsApp
          $wa_body = "🐾 *Paws Store*\n\nHello! 👋\nYour One-Time Password (OTP) for account verification is: *" . $otp . "*\n\n⏳ This code is valid for the next 10 minutes.\nFor your security, please do not share this code with anyone.\n\nThank you for choosing Paws Store!";
          sendWhatsAppMessage($phone, $wa_body);

          // Track OTP request and set success message
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
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    /* ========== GLOBAL STYLES ========== */
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

    /* ========== ANIMATIONS ========== */
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

    /* ========== CARD STYLING ========== */
    .register-card {
      max-width: 500px;
      width: 100%;
      background: #ffffff;
      border-radius: 40px;
      padding: 3rem 2.5rem;
      position: relative;
      box-shadow: 0 20px 60px rgba(92, 64, 51, 0.2);
      animation: slideUp 0.8s ease-out;
      border: 3px solid #c9a227;
    }

    /* ========== FORM GROUPS ========== */
    .form-group {
      margin-bottom: 1.8rem;
      animation: slideUp 0.8s ease-out backwards;
    }

    .form-group:nth-child(1) {
      animation-delay: 0.1s;
    }

    .form-group:nth-child(2) {
      animation-delay: 0.2s;
    }

    .form-group:nth-child(3) {
      animation-delay: 0.3s;
    }

    .form-group:nth-child(4) {
      animation-delay: 0.4s;
    }

    .form-group:nth-child(5) {
      animation-delay: 0.5s;
    }

    .form-group label {
      display: block;
      font-weight: 700;
      color: #2C1A0E;
      margin-bottom: 0.6rem;
      font-size: 0.95rem;
    }

    .form-group input {
      width: 100%;
      padding: 0.9rem 1.2rem;
      border: 2px solid #e8dcc4;
      border-radius: 15px;
      font-size: 1rem;
      font-family: inherit;
      background-color: #faf6f0;
      color: #2d2a26;
      transition: all 0.3s ease;
    }

    .form-group input:focus {
      outline: none;
      background-color: #ffffff;
      border-color: #c9a227;
      box-shadow: 0 0 0 3px rgba(201, 162, 39, 0.15);
      transform: scale(1.02);
    }

    .form-group input::placeholder {
      color: #999;
    }

    /* ========== PASSWORD FIELD ========== */
    .password-wrapper {
      position: relative;
    }

    .password-wrapper input {
      padding-right: 50px;
    }

    .password-toggle {
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      font-size: 18px;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      line-height: 1;
      color: #999;
    }

    /* ========== PASSWORD REQUIREMENTS ========== */
    .password-requirements {
      margin-top: 8px;
      font-size: 12px;
      color: #666;
    }

    .password-requirements div {
      margin: 4px 0;
      display: flex;
      align-items: center;
    }

    .req-check {
      margin-right: 6px;
      font-size: 14px;
    }

    .requirement-valid {
      color: #4caf50;
      font-weight: 600;
    }

    .requirement-invalid {
      color: #f44336;
      font-weight: 600;
    }

    /* ========== HEADER SECTION ========== */
    .register-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .register-header h1 {
      font-size: 2rem;
      color: #2C1A0E;
      margin-top: 0.5rem;
      font-weight: 700;
    }

    .register-header p {
      color: #8b7355;
      font-size: 0.95rem;
      margin-top: 0.5rem;
    }

    /* ========== ALERT MESSAGES ========== */
    .message {
      padding: 12px 15px;
      border-radius: 10px;
      margin-bottom: 15px;
      font-size: 14px;
      text-align: center;
      animation: slideUp 0.5s ease-out;
    }

    .error {
      color: #c81e1e;
      background-color: #ffe0e0;
      border-left: 4px solid #c81e1e;
    }

    .success {
      color: #1e7e1e;
      background-color: #e0f7e0;
      border-left: 4px solid #1e7e1e;
    }

    /* ========== BUTTONS ========== */
    button {
      width: 100%;
      padding: 1rem;
      background: linear-gradient(135deg, #c9a227 0%, #a67e1f 100%);
      color: white;
      border: none;
      border-radius: 15px;
      font-weight: 700;
      font-size: 1rem;
      font-family: inherit;
      cursor: pointer;
      margin-top: 0.5rem;
      transition: all 0.3s ease;
      animation: slideUp 0.8s ease-out backwards 0.5s;
    }

    button:hover:not(:disabled) {
      transform: translateY(-3px);
      box-shadow: 0 10px 25px rgba(201, 162, 39, 0.3);
    }

    button:active:not(:disabled) {
      transform: translateY(-1px);
    }

    button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    /* ========== FOOTER ========== */
    .form-footer {
      text-align: center;
      margin-top: 1.5rem;
      animation: slideUp 0.8s ease-out backwards 0.6s;
    }

    .form-footer p {
      color: #8b7355;
      font-size: 0.95rem;
    }

    .form-footer a {
      color: #c9a227;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .form-footer a:hover {
      color: #a67e1f;
      text-decoration: underline;
    }

    /* ========== RESPONSIVE DESIGN ========== */
    @media (max-width: 480px) {
      .register-card {
        border-radius: 30px;
        padding: 2rem 1.5rem;
        border: 2px solid #c9a227;
      }

      .form-group input {
        padding: 0.8rem 1rem;
      }

      button {
        padding: 0.9rem;
      }
    }
  </style>
</head>

<body>
  <div class="register-card">
    <!-- Header Section -->
    <div class="register-header">
      <h1>Join Paws Store</h1>
      <p>Find your perfect pet companion</p>
    </div>

    <!-- Alert Messages Section -->
    <?php if ($error != ""): ?>
      <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success != ""): ?>
      <div class="message success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Registration Form Section -->
    <form method="POST" action="register.php" id="registerForm">
      <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

      <!-- Username Field -->
      <div class="form-group">
        <label for="username">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
          placeholder="Choose a username"
          required
          autocomplete="username">
      </div>

      <!-- Email Field -->
      <div class="form-group">
        <label for="email">Email Address</label>
        <input
          type="email"
          id="email"
          name="email"
          value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
          placeholder="your@email.com"
          required
          autocomplete="email">
      </div>

      <!-- Phone Field -->
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input
          type="tel"
          id="phone"
          name="phone"
          value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
          placeholder="10-digit mobile number"
          required
          autocomplete="tel">
      </div>

      <!-- Password Field -->
      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-wrapper">
          <input
            type="password"
            id="password"
            name="password"
            value="<?php echo htmlspecialchars($_POST['password'] ?? ''); ?>"
            placeholder="Create a strong password"
            required
            autocomplete="new-password"
            oninput="updatePasswordRequirements()">
          <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password', this)">👁️</button>
        </div>
        <div class="password-requirements" id="passwordRequirements">
          <div>
            <span class="req-check requirement-invalid" id="req-length">✗</span>
            <span>At least 8 characters</span>
          </div>
          <div>
            <span class="req-check requirement-invalid" id="req-number">✗</span>
            <span>At least 1 number (0-9)</span>
          </div>
          <div>
            <span class="req-check requirement-invalid" id="req-special">✗</span>
            <span>At least 1 special character (!@#$%)</span>
          </div>
        </div>
      </div>

      <!-- Confirm Password Field -->
      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <div class="password-wrapper">
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            value="<?php echo htmlspecialchars($_POST['confirm_password'] ?? ''); ?>"
            placeholder="Confirm your password"
            required
            autocomplete="new-password"
            oninput="updateConfirmPasswordStatus()">
          <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm_password', this)">👁️</button>
        </div>
        <div class="password-requirements">
          <div>
            <span class="req-check requirement-invalid" id="match-check">✗</span>
            <span>Passwords must match</span>
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <button type="submit" class="btn">Create Account</button>
    </form>

    <!-- Footer Section -->
    <div class="form-footer">
      <p>Already have an account? <a href="login.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>">Login here</a></p>
    </div>
  </div>

  <script>
    /**
     * ========== PASSWORD VISIBILITY TOGGLE ==========
     * Toggle password field visibility between text and password type
     */
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

    /**
     * ========== PASSWORD REQUIREMENTS VALIDATION ==========
     * Check password requirements and update UI indicators
     */
    function updatePasswordRequirements() {
      const password = document.getElementById('password').value;
      const hasLength = password.length >= 8;
      const hasNumber = /[0-9]/.test(password);
      const hasSpecial = /[^a-zA-Z0-9]/.test(password);

      updateRequirement('req-length', hasLength);
      updateRequirement('req-number', hasNumber);
      updateRequirement('req-special', hasSpecial);
    }

    /**
     * ========== CONFIRM PASSWORD STATUS ==========
     * Check if confirm password matches password field
     */
    function updateConfirmPasswordStatus() {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const matchCheck = document.getElementById('match-check');

      if (confirmPassword === '') {
        matchCheck.textContent = '✗';
        matchCheck.classList.remove('requirement-valid');
        matchCheck.classList.add('requirement-invalid');
      } else if (password === confirmPassword) {
        matchCheck.textContent = '✓';
        matchCheck.classList.remove('requirement-invalid');
        matchCheck.classList.add('requirement-valid');
      } else {
        matchCheck.textContent = '✗';
        matchCheck.classList.remove('requirement-valid');
        matchCheck.classList.add('requirement-invalid');
      }
    }

    /**
     * ========== UPDATE REQUIREMENT INDICATOR ==========
     * Update the visual indicator for a requirement
     */
    function updateRequirement(elementId, isValid) {
      const element = document.getElementById(elementId);
      if (isValid) {
        element.textContent = '✓';
        element.classList.remove('requirement-invalid');
        element.classList.add('requirement-valid');
      } else {
        element.textContent = '✗';
        element.classList.remove('requirement-valid');
        element.classList.add('requirement-invalid');
      }
    }

    /**
     * ========== PAGE INITIALIZATION ==========
     * Initialize password validation on page load
     */
    document.addEventListener('DOMContentLoaded', () => {
      updatePasswordRequirements();
      updateConfirmPasswordStatus();
    });
  </script>
</body>

</html>