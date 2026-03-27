<?php
session_start();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Frequently Asked Questions — Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ps-faq-header {
            text-align: center;
            padding: 60px 20px 20px;
        }

        .ps-faq-header h1 {
            font-family: "Playfair Display", serif;
            font-size: 42px;
            color: #2c1a0e;
            margin-bottom: 15px;
        }

        .ps-faq-header p {
            font-size: 18px;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .ps-faq-container {
            max-width: 800px;
            margin: 40px auto 80px;
            padding: 0 20px;
        }

        .faq-item {
            background: #fff;
            border: 1px solid #e8e0d4;
            border-radius: 12px;
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            border-color: #d8c094;
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.08);
        }

        .faq-question {
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-weight: 700;
            font-size: 18px;
            color: #2c1a0e;
            user-select: none;
        }

        .faq-icon {
            font-size: 24px;
            color: #b5860d;
            transition: transform 0.3s ease;
            font-weight: 400;
        }

        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, padding 0.3s ease;
            background: #fdfaf6;
            color: #555;
            line-height: 1.6;
            font-size: 16px;
            padding: 0 24px;
        }

        .faq-item.active .faq-answer {
            max-height: 300px;
            padding: 0 24px 20px 24px;
            border-top: 1px solid #f0eade;
        }

        .faq-item.active .faq-icon {
            transform: rotate(45deg);
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
        <div class="ps-faq-header">
            <h1>Frequently Asked Questions</h1>
            <p>Have questions about adopting a pet, our delivery process, or pet care? Find all the answers you need right here.</p>
        </div>

        <div class="ps-faq-container">
            <!-- FAQ Item 1 -->
            <div class="faq-item">
                <div class="faq-question">What is Paws Store? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>Paws Store is India's most trusted online pet store. We connect loving families with healthy, vet-checked pets from verified breeders across the country. Our goal is to make bringing a pet home safe, joyful, and hassle-free.
                </div>
            </div>
            <!-- FAQ Item 2 -->
            <div class="faq-item">
                <div class="faq-question">Are the pets healthy and vaccinated? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>Yes, absolutely! The health of our pets is our top priority. Every pet listed on Paws Store is thoroughly vet-checked, fully vaccinated up to their age, and dewormed before they are handed over to you.
                </div>
            </div>
            <!-- FAQ Item 3 -->
            <div class="faq-item">
                <div class="faq-question">How does the delivery process work? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>We offer safe, PAN India door-to-door delivery. Once you place an order, we arrange specialized, climate-controlled pet transport to ensure your new companion travels comfortably and safely to your doorstep. Live tracking is provided for your peace of mind.
                </div>
            </div>
            <!-- FAQ Item 4 -->
            <div class="faq-item">
                <div class="faq-question">What payment methods do you accept? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>We accept secure payments via multiple methods including UPI, QR Code scanning, and direct Bank Transfers. All transactions are highly secured to protect your information.
                </div>
            </div>
            <!-- FAQ Item 5 -->
            <div class="faq-item">
                <div class="faq-question">Do I get any vet support after purchasing a pet? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>Yes! To help you get started on the right foot, we provide a free initial vet consultation with every first purchase. Our expert pet care team is also available 24/7 for ongoing support and advice.
                </div>
            </div>
            <!-- FAQ Item 6 -->
            <div class="faq-item">
                <div class="faq-question">How do you verify your breeders? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>We have a strict, multi-step verification process for all breeders. This includes background checks, facility inspections to ensure ethical breeding practices, and verifying their track record of raising healthy pets.
                </div>
            </div>
            <!-- FAQ Item 7 -->
            <div class="faq-item">
                <div class="faq-question">What types of pets do you sell? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>We currently offer a wide variety of dogs, cats, freshwater and saltwater fish, as well as exotic and friendly birds. You can browse them all in our categorized shop.
                </div>
            </div>
            <!-- FAQ Item 8 -->
            <div class="faq-item">
                <div class="faq-question">How do I track my order? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>You can easily track your pet's delivery status by navigating to the 'Orders' section in your account profile. We also send updates directly to your registered email and phone number.
                </div>
            </div>
            <!-- FAQ Item 9 -->
            <div class="faq-item">
                <div class="faq-question">Can I return or exchange a pet? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>Due to the sensitive nature of live animals and health protocols, we generally do not accept returns. However, if there are severe health issues documented by a verified vet within 48 hours of delivery, please contact our support team immediately for assistance.
                </div>
            </div>
            <!-- FAQ Item 10 -->
            <div class="faq-item">
                <div class="faq-question">Do you sell pet food and accessories? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>Currently, our primary focus is on connecting you with the perfect pet companion. However, we are actively working on expanding our catalog to include premium pet food, toys, and accessories very soon!
                </div>
            </div>
            <!-- FAQ Item 11 -->
            <div class="faq-item">
                <div class="faq-question">Is it safe to transport pets over long distances? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>Absolutely. We partner with specialized pet relocation experts who ensure that your pet travels in well-ventilated, secure, and stress-free environments, with scheduled stops for food and water on long journeys.
                </div>
            </div>
            <!-- FAQ Item 12 -->
            <div class="faq-item">
                <div class="faq-question">How do I contact customer support? <span class="faq-icon">+</span></div>
                <div class="faq-answer">
                    <br>You can reach our dedicated support team 24/7 by calling us at +91 97988 89456, or by emailing us at support@pawsstore.in. We are always happy to help!
                </div>
            </div>
        </div>
    </div>

    <footer id="contact">
        <div class="ps-footer">
            <div class="ps-footer-bottom" style="text-align: center; color: white;">
                © 2026 Paws Store. Made with 🐾 in India.
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Accordion Logic
            const faqItems = document.querySelectorAll('.faq-item');

            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                question.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');
                    
                    // Close all items
                    faqItems.forEach(faq => {
                        faq.classList.remove('active');
                    });

                    // Toggle the clicked item
                    if (!isActive) {
                        item.classList.add('active');
                    }
                });
            });

            // Cart Count Update
            let cart = JSON.parse(localStorage.getItem('pawsCart')) || [];

            function updateCartCount() {
                const cartCountElement = document.getElementById('cart-count');
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                if (cartCountElement) {
                    cartCountElement.textContent = totalItems;
                    cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
                }
            }
            updateCartCount();
        });
    </script>
</body>

</html>