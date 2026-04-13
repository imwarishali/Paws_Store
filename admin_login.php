<?php
session_start();

// If admin is already logged in, redirect to admin dashboard
if (isset($_SESSION["admin_user"])) {
    header("Location: admin.php");
    exit();
}

require_once 'db.php';

$error_message = null;

if (isset($_GET['timeout']) && $_GET['timeout'] == 1) {
    $error_message = "Your session has expired due to inactivity. Please log in again.";
}

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
                $_SESSION['admin_last_activity'] = time();
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
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

        @keyframes floatAnimation {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }

            100% {
                background-position: 1000px 0;
            }
        }

        body {
            background: linear-gradient(135deg, #fdfaf6 0%, #f5ede0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            font-family: 'Nunito', sans-serif;
            position: relative;
            overflow-x: hidden;
            min-height: auto;
            height: auto;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 50%, rgba(181, 134, 13, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(92, 64, 51, 0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .ps-profile-container {
            max-width: 480px;
            width: 100%;
            background: #fff;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(92, 64, 51, 0.12);
            border: 1px solid #e8e0d4;
            position: relative;
            z-index: 1;
            animation: slideInUp 0.8s ease-out;
        }

        .ps-profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .ps-profile-header h1 {
            font-family: "Playfair Display", serif;
            font-size: 2rem;
            color: #2c1a0e;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .ps-profile-header p {
            color: #c9a227;
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .ps-profile-avatar {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #5c4033 0%, #8b6c59 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 1.5rem;
            color: #c9a227;
            border: 2.5px solid #c9a227;
            box-shadow: 0 4px 15px rgba(92, 64, 51, 0.2);
            animation: floatAnimation 3s ease-in-out infinite;
        }

        .ps-form-group {
            margin-bottom: 1.5rem;
            animation: slideInUp 0.8s ease-out backwards;
        }

        .ps-form-group:nth-child(1) {
            animation-delay: 0.1s;
        }

        .ps-form-group:nth-child(2) {
            animation-delay: 0.2s;
        }

        .ps-form-group label {
            display: block;
            font-weight: 600;
            color: #2c1a0e;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .ps-form-group input {
            width: 100%;
            padding: 0.9rem 1rem;
            border: 2px solid #e8dcc4;
            border-radius: 10px;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #faf6f0;
        }

        .ps-form-group input:focus {
            outline: none;
            border-color: #c9a227;
            box-shadow: 0 0 0 3px rgba(201, 162, 39, 0.1);
            background-color: #ffffff;
        }

        .ps-form-group input::placeholder {
            color: #999;
        }

        .ps-btn-save {
            width: 100%;
            background: linear-gradient(135deg, #5c4033 0%, #3d2a21 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Nunito', sans-serif;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 0.5rem;
            animation: slideInUp 0.8s ease-out 0.3s backwards;
        }

        .ps-btn-save::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .ps-btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(92, 64, 51, 0.2);
        }

        .ps-btn-save:hover::before {
            left: 100%;
        }

        .ps-btn-save:active {
            transform: translateY(0);
        }

        .msg-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c8cc 100%);
            color: #721c24;
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 600;
            text-align: center;
            border: 1px solid #f5c6cb;
            animation: slideInUp 0.5s ease-out;
        }

        .ps-password-wrapper {
            position: relative;
            display: block;
        }

        .ps-password-wrapper input {
            padding-right: 45px;
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
            transition: all 0.3s ease;
        }

        .ps-password-toggle:hover {
            transform: translateY(-50%) scale(1.2);
        }

        @media (max-width: 600px) {
            .ps-profile-container {
                padding: 2rem;
                border-radius: 16px;
            }

            .ps-profile-header h1 {
                font-size: 1.5rem;
            }

            .ps-profile-avatar {
                width: 70px;
                height: 70px;
                font-size: 32px;
            }
        }
    </style>
</head>

<body>
    <div class="ps-profile-container">
        <div class="ps-profile-header">
            <div class="ps-profile-avatar">🐾</div>
            <h1>Admin Login</h1>
            <p>Paws Store Management</p>
        </div>

        <?php if ($error_message): ?>
            <div class="msg-error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <div class="ps-form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter admin username" required>
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
        // TOAST NOTIFICATION FUNCTION
        function showToast(message, icon = '✅') {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = 'toast-msg';
            toast.innerHTML = `<span class="toast-icon">${icon}</span> <span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        }

        <?php if ($error_message): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showToast("<?php echo addslashes($error_message); ?>", "⚠️");
            });
        <?php endif; ?>

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