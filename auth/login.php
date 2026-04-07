<?php
session_start();

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

    .login-wrap {
      display: flex;
      max-width: 900px;
      width: 100%;
      background: var(--white);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    .login-left {
      flex: 1;
      background: linear-gradient(135deg, var(--brown) 0%, #8b6914 100%);
      padding: 3rem;
      color: var(--cream);
    }

    .login-left h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.75rem;
      margin-bottom: 0.5rem;
    }

    .login-left p {
      opacity: 0.9;
      font-size: 0.95rem;
      margin-bottom: 2rem;
    }

    .login-left .art {
      font-size: 4rem;
      margin-top: 2rem;
    }

    .login-right {
      flex: 1;
      padding: 3rem;
    }

    .login-right h2 {
      font-size: 1.5rem;
      color: var(--brown);
      margin-bottom: 0.5rem;
    }

    .login-right .sub {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-group label {
      display: block;
      font-weight: 500;
      color: var(--text);
      margin-bottom: 0.4rem;
      font-size: 0.9rem;
    }

    .form-group input {
      width: 100%;
      padding: 0.85rem 1rem;
      border: 1px solid rgba(92, 64, 51, 0.2);
      border-radius: 10px;
      font-size: 1rem;
      font-family: inherit;
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
    }

    .btn:hover {
      background: #8b6914;
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

    .info {
      color: #004085;
      background-color: #cce5ff;
      border: 1px solid #b8daff;
    }

    .register-link {
      margin-top: 1rem;
      font-size: 1rem;
      color: var(--text-muted);
      text-align: center;
    }

    @media(max-width:600px) {
      .login-left {
        display: none;
      }
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
  <div class="login-wrap">
    <div class="login-left">
      <h1>Paws Store</h1>
      <p>Your one-stop pet shop for adoption, food, and care.</p>
      <div class="art">🐕 🐈 🐹</div>
    </div>

    <div class="login-right">
      <h2>Welcome back</h2>
      <p class="sub">Sign in to your account to continue</p>

      <?php if (!empty($error)): ?>
        <p class="message error"><?php echo htmlspecialchars($error); ?></p>
      <?php endif; ?>
      <?php if (!empty($info_message)): ?>
        <p class="message info"><?php echo htmlspecialchars($info_message); ?></p>
      <?php endif; ?>

      <form method="POST">
        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
        <input type="hidden" name="registered" value="<?php echo htmlspecialchars($registered); ?>">
        <div class="form-group">
          <label for="username">Username or Email</label>
          <input type="text" id="username" name="username" placeholder="Enter username or email" required>
        </div>
        <div class="form-group">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
            <label for="password" style="margin-bottom: 0;">Password</label>
            <a href="forgot_password.php" style="font-size: 0.85rem; color: var(--accent); text-decoration: none; font-weight: 500;">Forgot Password?</a>
          </div>
          <div class="password-wrapper">
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password', this)">👁️</button>
          </div>
        </div>
        <button type="submit" class="btn">Sign In</button>
      </form>

      <p class="register-link">Don't have an account? <a href="register.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>">Register here</a></p>
    </div>
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