<?php
session_start();

// If admin is not logged in, redirect to the new admin login page
if (!isset($_SESSION["admin_user"])) {
    header("Location: admin_login.php");
    exit();
}

// 30 minutes inactivity timeout
$timeout_duration = 1800; // 30 minutes in seconds
if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity']) > $timeout_duration) {
    unset($_SESSION['admin_user']);
    unset($_SESSION['admin_last_activity']);
    header("Location: admin_login.php?timeout=1");
    exit();
}
$_SESSION['admin_last_activity'] = time(); // Update last activity time

require_once 'db.php';

$success_message = null;
$error_message = null;

try {
    // Determine which section to show (default is dashboard)
    $page = $_GET['page'] ?? 'dashboard';

    // ==========================================
    // 1. HANDLE POST SUBMISSIONS (CREATE/UPDATE/DELETE)
    // ==========================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // --- Orders Actions ---
        if (isset($_POST['update_status'])) {
            $order_id = $_POST['order_id'];
            $new_status = $_POST['new_status'];
            $update_stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
            $update_stmt->execute([$new_status, $order_id]);
            $success_message = "Order #{$order_id} status updated to '{$new_status}' successfully!";
        } elseif (isset($_POST['confirm_order'])) {
            $order_id = $_POST['order_id'];
            $update_stmt = $pdo->prepare("UPDATE orders SET order_status = 'Confirmed' WHERE id = ?");
            $update_stmt->execute([$order_id]);
            $success_message = "Order #{$order_id} status updated to 'Confirmed' successfully!";
        }
        // --- Pets Actions ---
        elseif (isset($_POST['edit_pet'])) {
            $stmt = $pdo->prepare("UPDATE pets SET name = ?, category = ?, price = ?, image = ?, description = ?, status = ? WHERE id = ?");
            $stmt->execute([$_POST['name'], $_POST['category'], $_POST['price'], $_POST['image'], $_POST['description'], $_POST['status'], $_POST['pet_id']]);
            $success_message = "Pet '{$_POST['name']}' updated successfully!";
        } elseif (isset($_POST['add_pet'])) {
            $stmt = $pdo->prepare("INSERT INTO pets (name, category, price, image, description, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['name'], $_POST['category'], $_POST['price'], $_POST['image'], $_POST['description'], $_POST['status']]);
            $success_message = "Pet '{$_POST['name']}' added successfully!";
        } elseif (isset($_POST['delete_pet'])) {
            $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
            $stmt->execute([$_POST['pet_id']]);
            $success_message = "Pet deleted successfully!";
        }
        // --- Users Actions ---
        elseif (isset($_POST['delete_user'])) {
            $delete_id = $_POST['user_id'];
            $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delete_stmt->execute([$delete_id]);
            $success_message = "User deleted successfully!";
        } elseif (isset($_POST['update_user_password'])) {
            $user_id = $_POST['user_id'];
            $new_password = $_POST['new_password'];

            if (strlen($new_password) < 8) {
                $error_message = "Password must be at least 8 characters long.";
            } elseif (!preg_match('/[0-9]/', $new_password)) {
                $error_message = "Password must contain at least one number.";
            } elseif (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
                $error_message = "Password must contain at least one special character.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->execute([$hashed_password, $user_id]);
                $success_message = "User password updated successfully!";
            }
        }
        // --- Admin Actions ---
        elseif (isset($_POST['add_admin'])) {
            $new_username = trim($_POST['username']);
            $new_password = $_POST['password'];

            if (strlen($new_password) < 8) {
                $error_message = "Password must be at least 8 characters long.";
            } elseif (!preg_match('/[0-9]/', $new_password)) {
                $error_message = "Password must contain at least one number.";
            } elseif (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
                $error_message = "Password must contain at least one special character.";
            } else {
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE username = ?");
                $check_stmt->execute([$new_username]);
                if ($check_stmt->fetchColumn() > 0) {
                    $error_message = "Username already exists!";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $insert_stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
                    $insert_stmt->execute([$new_username, $hashed_password]);
                    $success_message = "Admin user '{$new_username}' added successfully!";
                }
            }
        } elseif (isset($_POST['delete_admin'])) {
            $delete_stmt = $pdo->prepare("DELETE FROM admin WHERE id = ? AND id != ?");
            $delete_stmt->execute([$_POST['admin_id'], $_SESSION['admin_user']['id']]);
            $success_message = "Admin account deleted successfully!";
        } elseif (isset($_POST['update_admin_password'])) {
            $admin_id = $_POST['admin_id'];
            $new_password = $_POST['new_password'];

            if (strlen($new_password) < 8) {
                $error_message = "Password must be at least 8 characters long.";
            } elseif (!preg_match('/[0-9]/', $new_password)) {
                $error_message = "Password must contain at least one number.";
            } elseif (!preg_match('/[^a-zA-Z0-9]/', $new_password)) {
                $error_message = "Password must contain at least one special character.";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
                $update_stmt->execute([$hashed_password, $admin_id]);
                $success_message = "Admin password updated successfully!";
            }
        }
    }

    // ==========================================
    // 2. FETCH DATA BASED ON ACTIVE PAGE
    // ==========================================
    $dates = [];
    $revenues = [];
    $edit_pet = null;
    $search_query = $_GET['search_query'] ?? '';

    // Initialize all view variables to prevent IDE "Undefined Variable" warnings
    $total_revenue = 0;
    $total_orders = 0;
    $total_pets = 0;
    $pending_orders = 0;
    $total_users = 0;
    $recent_orders = [];
    $all_orders = [];
    $all_pets = [];
    $all_users = [];
    $all_admins = [];

    if ($page === 'dashboard') {
        $total_revenue = (float)$pdo->query("SELECT SUM(total_amount) FROM orders WHERE order_status != 'Cancelled'")->fetchColumn();
        $total_orders = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $total_pets = (int)$pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
        $pending_orders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Processing'")->fetchColumn();
        $total_users = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

        $chart_stmt = $pdo->query("SELECT DATE(created_at) as order_date, SUM(total_amount) as daily_revenue FROM orders WHERE order_status != 'Cancelled' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC");
        foreach ($chart_stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $dates[] = date('M d', strtotime($row['order_date']));
            $revenues[] = (float)$row['daily_revenue'];
        }

        $recent_orders = $pdo->query("SELECT o.order_number, o.total_amount, o.order_status, o.created_at, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($page === 'orders') {
        $sql = "SELECT o.*, CONCAT(p.name, ' (x', o.quantity, ')') AS pet_names FROM orders o LEFT JOIN pets p ON o.pet_id = p.id";
        $params = [];
        if (!empty($search_query)) {
            $sql .= " WHERE o.order_number LIKE ? OR o.user_id LIKE ?";
            $params[] = '%' . $search_query . '%';
            $params[] = '%' . $search_query . '%';
        }
        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Handle CSV Export
        if (isset($_GET['export_csv']) && $_GET['export_csv'] == '1') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=orders_export_' . date('Y-m-d') . '.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Order ID', 'User ID', 'Pets Ordered', 'Date Placed', 'Amount (INR)', 'Status']);
            foreach ($all_orders as $order) {
                fputcsv($output, [
                    $order['order_number'],
                    $order['user_id'],
                    $order['pet_names'] ? $order['pet_names'] : 'N/A',
                    date('d M Y H:i:s', strtotime($order['created_at'])),
                    $order['total_amount'],
                    $order['order_status']
                ]);
            }
            fclose($output);
            exit();
        }
    } elseif ($page === 'pets') {
        if (isset($_GET['edit_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
            $stmt->execute([$_GET['edit_id']]);
            $edit_pet = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        $all_pets = $pdo->query("SELECT * FROM pets ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($page === 'users') {
        $sql = "SELECT * FROM users";
        $params = [];
        if (!empty($search_query)) {
            $sql .= " WHERE username LIKE ? OR email LIKE ?";
            $params[] = '%' . $search_query . '%';
            $params[] = '%' . $search_query . '%';
        }
        $sql .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($page === 'admins') {
        $all_admins = $pdo->query("SELECT id, username FROM admin ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <?php if ($page === 'dashboard'): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
    <style>
        body {
            margin: 0;
            background: #faf7f2;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 250px;
            background: #fff;
            border-right: 1px solid #e8e0d4;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 20px;
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 700;
            color: #2c1a0e;
            border-bottom: 1px solid #e8e0d4;
            text-align: center;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .sidebar-nav a,
        .sidebar-bottom a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 25px;
            color: #555;
            text-decoration: none;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
        }

        .sidebar-nav a:hover,
        .sidebar-bottom a:hover {
            background: #fdfaf6;
            color: #b5860d;
        }

        .sidebar-nav a.active {
            background: #fef9f0;
            color: #b5860d;
            border-right: 4px solid #b5860d;
        }

        .sidebar-bottom {
            padding: 20px 0;
            border-top: 1px solid #e8e0d4;
        }

        .sidebar-bottom .logout-link {
            color: #dc3545;
        }

        .sidebar-bottom .logout-link:hover {
            background: #fdf5f5;
            color: #c82333;
        }

        .admin-main {
            flex: 1;
            margin-left: 250px;
            padding: 40px 30px;
            background: #faf7f2;
        }

        /* Unified Admin UI Styles */
        .admin-dashboard,
        .admin-container {
            max-width: 1200px;
            margin: 0 auto 40px;
        }

        .admin-container {
            padding: 30px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e8e0d4;
            margin-bottom: 40px;
        }

        .dashboard-header,
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-header h1,
        .admin-header h1,
        .admin-header h2 {
            font-family: 'Playfair Display', serif;
            color: #2c1a0e;
            margin: 0;
        }

        .msg-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .msg-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Dashboard Specific */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e8e0d4;
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 35px;
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fdfaf6;
            border-radius: 12px;
            color: #b5860d;
        }

        .stat-info h3 {
            margin: 0 0 5px 0;
            color: #666;
            font-size: 14px;
        }

        .stat-info .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #2c1a0e;
        }

        .dashboard-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .chart-container,
        .recent-orders {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e8e0d4;
        }

        .chart-container h2,
        .recent-orders h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            color: #2c1a0e;
        }

        .recent-order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .recent-order-item:last-child {
            border-bottom: none;
        }

        .recent-order-amount {
            font-weight: 700;
            color: #b5860d;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background-color: #f9f9f9;
            color: #2c1a0e;
            font-weight: 700;
        }

        .status-processing {
            padding: 5px 10px;
            border-radius: 12px;
            background: #f5f2eb;
        }

        /* Forms & Buttons */
        .search-form {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-form input,
        .status-select {
            flex: 1;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
        }

        .search-form button,
        .update-btn {
            padding: 10px 20px;
            background: #2c1a0e;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .search-form a {
            color: #dc3545;
            text-decoration: none;
            font-weight: bold;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c1a0e;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
        }

        .submit-btn {
            background: #b5860d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            transition: background 0.3s;
        }

        /* Badges & Actions */
        .role-badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .role-admin {
            background: #cce5ff;
            color: #004085;
        }

        .role-user {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }

        .edit-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
        }

        .invoice-btn {
            background-color: #17a2b8;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }

        .screenshot-btn {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
        }

        @media (max-width: 900px) {

            .dashboard-content,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: 1;
            }

            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            .admin-main {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-logo">🐾 Admin Panel</div>
            <nav class="sidebar-nav">
                <a href="admin.php?page=dashboard" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>"><span>📊</span> Dashboard</a>
                <a href="admin.php?page=orders" class="<?php echo $page === 'orders' ? 'active' : ''; ?>"><span>📦</span> Orders</a>
                <a href="admin.php?page=pets" class="<?php echo $page === 'pets' ? 'active' : ''; ?>"><span>🐶</span> Pets</a>
                <a href="admin.php?page=users" class="<?php echo $page === 'users' ? 'active' : ''; ?>"><span>👥</span> Users</a>
                <a href="admin.php?page=admins" class="<?php echo $page === 'admins' ? 'active' : ''; ?>"><span>🛡️</span> Admins</a>
            </nav>
            <div class="sidebar-bottom">
                <a href="index.php"><span>🏠</span> Store Home</a>
                <a href="admin_logout.php" class="logout-link"><span>🚪</span> Logout</a>
            </div>
        </aside>

        <main class="admin-main">
            <?php if (isset($success_message)): ?>
                <div class="admin-dashboard" style="padding-bottom: 0; margin-bottom: 0;">
                    <div class="msg-success"><?php echo htmlspecialchars($success_message); ?></div>
                </div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="admin-dashboard" style="padding-bottom: 0; margin-bottom: 0;">
                    <div class="msg-error"><?php echo htmlspecialchars($error_message); ?></div>
                </div>
            <?php endif; ?>

            <!-- ========================================== -->
            <!-- DASHBOARD SECTION -->
            <!-- ========================================== -->
            <?php if ($page === 'dashboard'): ?>
                <div class="admin-dashboard">
                    <div class="dashboard-header">
                        <h1>Overview Dashboard</h1>
                        <div style="font-weight: 600; color: #666;"><?php echo date('l, F j, Y'); ?></div>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">💰</div>
                            <div class="stat-info">
                                <h3>Total Revenue</h3>
                                <div class="stat-value">₹<?php echo number_format($total_revenue); ?></div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">📦</div>
                            <div class="stat-info">
                                <h3>Total Orders</h3>
                                <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">⏳</div>
                            <div class="stat-info">
                                <h3>Pending Orders</h3>
                                <div class="stat-value"><?php echo number_format($pending_orders); ?></div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">🐾</div>
                            <div class="stat-info">
                                <h3>Total Pets</h3>
                                <div class="stat-value"><?php echo number_format($total_pets); ?></div>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">👥</div>
                            <div class="stat-info">
                                <h3>Total Customers</h3>
                                <div class="stat-value"><?php echo number_format($total_users); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-content">
                        <div class="chart-container">
                            <h2>Revenue Last 7 Days</h2>
                            <canvas id="revenueChart" height="100"></canvas>
                        </div>
                        <div class="recent-orders">
                            <h2>Recent Orders</h2>
                            <?php if (empty($recent_orders)): ?>
                                <p style="color: #666;">No recent orders found.</p>
                            <?php else: ?>
                                <?php foreach ($recent_orders as $order): ?>
                                    <div class="recent-order-item">
                                        <div>
                                            <strong style="display:block; color:#2c1a0e;">#<?php echo htmlspecialchars($order['order_number']); ?></strong>
                                            <small style="color:#888;"><?php echo date('M d', strtotime($order['created_at'])); ?> - <?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></small>
                                        </div>
                                        <div style="text-align: right;">
                                            <div class="recent-order-amount">₹<?php echo number_format($order['total_amount']); ?></div>
                                            <small style="background:#f0f0f0; padding:2px 6px; border-radius:4px; font-size:11px;"><?php echo htmlspecialchars($order['order_status']); ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div style="text-align: center; margin-top: 15px;"><a href="admin.php?page=orders" style="color: #b5860d; text-decoration: none; font-weight: bold;">View All Orders &rarr;</a></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- ORDERS SECTION -->
                <!-- ========================================== -->
            <?php elseif ($page === 'orders'): ?>
                <div class="admin-container">
                    <div class="admin-header">
                        <h1>Manage Customer Orders</h1>
                    </div>
                    <form method="GET" class="search-form" action="admin.php">
                        <input type="hidden" name="page" value="orders">
                        <input type="text" name="search_query" placeholder="Search by Order ID or User ID..." value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" style="padding:10px 20px;">Search</button>
                        <?php if (!empty($search_query)): ?>
                            <a href="admin.php?page=orders">Clear Search</a>
                        <?php endif; ?>
                        <button type="submit" name="export_csv" value="1" style="background-color: #28a745; margin-left: auto;">Export CSV</button>
                    </form>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User ID</th>
                                <th>Pets Ordered</th>
                                <th>Date Placed</th>
                                <th>Amount</th>
                                <th>Current Status</th>
                                <th style="width: 320px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_orders)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No orders found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_orders as $order): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($order['user_id']); ?></td>
                                        <td style="font-size: 14px;"><?php echo $order['pet_names'] ? strip_tags($order['pet_names'], '<br>') : 'N/A'; ?></td>
                                        <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                        <td>₹<?php echo number_format($order['total_amount']); ?></td>
                                        <td><span class="status-processing"><?php echo htmlspecialchars($order['order_status']); ?></span></td>
                                        <td>
                                            <div style="display: flex; gap: 8px; align-items: center;">
                                                <form method="POST" style="display: flex; gap: 8px; align-items: center; margin: 0;">
                                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                    <select name="new_status" class="status-select" style="padding: 6px;">
                                                        <option value="Processing" <?php echo $order['order_status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option value="Confirmed" <?php echo $order['order_status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="Shipped" <?php echo $order['order_status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                                        <option value="Delivered" <?php echo $order['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                                        <option value="Cancelled" <?php echo $order['order_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="update-btn">Update</button>
                                                    <?php if ($order['order_status'] === 'Processing'): ?>
                                                        <button type="submit" name="confirm_order" style="background-color: #28a745; color: white; border: none; padding: 10px 16px; border-radius: 6px; cursor: pointer; font-weight: 600;">Confirm</button>
                                                    <?php endif; ?>
                                                </form>
                                                <a href="invoice.php?order_id=<?php echo urlencode($order['order_number']); ?>" class="invoice-btn">Invoice</a>
                                                <?php if (!empty($order['payment_screenshot'])): ?>
                                                    <a href="<?php echo htmlspecialchars($order['payment_screenshot']); ?>" target="_blank" class="screenshot-btn">Screenshot</a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ========================================== -->
                <!-- PETS SECTION -->
                <!-- ========================================== -->
            <?php elseif ($page === 'pets'): ?>
                <div class="admin-container">
                    <div class="admin-header">
                        <h2 style="display: inline-block;"><?php echo $edit_pet ? 'Edit Pet #' . $edit_pet['id'] : 'Add New Pet'; ?></h2>
                        <?php if ($edit_pet): ?>
                            <a href="admin.php?page=pets" style="color: #dc3545; text-decoration: none; font-weight: bold; margin-left: 15px; font-size: 14px;">(Cancel Edit)</a>
                        <?php endif; ?>
                    </div>
                    <form method="POST">
                        <?php if ($edit_pet): ?>
                            <input type="hidden" name="pet_id" value="<?php echo $edit_pet['id']; ?>">
                        <?php endif; ?>
                        <div class="form-grid">
                            <div class="form-group"><label>Pet Name & Breed</label><input type="text" name="name" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['name']) : ''; ?>" required></div>
                            <div class="form-group"><label>Category</label>
                                <select name="category" required>
                                    <option value="Dogs" <?php echo ($edit_pet && $edit_pet['category'] === 'Dogs') ? 'selected' : ''; ?>>Dogs</option>
                                    <option value="Cats" <?php echo ($edit_pet && $edit_pet['category'] === 'Cats') ? 'selected' : ''; ?>>Cats</option>
                                    <option value="Fish" <?php echo ($edit_pet && $edit_pet['category'] === 'Fish') ? 'selected' : ''; ?>>Fish</option>
                                    <option value="Birds" <?php echo ($edit_pet && $edit_pet['category'] === 'Birds') ? 'selected' : ''; ?>>Birds</option>
                                </select>
                            </div>
                            <div class="form-group"><label>Price (₹)</label><input type="number" name="price" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['price']) : ''; ?>" required min="0"></div>
                            <div class="form-group"><label>Status</label><input type="text" name="status" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['status']) : 'Available for Adoption'; ?>" required></div>
                            <div class="form-group full-width"><label>Image Path</label><input type="text" name="image" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['image']) : ''; ?>" required></div>
                            <div class="form-group full-width"><label>Description</label><textarea name="description" rows="3" required><?php echo $edit_pet ? htmlspecialchars($edit_pet['description']) : ''; ?></textarea></div>
                        </div>
                        <?php if ($edit_pet): ?>
                            <button type="submit" name="edit_pet" class="submit-btn" style="background: #28a745;">Update Pet Details</button>
                        <?php else: ?>
                            <button type="submit" name="add_pet" class="submit-btn">+ Add Pet to Store</button>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="admin-container">
                    <div class="admin-header">
                        <h2>Current Pets Database</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_pets as $pet): ?>
                                <tr>
                                    <td>#<?php echo $pet['id']; ?></td>
                                    <td><img src="<?php echo htmlspecialchars($pet['image']); ?>" style="width:50px; height:50px; object-fit:cover; border-radius:6px;" alt="pet"></td>
                                    <td><strong><?php echo htmlspecialchars($pet['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($pet['category']); ?></td>
                                    <td>₹<?php echo number_format($pet['price']); ?></td>
                                    <td><span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;"><?php echo htmlspecialchars($pet['status']); ?></span></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this pet?');" style="display: flex; align-items: center; margin: 0;">
                                            <a href="admin.php?page=pets&edit_id=<?php echo $pet['id']; ?>" class="edit-btn">Edit</a>
                                            <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                                            <button type="submit" name="delete_pet" class="delete-btn">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ========================================== -->
                <!-- USERS SECTION -->
                <!-- ========================================== -->
            <?php elseif ($page === 'users'): ?>
                <div class="admin-container">
                    <div class="admin-header">
                        <h1>Manage Registered Users</h1>
                    </div>
                    <form method="GET" class="search-form" action="admin.php">
                        <input type="hidden" name="page" value="users">
                        <input type="text" name="search_query" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search_query); ?>">
                        <button type="submit" style="padding:10px 20px;">Search</button>
                        <?php if (!empty($search_query)): ?>
                            <a href="admin.php?page=users">Clear Search</a>
                        <?php endif; ?>
                    </form>
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_users)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No users found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_users as $u): ?>
                                    <tr>
                                        <td><strong>#<?php echo htmlspecialchars($u['id']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($u['username'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($u['email'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($u['phone'] ?? 'N/A'); ?></td>
                                        <td>
                                            <div style="display: flex; gap: 10px; align-items: center;">
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="margin: 0;">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <button type="submit" name="delete_user" class="delete-btn">Delete</button>
                                                </form>
                                                <form method="POST" style="margin: 0; display: flex; gap: 5px; align-items: center;">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                    <input type="password" name="new_password" placeholder="New Password" required style="padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; width: 150px; font-family: 'Nunito', sans-serif;">
                                                    <button type="submit" name="update_user_password" class="edit-btn" style="background-color: #17a2b8;">Update Password</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- ========================================== -->
                <!-- ADMINS SECTION -->
                <!-- ========================================== -->
            <?php elseif ($page === 'admins'): ?>
                <div class="admin-container">
                    <div class="admin-header">
                        <h2>Add New Admin</h2>
                    </div>
                    <form method="POST">
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="username" placeholder="Enter new username" required>
                            </div>
                            <div class="form-group">
                                <label for="admin_password">Password</label>
                                <div class="ps-password-wrapper">
                                    <input type="password" id="admin_password" name="password" placeholder="Min 8 chars, 1 number, 1 symbol" required>
                                    <button type="button" class="ps-password-toggle" onclick="togglePasswordVisibility('admin_password', this)">👁️</button>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="add_admin" class="submit-btn">+ Create Admin Account</button>
                    </form>
                </div>

                <div class="admin-container">
                    <div class="admin-header">
                        <h2>Manage Admin Accounts</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Admin ID</th>
                                <th>Username</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_admins as $admin): ?>
                                <tr>
                                    <td><strong>#<?php echo htmlspecialchars($admin['id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td>
                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <?php if ($admin['id'] != $_SESSION['admin_user']['id']): ?>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this admin account?');" style="margin: 0;">
                                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                    <button type="submit" name="delete_admin" class="delete-btn">Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: #666; font-style: italic; min-width: 82px;">Current User</span>
                                            <?php endif; ?>
                                            <form method="POST" style="margin: 0; display: flex; gap: 5px; align-items: center;">
                                                <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                <input type="password" name="new_password" placeholder="New Password" required style="padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; width: 150px; font-family: 'Nunito', sans-serif;">
                                                <button type="submit" name="update_admin_password" class="edit-btn" style="background-color: #17a2b8;">Update Password</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <!-- Load Chart.js logic only if on dashboard -->
    <?php if ($page === 'dashboard'): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const ctx = document.getElementById('revenueChart').getContext('2d');
                const dates = <?php echo json_encode($dates); ?>;
                const revenues = <?php echo json_encode($revenues); ?>;

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: dates.length > 0 ? dates : ['No Data'],
                        datasets: [{
                            label: 'Daily Revenue (₹)',
                            data: revenues.length > 0 ? revenues : [0],
                            borderColor: '#b5860d',
                            backgroundColor: 'rgba(181, 134, 13, 0.1)',
                            borderWidth: 3,
                            pointBackgroundColor: '#b5860d',
                            pointRadius: 4,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₹' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            });
        </script>
    <?php endif; ?>

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