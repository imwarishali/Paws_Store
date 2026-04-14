<?php
require_once 'config.php';
require_once 'db.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Terms and Conditions for using Paws Store services." />
    <title>Terms & Conditions — Paws Store</title>
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
            <h1 class="legal-title">Terms & Conditions</h1>
            <p class="legal-date">Last Updated: April 14, 2026</p>

            <div class="legal-section">
                <h2 class="legal-h2">1. Agreement to Terms</h2>
                <p class="legal-p">
                    By accessing and using the Paws Store website, you accept and agree to be bound by and comply with these Terms and Conditions. If you do not agree to abide by the above, please do not use this service.
                </p>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">2. Use License</h2>
                <p class="legal-p">
                    Permission is granted to temporarily download one copy of the materials (information or software) on Paws Store's website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:
                </p>
                <ul class="legal-ul">
                    <li>Modifying or copying the materials</li>
                    <li>Using the materials for any commercial purpose or for any public display</li>
                    <li>Attempting to decompile or reverse engineer any software contained on the website</li>
                    <li>Removing any copyright or other proprietary notations from the materials</li>
                    <li>Transferring the materials to another person or "mirroring" the materials on any other server</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">3. Disclaimer</h2>
                <p class="legal-p">
                    The materials on the website are provided for informational purposes only. Paws Store does not warrant the accuracy, completeness, or usefulness of this information. Any reliance on the materials on this website is at your own risk.
                </p>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">4. Limitations</h2>
                <p class="legal-p">
                    In no event shall Paws Store or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on the website.
                </p>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">5. Accuracy of Materials</h2>
                <p class="legal-p">
                    The materials appearing on the Paws Store website could include technical, typographical, or photographic errors. Paws Store does not warrant that any of the materials on the website are accurate, complete, or current.
                </p>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">6. Links</h2>
                <p class="legal-p">
                    Paws Store has not reviewed all of the sites linked to its website and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by Paws Store of the site. Use of any such linked website is at the user's own risk.
                </p>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">7. Modifications</h2>
                <p class="legal-p">
                    Paws Store may revise these terms of service for the website at any time without notice. By using this website, you are agreeing to be bound by the then current version of these terms of service.
                </p>
            </div>

            <div class="legal-section">
                <h2 class="legal-h2">8. Contact Us</h2>
                <p class="legal-p">
                    If you have any questions about these Terms & Conditions, please contact us at:<br />
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