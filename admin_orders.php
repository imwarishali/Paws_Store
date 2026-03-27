<?php
session_start();

// Basic security: Ensure the user is logged in. 
// In a production app, you would also check if $_SESSION['user']['is_admin'] == true
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

$host = 'localhost';
$dbname = 'pet_store';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the logged-in user is an admin
    $user_id = $_SESSION["user"]["id"] ?? 0;
    $admin_stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $admin_stmt->execute([$user_id]);
    $user_data = $admin_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data || empty($user_data['is_admin'])) {
        header("Location: index.php"); // Redirect non-admins to the homepage
        exit();
    }

    // Handle the Status Update form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];

        $update_stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $update_stmt->execute([$new_status, $order_id]);

        $success_message = "Order #{$order_id} status updated to '{$new_status}' successfully!";
    }

    // Fetch all orders from the database along with their associated pet names
    $stmt = $pdo->query("
        SELECT o.*, 
               GROUP_CONCAT(CONCAT(p.name, ' (x', oi.quantity, ')') SEPARATOR '<br>') AS pet_names 
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        LEFT JOIN pets p ON oi.pet_id = p.id 
        GROUP BY o.id 
        ORDER BY o.created_at DESC
    ");
    $all_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e8e0d4;
        }

        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            color: #2c1a0e;
            margin-bottom: 20px;
        }

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

        .status-select {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-family: 'Nunito', sans-serif;
        }

        .update-btn {
            padding: 8px 16px;
            background: #b5860d;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .update-btn:hover {
            background: #9a7210;
        }

        .msg-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <nav class="fk-nav-header">
        <div class="fk-nav-top">
            <a href="index.php" class="fk-logo">🐾 Paws Store Admin</a>
            <div class="fk-nav-right" style="margin-left: auto;">
                <a href="admin_pets.php" class="fk-cart-btn" style="margin-right: 15px;">
                    <span class="fk-cart-icon">🐶</span> Manage Pets
                </a>
                <a href="index.php" class="fk-cart-btn">
                    <span class="fk-cart-icon">🏠</span> Store Home
                </a>
            </div>
        </div>
    </nav>

    <div class="ps-wrap">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Manage Customer Orders</h1>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="msg-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Pets Ordered</th>
                        <th>Date Placed</th>
                        <th>Total Amount</th>
                        <th>Current Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($all_orders)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No orders found in the database.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($all_orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['user_id']); ?></td>
                                <td style="line-height: 1.6; font-size: 14px; color: #555;"><?php echo $order['pet_names'] ? strip_tags($order['pet_names'], '<br>') : 'N/A'; ?></td>
                                <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                                <td>₹<?php echo number_format($order['total_amount']); ?></td>
                                <td>
                                    <span class="status-processing" style="padding: 5px 10px; border-radius: 12px; background: #f5f2eb;"><?php echo htmlspecialchars($order['order_status']); ?></span>
                                </td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 10px; align-items: center; margin: 0;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <select name="new_status" class="status-select">
                                            <option value="Processing" <?php echo $order['order_status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Shipped" <?php echo $order['order_status'] === 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="Delivered" <?php echo $order['order_status'] === 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="Cancelled" <?php echo $order['order_status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <button type="submit" name="update_status" class="update-btn">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>