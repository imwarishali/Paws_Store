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
            background: rgba(250, 246, 240, 0.95);
            border-bottom: 1px solid rgba(92, 64, 51, 0.08);
            padding: 1rem 2rem;
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

        /* Hero */
        .hero {
            padding: 8rem 2rem 4rem;
            text-align: center;
            max-width: 900px;
            margin: auto;
        }

        .hero h1 {
            font-family: "Playfair Display", serif;
            color: var(--brown);
            font-size: 3rem;
        }

        .hero p {
            color: var(--text-muted);
            margin: 1rem 0;
        }

        .btn-back {
            margin-top: 1rem;
        }

        .contact-highlights {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 1.5rem 0;
            flex-wrap: wrap;
        }

        .highlight-item {
            background: var(--white);
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(92, 64, 51, 0.08);
            border: 1px solid var(--accent-soft);
            color: var(--brown);
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .highlight-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(92, 64, 51, 0.15);
            border-color: var(--accent);
        }

        .highlight-item a {
            text-decoration: none;
            color: var(--accent);
        }

        .map-container {
            margin-top: 3rem;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            border: 1px solid var(--accent-soft);
        }

        .map-container iframe {
            width: 100%;
            height: 350px;
            border: 0;
            display: block;
        }

        #location {
            max-width: 900px;
            margin: auto;
            padding: 2rem 2rem 4rem;
            text-align: center;
        }

        /* Services */
        .services {
            padding: 2rem;
            max-width: 1600px;
            margin: auto;
        }

        .card {
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: none;
            transition: 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card img {
            height: 180px;
            object-fit: cover;
            border-top-left-radius: var(--radius);
            border-top-right-radius: var(--radius);
        }

        .card-title {
            color: var(--brown);
            font-weight: 600;
        }

        .service-price {
            color: var(--brown);
            background: var(--accent-soft);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 4px 10px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 12px;
        }

        .card.fade-in {
            opacity: 0;
            transform: translateY(40px);
            /* Smooth, elegant slide up and fade in */
            transition: opacity 0.7s cubic-bezier(0.25, 1, 0.5, 1), transform 0.7s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .card.fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .card.fade-in.visible:hover {
            transition: all 0.2s ease;
            transform: translateY(-5px);
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
            nav ul {
                gap: 1rem;
                font-size: 0.9rem;
                padding-left: 0;
            }

            .map-container iframe {
                height: 250px;
            }

            #location {
                padding: 2rem 1rem 3rem;
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
        <h2 style="font-family: 'Playfair Display', serif; color: var(--brown); font-size: 2.5rem; margin-bottom: 1.5rem;">Visit Our Clinic</h2>
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