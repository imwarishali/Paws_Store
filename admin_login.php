<?php
session_start();

// If admin is already logged in, redirect to admin dashboard
if (isset($_SESSION["admin_user"])) {
    header("Location: admin.php");
    exit();
}

require_once 'db.php';

$error_message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = "Please enter both username and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password. Assumes passwords in the `admin` table are hashed.
            if ($admin && password_verify($password, $admin['password'])) {
                // Login successful: store admin info in session
                $_SESSION['admin_user'] = [
                    'id' => $admin['id'],
                    'username' => $admin['username']
                ];
                header("Location: admin.php");
                exit();
            } else {
                // Login failed
                $error_message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login — Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #fdfaf6;
        }

        .ps-profile-container {
            max-width: 450px;
            margin: 80px auto;
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e8e0d4;
        }

        .ps-profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .ps-profile-header h1 {
            font-family: "Playfair Display", serif;
            font-size: 32px;
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .ps-profile-avatar {
            width: 80px;
            height: 80px;
            background: #f5ecd8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 20px;
            color: #b5860d;
            border: 2px solid #b5860d;
        }

        .ps-form-group {
            margin-bottom: 20px;
        }

        .ps-form-group label {
            display: block;
            font-weight: 600;
            color: #2c1a0e;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .ps-form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
        }

        .ps-btn-save {
            width: 100%;
            background: #b5860d;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }

        .msg-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        .ps-password-wrapper {
            position: relative;
            display: block;
        }

        .ps-password-wrapper input {
            padding-right: 40px;
        }

        .ps-password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
        }
    </style>
</head>

<body>
    <div class="ps-profile-container">
        <div class="ps-profile-header">
            <div class="ps-profile-avatar">🐾</div>
            <h1>Admin Login</h1>
            <p style="color: #666;">Paws Store Management</p>
        </div>

        <?php if ($error_message): ?>
            <div class="msg-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <div class="ps-form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="ps-form-group">
                <label for="password">Password</label>
                <div class="ps-password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="ps-password-toggle" onclick="togglePasswordVisibility('password', this)">👁️</button>
                </div>
            </div>
            <button type="submit" class="ps-btn-save">Login</button>
        </form>
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