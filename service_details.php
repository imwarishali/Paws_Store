<?php
session_start();
$id = isset($_GET['id']) ? $_GET['id'] : 'grooming';

// Detailed information for each service
$services = [
    'grooming' => [
        'title' => 'Pet Grooming',
        'price' => 'Starting from ₹499',
        'image' => 'Assets/Services/grooming.jpg',
        'description' => 'Keep your pet looking sharp and feeling fresh! Our expert groomers use pet-safe products for a relaxing spa day. We handle pets of all sizes and temperaments with care.',
        'features' => ['Bath & deep cleaning', 'Hair trimming & styling', 'Nail clipping & ear cleaning', 'Tick & flea treatment (add-on)']
    ],
    'vaccination' => [
        'title' => 'Vaccination',
        'price' => 'Starting from ₹299',
        'image' => 'Assets/Services/vaccination.jpg',
        'description' => 'Protect your furry friend from common illnesses with our comprehensive, age-appropriate immunization schedules. We provide safe, stress-free administration by certified vets.',
        'features' => ['Core & Non-core vaccines', 'Digital health records', 'Vet certified administration', 'Reminders for next due dose']
    ],
    'training' => [
        'title' => 'Pet Training',
        'price' => 'Starting from ₹999 / session',
        'image' => 'Assets/Services/training.jpg',
        'description' => 'Build a stronger bond through positive reinforcement, from puppy basics to advanced behavioral obedience. Our certified trainers work with you and your pet to ensure lasting results.',
        'features' => ['Basic obedience commands', 'Behavioral correction', 'Potty training', 'Socialization skills']
    ],
    'health' => [
        'title' => 'Health Checkups',
        'price' => 'Starting from ₹399',
        'image' => 'Assets/Services/Health_Checkup.jpg',
        'description' => 'Regular checkups are key to a long, happy life. Our clinic offers full-body exams, expert diagnostics, and nutritional counseling to keep your pet in top shape.',
        'features' => ['Full physical examination', 'Weight & diet consultation', 'Dental checkup', 'Parasite screening']
    ],
    'boarding' => [
        'title' => 'Pet Boarding',
        'price' => 'Starting from ₹599 / day',
        'image' => 'Assets/Services/Pet_Boarding.jpg',
        'description' => 'Going out of town? We offer spacious, clean, and fully supervised boarding so your pet feels right at home. They will enjoy playtime, nutritious meals, and constant care.',
        'features' => ['Climate-controlled kennels', 'Supervised playtime', 'Regular feeding schedules', 'Daily photo/video updates']
    ],
    'photography' => [
        'title' => 'Photography',
        'price' => 'Starting from ₹1,499 / session',
        'image' => 'Assets/Services/Photography.jpg',
        'description' => 'Capture the unique personality of your furry friend with our professional pet photography sessions. Whether in our studio or outdoors, we create beautiful, lasting memories of your pet that you will cherish forever.',
        'features' => ['1-hour professional session', '15 high-resolution edited photos', 'Choice of studio or outdoor setting', 'Fun props and treats included']
    ]
];

// Fallback to grooming if an invalid ID is passed
if (!array_key_exists($id, $services)) {
    $id = 'grooming';
}

$service = $services[$id];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($service['title']); ?> - Paws Store</title>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600&display=swap"
        rel="stylesheet" />

    <style>
        :root {
            --cream: #faf6f0;
            --brown: #5c4033;
            --accent: #c9a227;
            --accent-soft: #e8d5a3;
            --text: #2d2a26;
            --text-muted: #6b6560;
            --white: #ffffff;
            --shadow: 0 4px 20px rgba(92, 64, 51, 0.08);
            --radius: 16px;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "Outfit", sans-serif;
            background: var(--cream);
            color: var(--text);
            margin: 0;
            padding: 0;
        }

        /* Header */
        header {
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
            background: rgba(250, 246, 240, 0.95);
            border-bottom: 1px solid rgba(92, 64, 51, 0.08);
            padding: 1rem 2rem;
        }

        .header-inner {
            max-width: 1400px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: "Playfair Display", serif;
            font-size: 1.7rem;
            color: var(--brown);
        }

        .logo span {
            color: var(--accent);
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
            margin: 0;
            padding: 0;
        }

        nav a {
            text-decoration: none;
            color: var(--text);
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        nav a:hover {
            color: var(--brown);
            background: var(--accent-soft);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(92, 64, 51, 0.15);
        }

        /* Details Container */
        .ps-wrap {
            width: 100%;
            max-width: 1400px;
            margin: auto;
            padding: 0 20px;
        }

        .ps-details-container {
            margin: 140px auto 80px;
            display: flex;
            gap: 50px;
            background: var(--white);
            padding: 40px;
            border-radius: var(--radius);
            border: 1px solid #e8e0d4;
            box-shadow: var(--shadow);
        }

        .ps-details-left {
            flex: 1.2;
            border-radius: 12px;
            overflow: hidden;
            background: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .ps-details-left img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            max-height: 500px;
        }

        .ps-details-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .ps-details-header {
            margin-bottom: 15px;
        }

        .ps-meta-badge {
            background: var(--accent-soft);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            color: var(--brown);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ps-details-title {
            font-family: "Playfair Display", serif;
            font-size: 38px;
            color: var(--brown);
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .ps-details-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 24px;
        }

        .ps-details-desc {
            font-size: 16px;
            line-height: 1.8;
            color: var(--text-muted);
            margin-bottom: 35px;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
            padding: 24px 0;
        }

        .ps-details-desc ul {
            margin-top: 10px;
            padding-left: 20px;
            color: var(--text);
        }

        .ps-details-desc li {
            margin-bottom: 8px;
        }

        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .btn-back:hover {
            color: var(--brown);
        }



        /* Related Services */
        .related-section {
            margin-top: 60px;
            padding-top: 40px;
            border-top: 1px solid #e8e0d4;
        }

        .related-title {
            font-family: "Playfair Display", serif;
            font-size: 28px;
            color: var(--brown);
            text-align: center;
            margin-bottom: 30px;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .related-card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            text-decoration: none;
            color: var(--text);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            border: 1px solid #e8e0d4;
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(92, 64, 51, 0.12);
        }

        .related-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .related-content {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .related-card-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--brown);
            margin-bottom: 8px;
        }

        .related-price {
            color: var(--brown);
            background: var(--accent-soft);
            font-size: 13px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 12px;
            align-self: flex-start;
        }

        /* Footer */
        footer {
            background: var(--brown);
            color: var(--cream);
            padding: 3rem 2rem;
            margin-top: 3rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .ps-details-container {
                flex-direction: column;
                padding: 24px;
                gap: 30px;
                margin-top: 100px;
            }

            nav ul {
                gap: 1rem;
                font-size: 0.9rem;
                padding-left: 0;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <!-- HEADER -->
    <header>
        <div class="header-inner">
            <a href="index.php" class="logo" style="text-decoration: none;">🐾 Paws Store<span>.</span></a>
            <nav>
                <ul>
                    <li><a href="index.php">🏠 Home</a></li>
                    <li><a href="other_services.php">⬅️ Services</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="ps-wrap">
        <div class="ps-details-container">
            <div class="ps-details-left">
                <img src="<?php echo htmlspecialchars($service['image']); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>">
            </div>
            <div class="ps-details-right">
                <a href="other_services.php" class="btn-back">⬅ Go Back</a>
                <div class="ps-details-header">
                    <span class="ps-meta-badge">Clinic Service</span>
                </div>

                <h1 class="ps-details-title"><?php echo htmlspecialchars($service['title']); ?></h1>

                <div class="ps-details-price"><?php echo htmlspecialchars($service['price']); ?></div>

                <div class="ps-details-desc">
                    <?php echo htmlspecialchars($service['description']); ?>
                    <br><br>
                    <strong style="color: var(--brown);">What's Included:</strong>
                    <ul>
                        <?php foreach ($service['features'] as $feature): ?>
                            <li><?php echo htmlspecialchars($feature); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div style="margin-top: 10px; padding: 16px; background: var(--accent-soft); border-radius: 8px; color: var(--brown); font-weight: 600; text-align: center; border: 1px solid #d8c094;">
                    🚶‍♂️ Walk-ins are welcome at our clinic!
                </div>
            </div>
        </div>

        <!-- RELATED SERVICES -->
        <div class="related-section">
            <h2 class="related-title">Explore Other Services</h2>
            <div class="related-grid">
                <?php
                $related_services = [];
                foreach ($services as $key => $svc) {
                    if ($key !== $id) {
                        $related_services[$key] = $svc;
                    }
                }
                $keys = array_keys($related_services);
                shuffle($keys);
                $selected_keys = array_slice($keys, 0, 16);

                foreach ($selected_keys as $rel_key):
                    $rel_svc = $related_services[$rel_key];
                ?>
                    <a href="service_details.php?id=<?php echo htmlspecialchars($rel_key); ?>" class="related-card">
                        <img src="<?php echo htmlspecialchars($rel_svc['image']); ?>" alt="<?php echo htmlspecialchars($rel_svc['title']); ?>" class="related-img">
                        <div class="related-content">
                            <div class="related-card-title"><?php echo htmlspecialchars($rel_svc['title']); ?></div>
                            <div class="related-price"><?php echo htmlspecialchars($rel_svc['price']); ?></div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        <p>© 2025 Paws Store. Made with 🐾 in India.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {});
    </script>
</body>

</html>