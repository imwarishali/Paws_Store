<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit();
}

// Get form data
$cart = json_decode($_POST['cart'], true);
$address = json_decode($_POST['address'], true);
$transaction_id = trim($_POST['razorpay_payment_id'] ?? '');
$razorpay_signature = trim($_POST['razorpay_signature'] ?? '');
$special_offer = $_POST['special_offer'] ?? 'none';

if (empty($transaction_id)) {
    die("Invalid Payment ID from Razorpay.");
}

$payment_method = $_POST['payment_method'] ?? 'Razorpay';


// Connect to database
require_once 'db.php';

$user_id = $_SESSION['user']['id'] ?? 1;

// Verify Razorpay Signature
$env = parse_ini_file('.env');
$keySecret = $env['RAZORPAY_KEY_SECRET'] ?? '';
$razorpay_order_id = $_POST['razorpay_order_id'] ?? '';

if ($keySecret && $razorpay_order_id && $transaction_id) {
    $expected_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $transaction_id, $keySecret);
    if (!hash_equals($expected_signature, $razorpay_signature)) {
        die("Payment signature verification failed.");
    }
}

// Calculate total securely on the server
$subtotal = 0;
if (!empty($cart)) {
    $pet_ids = array_column($cart, 'id');
    $placeholders = implode(',', array_fill(0, count($pet_ids), '?'));

    $price_stmt = $pdo->prepare("SELECT id, price FROM pets WHERE id IN ($placeholders)");
    $price_stmt->execute($pet_ids);
    $db_prices = [];
    while ($row = $price_stmt->fetch(PDO::FETCH_ASSOC)) {
        $db_prices[$row['id']] = $row['price'];
    }

    foreach ($cart as $item) {
        if (isset($db_prices[$item['id']])) {
            $subtotal += $db_prices[$item['id']] * ($item['quantity'] ?? 1);
        }
    }
}

$discount = 0;
if ($special_offer === 'firstTime') {
    $order_check_stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $order_check_stmt->execute([$user_id]);
    $previous_orders = $order_check_stmt->fetchColumn();

    if ($previous_orders == 0) {
        $discount = round($subtotal * 0.1);
    }
} else if ($special_offer === 'bulkDiscount' && count($cart) >= 2) {
    $discount = round($subtotal * 0.05);
} else if ($special_offer === 'freeVet') {
    $discount = 500;
} else if ($special_offer === 'save20' && $subtotal > 10000) {
    $discount = 2000;
}

$shipping = $subtotal > 5000 ? 0 : 500;
$tax = round(($subtotal - $discount) * 0.18);
$total = $subtotal - $discount + $shipping + $tax;

try {
    // 1. Insert into orders table
    $order_number = 'ORD' . time() . rand(100, 999);
    $shipping_address = is_array($address) ? implode(', ', $address) : (string)$address; // Store as a clean, comma-separated string

    $remaining_total = $total;
    $items_count = count($cart);
    $current_item = 0;

    foreach ($cart as $item) {
        $current_item++;
        $pet_id = $item['id'];
        $quantity = $item['quantity'] ?? 1;
        $price = $db_prices[$pet_id] ?? 0;

        $item_subtotal = $price * $quantity;
        if ($subtotal > 0) {
            $item_proportion = $item_subtotal / $subtotal;
            $item_total = round($total * $item_proportion);
        } else {
            $item_total = 0;
        }

        if ($current_item === $items_count) {
            $item_total = $remaining_total;
        }
        $remaining_total -= $item_total;

        $stmt = $pdo->prepare("
            INSERT INTO orders (order_number, user_id, pet_id, quantity, total_amount, shipping_address, order_status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Confirmed')
        ");

        $stmt->execute([
            $order_number,
            $user_id,
            $pet_id,
            $quantity,
            $item_total,
            $shipping_address,
        ]);

        $order_id = $pdo->lastInsertId();

        $payment_stmt = $pdo->prepare("
            INSERT INTO payments (order_id, transaction_id, payment_method, payment_status) 
            VALUES (?, ?, ?, 'Completed')
        ");
        $payment_stmt->execute([
            $order_id,
            $transaction_id,
            $payment_method
        ]);
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Send Confirmation Email
$to = $_SESSION['user']['email'] ?? '';
$subject = "Order Confirmed! - Paws Store [" . $order_number . "]";
$message = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Order Confirmation</title>
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
                            <h2 style='color: #2c1a0e; margin-top: 0;'>Thank you for your order, " . htmlspecialchars($_SESSION['user']['username'] ?? 'Customer') . "!</h2>
                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>We're excited to confirm that your order <strong>#" . $order_number . "</strong> has been successfully placed. Your furry friend will be delivered to you within 3-5 business days!</p>
                            
                            <div style='background-color: #fdfaf6; border: 1px solid #e8e0d4; border-radius: 8px; padding: 20px; margin: 30px 0;'>
                                <h3 style='margin-top: 0; color: #2c1a0e; border-bottom: 2px solid #b5860d; padding-bottom: 10px;'>Order Summary</h3>
                                <p style='margin: 10px 0; color: #555555;'><strong>Total Paid:</strong> <span style='color: #b5860d; font-weight: bold; font-size: 18px;'>₹" . number_format($total) . "</span></p>
                                <p style='margin: 10px 0; color: #555555;'><strong>Delivery Address:</strong> " . htmlspecialchars($shipping_address) . "</p>
                            </div>
                            
                            <p style='font-size: 16px; line-height: 1.5; color: #555555;'>You can track your order status anytime by logging into your account and checking your Order History.</p>
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

// Send Order Confirmed WhatsApp
$env = parse_ini_file('.env');
$instance_id = $env['ULTRAMSG_INSTANCE_ID'] ?? '';
$token = $env['ULTRAMSG_TOKEN'] ?? '';
$phone = $address['phone'] ?? $_SESSION['user']['phone'] ?? '';
$clean_phone = preg_replace('/[^0-9]/', '', $phone);

if (!empty($instance_id) && !empty($token) && strlen($clean_phone) >= 10) {
    if (strlen($clean_phone) == 10) {
        $clean_phone = "91" . $clean_phone;
    }
    $wa_body = "🐾 *Paws Store*\n\nThank you for your order, *" . htmlspecialchars($_SESSION['user']['username'] ?? 'Customer') . "*! 🎉\n\nYour order *#" . $order_number . "* has been successfully placed.\n\n*Total Paid:* ₹" . number_format($total) . "\n\nYour furry friend will be delivered to you within 3-5 business days. You can track your order status anytime by logging into your account.";

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

// Clear cart
unset($_SESSION['cart']); // If using session cart

// Redirect to success page
header("Location: payment_success.php?order_id=" . $order_number);
exit();
