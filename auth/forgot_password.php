<?php
session_start();

if (isset($_SESSION["user"])) {
    header("Location: ../index.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = 'localhost';
    $dbname = 'pet_store';
    $db_user = 'root';
    $db_pass = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
            // Verify user exists with given email and username
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND username = ?");
            $stmt->execute([$email, $username]);

            if ($stmt->rowCount() > 0) {
                $user_id = $stmt->fetchColumn();
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($update_stmt->execute([$password_hash, $user_id])) {
                    $success = "Password successfully reset! Redirecting to login...";
                    echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
                }
            } else {
                $error = "No account found with this Email and Username combination.";
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
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Your username" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="new_password" name="new_password" placeholder="Min 8 chars, 1 number, 1 symbol" required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('new_password', this)">👁️</button>
                </div>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your new password" required>
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