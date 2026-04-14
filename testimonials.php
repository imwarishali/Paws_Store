<?php
session_start();
require_once 'db.php';
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Customer Testimonials — Paws Store</title>
    <meta name="description" content="Read what our happy customers say about Paws Store. Real reviews from pet lovers.">
    <meta name="keywords" content="testimonials, reviews, paws store, pet lovers">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ps-testimonials-section {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .ps-testimonials-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .ps-testimonials-header h1 {
            font-family: 'Playfair Display';
            font-size: 48px;
            color: #2c1a0e;
            margin-bottom: 15px;
        }

        .ps-testimonials-header p {
            font-size: 18px;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .ps-testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .ps-testimonial-card {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid #b5860d;
        }

        .ps-testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(181, 134, 13, 0.2);
        }

        .ps-testimonial-stars {
            color: #ffc107;
            font-size: 16px;
            margin-bottom: 15px;
            letter-spacing: 2px;
        }

        .ps-testimonial-text {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
            font-style: italic;
        }

        .ps-testimonial-author {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .ps-testimonial-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #b5860d;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 20px;
        }

        .ps-testimonial-info h3 {
            margin: 0;
            color: #2c1a0e;
            font-size: 16px;
            font-weight: 600;
        }

        .ps-testimonial-info p {
            margin: 5px 0 0;
            color: #b5860d;
            font-size: 14px;
            font-weight: 500;
        }

        .ps-submit-testimonial {
            background: linear-gradient(135deg, #2c1a0e 0%, #4a3020 100%);
            padding: 40px;
            border-radius: 12px;
            color: white;
            margin-bottom: 60px;
        }

        .ps-submit-testimonial h2 {
            font-family: 'Playfair Display';
            font-size: 32px;
            margin-bottom: 20px;
        }

        .ps-testimonial-form {
            display: grid;
            gap: 15px;
            max-width: 600px;
        }

        .ps-form-group {
            display: flex;
            flex-direction: column;
        }

        .ps-form-group label {
            margin-bottom: 8px;
            font-weight: 600;
        }

        .ps-form-group input,
        .ps-form-group textarea {
            padding: 12px;
            border: 2px solid transparent;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Nunito';
            transition: border-color 0.3s ease;
        }

        .ps-form-group input:focus,
        .ps-form-group textarea:focus {
            outline: none;
            border-color: #b5860d;
        }

        .ps-form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .ps-rating-input {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .ps-rating-input label {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            margin: 0;
            font-weight: 400;
        }

        .ps-form-submit {
            background: #b5860d;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .ps-form-submit:hover {
            background: #9a6e0a;
        }

        @media (max-width: 768px) {
            .ps-testimonials-header h1 {
                font-size: 32px;
            }

            .ps-testimonials-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'assets/header.php'; ?>

    <main>
        <div class="ps-testimonials-section">
            <div class="ps-testimonials-header">
                <h1>What Our Customers Say</h1>
                <p>Real reviews from real pet lovers who trust Paws Store for their furry friends</p>
            </div>

            <!-- Testimonials Grid -->
            <div class="ps-testimonials-grid">
                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY created_at DESC LIMIT 9");
                    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!empty($testimonials)) {
                        foreach ($testimonials as $testimonial) {
                            $stars = str_repeat('★', $testimonial['rating'] ?? 5);
                            $initials = strtoupper(substr($testimonial['name'], 0, 1));
                ?>
                            <div class="ps-testimonial-card">
                                <div class="ps-testimonial-stars"><?php echo $stars; ?></div>
                                <p class="ps-testimonial-text">"<?php echo htmlspecialchars($testimonial['testimonial_text']); ?>"</p>
                                <div class="ps-testimonial-author">
                                    <div class="ps-testimonial-avatar"><?php echo $initials; ?></div>
                                    <div class="ps-testimonial-info">
                                        <h3><?php echo htmlspecialchars($testimonial['name']); ?></h3>
                                        <p><?php echo htmlspecialchars($testimonial['role'] ?? 'Happy Pet Owner'); ?></p>
                                    </div>
                                </div>
                            </div>
                <?php
                        }
                    } else {
                        echo '<p style="grid-column: 1/-1; text-align: center; color: #999;">No testimonials yet. Be the first!</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p style="grid-column: 1/-1; text-align: center; color: red;">Error loading testimonials</p>';
                }
                ?>
            </div>

            <!-- Submit Testimonial Form -->
            <div class="ps-submit-testimonial">
                <h2>Share Your Experience</h2>
                <p style="margin-bottom: 30px; opacity: 0.9;">Help other pet lovers by sharing your experience with Paws Store</p>

                <form class="ps-testimonial-form" method="POST" action="handlers/testimonial_handler.php">
                    <div class="ps-form-group">
                        <label for="name">Your Name *</label>
                        <input type="text" id="name" name="name" required placeholder="Enter your full name">
                    </div>

                    <div class="ps-form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required placeholder="Your email address">
                    </div>

                    <div class="ps-form-group">
                        <label for="role">Role (Pet Owner, Breeder, etc.)</label>
                        <input type="text" id="role" name="role" placeholder="e.g., Dog Lover, Cat Breeder">
                    </div>

                    <div class="ps-form-group">
                        <label>Rating *</label>
                        <div class="ps-rating-input">
                            <label><input type="radio" name="rating" value="5" required> ★★★★★ Excellent</label>
                            <label><input type="radio" name="rating" value="4"> ★★★★ Good</label>
                            <label><input type="radio" name="rating" value="3"> ★★★ OK</label>
                        </div>
                    </div>

                    <div class="ps-form-group">
                        <label for="testimonial">Your Testimonial *</label>
                        <textarea id="testimonial" name="testimonial" required placeholder="Tell us about your experience with Paws Store..."></textarea>
                    </div>

                    <button type="submit" class="ps-form-submit">Submit Testimonial</button>
                </form>
            </div>
        </div>
    </main>

    <?php include 'assets/footer.php'; ?>
</body>

</html>