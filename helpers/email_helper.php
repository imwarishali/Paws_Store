<?php

/**
 * Email Helper Functions & Templates
 * Centralize all email operations
 */

require_once __DIR__ . '/../config.php';

/**
 * Send standard email with HTML template
 */
function sendEmail($to, $subject, $username, $title, $message_content, $is_security = false)
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $env = parse_ini_file(__DIR__ . '/.env');
    $system_email = $env['SYSTEM_EMAIL'] ?? SYSTEM_EMAIL_SENDER;

    $accent_color = $is_security ? '#dc2626' : '#b5860d';
    $header_bg = $is_security ? '#7c2d12' : '#2c1a0e';

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>{$title}</title>
    </head>
    <body style='margin: 0; padding: 0; font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; background-color: #faf7f2; color: #333333;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background-color: #faf7f2; padding: 20px;'>
            <tr>
                <td align='center'>
                    <table width='600' cellpadding='0' cellspacing='0' style='background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
                        <tr>
                            <td style='background-color: {$header_bg}; padding: 30px; text-align: center;'>
                                <h1 style='color: {$accent_color}; margin: 0; font-size: 28px; font-weight: normal;'>🐾 Paws Store</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 40px 30px;'>
                                <h2 style='color: #2c1a0e; margin-top: 0;'>{$title}</h2>
                                <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Hello " . htmlspecialchars($username) . ",</p>
                                <div style='background-color: #fdfaf6; border: 1px solid #e8e0d4; border-radius: 8px; padding: 20px; margin: 30px 0; text-align: center;'>
                                    {$message_content}
                                </div>
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
    $headers .= "From: Paws Store <" . $system_email . ">\r\n";

    return @mail($to, $subject, $message, $headers);
}

/**
 * Send OTP verification email
 */
function sendOTPEmail($to, $username, $otp)
{
    $title = "Verify Your Email";
    $content = "
        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Thank you for registering at Paws Store. To complete your registration, please use the following One-Time Password (OTP):</p>
        <h1 style='margin: 0; font-size: 36px; color: #b5860d; letter-spacing: 5px;'>{$otp}</h1>
        <p style='font-size: 14px; color: #888888; margin-top: 20px;'>This OTP is valid for 10 minutes only.</p>
    ";

    return sendEmail($to, "Verify Your Email - Paws Store", $username, $title, $content);
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($to, $username)
{
    $title = "Password Reset Successful";
    $content = "
        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Your password has been successfully reset. If you didn't make this change, please contact support immediately.</p>
        <p style='font-size: 14px; color: #888888;'>For security, never share your password with anyone.</p>
    ";

    return sendEmail($to, "Password Reset - Paws Store", $username, $title, $content, true);
}

/**
 * Send order status update email
 */
function sendOrderStatusEmail($to, $username, $order_number, $status)
{
    $status_messages = [
        'Shipped' => 'Your order has been <strong>shipped</strong> and is on its way to you! You can expect delivery soon.',
        'Delivered' => 'Your order has been <strong>delivered</strong> successfully! We hope you and your new furry friend share lots of joyful moments together.',
        'Cancelled' => 'Your order has been <strong>cancelled</strong>. A full refund has been initiated to your original payment method.'
    ];

    $status_message = $status_messages[$status] ?? 'Your order status has been updated.';
    $title = $status === 'Cancelled' ? 'Order Cancelled & Refund Initiated' : 'Order Status Update';

    $content = "
        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Great news about your order <strong>#" . htmlspecialchars($order_number) . "</strong>!</p>
        <p style='font-size: 15px; line-height: 1.6; color: #555555;'>{$status_message}</p>
        <p style='font-size: 14px; color: #888888;'>You can view your complete order details by logging into your account.</p>
    ";

    $subject = $status === 'Cancelled' ? "Order Cancelled & Refund - Paws Store [#" . $order_number . "]" : "Order Update: " . $status . " - Paws Store [#" . $order_number . "]";

    return sendEmail($to, $subject, $username, $title, $content);
}

/**
 * Send profile update notification
 */
function sendProfileUpdateEmail($to, $username)
{
    $title = "Profile Updated Successfully";
    $content = "
        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Your profile details have been successfully updated.</p>
        <p style='font-size: 14px; color: #888888;'>If you didn't make this change, please contact support immediately.</p>
    ";

    return sendEmail($to, "Profile Updated - Paws Store", $username, $title, $content, true);
}

/**
 * Send account deletion notification
 */
function sendAccountDeletedEmail($to, $username)
{
    $title = "Account Deleted";
    $content = "
        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Your Paws Store account has been permanently deleted by the administrator.</p>
        <p style='font-size: 14px; color: #888888;'>If you believe this was a mistake, please contact our support team.</p>
    ";

    return sendEmail($to, "Account Deleted - Paws Store", $username, $title, $content, true);
}

/**
 * Send welcome email
 */
function sendWelcomeEmail($to, $username)
{
    $title = "Welcome to Paws Store!";
    $content = "
        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>We are absolutely thrilled to have you join the Paws Store family! 🎉</p>
        <p style='font-size: 16px; line-height: 1.5; color: #555555;'>Your account has been successfully created. You can now explore our wide variety of lovely pets, save your favorites to your wishlist, and track your orders easily.</p>
        <p style='font-size: 14px; color: #888888;'>If you have any questions, our support team is always here for you.</p>
    ";

    return sendEmail($to, "Welcome to Paws Store! 🐾", $username, $title, $content);
}

/**
 * Send WhatsApp message
 */
function sendWhatsAppMessage($phone, $message)
{
    $env = parse_ini_file(__DIR__ . '/../.env');
    $instance_id = $env['ULTRAMSG_INSTANCE_ID'] ?? '';
    $token = $env['ULTRAMSG_TOKEN'] ?? '';

    $clean_phone = preg_replace('/[^0-9]/', '', $phone);

    if (empty($instance_id) || empty($token) || strlen($clean_phone) < 10) {
        return false;
    }

    // Add India country code if only 10 digits
    if (strlen($clean_phone) === 10) {
        $clean_phone = "91" . $clean_phone;
    }

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.ultramsg.com/" . $instance_id . "/messages/chat",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            "token" => $token,
            "to" => "+" . $clean_phone,
            "body" => $message
        ]),
        CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        error_log("WhatsApp API Error: " . $err);
        return false;
    }

    return true;
}
