<?php

require_once '../config.php';

if (isset($_SESSION["user"])) {
  if (isset($_SESSION["user"]["is_admin"]) && $_SESSION["user"]["is_admin"] == 1) {
    header("Location: ../admin_orders.php");
  } else {
    header("Location: ../index.php");
  }
  exit();
}

$error = "";
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '';
$registered = $_GET['registered'] ?? $_POST['registered'] ?? '';
$info_message = "";

if ($_SERVER["REQUEST_METHOD"] == "GET" && $redirect === 'cart.php') {
  $info_message = "Please log in or sign up to view your cart and complete checkout.";
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && $redirect === 'wishlist.php') {
  $info_message = "Please log in or sign up to view your wishlist.";
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && $redirect === 'order_history.php') {
  $info_message = "Please log in or sign up to view your order history.";
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && strpos($redirect, 'pet_details.php') !== false) {
  $info_message = "Please log in or sign up to continue viewing this pet.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  try {
    require_once '../db.php';

    $login_id = trim($_POST["username"]);
    $pass = $_POST["password"];

    if (empty($login_id) || empty($pass)) {
      $error = "Please enter both username/email and password.";
    } else {
      $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
      $stmt->execute([$login_id, $login_id]);
      $user = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($user && password_verify($pass, $user["password"])) {
        $_SESSION["user"] = $user;

        $target_url = '../index.php';
        if ($user["is_admin"] == 1) {
          $target_url = '../admin_orders.php';
        } else {
          if (!empty($redirect)) {
            $target_url = '../' . ltrim($redirect, '/');
          }
        }

        if ($registered == '1') {
          echo "<script>window.location.replace('" . $target_url . "');</script>";
        } else {
          echo "<script>
            localStorage.removeItem('pawsCart_guest');
            localStorage.removeItem('pawsWishlist_guest');
            window.location.replace('" . $target_url . "');
          </script>";
        }
        exit();
      } else {
        $error = "Invalid username/email or password";
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
  <title>Login - Paws Store</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    @keyframes float {

      0%,
      100% {
        transform: translateY(0px);
      }

      50% {
        transform: translateY(-15px);
      }
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

    @keyframes eyesBlink {
      0% {
        height: 36px;
      }

      50% {
        height: 4px;
      }

      100% {
        height: 4px;
      }
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

    .login-card {
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

    /* Decorative dots on left */
    .login-card::before {

      position: absolute;
      left: -30px;
      top: 80px;
      font-size: 20px;
      color: #c9a227;
      letter-spacing: 20px;
    }

    /* Decorative dots on right */
    .login-card::after {

      position: absolute;
      right: -50px;
      bottom: 150px;
      font-size: 15px;
      color: rgba(255, 255, 255, 0.5);
      letter-spacing: 15px;
    }

    /* Mascot Section */
    .mascot-section {
      text-align: center;
      margin-bottom: 2rem;
    }

    .pet-mascot {
      width: 160px;
      height: 160px;
      background: linear-gradient(135deg, #5c4033 0%, #8b6c59 100%);
      border-radius: 50%;
      margin: 0 auto 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      box-shadow: 0 15px 40px rgba(92, 64, 51, 0.3);
      animation: float 3s ease-in-out infinite;
      border: 3px solid #c9a227;
    }

    .pet-mascot::before {
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.3), transparent 70%);
      border-radius: 50%;
    }

    .eyes-container {
      position: absolute;
      top: 40px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 35px;
      width: 100%;
      justify-content: center;
      z-index: 2;
    }

    .eye {
      width: 28px;
      height: 36px;
      background: white;
      border-radius: 50%;
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      border: 2px solid #333;
      transition: all 0.4s ease;
    }

    .pupil {
      width: 16px;
      height: 16px;
      background: #333;
      border-radius: 50%;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      transition: all 0.3s ease;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    .pupil::after {
      content: '';
      position: absolute;
      top: 2px;
      left: 2px;
      width: 6px;
      height: 6px;
      background: rgba(255, 255, 255, 0.5);
      border-radius: 50%;
    }

    .eyes-container.open .pupil {
      top: 50%;
      opacity: 1;
    }

    .hand-cover {
      position: absolute;
      top: 25px;
      left: 50%;
      transform: translateX(-50%) scale(0);
      font-size: 60px;
      z-index: 5;
      transition: all 0.5s ease;
      opacity: 0;
    }

    .hand-cover.show {
      transform: translateX(-50%) scale(1);
      opacity: 1;
    }

    .eyes-container.closed .eye {
      opacity: 0;
    }

    .mascot-nose {
      position: absolute;
      top: 55%;
      left: 50%;
      transform: translateX(-50%);
      font-size: 32px;
      z-index: 3;
    }

    .mascot-mouth {
      position: absolute;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      font-size: 32px;
      z-index: 3;
    }

    /* Form Elements */
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

    .password-wrapper {
      position: relative;
    }

    .password-wrapper input {
      padding-right: 50px;
    }

    .password-toggle {
      position: absolute;
      right: 8px;
      top: 45%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      font-size: 18px;
      transition: transform 0.2s ease, color 0.2s ease;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      line-height: 1;
      color: #999;
    }

    .password-toggle:hover {
      color: #c9a227;
    }

    .password-toggle:active {
      transform: translateY(-50%);
    }

    .btn {
      width: 100%;
      padding: 1rem;
      background: linear-gradient(135deg, #5c4033 0%, #3d2a21 100%);
      color: white;
      border: none;
      border-radius: 15px;
      font-size: 1.1rem;
      font-weight: 700;
      cursor: pointer;
      font-family: inherit;
      transition: all 0.3s ease;
      margin-top: 1.5rem;
      box-shadow: 0 8px 20px rgba(92, 64, 51, 0.2);
      animation: slideUp 0.8s ease-out 0.3s backwards;
    }

    .btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 30px rgba(92, 64, 51, 0.3);
    }

    .btn:active {
      transform: translateY(-1px);
    }

    .message {
      padding: 0.8rem 1rem;
      border-radius: 10px;
      margin-bottom: 1.5rem;
      font-size: 0.9rem;
      text-align: center;
      animation: slideUp 0.5s ease-out;
      font-weight: 600;
    }

    .error {
      background-color: #FFE0E0;
      color: #C41E3A;
      border: 2px solid #FF6B6B;
    }

    .info {
      background-color: #E3F2FD;
      color: #0052CC;
      border: 2px solid #2196F3;
    }

    .forgot-password {
      text-align: center;
      margin-top: -1rem;
      margin-bottom: 1rem;
    }

    .forgot-password a {
      color: #2C1A0E;
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 700;
      transition: all 0.3s ease;
    }

    .forgot-password a:hover {
      text-decoration: underline;
    }

    .register-link {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.9rem;
      color: #2C1A0E;
      font-weight: 600;
    }

    .register-link a {
      color: #c9a227;
      text-decoration: none;
      font-weight: 700;
      transition: all 0.3s ease;
    }

    .register-link a:hover {
      text-decoration: underline;
    }

    @media(max-width: 600px) {
      .login-card {
        padding: 2rem 1.5rem;
        border-radius: 30px;
      }

      .pet-mascot {
        width: 130px;
        height: 130px;
      }

      .login-card::before,
      .login-card::after {
        display: none;
      }
    }
  </style>
</head>

<body>
  <div class="login-card">
    <!-- Mascot with Eyes -->
    <div class="mascot-section">
      <div class="pet-mascot">
        <div class="hand-cover">✋</div>
        <div class="eyes-container open">
          <div class="eye">
            <div class="pupil"></div>
          </div>
          <div class="eye">
            <div class="pupil"></div>
          </div>
        </div>
        <div class="mascot-nose">👃</div>
        <div class="mascot-mouth">😊</div>
      </div>
    </div>

    <!-- Error/Info Messages -->
    <?php if (!empty($error)): ?>
      <p class="message error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($info_message)): ?>
      <p class="message info"><?php echo htmlspecialchars($info_message); ?></p>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST">
      <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
      <input type="hidden" name="registered" value="<?php echo htmlspecialchars($registered); ?>">

      <div class="form-group">
        <label for="username">Email</label>
        <input type="text" id="username" name="username" placeholder="Enter your email" required>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-wrapper">
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
          <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password', this)">👁️</button>
        </div>
      </div>

      <div class="forgot-password">
        <a href="forgot_password.php">Forgot Password?</a>
      </div>

      <button type="submit" class="btn">Log in</button>
    </form>

    <p class="register-link">Not registered? <a href="register.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>">Create account</a></p>
  </div>

  <script>
    const eyesContainer = document.querySelector('.eyes-container');
    const handCover = document.querySelector('.hand-cover');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

    // Eye animation based on input focus
    usernameInput.addEventListener('focus', () => {
      eyesContainer.classList.remove('closed');
      eyesContainer.classList.add('open');
      handCover.classList.remove('show');
    });

    usernameInput.addEventListener('blur', () => {
      if (passwordInput !== document.activeElement) {
        eyesContainer.classList.remove('closed');
        eyesContainer.classList.add('open');
        handCover.classList.remove('show');
      }
    });

    passwordInput.addEventListener('focus', () => {
      eyesContainer.classList.remove('open');
      eyesContainer.classList.add('closed');
      handCover.classList.remove('show');
    });

    passwordInput.addEventListener('blur', () => {
      eyesContainer.classList.remove('closed');
      eyesContainer.classList.add('open');
      handCover.classList.remove('show');
    });

    // Password visibility toggle
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