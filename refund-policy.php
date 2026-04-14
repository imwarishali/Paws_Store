<?php
require_once 'config.php';
require_once 'db.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Refund and Return Policy for Paws Store. Learn about our pet guarantee and refund process." />
    <title>Refund Policy — Paws Store</title>
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
            <h1 class="legal-title">Refund & Return Policy</h1>
            <p class="legal-date">Last Updated: April 14, 2026</p>

            <div class="legal-section">
                <h2 class="legal-h2">Pet Satisfaction Guarantee</h2>
                <p class="legal-p">
                    At Paws Store, we stand behind the health and quality of every pet we deliver. If you're not completely satisfied with your pet or if there are any health concerns, we offer the following:
                </p>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">7-Day Health Guarantee</h2>
                <ul class="legal-ul">
                    <li>All pets come with a 7-day health guarantee from the date of delivery</li>
                    <li>If your pet shows signs of illness within 7 days of delivery, we will cover veterinary treatment costs</li>
                    <li>You must report any health issues within 24 hours of noticing them</li>
                    <li>A veterinary certificate is required for all health claims</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">Refund Process</h2>
                <ol class="legal-ul">
                    <li>Contact our support team at support@pawsstore.in with order details</li>
                    <li>Provide veterinary documentation or proof of the issue</li>
                    <li>Our team will review and respond within 24-48 hours</li>
                    <li>Approved refunds will be processed within 5-7 business days</li>
                </ol>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">Return Conditions</h2>
                <p class="legal-p">Please note that returns are subject to the following conditions:</p>
                <ul class="legal-ul">
                    <li>Pet must be returned in the same condition as received</li>
                    <li>All original documentation must be provided</li>
                    <li>Return shipping costs may apply for non-defective pets</li>
                    <li>We reserve the right to inspect the pet before processing refunds</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">Non-Refundable Items</h2>
                <p class="legal-p">The following are non-refundable:</p>
                <ul class="legal-ul">
                    <li>Pet food and accessories</li>
                    <li>Delivery charges for non-defective pets</li>
                    <li>Pets with natural behavioral characteristics</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">Contact Us</h2>
                <p class="legal-p">
                    For any refund or return inquiries, please contact us at:<br />
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