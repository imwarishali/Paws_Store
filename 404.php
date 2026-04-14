<?php
http_response_code(404);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Page not found. The page you're looking for doesn't exist." />
    <title>404 - Page Not Found — Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 70vh;
            text-align: center;
            padding: 40px 24px;
        }

        .error-code {
            font-family: "Playfair Display", serif;
            font-size: 120px;
            font-weight: 700;
            color: #b5860d;
            margin-bottom: 10px;
            line-height: 1;
        }

        .error-title {
            font-family: "Playfair Display", serif;
            font-size: 42px;
            color: #2c1a0e;
            margin-bottom: 15px;
        }

        .error-message {
            font-size: 18px;
            color: #666;
            max-width: 500px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .error-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary,
        .btn-secondary {
            padding: 14px 32px;
            border-radius: 28px;
            font-size: 15px;
            font-weight: 700;
            font-family: "Nunito", sans-serif;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #b5860d;
            color: white;
        }

        .btn-primary:hover {
            background: #9a7210;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.3);
        }

        .btn-secondary {
            background: transparent;
            color: #b5860d;
            border: 2px solid #b5860d;
        }

        .btn-secondary:hover {
            background: #b5860d;
            color: white;
            transform: translateY(-2px);
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
                </a>
            </div>
        </div>
    </nav>

    <div class="ps-wrap">
        <div class="error-container">
            <div class="error-icon">🦴</div>
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-message">
                Oops! It seems the page you're looking for has gone to fetch. Don't worry, we can help you find what you need.
            </p>
            <div class="error-buttons">
                <a href="index.php" class="btn-primary">Back to Home</a>
                <a href="index.php#pets" class="btn-secondary">Browse Pets</a>
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
                    <a href="contact.php">Contact</a>
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