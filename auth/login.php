<?php
session_start();

if (isset($_SESSION["user"])) {
  header("Location: ../index.php");
  exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  require_once "database.php";
  $login_id = $_POST["username"];
  $password = $_POST["password"];

  if (empty($login_id) || empty($password)) {
    $error = "Please enter both username/email and password.";
  } else {
    $sql = "SELECT id, username, email, phone, password FROM users WHERE username = ? OR email = ?";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $sql)) {
      mysqli_stmt_bind_param($stmt, "ss", $login_id, $login_id);
      mysqli_stmt_execute($stmt);

      mysqli_stmt_store_result($stmt);

      if (mysqli_stmt_num_rows($stmt) > 0) {

        mysqli_stmt_bind_result($stmt, $id, $username_db, $email_db, $phone_db, $password_db);
        mysqli_stmt_fetch($stmt);

        $user = [
          "id" => $id,
          "username" => $username_db,
          "email" => $email_db,
          "phone" => $phone_db,
          "password" => $password_db
        ];

        if (password_verify($password, $user["password"])) {
          $_SESSION["user"] = $user;
          header("Location: ../index.php");
          exit();
        } else {
          $error = "Invalid username/email or password";
        }
      } else {
        $error = "Invalid username/email or password";
      }
    } else {
      $error = "Database error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
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

      <form method="POST">
        <div class="form-group">
          <label>Username or Email</label>
          <input type="text" name="username" placeholder="Enter username or email" required>
        </div>
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="btn">Sign In</button>
      </form>

      <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
  </div>
</body>

</html>