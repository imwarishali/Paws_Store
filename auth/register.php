<<<<<<< HEAD
<?php
session_start();

if (isset($_SESSION["user"])) {
  header("Location: ../index.php");
  exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  require_once "database.php";

  $username = $_POST["username"];
  $email = $_POST["email"];
  $phone = $_POST["phone"];
  $password = $_POST["password"];
  $confirm_password = $_POST["confirm_password"];

  if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
    $error = "All fields are required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
  } elseif ($password !== $confirm_password) {
    $error = "Passwords do not match.";
  } elseif (strlen($password) < 8) {
    $error = "Password must be at least 8 characters long.";
  } else {
    $sql = "SELECT id FROM users WHERE email = ? OR username = ?";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $sql)) {
      mysqli_stmt_bind_param($stmt, "ss", $email, $username);
      mysqli_stmt_execute($stmt);
      mysqli_stmt_store_result($stmt);

      if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "An account with this email or username already exists.";
      } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql_insert = "INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)";
        $stmt_insert = mysqli_stmt_init($conn);

        if (mysqli_stmt_prepare($stmt_insert, $sql_insert)) {
          mysqli_stmt_bind_param($stmt_insert, "ssss", $username, $email, $phone, $password_hash);

          if (mysqli_stmt_execute($stmt_insert)) {
            $success = "Registration successful! Redirecting to login...";
            echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
          } else {
            $error = "Registration failed: " . mysqli_error($conn);
          }
        } else {
          $error = "Database error: " . mysqli_error($conn);
        }
      }
    } else {
      $error = "Database error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
  }
}
?>
=======
>>>>>>> 6915c4197beae6453652ee6418ca4caff90fc71f
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
<<<<<<< HEAD

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
=======
>>>>>>> 6915c4197beae6453652ee6418ca4caff90fc71f
  </style>
</head>

<body>
  <div class="register-wrap">
    <h1>Create account</h1>
<<<<<<< HEAD
    <p class="sub">Join us and find your new best friend!</p>

    <?php if ($error != ""): ?>
      <p class="message error"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success != ""): ?>
      <p class="message success"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST" action="register.php">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Your name" required>
      </div>
      <div class="form-group">
        <label>Email address</label>
        <input type="email" name="email" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label>Phone number</label>
        <input type="tel" name="phone" placeholder="+91 98765 43210">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Min 8 characters" required>
      </div>
      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" placeholder="Re-enter your password" required>
      </div>
      <button type="submit" class="btn">Register</button>
    </form>
    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
=======
    <form>
      <div class="form-group">
        <label>username</label>
        <input type="text" placeholder="Your name">
      </div>
      <div class="form-group">
        <label>Email address</label>
        <input type="email" placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>Phone number</label>
        <input type="tel" placeholder="+91 98765 43210">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" placeholder="Min 8 characters">
      </div>
      <button type="submit" class="btn">Register</button>
    </form>
    <p class="login-link">Already have an account? <a href="login.php">Login</a></p>
>>>>>>> 6915c4197beae6453652ee6418ca4caff90fc71f
  </div>
</body>

</html>