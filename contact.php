<?php
require_once 'config.php';
require_once 'db.php';

$success = null;
$error = null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    // Validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Send email
        $to = 'support@pawsstore.in';
        $email_subject = "New Contact Form Submission: " . htmlspecialchars($subject);
        $email_message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>New Contact Message</title>
        </head>
        <body style='font-family: Arial, sans-serif; color: #333;'>
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>
            <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
            <hr>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . htmlspecialchars($email) . "\r\n";

        if (@mail($to, $email_subject, $email_message, $headers)) {
            $success = "Thank you for contacting us! We will get back to you shortly.";
        } else {
            $error = "There was an error sending your message. Please try again.";
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Contact Paws Store. Get in touch with our team for inquiries, support, and feedback." />
    <title>Contact Us — Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .contact-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 60px 24px;
        }

        .contact-title {
            font-family: "Playfair Display", serif;
            font-size: 42px;
            color: #2c1a0e;
            text-align: center;
            margin-bottom: 12px;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-top: 50px;
        }

        .contact-card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            border: 1px solid #e8e0d4;
            text-align: center;
        }

        .contact-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .contact-card h3 {
            font-family: "Playfair Display", serif;
            font-size: 20px;
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .contact-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .contact-card a {
            color: #b5860d;
            text-decoration: none;
            font-weight: 600;
        }

        .contact-form {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #e8e0d4;
            margin-top: 50px;
        }

        .contact-form h2 {
            font-family: "Playfair Display", serif;
            font-size: 28px;
            color: #2c1a0e;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c1a0e;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: "Nunito", sans-serif;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #b5860d;
            box-shadow: 0 0 0 2px rgba(181, 134, 13, 0.1);
        }

        .form-submit {
            background: #b5860d;
            color: white;
            border: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.3s;
        }

        .form-submit:hover {
            background: #9a7210;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <a href="cart.php" class="fk-cart-btn">
                    <span class="fk-cart-icon">🛒</span> Cart
                    <span id="cart-count" class="cart-count">0</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="ps-wrap">
        <div class="contact-container">
            <h1 class="contact-title">Get in Touch</h1>
            <p style="text-align: center; color: #666; font-size: 16px;">Have questions? We'd love to hear from you. Send us a message!</p>

            <div class="contact-grid">
                <div class="contact-card">
                    <div class="contact-icon">📞</div>
                    <h3>Call Us</h3>
                    <p>
                        <a href="tel:+919798889456">+91 97988 89456</a><br>
                        Available Mon-Sat, 10 AM - 6 PM IST
                    </p>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">📧</div>
                    <h3>Email Us</h3>
                    <p>
                        <a href="mailto:support@pawsstore.in">support@pawsstore.in</a><br>
                        We'll respond within 24 hours
                    </p>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">💬</div>
                    <h3>WhatsApp</h3>
                    <p>
                        <a href="https://wa.me/919798889456" target="_blank">Send WhatsApp Message</a><br>
                        Quick response via WhatsApp
                    </p>
                </div>
            </div>

            <div class="contact-form">
                <h2>Send us a Message</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>

                    <button type="submit" class="form-submit">Send Message</button>
                </form>
            </div>
        </div>
    </div>

    <footer id="contact">
        <div class="ps-footer">
            <div class="ps-footer-grid">
                <div>
                    <div class="ps-footer-brand">🐾 Paws Store</div>
                    <div class="ps-footer-tagline">Bringing joy home,<br />one paw at a time.</div>
                </div>
                <div class="ps-footer-col">
                    <h4>Quick Links</h4>
                    <a href="index.php">Home</a>
                    <a href="index.php#pets">Pets</a>
                    <a href="index.php#categories">Shop</a>
                    <a href="FAQ.php">FAQ</a>
                </div>
                <div class="ps-footer-col">
                    <h4>Support</h4>
                    <a href="FAQ.php">FAQ</a>
                    <a href="track_order.php">Track Order</a>
                </div>
                <div class="ps-footer-col">
                    <h4>Contact</h4>
                    <div class="ps-footer-contact">📞 <a href="tel:+919798889456" style="color: inherit; text-decoration: none;">+91 97988 89456</a></div>
                    <div class="ps-footer-contact">✉️ <a href="mailto:support@pawsstore.in" style="color: inherit; text-decoration: none;">support@pawsstore.in</a></div>
                </div>
            </div>
            <div class="ps-footer-bottom">
                © 2026 Paws Store. Made with 🐾 in India.
            </div>
        </div>
    </footer>
</body>

</html>