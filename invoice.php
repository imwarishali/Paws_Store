<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    die("Order ID not provided.");
}

$order_number = $_GET['order_id'];
$user_id = $_SESSION['user']['id'];

require_once 'db.php';

try {
    $is_admin = isset($_SESSION['admin_user']);
    // Build the query dynamically based on user role
    $sql = "
        SELECT o.*, p.name AS pet_name, p.price AS pet_price, pm.transaction_id, pm.payment_method AS pm_method, pm.payment_date
        FROM orders o
        LEFT JOIN pets p ON o.pet_id = p.id
        LEFT JOIN payments pm ON o.id = pm.order_id
        WHERE o.order_number = ?
    ";
    $params = [$order_number];

    // If the user is not an admin, add a security check to ensure they only see their own orders
    if (!$is_admin) {
        $sql .= " AND o.user_id = ?";
        $params[] = $user_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order not found or access denied.");
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$address_array = [];
if (!empty($order['shipping_address'])) {
    $decoded = json_decode($order['shipping_address'], true);
    if (is_string($decoded)) {
        $decoded = json_decode($decoded, true);
    }
    $address_array = is_array($decoded) ? $decoded : [$order['shipping_address']];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice - <?php echo htmlspecialchars($order['order_number']); ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #f9f9f9;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 40px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            background: #fff;
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #b5860d;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #b5860d;
            margin: 0 0 5px 0;
        }

        .header p {
            margin: 0;
            color: #666;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-details h2 {
            margin: 0 0 10px 0;
            color: #555;
        }

        .invoice-details p {
            margin: 5px 0;
        }

        .addresses {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .address-box {
            width: 45%;
            line-height: 1.6;
        }

        .address-box h3 {
            margin-bottom: 10px;
            color: #2c1a0e;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .table th,
        .table td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .table th {
            background-color: #f5f2eb;
            color: #2c1a0e;
            font-weight: 600;
        }

        .total-row td {
            font-weight: bold;
            font-size: 18px;
            background-color: #fdfaf6;
        }

        .footer {
            text-align: center;
            color: #777;
            font-size: 14px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .btn-container {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px auto;
            max-width: 800px;
        }

        .action-btn {
            display: block;
            width: 250px;
            padding: 12px;
            text-align: center;
            color: white;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            border: none;
            font-size: 16px;
            transition: background 0.3s;
        }

        .print-btn {
            background: #b5860d;
        }

        .print-btn:hover {
            background: #9a7210;
        }

        .back-btn {
            background: #6c757d;
        }

        .back-btn:hover {
            background: #5a6268;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .invoice-box {
                box-shadow: none;
                border: none;
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="btn-container no-print">
        <button onclick="history.back()" class="action-btn back-btn">⬅️ Back</button>
        <button onclick="window.print()" class="action-btn print-btn">🖨️ Print / Save as PDF</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div>
                <h1>🐾 Paws Store</h1>
                <p>Bringing joy home, one paw at a time.</p>
            </div>
            <div class="invoice-details">
                <h2>INVOICE</h2>
                <p><strong>Order #:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                <p><strong>Date:</strong> <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($order['transaction_id'] ?? 'N/A'); ?></p>
            </div>
        </div>
        <div class="addresses">
            <div class="address-box">
                <h3>Billed To:</h3>
                <?php
                foreach ($address_array as $key => $val) {
                    $label = is_string($key) ? ucfirst($key) . ': ' : '';
                    echo "<strong>" . htmlspecialchars($label) . "</strong>" . htmlspecialchars($val) . "<br>";
                }
                ?>
            </div>
            <div class="address-box">
                <h3>Payment Info:</h3>
                <p><strong>Method:</strong> <?php echo htmlspecialchars(ucfirst($order['pm_method'] ?? $order['payment_method'] ?? 'N/A')); ?></p>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($order['order_status'] ?? 'Completed'); ?></p>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($order['pet_name'] ?? 'Pet'); ?></td>
                    <td><?php echo htmlspecialchars($order['quantity'] ?? 1); ?></td>
                    <td>₹<?php echo number_format($order['pet_price'] ?? $order['total_amount']); ?></td>
                    <td>₹<?php echo number_format(($order['pet_price'] ?? $order['total_amount']) * ($order['quantity'] ?? 1)); ?></td>
                </tr>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Grand Total (Including Taxes & Shipping):</td>
                    <td>₹<?php echo number_format($order['total_amount']); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="footer">
            <p>Thank you for shopping with Paws Store!</p>
            <p>If you have any questions concerning this invoice, contact support@pawsstore.in</p>
        </div>
    </div>
</body>

</html>