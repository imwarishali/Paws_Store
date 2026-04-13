<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php?redirect=order_history.php");
    exit();
}

require_once 'db.php';

$orders = [];
try {
    // Handle order cancellation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_number'])) {
        $cancel_num = $_POST['cancel_order_number'];
        $user_id = $_SESSION['user']['id'];

        // First, check the current order status
        $status_check = $pdo->prepare("SELECT order_status FROM orders WHERE order_number = ? AND user_id = ?");
        $status_check->execute([$cancel_num, $user_id]);
        $current_status = $status_check->fetchColumn();

        // Define statuses that cannot be cancelled
        $non_cancellable_statuses = ['Out for Delivery', 'Delivered', 'Cancelled'];

        if ($current_status && in_array($current_status, $non_cancellable_statuses)) {
            $error_message = "❌ Cannot cancel order! Status: <strong>{$current_status}</strong>. Orders that are out for delivery, already delivered, or already cancelled cannot be cancelled.";
        } else {
            // Fetch the Razorpay Transaction ID for this order
            $txn_stmt = $pdo->prepare("
                SELECT p.transaction_id 
                FROM orders o 
                JOIN payments p ON o.id = p.order_id 
                WHERE o.order_number = ? AND o.user_id = ? 
                LIMIT 1
            ");
            $txn_stmt->execute([$cancel_num, $user_id]);
            $transaction_id = $txn_stmt->fetchColumn();

            // Update the order status to Cancelled
            $update_stmt = $pdo->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE order_number = ? AND user_id = ?");
            $update_stmt->execute([$cancel_num, $user_id]);

            // Update payment status to Refunded
            $refund_stmt = $pdo->prepare("UPDATE payments p JOIN orders o ON p.order_id = o.id SET p.payment_status = 'Refunded' WHERE o.order_number = ? AND o.user_id = ?");
            $refund_stmt->execute([$cancel_num, $user_id]);

            // Process Automatic Razorpay Refund
            if ($transaction_id && strpos($transaction_id, 'pay_') === 0) {
                $env = parse_ini_file('.env');
                $keyId = $env['RAZORPAY_KEY_ID'] ?? '';
                $keySecret = $env['RAZORPAY_KEY_SECRET'] ?? '';

                if ($keyId && $keySecret) {
                    $ch = curl_init("https://api.razorpay.com/v1/payments/{$transaction_id}/refund");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_USERPWD, $keyId . ':' . $keySecret);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([])); // Empty JSON array triggers a full refund
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_exec($ch);
                    curl_close($ch);
                }
            }

            $success_message = "Your order has been successfully cancelled. A refund has been initiated!";

            // Send Order Cancellation Email to Customer
            $to = $_SESSION['user']['email'] ?? '';
            $username = $_SESSION['user']['username'] ?? 'Customer';
            $subject = "Order Cancelled - Paws Store [" . $cancel_num . "]";
            $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Order Cancelled</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; background-color: #faf7f2; color: #333333;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #faf7f2; padding: 20px;'>
                <tr>
                    <td align='center'>
                        <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                            <tr>
                                <td style='background-color: #2c1a0e; padding: 30px; text-align: center;'>
                                    <h1 style='color: #b5860d; margin: 0; font-size: 28px; font-weight: normal;'>🐾 Paws Store</h1>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 40px 30px;'>
                                    <h2 style='color: #2c1a0e; margin-top: 0;'>Order Cancelled</h2>
                                    <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Hello " . htmlspecialchars($username) . ",</p>
                                    <p style='font-size: 16px; line-height: 1.5; color: #555555;'>This email is to confirm that your order <strong>#" . htmlspecialchars($cancel_num) . "</strong> has been successfully cancelled.</p>
                                    
                                    <div style='background-color: #fdfaf6; border: 1px solid #e8e0d4; border-radius: 8px; padding: 20px; margin: 30px 0; text-align: center;'>
                                        <p style='margin: 0; font-size: 16px; color: #555555;'><strong>Refund Initiated:</strong> Your refund will reflect in your original payment method within 3-5 business days.</p>
                                    </div>
                                    
                                    <p style='font-size: 16px; line-height: 1.5; color: #555555;'>If you change your mind, you can always place a new order. We'd love to help you find your perfect companion!</p>
                                </td>
                            </tr>
                            <tr>
                                <td style='background-color: #f9f9f9; padding: 20px; text-align: center; border-top: 1px solid #eeeeee;'>
                                    <p style='margin: 0; color: #888888; font-size: 14px;'>Best Regards,<br><strong style='color: #2c1a0e;'>🐾 Paws Store Team</strong></p>
                                    <p style='margin: 10px 0 0 0; color: #aaaaaa; font-size: 12px;'>© " . date('Y') . " Paws Store. Made with love in India.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: Paws Store <warishali105@gmail.com>\r\n";

            if (!empty($to)) {
                @mail($to, $subject, $message, $headers);
            }

            // Send Cancelled WhatsApp
            $env = parse_ini_file('.env');
            $instance_id = $env['ULTRAMSG_INSTANCE_ID'] ?? '';
            $token = $env['ULTRAMSG_TOKEN'] ?? '';
            $phone = $_SESSION['user']['phone'] ?? '';
            $clean_phone = preg_replace('/[^0-9]/', '', $phone);

            if (!empty($instance_id) && !empty($token) && strlen($clean_phone) >= 10) {
                if (strlen($clean_phone) == 10) {
                    $clean_phone = "91" . $clean_phone;
                }
                $wa_body = "🐾 *Paws Store*\n\nHello *" . htmlspecialchars($username) . "*,\n\nYour order *#" . htmlspecialchars($cancel_num) . "* has been successfully cancelled.\n\n*Refund Initiated:* Your refund will reflect in your original payment method within 3-5 business days.\n\nIf you change your mind, we're always here to help you find your perfect companion!";

                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => http_build_query(["token" => $token, "to" => "+" . $clean_phone, "body" => $wa_body]),
                    CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ]);
                curl_exec($curl);
                curl_close($curl);
            }
        }
    }

    $stmt = $pdo->prepare("
        SELECT o.*, p.name AS pet_name, p.price AS pet_price, p.image AS pet_image, pm.transaction_id, pm.payment_method
        FROM orders o
        LEFT JOIN pets p ON o.pet_id = p.id
        LEFT JOIN payments pm ON o.id = pm.order_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user']['id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .history-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .history-header {
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #2c1a0e 0%, #5c3d2e 100%);
            padding: 50px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .history-header h1 {
            font-family: 'Playfair Display', serif;
            color: #ffffff;
            margin-bottom: 15px;
            font-size: 42px;
            letter-spacing: 0.5px;
        }

        .history-header p {
            color: #f0f0f0;
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .orders-grid {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .order-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e0e0e0;
            padding: 28px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #b5860d, #d4af37);
            transform: translateX(-100%);
            transition: transform 0.4s ease;
        }

        .order-card:hover {
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            transform: translateY(-4px);
            border-color: #b5860d;
        }

        .order-card:hover::before {
            transform: translateX(0);
        }

        .order-card h3 {
            margin: 0 0 16px 0;
            font-size: 22px;
            color: #2c1a0e;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .order-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
        }

        .order-meta span {
            font-size: 13px;
            color: #555;
            padding: 8px 14px;
            border-radius: 16px;
            background: linear-gradient(135deg, #f9f6f1 0%, #f2ede4 100%);
            font-weight: 500;
        }

        .order-info {
            background: #fafafa;
            border-radius: 12px;
            padding: 16px;
            margin: 16px 0;
            border: 1px solid #f0f0f0;
        }

        .order-info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }

        .order-info-label {
            color: #666;
            font-weight: 600;
        }

        .order-info-value {
            color: #2c1a0e;
            font-weight: 700;
        }

        .status-badge {
            padding: 6px 14px !important;
            font-weight: 600;
            border-radius: 20px;
            font-size: 13px !important;
            display: inline-block;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .status-Confirmed {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
            color: #1565c0 !important;
        }

        .status-Shipped {
            background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%) !important;
            color: #2e7d32 !important;
        }

        .status-Out\ for\ Delivery {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%) !important;
            color: #e65100 !important;
        }

        .status-Delivered {
            background: linear-gradient(135deg, #e8f5e9 0%, #a5d6a7 100%) !important;
            color: #1b5e20 !important;
        }

        .status-Cancelled {
            background: linear-gradient(135deg, #ffebee 0%, #ef9a9a 100%) !important;
            color: #b71c1c !important;
        }

        .order-search-input {
            width: 100%;
            padding: 14px 24px;
            border-radius: 28px;
            border: 2px solid #e0e0e0;
            outline: none;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .order-search-input:focus {
            border-color: #b5860d;
            background: #ffffff;
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.2);
        }

        .order-details {
            border-top: 1px solid #eee;
            padding-top: 16px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-row strong {
            color: #2c1a0e;
            font-weight: 700;
        }

        .no-orders {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-orders a {
            display: inline-block;
            background: #b5860d;
            color: #fff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 24px;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .no-orders a:hover {
            background: #9a7210;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.2);
        }

        .btn-cancel-order {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff5252 100%);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 107, 107, 0.25);
        }

        .btn-cancel-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        .btn-reorder {
            background: linear-gradient(135deg, #26c485 0%, #1da365 100%);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(38, 196, 133, 0.25);
        }

        .btn-reorder:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(38, 196, 133, 0.4);
        }

        .btn-invoice {
            background: linear-gradient(135deg, #5c3d2e 0%, #2c1a0e 100%);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            box-shadow: 0 4px 12px rgba(44, 26, 14, 0.25);
        }

        .btn-invoice:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(44, 26, 14, 0.4);
        }

        .order-actions {
            margin-top: auto;
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-start;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .star-rating {
            display: inline-flex;
            gap: 4px;
            font-size: 22px;
            margin-top: 8px;
        }

        .star {
            color: #e8e0d4;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .star.hover,
        .star.active {
            color: #b5860d;
        }

        .star:hover {
            transform: scale(1.15);
        }

        .refund-notice {
            background: #e0f7fa;
            border: 1px solid #b2ebf2;
            color: #006064;
            padding: 14px 18px;
            border-radius: 8px;
            margin-top: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Order Details Modal */
        .order-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
            padding: 20px;
            animation: fadeIn 0.3s ease;
        }

        .order-modal.show {
            display: flex;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .order-modal-content {
            background: white;
            border-radius: 16px;
            padding: 0;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
        }

        @keyframes slideUp {
            from {
                transform: translateY(40px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .order-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #2c1a0e 0%, #5c3d2e 100%);
            padding: 28px;
            color: white;
            flex-shrink: 0;
            border-radius: 16px 16px 0 0;
        }

        .order-modal-header h2 {
            color: white;
            margin: 0;
            font-size: 26px;
            font-family: 'Playfair Display', serif;
            letter-spacing: 0.5px;
        }

        .modal-close-btn {
            background: none;
            border: none;
            font-size: 32px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.7);
            padding: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .modal-close-btn:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(90deg);
        }

        /* Status Timeline in Modal */
        .status-timeline {
            margin: 30px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0;
            padding: 20px;
            background: linear-gradient(135deg, #fafafa 0%, #f5f2ed 100%);
            border-radius: 16px;
            border: 1px solid #f0f0f0;
        }

        .timeline-step {
            display: flex;
            gap: 20px;
            margin-bottom: 8px;
            position: relative;
            padding-left: 0;
            width: 100%;
            max-width: 100%;
            align-items: flex-start;
            opacity: 0.6;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .timeline-step.active,
        .timeline-step.completed {
            opacity: 1;
        }

        .timeline-step::before {
            content: '';
            position: absolute;
            left: 28px;
            top: 56px;
            width: 2px;
            height: 54px;
            background: linear-gradient(to bottom, #ddd, transparent);
        }

        .timeline-step.completed::before {
            background: linear-gradient(to bottom, #4caf50, #81c784);
        }

        .timeline-step:last-child::before {
            display: none;
        }

        .timeline-marker {
            width: 56px;
            height: 56px;
            background: #f5f2eb;
            border-radius: 50%;
            border: 3px solid #e8e0d4;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            font-size: 24px;
            position: relative;
        }

        .timeline-step.completed .timeline-marker {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            border-color: #4caf50;
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.25);
            color: white;
        }

        .timeline-step.active .timeline-marker {
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            border-color: #b5860d;
            box-shadow: 0 8px 24px rgba(181, 134, 13, 0.3);
            animation: timelinePulse 2s infinite;
            color: white;
            font-weight: bold;
        }

        @keyframes timelinePulse {

            0%,
            100% {
                box-shadow: 0 8px 24px rgba(181, 134, 13, 0.3);
                transform: scale(1);
            }

            50% {
                box-shadow: 0 10px 32px rgba(181, 134, 13, 0.45);
                transform: scale(1.06);
            }
        }

        .timeline-content {
            flex: 1;
            padding: 12px 16px;
            background: #ffffff;
            border-radius: 12px;
            border-left: 4px solid #e8e0d4;
            transition: all 0.4s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .timeline-step.completed .timeline-content {
            background: linear-gradient(135deg, #e8f5e9 0%, #f1f8e9 100%);
            border-left-color: #4caf50;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.12);
        }

        .timeline-step.active .timeline-content {
            background: linear-gradient(135deg, #fff9e6 0%, #fffde7 100%);
            border-left-color: #b5860d;
            box-shadow: 0 6px 16px rgba(181, 134, 13, 0.2);
        }

        .timeline-content h4 {
            margin: 0 0 6px 0;
            color: #2c1a0e;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .timeline-content p {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            font-weight: 500;
        }

        .order-modal-body {
            padding: 28px;
            background: #fafafa;
            overflow-y: auto;
            flex: 1;
        }

        .modal-order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background: linear-gradient(135deg, #f9f6f1 0%, #f2ede4 100%);
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #e8e0d4;
        }

        .modal-order-header h3 {
            margin: 0;
            color: #2c1a0e;
            font-size: 16px;
            font-weight: 700;
        }

        .modal-order-header span {
            background: white;
            padding: 6px 14px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 600;
            color: #666;
            border: 1px solid #e0e0e0;
        }

        .modal-order-amount {
            display: flex;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.2);
        }

        .modal-order-amount div {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
        }

        .modal-order-amount-label {
            font-size: 13px;
            font-weight: 600;
            opacity: 0.9;
            margin-bottom: 4px;
        }

        .modal-order-amount-value {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .modal-section-title {
            font-size: 15px;
            font-weight: 700;
            color: #2c1a0e;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e0e0e0;
            letter-spacing: 0.3px;
        }

        .modal-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .modal-detail-row:last-child {
            border-bottom: none;
        }

        .modal-detail-label {
            color: #666;
            font-weight: 600;
            font-size: 14px;
        }

        .modal-detail-value {
            color: #2c1a0e;
            font-weight: 600;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .history-header {
                padding: 35px 20px;
                margin-bottom: 25px;
            }

            .history-header h1 {
                font-size: 32px;
                margin-bottom: 10px;
            }

            .history-header p {
                font-size: 14px;
            }

            .order-card {
                padding: 20px;
            }

            .order-card h3 {
                font-size: 18px;
            }

            .order-modal-content {
                margin: 0;
                border-radius: 12px;
                max-height: 95vh;
            }

            .order-modal-header h2 {
                font-size: 22px;
            }

            .order-modal-header {
                padding: 20px;
            }

            .order-modal-body {
                padding: 20px;
            }

            .modal-detail-section h3 {
                font-size: 14px;
            }

            .btn-cancel-order,
            .btn-reorder,
            .btn-invoice {
                padding: 8px 14px;
                font-size: 13px;
            }

            .order-actions {
                gap: 8px;
            }

            .status-timeline {
                padding: 16px;
                margin: 20px 0;
            }

            .timeline-step {
                margin-bottom: 12px;
            }

            .timeline-marker {
                width: 48px;
                height: 48px;
                font-size: 20px;
            }

            .timeline-step::before {
                left: 24px;
                height: 48px;
                top: 48px;
            }

            .timeline-content {
                padding: 10px 14px;
                border-radius: 10px;
            }

            .timeline-content h4 {
                font-size: 15px;
                margin-bottom: 4px;
            }

            .timeline-content p {
                font-size: 13px;
            }

            .modal-order-amount-value {
                font-size: 24px;
            }

            .modal-section-title {
                font-size: 14px;
                margin-bottom: 14px;
            }
        }
    </style>
</head>

<body>
    <nav class="fk-nav-header">
        <div class="fk-nav-top">
            <a href="index.php" class="fk-logo">🐾 Paws Store</a>
            <div class="fk-nav-right" style="margin-left: auto;">
                <a href="index.php" class="fk-cart-btn" style="margin-right: 15px;">
                    <span class="fk-cart-icon">🏠</span> Home
                </a>
                <a href="wishlist.php" class="fk-cart-btn" style="margin-right: 15px;">
                    <span class="fk-cart-icon">🤍</span> Wishlist
                </a>
                <a href="cart.php" class="fk-cart-btn">
                    <span class="fk-cart-icon">🛒</span> Cart
                    <span id="cart-count" class="cart-count">0</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="ps-wrap">
        <div class="history-container">
            <div class="history-header">
                <h1>Your Order History</h1>
                <p>Track your past orders and see their current status.</p>
                <a href="track_order.php" style="display: inline-block; margin-top: 15px; color: #b5860d; text-decoration: none; font-weight: 600; padding: 10px 20px; border: 2px solid #b5860d; border-radius: 6px; transition: all 0.3s;">
                    🚚 Track Pet Delivery
                </a>
                <?php if (!empty($orders)): ?>
                    <div style="margin-top: 20px; max-width: 400px; margin-left: auto; margin-right: auto; position: relative;">
                        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 16px; color: #888;">🔍</span>
                        <input type="text" id="order-search" class="order-search-input" placeholder="Search by Order ID, Pet Name or Status..." style="padding-left: 45px;">
                    </div>
                <?php endif; ?>
            </div>


            <?php if (empty($orders)): ?>
                <div class="no-orders">
                    <h2>No orders yet</h2>
                    <p>You haven’t placed any orders yet. Browse pets and place your first order!</p>
                    <a href="index.php" style="margin-top: 10px;">Browse Pets</a>
                </div>
            <?php else: ?>
                <div class="orders-grid">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card" onclick="openOrderDetail(event, '<?php echo htmlspecialchars($order['order_number']); ?>', '<?php echo htmlspecialchars($order['pet_id'] ?? ''); ?>')">
                            <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 15px;">
                                <?php if (!empty($order['pet_image'])): ?>
                                    <img src="<?php echo htmlspecialchars($order['pet_image']); ?>" style="width: 60px; height: 60px; border-radius: 10px; object-fit: cover;">
                                <?php endif; ?>
                                <div>
                                    <h3 style="margin-bottom: 5px; font-size: 18px; color: #2c1a0e;"><?php echo htmlspecialchars($order['pet_name'] ?? 'Pet'); ?></h3>
                                    <div style="color: #666; font-size: 13px; font-weight: 600;">Qty: <?php echo htmlspecialchars($order['quantity']); ?></div>
                                    <div style="color: #999; font-size: 12px; margin-top: 4px;">Tap to view details</div>
                                    <?php if ($order['order_status'] === 'Delivered'): ?>
                                        <div class="star-rating" data-id="<?php echo htmlspecialchars($order['order_number'] . '_' . $order['pet_id']); ?>" data-name="<?php echo htmlspecialchars($order['pet_name'] ?? 'Pet'); ?>" title="Rate this pet" style="margin-top: 6px;">
                                            <span class="star" data-val="1">★</span>
                                            <span class="star" data-val="2">★</span>
                                            <span class="star" data-val="3">★</span>
                                            <span class="star" data-val="4">★</span>
                                            <span class="star" data-val="5">★</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="order-meta">
                                <span><strong>Order ID:</strong> #<?php echo htmlspecialchars($order['id']); ?></span>
                                <span><strong>Date:</strong> <?php echo date('d M Y', strtotime($order['created_at'])); ?></span>
                                <span><strong>Status:</strong>
                                    <?php if ($order['order_status'] === 'Cancelled'): ?>
                                        <span class="status-badge status-Cancelled">🚫 Cancelled</span>
                                    <?php elseif ($order['order_status'] === 'Out for Delivery'): ?>
                                        <span class="status-badge status-Out\ for\ Delivery">🚚 Out for Delivery</span>
                                    <?php elseif ($order['order_status'] === 'Delivered'): ?>
                                        <span class="status-badge status-Delivered">✅ Delivered</span>
                                    <?php elseif ($order['order_status'] === 'Confirmed'): ?>
                                        <span class="status-badge status-Confirmed">📦 Confirmed</span>
                                    <?php elseif ($order['order_status'] === 'Shipped'): ?>
                                        <span class="status-badge status-Shipped">📤 Shipped</span>
                                    <?php else: ?>
                                        <span class="status-badge status-<?php echo htmlspecialchars($order['order_status']); ?>"><?php echo htmlspecialchars($order['order_status']); ?></span>
                                    <?php endif; ?>
                                </span>
                                <?php if (in_array($order['order_status'], ['Confirmed', 'Shipped'])): ?>
                                    <span style="color: #0c5460; background: #e0f7fa;"><strong>Est. Delivery:</strong> <?php echo date('d M Y', strtotime($order['created_at'] . ' + 5 days')); ?></span>
                                <?php elseif (in_array($order['order_status'], ['Out for Delivery', 'Delivered']) && !empty($order['delivery_date'])): ?>
                                    <span style="color: #e65100; background: #fff3e0;"><strong>📅 Delivery:</strong> <?php echo date('d M Y', strtotime($order['delivery_date'])); ?> @ <?php echo htmlspecialchars($order['delivery_time'] ?? 'TBD'); ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="order-details">
                                <div class="detail-row">
                                    <strong>Amount Paid:</strong>
                                    <span style="color: #b5860d; font-weight: 700; font-size: 16px;">₹<?php echo number_format($order['total_amount']); ?></span>
                                </div>
                                <div class="detail-row" style="flex-direction: column; gap: 5px;">
                                    <strong>Delivery Address:</strong>
                                    <span style="font-size: 14px; color: #555; line-height: 1.4;">
                                        <?php
                                        $address = $order['shipping_address'] ?? '';
                                        $decoded = json_decode($address, true);
                                        if (is_string($decoded)) {
                                            $decoded = json_decode($decoded, true);
                                        }
                                        $address_array = is_array($decoded) ? $decoded : [$address];
                                        echo htmlspecialchars(implode(', ', $address_array));
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <?php if ($order['order_status'] === 'Cancelled'): ?>
                                <div class="refund-notice" style="padding: 10px; margin-top: 10px;">
                                    <span style="font-size: 20px;">💸</span>
                                    <div style="font-size: 13px;">
                                        <strong>Refund Initiated!</strong> ₹<?php echo number_format($order['total_amount']); ?> will reflect in your account within 3-5 days.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="order-actions" style="gap: 10px; margin-top: auto; border-top: 1px solid #eee; padding-top: 15px;" onclick="event.stopPropagation();">
                                <?php if (in_array($order['order_status'], ['Shipped', 'Out for Delivery'])): ?>
                                    <a href="track_order.php?ref=<?php echo urlencode($order['order_number']); ?>&pet_id=<?php echo urlencode($order['pet_id']); ?>" class="btn-track" style="flex: 1; min-width: 150px; padding: 8px; text-align: center; background: #7b1fa2; color: white; text-decoration: none; border-radius: 6px; box-sizing: border-box; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#6a1b9a'" onmouseout="this.style.background='#7b1fa2'">🚚 Track Delivery</a>
                                <?php endif; ?>
                                <?php if (!in_array($order['order_status'], ['Shipped', 'Out for Delivery', 'Delivered', 'Cancelled'])): ?>
                                    <form method="POST" style="margin: 0; flex: 1; min-width: 150px;">
                                        <input type="hidden" name="cancel_order_number" value="<?php echo $order['order_number']; ?>">
                                        <button type="submit" class="btn-cancel-order" style="width: 100%;" onclick="if(this.innerText === 'Cancel Order') { this.innerText = 'Confirm Cancel'; this.style.backgroundColor = '#852029'; setTimeout(() => { this.innerText = 'Cancel Order'; this.style.backgroundColor = ''; }, 3000); return false; }">Cancel Order</button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" class="btn-reorder" style="flex: 1; min-width: 150px; padding: 8px;" onclick="reorderPet('<?php echo $order['pet_id']; ?>', '<?php echo addslashes(htmlspecialchars($order['pet_name'] ?? 'Pet')); ?>', <?php echo (float)($order['pet_price'] ?? 0); ?>, '<?php echo addslashes(htmlspecialchars($order['pet_image'] ?? '')); ?>', <?php echo (int)$order['quantity']; ?>)">Reorder</button>
                                <?php if ($order['order_status'] !== 'Cancelled'): ?>
                                    <a href="invoice.php?order_id=<?php echo urlencode($order['order_number']); ?>" class="btn-invoice" style="flex: 1; min-width: 150px; text-align: center; padding: 8px; box-sizing: border-box;">Invoice</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="no-matching-orders" style="display: none; text-align: center; padding: 40px; background: #fff; border-radius: 14px; border: 1px solid #e8e0d4; margin-top: 20px;">
                    <h3 style="color: #2c1a0e; margin-bottom: 10px;">No matching orders found</h3>
                    <p style="color: #666;">Try adjusting your search criteria.</p>
                </div>
            <?php endif; ?>

            <!-- Order Detail Modal -->
            <div id="orderDetailModal" class="order-modal" onclick="closeOrderDetail(event)">
                <div class="order-modal-content" onclick="event.stopPropagation()">
                    <div class="order-modal-header">
                        <h2 id="modalOrderTitle">Order Details</h2>
                        <button class="modal-close-btn" onclick="closeOrderDetail()">✕</button>
                    </div>

                    <div id="modalContent">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
            </div>

        </div>
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

        function reorderPet(id, name, price, image, quantity) {
            fetch('cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'add',
                    id: id,
                    quantity: quantity
                })
            }).then(response => response.json()).then(data => {
                showToast(name + " added to cart!", '🛒');
                setTimeout(() => {
                    window.location.href = 'cart.php';
                }, 1200);
            });
        }

        function reorderMultiple(itemsJson) {
            try {
                const items = JSON.parse(itemsJson);
                let addedCount = 0;

                function addNextItem(index) {
                    if (index >= items.length) {
                        showToast(`✅ ${addedCount} items added to cart!`, '🛒');
                        setTimeout(() => {
                            window.location.href = 'cart.php';
                        }, 1200);
                        return;
                    }

                    const item = items[index];
                    fetch('cart_action.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'add',
                            id: item.pet_id,
                            quantity: item.quantity
                        })
                    }).then(response => response.json()).then(data => {
                        addedCount++;
                        addNextItem(index + 1);
                    }).catch(err => {
                        console.error('Error adding item:', err);
                        addNextItem(index + 1);
                    });
                }

                addNextItem(0);
            } catch (err) {
                console.error('Error parsing items:', err);
                showToast('Error adding items to cart', '⚠️');
            }
        }

        // ORDER DETAIL MODAL FUNCTIONS
        function openOrderDetail(event, orderNumber, petId) {
            event.preventDefault();

            // Get the order card data
            const card = event.currentTarget;
            const petName = card.querySelector('h3').textContent;
            const amount = card.querySelector('[style*="color: #b5860d"]')?.textContent || '₹0';
            const statusBadge = card.querySelector('.status-badge')?.textContent || 'Unknown';

            // Create the modal content
            const modalContent = document.getElementById('modalContent');
            const statusSteps = getStatusSteps(statusBadge.trim());

            modalContent.innerHTML = `
                <div class="order-modal-body">
                    <div class="modal-order-header">
                        <h3>${petName}</h3>
                        <span>${statusBadge}</span>
                    </div>

                    <div class="modal-order-amount">
                        <div>
                            <div class="modal-order-amount-label">Total Amount</div>
                            <div class="modal-order-amount-value">${amount}</div>
                        </div>
                    </div>

                    <div class="modal-section-title">📅 ORDER STATUS TIMELINE</div>
                    <div class="status-timeline">
                        ${statusSteps}
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid #e8e0d4;">
                        <a href="track_order.php?ref=${orderNumber}&pet_id=${petId}" class="btn-track" style="flex: 1; padding: 12px; text-align: center; background: linear-gradient(135deg, #7b1fa2 0%, #6a1b9a 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(123, 31, 162, 0.25);" onmouseover="this.style.transform='translateY(-2px); this.style.boxShadow='0 6px 20px rgba(123, 31, 162, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(123, 31, 162, 0.25)'">🚚 Full Tracking</a>
                        <a href="invoice.php?order_id=${orderNumber}" class="btn-invoice" style="flex: 1; padding: 12px; text-align: center; background: linear-gradient(135deg, #2c1a0e 0%, #5c3d2e 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(44, 26, 14, 0.25);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(44, 26, 14, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(44, 26, 14, 0.25)'">📄 Invoice</a>
                    </div>
                </div>
            `;

            document.getElementById('modalOrderTitle').textContent = `Order #${orderNumber}`;
            document.getElementById('orderDetailModal').classList.add('show');
        }

        function closeOrderDetail(event) {
            if (event && event.target !== document.getElementById('orderDetailModal')) {
                return;
            }
            document.getElementById('orderDetailModal').classList.remove('show');
        }

        function getStatusSteps(status) {
            const steps = [{
                    name: 'Order Confirmed',
                    emoji: '📦',
                    status: 'confirmed'
                },
                {
                    name: 'Shipped',
                    emoji: '📤',
                    status: 'shipped'
                },
                {
                    name: 'Out for Delivery',
                    emoji: '🚚',
                    status: 'outForDelivery'
                },
                {
                    name: 'Delivered',
                    emoji: '✅',
                    status: 'delivered'
                }
            ];

            let statusMap = {
                '📦 Confirmed': 'confirmed',
                'Confirmed': 'confirmed',
                '📤 Shipped': 'shipped',
                'Shipped': 'shipped',
                '🚚 Out for Delivery': 'outForDelivery',
                'Out for Delivery': 'outForDelivery',
                '✅ Delivered': 'delivered',
                'Delivered': 'delivered',
                '🚫 Cancelled': 'cancelled',
                'Cancelled': 'cancelled'
            };

            let currentStatus = statusMap[status] || 'confirmed';
            let isCancelled = status.includes('Cancelled') || status.includes('🚫');

            if (isCancelled) {
                return `
                    <div class="timeline-step completed" style="opacity: 0.6;">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h4>❌ Cancelled</h4>
                            <p>This order has been cancelled</p>
                        </div>
                    </div>
                `;
            }

            const statusOrder = ['confirmed', 'shipped', 'outForDelivery', 'delivered'];
            const currentIndex = statusOrder.indexOf(currentStatus);

            let html = '';
            steps.forEach((step, index) => {
                let isCompleted = index < currentIndex;
                let isActive = index === currentIndex;
                let isFuture = index > currentIndex;

                html += `
                    <div class="timeline-step ${isCompleted ? 'completed' : ''} ${isActive ? 'active' : ''}">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <h4>${step.emoji} ${step.name}</h4>
                            <p>${isCompleted ? '✓ Completed' : isActive ? '• In Progress' : '○ Pending'}</p>
                        </div>
                    </div>
                `;
            });

            return html;
        }

        // Close modal when pressing Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeOrderDetail();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($success_message)): ?>
                showToast("<?php echo addslashes($success_message); ?>", "🚫");
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                showToast("<?php echo addslashes($error_message); ?>", "⚠️");
            <?php endif; ?>

            const currentUserId = '<?php echo isset($_SESSION["user"]["id"]) ? $_SESSION["user"]["id"] : "guest"; ?>';

            function updateCartCount(count) {
                const cartCountElement = document.getElementById('cart-count');
                if (cartCountElement) {
                    cartCountElement.textContent = count;
                    cartCountElement.style.display = count > 0 ? 'flex' : 'none';
                }
            }
            fetch('cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'get'
                })
            }).then(r => r.json()).then(d => {
                if (d.status === 'success') updateCartCount(d.cart_count);
            });

            // ORDER SEARCH FUNCTIONALITY
            const searchInput = document.getElementById('order-search');
            const orderCards = document.querySelectorAll('.order-card');
            const noOrdersMsg = document.getElementById('no-matching-orders');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    let visibleCount = 0;

                    orderCards.forEach(card => {
                        const orderText = card.textContent.toLowerCase();
                        if (orderText.includes(searchTerm)) {
                            card.style.display = 'flex';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (noOrdersMsg) {
                        noOrdersMsg.style.display = visibleCount === 0 ? 'block' : 'none';
                    }
                });
            }

            // STAR RATING SYSTEM
            const ratingKey = 'pawsRatings_' + currentUserId;
            let ratings = JSON.parse(localStorage.getItem(ratingKey)) || {};

            document.querySelectorAll('.star-rating').forEach(container => {
                const stars = container.querySelectorAll('.star');
                const uniqueId = container.getAttribute('data-id');
                const petName = container.getAttribute('data-name');

                // Load existing rating
                if (ratings[uniqueId]) {
                    stars.forEach(s => {
                        if (s.getAttribute('data-val') <= ratings[uniqueId]) {
                            s.classList.add('active');
                        }
                    });
                }

                stars.forEach(star => {
                    star.addEventListener('mouseover', function() {
                        const val = this.getAttribute('data-val');
                        stars.forEach(s => {
                            s.classList.toggle('hover', s.getAttribute('data-val') <= val);
                        });
                    });

                    star.addEventListener('mouseout', function() {
                        stars.forEach(s => s.classList.remove('hover'));
                    });

                    star.addEventListener('click', function() {
                        const val = this.getAttribute('data-val');
                        ratings[uniqueId] = val;
                        localStorage.setItem(ratingKey, JSON.stringify(ratings));

                        stars.forEach(s => {
                            s.classList.toggle('active', s.getAttribute('data-val') <= val);
                        });
                        showToast(`You rated ${petName} ${val} stars!`, "⭐");
                    });
                });
            });
        });
    </script>
</body>

</html>