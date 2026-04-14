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
$pet_index = isset($_GET['pet']) ? (int)$_GET['pet'] : 0;

require_once 'db.php';

try {
    $is_admin = isset($_SESSION['admin_user']);
    // Build the query to get all order items
    $sql = "
        SELECT o.*, p.name AS pet_name, p.price AS pet_price, pm.transaction_id, pm.payment_method AS pm_method, pm.payment_status, pm.payment_date
        FROM orders o
        LEFT JOIN pets p ON o.pet_id = p.id
        LEFT JOIN payments pm ON o.id = pm.order_id
        WHERE o.order_number = ?
    ";
    $params = [$order_number];

    // If the user is not an admin, add a security check
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

    // Validate pet_index
    if ($pet_index >= count($order_rows)) {
        $pet_index = 0;
    }

    $current_invoice = $order_rows[$pet_index];
    $total_pets = count($order_rows);

    // Calculate the amount for this specific pet
    $pet_price = $current_invoice['pet_price'] ?? 0;
    $pet_quantity = $current_invoice['quantity'] ?? 1;
    $pet_subtotal = $pet_price * $pet_quantity;

    // Calculate proportional taxes/shipping/fees based on number of items
    $grand_total_all = $current_invoice['total_amount'] ?? 0;
    $fees_per_item = ($grand_total_all / $total_pets);
    $pet_total = $fees_per_item;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$address_array = [];
if (!empty($current_invoice['shipping_address'])) {
    $decoded = json_decode($current_invoice['shipping_address'], true);
    if (is_string($decoded)) {
        $decoded = json_decode($decoded, true);
    }
    $address_array = is_array($decoded) ? $decoded : [$current_invoice['shipping_address']];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo htmlspecialchars($current_invoice['order_number']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', 'Segoe UI', 'Helvetica Neue', sans-serif;
            color: #2c1a0e;
            background: linear-gradient(135deg, #faf6f0 0%, #e8dcc4 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .invoice-header-bar {
            background: linear-gradient(135deg, #5c4033 0%, #3d2a21 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(92, 64, 51, 0.15);
            margin-bottom: -1px;
        }

        .invoice-header-bar h2 {
            font-size: 24px;
            margin: 0;
        }

        .invoice-counter {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .invoice-box {
            max-width: 900px;
            margin: 0 auto;
            padding: 50px;
            background: #ffffff;
            box-shadow: 0 8px 30px rgba(92, 64, 51, 0.15);
            border-radius: 0 0 12px 12px;
            position: relative;
        }

        .invoice-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 120px;
            font-weight: 900;
            color: rgba(220, 53, 69, 0.08);
            border: 15px solid rgba(220, 53, 69, 0.08);
            padding: 30px 50px;
            border-radius: 20px;
            pointer-events: none;
            z-index: 1;
            letter-spacing: 5px;
        }

        .invoice-content {
            position: relative;
            z-index: 2;
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #c9a227;
            padding-bottom: 30px;
            margin-bottom: 40px;
        }

        .logo-section h1 {
            font-size: 32px;
            color: #b5860d;
            margin: 0 0 8px 0;
            font-weight: 700;
        }

        .logo-section p {
            color: #666;
            font-size: 14px;
            margin: 0;
            font-style: italic;
        }

        .invoice-meta {
            text-align: right;
        }

        .invoice-meta table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-meta td {
            padding: 8px 0;
            border: none;
            font-size: 14px;
        }

        .invoice-meta-label {
            font-weight: 700;
            color: #2c1a0e;
            text-align: left;
            padding-right: 15px;
            width: 150px;
        }

        .invoice-meta-value {
            color: #555;
            text-align: right;
        }

        .addresses-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        .address-box {
            padding: 20px;
            background: linear-gradient(135deg, #faf6f0 0%, #f2ede4 100%);
            border-radius: 12px;
            border-left: 4px solid #c9a227;
        }

        .address-box h3 {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            color: #2c1a0e;
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        .address-box p {
            font-size: 14px;
            line-height: 1.8;
            color: #555;
            margin: 0;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 40px 0;
        }

        .items-table thead {
            background: linear-gradient(135deg, #f5f2eb 0%, #e8dcc4 100%);
        }

        .items-table th {
            padding: 16px;
            text-align: left;
            font-weight: 700;
            color: #2c1a0e;
            border-bottom: 2px solid #c9a227;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 18px 16px;
            border-bottom: 1px solid #e8dcc4;
            font-size: 14px;
            color: #555;
        }

        .items-table tbody tr:hover {
            background: #fafafa;
        }

        .item-description {
            font-weight: 600;
            color: #2c1a0e;
        }

        .price-cell {
            text-align: right;
            color: #b5860d;
            font-weight: 600;
        }

        .summary-section {
            margin-top: 40px;
            border-top: 2px solid #e8dcc4;
            padding-top: 30px;
            display: flex;
            justify-content: flex-end;
        }

        .summary-box {
            width: 350px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 14px;
            color: #555;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-row.total {
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: white;
            padding: 18px;
            margin-top: 12px;
            border-radius: 8px;
            border: none;
            font-size: 16px;
            font-weight: 700;
        }

        .summary-row.total span:first-child {
            font-weight: 700;
        }

        .footer-section {
            text-align: center;
            padding: 40px 0 0 0;
            border-top: 1px solid #e8dcc4;
            margin-top: 40px;
            color: #666;
            font-size: 13px;
        }

        .footer-section p {
            margin: 8px 0;
            line-height: 1.6;
        }

        .navigation-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .nav-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-prev {
            background: #e8dcc4;
            color: #2c1a0e;
        }

        .btn-prev:hover:not(:disabled) {
            background: #d4c4a8;
            transform: translateY(-2px);
        }

        .btn-next {
            background: #c9a227;
            color: white;
        }

        .btn-next:hover:not(:disabled) {
            background: #b5860d;
            transform: translateY(-2px);
        }

        .btn-prev:disabled,
        .btn-next:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            background: linear-gradient(135deg, #faf6f0 0%, #f2ede4 100%);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e8dcc4;
        }

        .action-btn {
            padding: 13px 32px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back {
            background: linear-gradient(135deg, #5c4033 0%, #3d2a21 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(92, 64, 51, 0.2);
        }

        .btn-back:hover {
            background: linear-gradient(135deg, #3d2a21 0%, #2c1a0e 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(92, 64, 51, 0.3);
        }

        .btn-print {
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.25);
        }

        .btn-print:hover {
            background: linear-gradient(135deg, #d4af37 0%, #b5860d 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.35);
        }

        .btn-pdf {
            background: linear-gradient(135deg, #2c6e7f 0%, #1f4d5a 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(44, 110, 127, 0.25);
        }

        .btn-pdf:hover {
            background: linear-gradient(135deg, #1f4d5a 0%, #0d2e38 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(44, 110, 127, 0.35);
        }

        @media (max-width: 768px) {
            .invoice-box {
                padding: 30px;
            }

            .invoice-header {
                flex-direction: column;
                gap: 20px;
            }

            .invoice-meta {
                text-align: left;
            }

            .addresses-section {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .items-table th,
            .items-table td {
                padding: 12px 8px;
                font-size: 12px;
            }

            .summary-box {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-btn,
            .nav-btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .invoice-header-bar,
            .action-buttons,
            .navigation-buttons,
            .no-print {
                display: none;
            }

            .invoice-box {
                box-shadow: none;
                max-width: 100%;
                margin: 0;
                border-radius: 0;
            }

            .invoice-watermark {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-container">
        <div class="invoice-header-bar">
            <h2>🐾 Invoice</h2>
            <div class="invoice-counter">
                Pet <?php echo ($pet_index + 1); ?> of <?php echo $total_pets; ?>
            </div>
        </div>

        <div class="action-buttons no-print">
            <button onclick="history.back()" class="action-btn btn-back">⬅️ Back</button>
            <button onclick="window.print()" class="action-btn btn-print">🖨️ Print</button>
            <button onclick="downloadPDF()" class="action-btn btn-pdf">📄 Download PDF</button>
        </div>

        <div class="invoice-box">
            <?php if (($current_invoice['payment_status'] ?? '') === 'Refunded'): ?>
                <div class="invoice-watermark">REFUNDED</div>
            <?php elseif (($current_invoice['order_status'] ?? '') === 'Cancelled'): ?>
                <div class="invoice-watermark">CANCELLED</div>
            <?php endif; ?>

            <div class="invoice-content">
                <div class="invoice-header">
                    <div class="logo-section">
                        <h1>🐾 Paws Store</h1>
                        <p>Bringing joy home, one paw at a time</p>
                    </div>
                    <div class="invoice-meta">
                        <table>
                            <tr>
                                <td class="invoice-meta-label">INVOICE #</td>
                                <td class="invoice-meta-value" style="font-weight: 700; color: #b5860d; font-size: 16px;"><?php echo htmlspecialchars($current_invoice['order_number']); ?></td>
                            </tr>
                            <tr>
                                <td class="invoice-meta-label">Date</td>
                                <td class="invoice-meta-value"><?php echo date('d M Y, H:i', strtotime($current_invoice['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <td class="invoice-meta-label">Transaction ID</td>
                                <td class="invoice-meta-value"><?php echo htmlspecialchars($current_invoice['transaction_id'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <td class="invoice-meta-label">Pet Invoice</td>
                                <td class="invoice-meta-value"><?php echo ($pet_index + 1); ?> / <?php echo $total_pets; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="addresses-section">
                    <div class="address-box">
                        <h3>📍 Billing To</h3>
                        <?php
                        foreach ($address_array as $key => $val) {
                            $label = is_string($key) ? ucfirst($key) . ': ' : '';
                            echo "<p><strong>" . htmlspecialchars($label) . "</strong>" . htmlspecialchars($val) . "</p>";
                        }
                        ?>
                    </div>
                    <div class="address-box">
                        <h3>💳 Payment Information</h3>
                        <p><strong>Method:</strong> <?php echo htmlspecialchars(ucfirst($current_invoice['pm_method'] ?? $current_invoice['payment_method'] ?? 'N/A')); ?></p>
                        <p><strong>Order Status:</strong> <?php echo htmlspecialchars($current_invoice['order_status'] ?? 'Completed'); ?></p>
                        <p><strong>Payment Status:</strong>
                            <?php if (($current_invoice['payment_status'] ?? '') === 'Refunded'): ?>
                                <span style="color: #dc3545; font-weight: 700;">Refunded</span>
                            <?php else: ?>
                                <span style="color: #28a745; font-weight: 600;"><?php echo htmlspecialchars($current_invoice['payment_status'] ?? 'Completed'); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th style="width: 100px; text-align: center;">Quantity</th>
                            <th style="width: 120px; text-align: right;">Unit Price</th>
                            <th style="width: 120px; text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="item-description"><?php echo htmlspecialchars($current_invoice['pet_name'] ?? 'Pet'); ?></td>
                            <td style="text-align: center;"><?php echo htmlspecialchars($current_invoice['quantity'] ?? 1); ?></td>
                            <td class="price-cell">₹<?php echo number_format($pet_price); ?></td>
                            <td class="price-cell">₹<?php echo number_format($pet_subtotal); ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="summary-section">
                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>₹<?php echo number_format($pet_subtotal); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>Taxes & Shipping (Apportioned):</span>
                            <span>₹<?php echo number_format(round($pet_total - $pet_subtotal, 2)); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total Amount:</span>
                            <span>₹<?php echo number_format(round($pet_total, 2)); ?></span>
                        </div>
                    </div>
                </div>

                <div class="footer-section">
                    <p><strong>Thank you for your business!</strong></p>
                    <p>We appreciate your purchase and look forward to serving you again.</p>
                    <p style="margin-top: 15px; font-size: 12px; color: #999;">For support, contact: support@pawsstore.in | Phone: 1-800-PAWS-123</p>
                </div>
            </div>
        </div>

        <?php if ($total_pets > 1): ?>
            <div class="navigation-buttons no-print">
                <button onclick="previousPet()" class="nav-btn btn-prev" <?php echo ($pet_index === 0) ? 'disabled' : ''; ?>>
                    ← Previous Pet
                </button>
                <span style="align-self: center; font-weight: 600; color: #2c1a0e;">
                    Pet <?php echo ($pet_index + 1); ?> of <?php echo $total_pets; ?>
                </span>
                <button onclick="nextPet()" class="nav-btn btn-next" <?php echo ($pet_index === $total_pets - 1) ? 'disabled' : ''; ?>>
                    Next Pet →
                </button>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        const currentPetIndex = <?php echo $pet_index; ?>;
        const totalPets = <?php echo $total_pets; ?>;
        const orderNumber = '<?php echo htmlspecialchars($current_invoice['order_number']); ?>';

        function nextPet() {
            if (currentPetIndex < totalPets - 1) {
                window.location.href = `invoice.php?order_id=${orderNumber}&pet=${currentPetIndex + 1}`;
            }
        }

        function previousPet() {
            if (currentPetIndex > 0) {
                window.location.href = `invoice.php?order_id=${orderNumber}&pet=${currentPetIndex - 1}`;
            }
        }

        function downloadPDF() {
            const element = document.querySelector('.invoice-box');
            const petNumber = currentPetIndex + 1;
            const opt = {
                margin: [10, 10, 10, 10],
                filename: `Invoice_${orderNumber}_Pet${petNumber}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    logging: false
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