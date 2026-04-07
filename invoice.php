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
        SELECT o.*, p.name AS pet_name, p.price AS pet_price, pm.transaction_id, pm.payment_method AS pm_method, pm.payment_status, pm.payment_date
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
    $order_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($order_rows)) {
        die("Order not found or access denied.");
    }
    $order = $order_rows[0];
    $grand_total = 0;
    foreach ($order_rows as $row) {
        $grand_total += $row['total_amount'];
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
            position: relative;
            overflow: hidden;
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

        .pdf-btn {
            background: #28a745;
        }

        .pdf-btn:hover {
            background: #218838;
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

        .watermark-stamp {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 110px;
            color: rgba(220, 53, 69, 0.12);
            /* Faded red */
            border: 12px solid rgba(220, 53, 69, 0.12);
            padding: 20px 40px;
            border-radius: 20px;
            font-weight: 900;
            pointer-events: none;
            z-index: 10;
        }
    </style>
</head>

<body>

    <div class="btn-container no-print">
        <button onclick="history.back()" class="action-btn back-btn">⬅️ Back</button>
        <button onclick="window.print()" class="action-btn print-btn">🖨️ Print</button>
        <button onclick="downloadPDF()" class="action-btn pdf-btn">📄 Download PDF</button>
    </div>

    <div class="invoice-box" id="invoice">
        <?php if (($order['payment_status'] ?? '') === 'Refunded'): ?>
            <div class="watermark-stamp">REFUNDED</div>
        <?php elseif (($order['order_status'] ?? '') === 'Cancelled'): ?>
            <div class="watermark-stamp">CANCELLED</div>
        <?php endif; ?>
        <div class="header">
            <div>
                <!-- Upload your logo to the Assets folder and name it logo.png -->
                <img src="Assets/logo.png" alt="Paws Store Logo" style="max-height: 60px; margin-bottom: 5px; display: none;" onload="this.style.display='block'; document.getElementById('text-logo').style.display='none';">
                <h1 id="text-logo">🐾 Paws Store</h1>
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
                <p><strong>Order Status:</strong> <?php echo htmlspecialchars($order['order_status'] ?? 'Completed'); ?></p>
                <p><strong>Payment Status:</strong>
                    <?php if (($order['payment_status'] ?? '') === 'Refunded'): ?>
                        <span style="color: #dc3545; font-weight: bold;">Refunded</span>
                    <?php else: ?>
                        <?php echo htmlspecialchars($order['payment_status'] ?? 'Completed'); ?>
                    <?php endif; ?>
                </p>
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
                <?php
                $base_subtotal_all = 0;
                foreach ($order_rows as $row):
                    $row_base = ($row['pet_price'] ?? 0) * ($row['quantity'] ?? 1);
                    $base_subtotal_all += $row_base;
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['pet_name'] ?? 'Pet'); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity'] ?? 1); ?></td>
                        <td>₹<?php echo number_format($row['pet_price'] ?? 0); ?></td>
                        <td>₹<?php echo number_format($row_base); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php
                $fees_and_discounts = $grand_total - $base_subtotal_all;
                if ($fees_and_discounts != 0):
                ?>
                    <tr>
                        <td colspan="3" style="text-align: right; color: #666; font-size: 14px;">Taxes, Shipping & Applied Offers:</td>
                        <td style="color: #666; font-size: 14px;"><?php echo $fees_and_discounts > 0 ? '+' : ''; ?>₹<?php echo number_format($fees_and_discounts); ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Grand Total (Including Taxes & Shipping):</td>
                    <td>₹<?php echo number_format($grand_total); ?></td>
                </tr>
            </tbody>
        </table>
        <div class="footer">
            <p>Thank you for shopping with Paws Store!</p>
            <p>If you have any questions concerning this invoice, contact support@pawsstore.in</p>
        </div>
    </div>

    <!-- html2pdf Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.getElementById('invoice');
            const opt = {
                margin: [10, 10, 10, 10],
                filename: 'Invoice_<?php echo htmlspecialchars($order['order_number']); ?>.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>

</html>