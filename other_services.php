<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Paws Store - Other Services</title>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600&display=swap"
        rel="stylesheet" />

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet" />

    <style>
        :root {
            --cream: #faf6f0;
            --brown: #2c1a0e;
            --brown-light: #5c3d2e;
            --accent: #b5860d;
            --accent-light: #d4af37;
            --accent-soft: #e8d5a3;
            --text: #2d2a26;
            --text-muted: #666;
            --white: #ffffff;
            --shadow: 0 4px 20px rgba(44, 26, 14, 0.08);
            --shadow-lg: 0 12px 35px rgba(44, 26, 14, 0.15);
            --radius: 16px;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 100px;
        }

        body {
            font-family: "Outfit", sans-serif;
            background: var(--cream);
            color: var(--text);
        }

        /* Header */
        header {
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
            background: rgba(250, 246, 240, 0.98);
            border-bottom: 2px solid #e8e0d4;
            padding: 1rem 2rem;
            box-shadow: 0 2px 8px rgba(44, 26, 14, 0.08);
            backdrop-filter: blur(10px);
        }

        .header-inner {
            max-width: 1600px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: "Playfair Display", serif;
            font-size: 1.7rem;
            color: var(--brown);
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .logo span {
            color: var(--accent);
        }

        nav ul {
            list-style: none;
            display: flex;
            gap: 2rem;
        }

        nav a {
            text-decoration: none;
            color: var(--text);
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-block;
            background: transparent;
        }

        nav a:hover {
            color: white;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(181, 134, 13, 0.25);
        }

        /* Hero */
        .hero {
            padding: 8rem 2rem 3rem;
            text-align: center;
            max-width: 1600px;
            margin: auto;
            animation: fadeInDown 0.8s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero h1 {
            font-family: "Playfair Display", serif;
            color: var(--brown);
            font-size: 3.5rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 1.5rem;
        }

        .hero p {
            color: var(--text-muted);
            margin: 1.5rem 0;
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .btn-back {
            margin-top: 1rem;
        }

        .contact-highlights {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .highlight-item {
            background: linear-gradient(135deg, #f9f6f1 0%, #f2ede4 100%);
            padding: 1rem 1.8rem;
            border-radius: 30px;
            box-shadow: 0 6px 20px rgba(44, 26, 14, 0.1);
            border: 1px solid #e8e0d4;
            color: var(--brown);
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .highlight-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(181, 134, 13, 0.2);
            border-color: var(--accent);
            background: linear-gradient(135deg, #fffae8 0%, #fff7d1 100%);
        }

        .highlight-item a {
            text-decoration: none;
            color: var(--accent);
            font-weight: 700;
            transition: color 0.3s ease;
        }

        .highlight-item a:hover {
            color: var(--accent-light);
        }

        .map-container {
            margin-top: 3rem;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(44, 26, 14, 0.15);
            border: 1px solid #e8e0d4;
            transition: all 0.4s ease;
        }

        .map-container:hover {
            box-shadow: 0 16px 45px rgba(44, 26, 14, 0.2);
            transform: translateY(-4px);
        }

        .map-container iframe {
            width: 100%;
            height: 350px;
            border: 0;
            display: block;
        }

        #location {
            max-width: 1600px;
            margin: auto;
            padding: 2rem 2rem 4rem;
            text-align: center;
        }

        /* Services */
        .services {
            padding: 3rem 2rem;
            max-width: 1600px;
            margin: auto;
        }

        .card {
            border-radius: var(--radius);
            box-shadow: 0 4px 15px rgba(44, 26, 14, 0.08);
            border: 1px solid #e0e0e0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
            background: white;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--accent-light));
            transform: translateX(-100%);
            transition: transform 0.4s ease;
            z-index: 1;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(44, 26, 14, 0.15);
            border-color: var(--accent);
        }

        .card:hover::before {
            transform: translateX(0);
        }

        .card img {
            height: 200px;
            object-fit: cover;
            border-top-left-radius: var(--radius);
            border-top-right-radius: var(--radius);
            transition: transform 0.4s ease;
        }

        .card:hover img {
            transform: scale(1.06);
        }

        .card-body {
            padding: 1.8rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .card-title {
            color: var(--brown);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 0.8rem;
            letter-spacing: 0.3px;
        }

        .service-price {
            color: white;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-light) 100%);
            font-weight: 700;
            font-size: 0.95rem;
            padding: 8px 14px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 12px;
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.25);
            width: fit-content;
        }

        .card-text {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.7;
            margin: 0;
            flex-grow: 1;
        }

        .card.fade-in {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s cubic-bezier(0.25, 1, 0.5, 1), transform 0.7s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .card.fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .card.fade-in.visible:hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            transform: translateY(-8px);
        }

        /* Footer */
        footer {
            background: linear-gradient(135deg, var(--brown) 0%, var(--brown-light) 100%);
            color: var(--cream);
            padding: 3rem 2rem;
            margin-top: 4rem;
            text-align: center;
            box-shadow: 0 -4px 16px rgba(44, 26, 14, 0.1);
        }

        footer p {
            margin: 0;
            font-size: 1rem;
            letter-spacing: 0.3px;
        }

        #location {
            max-width: 1600px;
            margin: auto;
            padding: 3rem 2rem 4rem;
            text-align: center;
        }

        #location h2 {
            font-family: 'Playfair Display', serif;
            color: var(--brown);
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        @media (max-width: 1024px) {
            .hero h1 {
                font-size: 2.8rem;
            }

            .card {
                border-radius: 14px;
            }
        }

        @media (max-width: 768px) {
            nav ul {
                gap: 1rem;
                font-size: 0.9rem;
                padding-left: 0;
            }

            nav a {
                padding: 6px 12px;
                font-size: 0.9rem;
            }

            .hero {
                padding: 7rem 1.5rem 2rem;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .hero p {
                font-size: 1rem;
            }

            #location h2 {
                font-size: 2rem;
            }

            .contact-highlights {
                gap: 1rem;
                margin: 1.5rem 0;
            }

            .highlight-item {
                padding: 0.8rem 1.2rem;
                font-size: 0.9rem;
                border-radius: 24px;
            }

            .map-container iframe {
                height: 250px;
            }

            #location {
                padding: 2rem 1rem 3rem;
            }

            .services {
                padding: 2rem 1rem;
            }

            .card img {
                height: 160px;
            }

            .card-body {
                padding: 1.3rem;
            }

            .card-title {
                font-size: 1.1rem;
                margin-bottom: 0.6rem;
            }

            .service-price {
                font-size: 0.85rem;
                padding: 6px 12px;
            }

            .card-text,
            .card p {
                font-size: 0.85rem;
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
                    <li><a href="#location">📍 Location</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- HERO -->
    <section class="hero">
        <h1>Our Other Services</h1>
        <p>
            Give your pets the care they truly deserve! Please note that our premium grooming,
            vaccination, training, and boarding services are <strong>exclusively available in-person</strong>
            at our physical clinic. We'd love to see you and your furry friend!
        </p>
    </section>

    <!-- SERVICES -->
    <section class="services">
        <div class="row g-4">
            <!-- Grooming -->
            <div class="col-md-4">
                <div class="card" data-service-id="grooming">
                    <img
                        src="Assets/Services/grooming.jpg"
                        alt="" />
                    <div class="card-body">
                        <h5 class="card-title">Grooming</h5>
                        <div class="service-price">Starting from ₹499</div>
                        <p class="text-muted" style="font-size: 0.95rem; margin-bottom: 10px;">
                            Keep your pet looking sharp and feeling fresh! Our expert groomers use pet-safe products for a relaxing spa day.
                        </p>
                        <p>• Bath & cleaning<br />• Hair trimming<br />• Hygiene care</p>
                    </div>
                </div>
            </div>

            <!-- Vaccination -->
            <div class="col-md-4">
                <div class="card" data-service-id="vaccination">
                    <img
                        src="Assets/Services/vaccination.jpg"
                        alt="" />
                    <div class="card-body">
                        <h5 class="card-title">Vaccination</h5>
                        <div class="service-price">Starting from ₹299</div>
                        <p class="text-muted" style="font-size: 0.95rem; margin-bottom: 10px;">
                            Protect your furry friend from common illnesses with our comprehensive, age-appropriate immunization schedules.
                        </p>
                        <p>
                            • Regular vaccines<br />• Prevent diseases<br />• Vet certified
                        </p>
                    </div>
                </div>
            </div>

            <!-- Training -->
            <div class="col-md-4">
                <div class="card" data-service-id="training">
                    <img
                        src="Assets/Services/training.jpg"
                        alt="" />
                    <div class="card-body">
                        <h5 class="card-title">Pet Training</h5>
                        <div class="service-price">Starting from ₹999 / session</div>
                        <p class="text-muted" style="font-size: 0.95rem; margin-bottom: 10px;">
                            Build a stronger bond through positive reinforcement, from puppy basics to advanced behavioral obedience.
                        </p>
                        <p>
                            • Basic commands<br />• Behavior training<br />• Professional
                            trainers
                        </p>
                    </div>
                </div>
            </div>

            <!-- Health -->
            <div class="col-md-4">
                <div class="card" data-service-id="health">
                    <img
                        src="Assets/Services/Health_Checkup.jpg"
                        alt="" />
                    <div class="card-body">
                        <h5 class="card-title">Health Checkups</h5>
                        <div class="service-price">Starting from ₹399</div>
                        <p class="text-muted" style="font-size: 0.95rem; margin-bottom: 10px;">
                            Regular checkups are key to a long, happy life. Our clinic offers full-body exams and expert diagnostics.
                        </p>
                        <p>
                            • Routine checkups<br />• Health monitoring<br />• Expert vets
                        </p>
                    </div>
                </div>
            </div>

            <!-- Boarding -->
            <div class="col-md-4">
                <div class="card" data-service-id="boarding">
                    <img
                        src="Assets/Services/Pet_Boarding.jpg"
                        alt="" />
                    <div class="card-body">
                        <h5 class="card-title">Pet Boarding</h5>
                        <div class="service-price">Starting from ₹599 / day</div>
                        <p class="text-muted" style="font-size: 0.95rem; margin-bottom: 10px;">
                            Going out of town? We offer spacious, clean, and fully supervised boarding so your pet feels right at home.
                        </p>
                        <p>
                            • Safe stay<br />• Daily care<br />• Comfortable environment
                        </p>
                    </div>
                </div>
            </div>

            <!-- Photography -->
            <div class="col-md-4">
                <div class="card" data-service-id="photography">
                    <img
                        src="Assets/Services/Photography.jpg"
                        alt="" />
                    <div class="card-body">
                        <h5 class="card-title">Pet Photography</h5>
                        <div class="service-price">Starting from ₹1,499 / session</div>
                        <p class="text-muted" style="font-size: 0.95rem; margin-bottom: 10px;">
                            Capture the unique personality of your furry friend with our professional pet photography sessions.
                        </p>
                        <p>
                            • Studio & outdoor<br />• Edited photos<br />• Props included
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- LOCATION & CONTACT -->
    <section id="location">
        <h2>Visit Our Clinic</h2>
        <div class="contact-highlights">
            <div class="highlight-item">
                📍 Visit us at:
                <strong><a href="https://maps.google.com" target="_blank">123 Pet Avenue, Koramangala, Bangalore, Karnataka 560034</a></strong>
            </div>
            <div class="highlight-item">📞 Phone: <strong><a href="tel:+919798889456">+91 97988 89456</a></strong></div>
            <div class="highlight-item">🕒 Hours: <strong>Mon - Sun, 9:00 AM - 8:00 PM</strong></div>
        </div>
        <div class="map-container">
            <iframe
                src="https://maps.google.com/maps?q=Koramangala,%20Bangalore&t=&z=14&ie=UTF8&iwloc=&output=embed"
                allowfullscreen=""
                loading="lazy">
            </iframe>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <p>© 2025 Paws Store. Made with 🐾 in India.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                let delay = 0;
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.classList.add('visible');
                        }, delay);
                        delay += 150; // 150ms stagger effect for cards showing up at the same time
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: "0px 0px -50px 0px"
            });

            document.querySelectorAll('.card').forEach(card => {
                card.classList.add('fade-in');
                observer.observe(card);

                // Click to view service details
                card.style.cursor = 'pointer';
                card.addEventListener('click', function() {
                    const serviceId = this.getAttribute('data-service-id');
                    if (serviceId) {
                        window.location.href = 'service_details.php?id=' + serviceId;
                    }
                });
            });
        });
    </script>
</body>

</html>