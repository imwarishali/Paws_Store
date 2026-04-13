<?php
require_once 'config.php';

// Admin authentication check
if (!isset($_SESSION["admin_user"])) {
    header("Location: admin_login.php");
    exit();
}

// Session timeout - 30 minutes
$timeout_duration = 1800;
if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity']) > $timeout_duration) {
    unset($_SESSION['admin_user']);
    unset($_SESSION['admin_last_activity']);
    header("Location: admin_login.php?timeout=1");
    exit();
}
$_SESSION['admin_last_activity'] = time();

require_once 'db.php';

$success_message = null;
$error_message = null;
$page = $_GET['page'] ?? 'dashboard';

// ==========================================
// HANDLE POST REQUESTS
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update Order Status
        if (isset($_POST['update_status'])) {
            $order_id = $_POST['order_id'] ?? null;
            $new_status = $_POST['new_status'] ?? null;

            if (!$order_id || !$new_status) {
                throw new Exception("Missing order_id or new_status");
            }

            $update_stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
            $result = $update_stmt->execute([$new_status, $order_id]);

            if (!$result) {
                throw new Exception("Database update failed");
            }

            error_log("[Out for Delivery] Order ID: $order_id updated to: $new_status");
            $success_message = "✓ Order status updated to <strong>{$new_status}</strong>";

            // Handle refunds for cancelled orders
            if ($new_status === 'Cancelled') {
                $refund_stmt = $pdo->prepare("UPDATE payments SET payment_status = 'Refunded' WHERE order_id = ?");
                $refund_stmt->execute([$order_id]);

                $txn_stmt = $pdo->prepare("SELECT transaction_id FROM payments WHERE order_id = ? LIMIT 1");
                $txn_stmt->execute([$order_id]);
                $transaction_id = $txn_stmt->fetchColumn();

                if ($transaction_id && strpos($transaction_id, 'pay_') === 0) {
                    $env = parse_ini_file('.env');
                    $keyId = $env['RAZORPAY_KEY_ID'] ?? '';
                    $keySecret = $env['RAZORPAY_KEY_SECRET'] ?? '';

                    if ($keyId && $keySecret) {
                        $ch = curl_init("https://api.razorpay.com/v1/payments/{$transaction_id}/refund");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_exec($ch);
                        curl_close($ch);
                    }
                }
                $success_message .= " & Refund initiated";
            }

            // Send email and WhatsApp notifications
            if (in_array($new_status, ['Shipped', 'Out for Delivery', 'Delivered', 'Cancelled'])) {
                $info_stmt = $pdo->prepare("SELECT o.order_number, u.email, u.username, u.phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
                $info_stmt->execute([$order_id]);
                $order_info = $info_stmt->fetch(PDO::FETCH_ASSOC);

                if ($order_info && !empty($order_info['email'])) {
                    $to = $order_info['email'];
                    $order_number = $order_info['order_number'];
                    $username = $order_info['username'] ?? 'Customer';
                    $phone = $order_info['phone'] ?? '';

                    $status_messages = [
                        'Shipped' => "Your order has been <strong>shipped</strong> and is on its way!",
                        'Out for Delivery' => "Your order is <strong>out for delivery today</strong>! 🚗 Please be available.",
                        'Delivered' => "Your order has been <strong>delivered</strong> successfully! 🎉",
                        'Cancelled' => "Your order has been <strong>cancelled</strong>. A full refund is being processed."
                    ];

                    $status_message = $status_messages[$new_status] ?? '';

                    $subject = "Order Update: {$new_status} - Paws Store [{$order_number}]";
                    $message = "
                    <!DOCTYPE html>
                    <html>
                    <head><meta charset='UTF-8'><title>Order Update</title></head>
                    <body style='font-family: Arial, sans-serif; background-color: #f5f5f5; margin: 0; padding: 0;'>
                        <table width='100%' style='background-color: #f5f5f5; padding: 20px;'>
                            <tr><td align='center'>
                                <table width='600' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);'>
                                    <tr style='background: linear-gradient(135deg, #2c1a0e 0%, #8B4513 100%); padding: 30px; text-align: center;'>
                                        <td><h1 style='color: #ffffff; margin: 0; font-size: 24px;'>🐾 Paws Store</h1></td>
                                    </tr>
                                    <tr><td style='padding: 40px;'>
                                        <h2 style='color: #2c1a0e; margin-top: 0;'>Order Update</h2>
                                        <p style='font-size: 16px; line-height: 1.6; color: #555;'>{$status_message}</p>
                                        <p style='font-size: 14px; color: #888; margin: 20px 0;'>Order #: <strong>{$order_number}</strong></p>
                                    </td></tr>
                                    <tr style='background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eee;'>
                                        <td><p style='margin: 0; color: #666;'>Best Regards, <strong>Paws Store Team</strong></p></td>
                                    </tr>
                                </table>
                            </td></tr>
                        </table>
                    </body>
                    </html>";

                    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: noreply@pawsstore.com\r\n";
                    @mail($to, $subject, $message, $headers);

                    // Send WhatsApp notification for Out for Delivery, Delivered, and Cancelled
                    if (in_array($new_status, ['Out for Delivery', 'Delivered', 'Cancelled'])) {
                        $env = parse_ini_file('.env');
                        $instance_id = $env['ULTRAMSG_INSTANCE_ID'] ?? '';
                        $token = $env['ULTRAMSG_TOKEN'] ?? '';
                        $clean_phone = preg_replace('/[^0-9]/', '', $phone);

                        if (!empty($instance_id) && !empty($token) && strlen($clean_phone) >= 10) {
                            if (strlen($clean_phone) == 10) {
                                $clean_phone = "91" . $clean_phone; // Add India country code
                            }

                            $wa_messages = [
                                'Out for Delivery' => "🚗 Order Out for Delivery!\n\nHello *" . htmlspecialchars($username) . "*,\n\nYour order *{$order_number}* is out for delivery today! Please be available to receive your pet(s).\n\nThank you!",
                                'Delivered' => "✅ Order Delivered!\n\nHello *" . htmlspecialchars($username) . "*,\n\nYour order *{$order_number}* has been successfully delivered! We hope you and your new pet(s) are doing great.\n\nThank you for shopping with Paws Store!",
                                'Cancelled' => "❌ Order Cancelled\n\nHello *" . htmlspecialchars($username) . "*,\n\nYour order *{$order_number}* has been cancelled. A full refund is being processed to your account.\n\nThank you!"
                            ];

                            $wa_body = $wa_messages[$new_status] ?? '';

                            if (!empty($wa_body)) {
                                $curl = curl_init();
                                curl_setopt_array($curl, [
                                    CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
                                    CURLOPT_RETURNTRANSFER => true,
                                    CURLOPT_POST => true,
                                    CURLOPT_POSTFIELDS => http_build_query([
                                        "token" => $token,
                                        "to" => $clean_phone,
                                        "body" => $wa_body
                                    ])
                                ]);
                                curl_exec($curl);
                                curl_close($curl);
                            }
                        }
                    }
                }
            }
        }

        // Add Pet
        elseif (isset($_POST['add_pet'])) {
            $name = $_POST['name'];
            $category = $_POST['category'];
            $price = $_POST['price'];
            $image = $_POST['image'];
            $description = $_POST['description'];
            $status = $_POST['status'] ?? 'Available';

            $stmt = $pdo->prepare("INSERT INTO pets (name, category, price, image, description, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $price, $image, $description, $status]);
            $success_message = "✓ Pet <strong>'{$name}'</strong> added successfully";
        }

        // Edit Pet
        elseif (isset($_POST['edit_pet'])) {
            $pet_id = $_POST['pet_id'];
            $name = $_POST['name'];
            $category = $_POST['category'];
            $price = $_POST['price'];
            $image = $_POST['image'];
            $description = $_POST['description'];
            $status = $_POST['status'] ?? 'Available';

            $stmt = $pdo->prepare("UPDATE pets SET name=?, category=?, price=?, image=?, description=?, status=? WHERE id=?");
            $stmt->execute([$name, $category, $price, $image, $description, $status, $pet_id]);
            $success_message = "✓ Pet details updated successfully";
        }

        // Delete Pet
        elseif (isset($_POST['delete_pet'])) {
            $pet_id = $_POST['pet_id'];
            $pdo->prepare("DELETE FROM pets WHERE id=?")->execute([$pet_id]);
            $success_message = "✓ Pet removed from database";
        }

        // Delete Order
        elseif (isset($_POST['delete_order'])) {
            $order_id = $_POST['order_id'];
            $pdo->prepare("DELETE FROM payments WHERE order_id=?")->execute([$order_id]);
            $pdo->prepare("DELETE FROM orders WHERE id=?")->execute([$order_id]);
            $success_message = "✓ Order permanently deleted";
        }

        // Delete User
        elseif (isset($_POST['delete_user'])) {
            $user_id = $_POST['user_id'];
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$user_id]);
            $success_message = "✓ User account deleted";
        }

        // Update User Password
        elseif (isset($_POST['update_user_password'])) {
            $user_id = $_POST['user_id'];
            $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$new_password, $user_id]);
            $success_message = "✓ User password updated";
        }

        // Add Admin
        elseif (isset($_POST['add_admin'])) {
            $username = $_POST['username'];
            $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM admin WHERE username=?");
            $check_stmt->execute([$username]);

            if ($check_stmt->fetchColumn() == 0) {
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $insert_stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
                $insert_stmt->execute([$username, $password]);
                $success_message = "✓ Admin account '<strong>{$username}</strong>' created";
            } else {
                $error_message = "✗ Username already exists";
            }
        }

        // Update Admin Password
        elseif (isset($_POST['update_admin_password'])) {
            $admin_id = $_POST['admin_id'];
            $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE admin SET password=? WHERE id=?")->execute([$new_password, $admin_id]);
            $success_message = "✓ Admin password updated";
        }

        // Delete Admin
        elseif (isset($_POST['delete_admin'])) {
            $admin_id = $_POST['admin_id'];
            $pdo->prepare("DELETE FROM admin WHERE id=?")->execute([$admin_id]);
            $success_message = "✓ Admin account deleted";
        }

        // Update Delivery Status
        elseif (isset($_POST['update_delivery_status'])) {
            $order_id = $_POST['order_id'];
            $new_status = $_POST['update_delivery_status'];
            $delivery_date = $_POST['delivery_date'] ?? date('Y-m-d');
            $delivery_time = $_POST['delivery_time'] ?? '9am-12pm';

            $update_stmt = $pdo->prepare("UPDATE orders SET order_status=?, delivery_date=?, delivery_time=? WHERE id=?");
            $update_stmt->execute([$new_status, $delivery_date, $delivery_time, $order_id]);
            $success_message = "✓ Delivery updated to <strong>{$new_status}</strong>";
        }
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}

// ==========================================
// LOAD PAGE DATA
// ==========================================
$dashboard_stats = ['total_orders' => 0, 'pending_orders' => 0, 'total_users' => 0, 'total_revenue' => 0];
$recent_orders = [];
$all_orders = [];
$all_pets = [];
$all_users = [];
$all_admins = [];

try {
    if ($page === 'dashboard') {
        $dashboard_stats['total_revenue'] = (float)$pdo->query("SELECT SUM(total_amount) FROM orders WHERE order_status != 'Cancelled'")->fetchColumn() ?? 0;
        $dashboard_stats['total_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $dashboard_stats['pending_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('Confirmed', 'Shipped')")->fetchColumn();
        $dashboard_stats['total_users'] = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

        $recent_orders = $pdo->query("SELECT o.id, o.order_number, o.total_amount, o.order_status, o.created_at, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($page === 'orders') {
        $search_query = $_GET['search'] ?? '';
        $status_filter = $_GET['status_filter'] ?? '';

        // Always include order_status in SELECT to ensure fresh data
        $sql = "SELECT o.id, o.order_number, o.pet_id, o.quantity, o.total_amount, o.order_status, o.delivery_date, o.delivery_time, o.created_at, u.username, u.email, p.name as pet_name 
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                LEFT JOIN pets p ON o.pet_id = p.id";

        $where = [];
        $params = [];

        if (!empty($search_query)) {
            $where[] = "(o.order_number LIKE ? OR u.username LIKE ? OR p.name LIKE ?)";
            $params = ['%' . $search_query . '%', '%' . $search_query . '%', '%' . $search_query . '%'];
        }
        if (!empty($status_filter) && $status_filter !== 'All') {
            $where[] = "o.order_status = ?";
            $params[] = $status_filter;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($page === 'pets') {
        $category_filter = $_GET['category'] ?? 'All';
        $sql = "SELECT * FROM pets WHERE 1=1";
        $params = [];

        if ($category_filter !== 'All') {
            $sql .= " AND category = ?";
            $params[] = $category_filter;
        }

        $sql .= " ORDER BY id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $all_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($page === 'users') {
        $all_users = $pdo->query("SELECT id, username, email, phone, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($page === 'admins') {
        $all_admins = $pdo->query("SELECT id, username FROM admin ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

$edit_pet = null;
if (isset($_GET['edit_id']) && $page === 'pets') {
    $edit_pet = $pdo->prepare("SELECT * FROM pets WHERE id=?")->execute([$_GET['edit_id']]) ? $pdo->query("SELECT * FROM pets WHERE id=" . intval($_GET['edit_id']))->fetch(PDO::FETCH_ASSOC) : null;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
            color: #333;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #2c1a0e 0%, #1a0f06 100%);
            padding: 25px 15px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            z-index: 1000;
        }

        .sidebar-header {
            color: white;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 35px;
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 8px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.95);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding-left: 20px;
        }

        .sidebar-menu a.active {
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: #fff;
        }

        .sidebar-menu i {
            width: 25px;
            text-align: center;
            margin-right: 12px;
            font-size: 18px;
        }

        /* Main Content */
        main {
            margin-left: 260px;
            padding: 25px;
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .top-header h1 {
            font-size: 28px;
            color: #2c1a0e;
            font-weight: 700;
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-info span {
            color: #666;
            font-size: 14px;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            background: #c82333;
        }

        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Dashboard Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #b5860d;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .number {
            color: #2c1a0e;
            font-size: 32px;
            font-weight: 700;
        }

        .stat-card .subtext {
            color: #999;
            font-size: 12px;
            margin-top: 8px;
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 25px;
        }

        .table-header {
            padding: 20px 25px;
            background: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            font-size: 20px;
            color: #2c1a0e;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
        }

        table th {
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            color: #2c1a0e;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
        }

        table tr:hover {
            background: #fafafa;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-confirmed {
            background: #e3f2fd;
            color: #1976d2;
        }

        .status-shipped {
            background: #cce5ff;
            color: #004085;
        }

        .status-out-for-delivery {
            background: #fff3e0;
            color: #e65100;
        }

        .status-delivered {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c1a0e;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: border 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #b5860d;
            box-shadow: 0 0 0 3px rgba(181, 134, 13, 0.1);
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.3);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Search & Filter */
        .search-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-filter input,
        .search-filter select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
        }

        .search-filter input {
            flex: 1;
            min-width: 250px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h2 {
            font-size: 20px;
            color: #2c1a0e;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
                transition: transform 0.3s;
            }

            main {
                margin-left: 0;
            }

            .sidebar.show {
                transform: translateX(0);
                width: 260px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            table {
                font-size: 12px;
            }

            table th,
            table td {
                padding: 10px;
            }
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .form-grid.full {
            grid-template-columns: 1fr;
        }

        .page-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .tab-navigation {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0;
        }

        .tab-navigation a {
            padding: 12px 20px;
            color: #666;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            cursor: pointer;
        }

        .tab-navigation a.active {
            color: #b5860d;
            border-bottom-color: #b5860d;
        }
    </style>
</head>

<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">🐾 Paws Admin</div>
        <ul class="sidebar-menu">
            <li><a href="admin.php" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="?page=orders" class="<?php echo $page === 'orders' ? 'active' : ''; ?>"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="?page=pets" class="<?php echo $page === 'pets' ? 'active' : ''; ?>"><i class="fas fa-paw"></i> Pets</a></li>
            <li><a href="?page=users" class="<?php echo $page === 'users' ? 'active' : ''; ?>"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="?page=deliveries" class="<?php echo $page === 'deliveries' ? 'active' : ''; ?>"><i class="fas fa-truck"></i> Deliveries</a></li>
            <li><a href="?page=admins" class="<?php echo $page === 'admins' ? 'active' : ''; ?>"><i class="fas fa-key"></i> Admins</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main>
        <!-- Top Header -->
        <div class="top-header">
            <h1><?php echo ucfirst($page); ?> Management</h1>
            <div class="admin-info">
                <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_user']['username'] ?? 'Admin'); ?></span>
                <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- DASHBOARD PAGE -->
        <?php if ($page === 'dashboard'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fas fa-shopping-bag"></i> Total Orders</h3>
                    <div class="number"><?php echo $dashboard_stats['total_orders']; ?></div>
                    <div class="subtext"><?php echo $dashboard_stats['pending_orders']; ?> pending</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-users"></i> Total Users</h3>
                    <div class="number"><?php echo $dashboard_stats['total_users']; ?></div>
                    <div class="subtext">Active customers</div>
                </div>
                <div class="stat-card">
                    <h3><i class="fas fa-rupee-sign"></i> Total Revenue</h3>
                    <div class="number">₹<?php echo number_format($dashboard_stats['total_revenue'], 0); ?></div>
                    <div class="subtext">This month</div>
                </div>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Recent Orders</h2>
                    <a href="?page=orders" class="btn btn-primary"><i class="fas fa-arrow-right"></i> View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></td>
                                <td><strong>₹<?php echo number_format($order['total_amount']); ?></strong></td>
                                <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['order_status'])); ?>"><?php echo $order['order_status']; ?></span></td>
                                <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- ORDERS PAGE -->
        <?php if ($page === 'orders'): ?>
            <div class="page-section">
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="page" value="orders">
                    <input type="text" name="search" placeholder="Search by Order #, Customer, or Pet..." value="<?php echo htmlspecialchars($search_query); ?>" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <select name="status_filter" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">All Status</option>
                        <option value="Confirmed" <?php echo $status_filter === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="Shipped" <?php echo $status_filter === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="Out for Delivery" <?php echo $status_filter === 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                        <option value="Delivered" <?php echo $status_filter === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="Cancelled" <?php echo $status_filter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
                </form>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Pet</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($order['pet_name'] ?? 'N/A'); ?></td>
                                <td>₹<?php echo number_format($order['total_amount']); ?></td>
                                <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $order['order_status'])); ?>"><?php echo $order['order_status']; ?></span></td>
                                <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
                                <td>
                                    <form method="POST" action="admin.php?page=orders" style="display: flex; gap: 8px; align-items: center;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="new_status" style="padding: 6px 8px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px;">
                                            <option value="Confirmed" <?php echo $order['order_status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="Shipped" <?php echo $order['order_status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="Out for Delivery" <?php echo $order['order_status'] === 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                            <option value="Delivered" <?php echo $order['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="Cancelled" <?php echo $order['order_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-primary btn-small"><i class="fas fa-check"></i> Update</button>
                                        <button type="submit" name="delete_order" class="btn btn-danger btn-small" onclick="return confirm('Delete this order?');"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- PETS PAGE -->
        <?php if ($page === 'pets'): ?>
            <div class="page-section">
                <h2 style="margin-bottom: 20px;"><i class="fas fa-paw"></i> <?php echo isset($_GET['edit_id']) ? 'Edit Pet' : 'Add New Pet'; ?></h2>
                <form method="POST" action="admin.php?page=pets">
                    <?php if (isset($_GET['edit_id'])): ?>
                        <input type="hidden" name="pet_id" value="<?php echo htmlspecialchars($_GET['edit_id']); ?>">
                    <?php endif; ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Pet Name & Breed</label>
                            <input type="text" name="name" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['name']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" required>
                                <option value="Dogs" <?php echo $edit_pet && $edit_pet['category'] === 'Dogs' ? 'selected' : ''; ?>>Dogs</option>
                                <option value="Cats" <?php echo $edit_pet && $edit_pet['category'] === 'Cats' ? 'selected' : ''; ?>>Cats</option>
                                <option value="Fish" <?php echo $edit_pet && $edit_pet['category'] === 'Fish' ? 'selected' : ''; ?>>Fish</option>
                                <option value="Birds" <?php echo $edit_pet && $edit_pet['category'] === 'Birds' ? 'selected' : ''; ?>>Birds</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Price (₹)</label>
                            <input type="number" name="price" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['price']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <input type="text" name="status" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['status']) : 'Available'; ?>" required>
                        </div>
                    </div>
                    <div class="form-group form-grid full">
                        <label>Image URL</label>
                        <input type="text" name="image" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['image']) : ''; ?>" required>
                    </div>
                    <div class="form-group form-grid full">
                        <label>Description</label>
                        <textarea name="description" rows="4" required><?php echo $edit_pet ? htmlspecialchars($edit_pet['description']) : ''; ?></textarea>
                    </div>
                    <div class="action-buttons">
                        <?php if (isset($_GET['edit_id'])): ?>
                            <button type="submit" name="edit_pet" class="btn btn-success"><i class="fas fa-save"></i> Update Pet</button>
                            <a href="?page=pets" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                        <?php else: ?>
                            <button type="submit" name="add_pet" class="btn btn-primary"><i class="fas fa-plus"></i> Add Pet</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Pets Database</h2>
                    <select onchange="location.href=this.value" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd;">
                        <option value="?page=pets">All Categories</option>
                        <option value="?page=pets&category=Dogs">Dogs</option>
                        <option value="?page=pets&category=Cats">Cats</option>
                        <option value="?page=pets&category=Fish">Fish</option>
                        <option value="?page=pets&category=Birds">Birds</option>
                    </select>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pet Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_pets as $pet): ?>
                            <tr>
                                <td>#<?php echo $pet['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($pet['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($pet['category']); ?></td>
                                <td>₹<?php echo number_format($pet['price']); ?></td>
                                <td><span class="status-badge status-confirmed" style="background: #e8f5e9; color: #2e7d32; display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px;"><?php echo htmlspecialchars($pet['status']); ?></span></td>
                                <td>
                                    <a href="?page=pets&edit_id=<?php echo $pet['id']; ?>" class="btn btn-primary btn-small"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="admin.php?page=pets" style="display: inline;">
                                        <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                                        <button type="submit" name="delete_pet" class="btn btn-danger btn-small" onclick="return confirm('Delete this pet?');"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- USERS PAGE -->
        <?php if ($page === 'users'): ?>
            <div class="table-container">
                <div class="table-header">
                    <h2>User Accounts</h2>
                    <span style="color: #666;">Total: <?php echo count($all_users); ?> users</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <form method="POST" action="admin.php?page=users" style="display: flex; gap: 8px; align-items: center;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="password" name="new_password" placeholder="New password" required style="padding: 6px 10px; font-size: 12px;border: 1px solid #ddd; border-radius: 4px;">
                                        <button type="submit" name="update_user_password" class="btn btn-primary btn-small"><i class="fas fa-key"></i> Reset</button>
                                        <button type="submit" name="delete_user" class="btn btn-danger btn-small" onclick="return confirm('Delete this user?');"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- DELIVERIES PAGE -->
        <?php if ($page === 'deliveries'): ?>
            <div class="page-section">
                <p style="color: #666; margin-bottom: 20px;"><i class="fas fa-info-circle"></i> Manage and track all deliveries from this section.</p>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Delivery Tracking</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Current Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $deliveries = $pdo->query("SELECT o.id, o.order_number, u.username, o.order_status, o.created_at FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($deliveries as $delivery):
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($delivery['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($delivery['username'] ?? 'N/A'); ?></td>
                                <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $delivery['order_status'])); ?>"><?php echo $delivery['order_status']; ?></span></td>
                                <td><?php echo date('d M Y H:i', strtotime($delivery['created_at'])); ?></td>
                                <td>
                                    <form method="POST" action="admin.php?page=deliveries" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $delivery['id']; ?>">
                                        <select name="update_delivery_status" style="padding: 6px 8px; font-size: 12px; border: 1px solid #ddd; border-radius: 4px;">
                                            <option value="Confirmed" <?php echo $delivery['order_status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="Shipped" <?php echo $delivery['order_status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="Out for Delivery" <?php echo $delivery['order_status'] === 'Out for Delivery' ? 'selected' : ''; ?>>Out for Delivery</option>
                                            <option value="Delivered" <?php echo $delivery['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary btn-small"><i class="fas fa-check"></i> Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- ADMINS PAGE -->
        <?php if ($page === 'admins'): ?>
            <div class="page-section">
                <h2 style="margin-bottom: 20px;"><i class="fas fa-key"></i> Create New Admin Account</h2>
                <form method="POST" action="admin.php?page=admins">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required>
                        </div>
                    </div>
                    <button type="submit" name="add_admin" class="btn btn-primary"><i class="fas fa-plus"></i> Create Admin</button>
                </form>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h2>Admin Accounts</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Admin ID</th>
                            <th>Username</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($all_admins as $admin): ?>
                            <tr>
                                <td>#<?php echo $admin['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($admin['username']); ?></strong></td>
                                <td>
                                    <?php if ($admin['id'] != $_SESSION['admin_user']['id']): ?>
                                        <form method="POST" action="admin.php?page=admins" style="display: inline;">
                                            <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                            <button type="submit" name="delete_admin" class="btn btn-danger btn-small" onclick="return confirm('Delete this admin?');"><i class="fas fa-trash"></i> Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #999; font-style: italic;">Current Admin</span>
                                    <?php endif; ?>
                                    <form method="POST" action="admin.php?page=admins" style="display: inline-block; margin-left: 10px;">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                        <input type="password" name="new_password" placeholder="New password" required style="padding: 6px 10px; font-size: 12px; border: 1px solid #ddd; border-radius: 4px;">
                                        <button type="submit" name="update_admin_password" class="btn btn-primary btn-small"><i class="fas fa-key"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    </script>
</body>

</html>