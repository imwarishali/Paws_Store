<?php
require_once 'config.php';
require_once 'db.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Privacy Policy for Paws Store. Learn how we protect your data and personal information." />
    <title>Privacy Policy — Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .legal-page {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .legal-title {
            font-family: "Playfair Display", serif;
            font-size: 36px;
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .legal-date {
            color: #999;
            font-size: 13px;
            margin-bottom: 30px;
        }

        .legal-section {
            margin-bottom: 32px;
        }

        .legal-h2 {
            font-family: "Playfair Display", serif;
            font-size: 22px;
            color: #2c1a0e;
            margin-bottom: 12px;
        }

        .legal-p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .legal-ul {
            color: #666;
            line-height: 1.8;
            margin-left: 24px;
            margin-bottom: 12px;
        }

        .legal-ul li {
            margin-bottom: 8px;
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
        <div class="legal-page">
            <h1 class="legal-title">Privacy Policy</h1>
            <p class="legal-date">Last Updated: April 14, 2026</p>

            <div class="legal-section">
                <h2 class="legal-h2">1. Introduction</h2>
                <p class="legal-p">
                    Paws Store ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.
                </p>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">2. Information We Collect</h2>
                <p class="legal-p">We may collect information about you in a variety of ways. The information we may collect on the Site includes:</p>
                <ul class="legal-ul">
                    <li><strong>Personal Data:</strong> Name, email address, phone number, shipping address, billing address</li>
                    <li><strong>Payment Information:</strong> Credit card numbers, debit card details, UPI information</li>
                    <li><strong>Account Information:</strong> Username, password, account preferences</li>
                    <li><strong>Browsing Data:</strong> IP address, browser type, pages visited, time spent on pages</li>
                    <li><strong>Device Information:</strong> Device type, operating system, device identifiers</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">3. How We Use Your Information</h2>
                <p class="legal-p">We use the information we collect in the following ways:</p>
                <ul class="legal-ul">
                    <li>Process your transactions and send related information</li>
                    <li>Send marketing and promotional communications (with your consent)</li>
                    <li>Respond to your inquiries and provide customer support</li>
                    <li>Improve our website and services</li>
                    <li>Prevent fraudulent transactions and enhance security</li>
                    <li>Comply with legal obligations</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">4. Security of Your Information</h2>
                <p class="legal-p">
                    We use administrative, technical, and physical security measures to protect your personal information. However, no method of transmission over the Internet or electronic storage is 100% secure. While we strive to use commercially acceptable means to protect your personal information, we cannot guarantee absolute security.
                </p>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">5. Contact Us</h2>
                <p class="legal-p">
                    If you have questions about this Privacy Policy, please contact us at:<br />
                    📧 Email: <a href="mailto:support@pawsstore.in">support@pawsstore.in</a><br />
                    📞 Phone: <a href="tel:+919798889456">+91 97988 89456</a>
                </p>
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