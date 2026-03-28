<?php
session_start();
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

$pageTitle = "Featured Pets";
if ($category === 'dogs') $pageTitle = "Dogs";
if ($category === 'cats') $pageTitle = "Cats";
if ($category === 'fish') $pageTitle = "Fish";
if ($category === 'birds') $pageTitle = "Birds";

require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT * FROM pets ORDER BY id ASC");
    $db_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_pets = [];
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?> — Paws Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
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
        <div class="ps-section ps-featured" style="padding-top: 40px; min-height: 60vh;">
            <div class="ps-section-title"><?php echo $pageTitle; ?></div>
            <div class="ps-section-sub">Find your perfect companion. All pets are vaccinated, dewormed & vet-checked.</div>

            <div class="ps-pets-grid" id="pets-grid">
                <!-- DOGS -->
                <div class="ps-pet-card" data-category="dogs" data-pet-id="1">
                    <div class="ps-pet-photo" style="background: #f5ecd8">
                        <img src="Assets/Dog/Labrador (Max).jpg" alt="Max" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Vaccinated</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Max — Labrador</div>
                        <div class="ps-pet-meta">2 months • Dewormed</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Bengaluru</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹15,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="dogs" data-pet-id="4">
                    <div class="ps-pet-photo" style="background: #f5ecd8">
                        <img src="Assets/Dog/Pug (Charlie).jpg" alt="Charlie" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Vaccinated</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Charlie — Pug</div>
                        <div class="ps-pet-meta">2 months • Dewormed</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Hyderabad</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹10,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="dogs" data-pet-id="5">
                    <div class="ps-pet-photo" style="background: #fceee0">
                        <img src="Assets/Dog/Golden Retriever (Bella).jpg" alt="Bella" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">House Trained</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Rocky — German Shepherd</div>
                        <div class="ps-pet-meta">4 months • Vaccinated</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Pune</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹18,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="dogs" data-pet-id="7">
                    <div class="ps-pet-photo" style="background: #f5ecd8">
                        <img src="Assets/Dog/Bulldog(Daisy).jpg" alt="Daisy" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Vaccinated</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Daisy — Bulldog</div>
                        <div class="ps-pet-meta">2 months • Dewormed</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Kolkata</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹16,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="dogs" data-pet-id="8">
                    <div class="ps-pet-photo" style="background: #fceee0">
                        <img src="Assets/Dog/Shih_Tzu(Teddy).jpg" alt="Teddy" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">House Trained</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Teddy — Shih Tzu</div>
                        <div class="ps-pet-meta">3 months • Vaccinated</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Ahmedabad</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹14,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="dogs" data-pet-id="9">
                    <div class="ps-pet-photo" style="background: #eef4f0">
                        <img src="Assets/Dog/Pomeranian (Coco).jpg" alt="Coco" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Dewormed</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Coco — Pomeranian</div>
                        <div class="ps-pet-meta">1 month • Vaccinated</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Jaipur</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹22,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="dogs" data-pet-id="10">
                    <div class="ps-pet-photo" style="background: #f5ecd8">
                        <img src="Assets/Dog/Rottweiler Puppy (Bruno).jpg" alt="Bruno" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Vaccinated</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Bruno — Rottweiler</div>
                        <div class="ps-pet-meta">5 months • Dewormed</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Lucknow</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹19,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="dogs" data-pet-id="11">
                    <div class="ps-pet-photo" style="background: #fceee0">
                        <img src="Assets/Dog/Siberian Husky Puppy (Milo).jpg" alt="Milo" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">House Trained</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Milo — Husky</div>
                        <div class="ps-pet-meta">3 months • Vaccinated</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Chandigarh</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹25,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>

                <!-- CATS -->
                <div class="ps-pet-card" data-category="cats" data-pet-id="12">
                    <div class="ps-pet-photo" style="background: #fceee0">
                        <img src="Assets/Cat/British Shorthair (Luna).jpg" alt="Luna" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Vaccinated</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Luna — British Shorthair</div>
                        <div class="ps-pet-meta">3 months • Dewormed</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Mumbai</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹18,500</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="cats" data-pet-id="13">
                    <div class="ps-pet-photo" style="background: #eef4f0">
                        <img src="Assets/Cat/Persian Cat (Whiskers).jpg" alt="Whiskers" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">House Trained</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Whiskers — Persian</div>
                        <div class="ps-pet-meta">4 months • Vaccinated</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Delhi</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹22,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="cats" data-pet-id="14">
                    <div class="ps-pet-photo" style="background: #f5ecd8">
                        <img src="Assets/Cat/Maine Coon (Shadow).jpg" alt="Shadow" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Dewormed</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Shadow — Maine Coon</div>
                        <div class="ps-pet-meta">2 months • Vaccinated</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Bengaluru</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹20,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="cats" data-pet-id="15">
                    <div class="ps-pet-photo" style="background: #fceee0">
                        <img src="Assets/Cat/Ragdoll (Misty).jpg" alt="Misty" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Vaccinated</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Misty — Ragdoll</div>
                        <div class="ps-pet-meta">3 months • Dewormed</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Hyderabad</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹24,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="cats" data-pet-id="16">
                    <div class="ps-pet-photo" style="background: #eef4f0">
                        <img src="Assets/Cat/Bengal Cat (Tiger).jpg" alt="Tiger" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">House Trained</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Tiger — Bengal</div>
                        <div class="ps-pet-meta">4 months • Vaccinated</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Chennai</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹19,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="cats" data-pet-id="17">
                    <div class="ps-pet-photo" style="background: #f5ecd8">
                        <img src="Assets/Cat/Siamese Cat (Smudge).jpg" alt="Smudge" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Dewormed</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Smudge — Siamese</div>
                        <div class="ps-pet-meta">2 months • Vaccinated</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Pune</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹17,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="cats" data-pet-id="18">
                    <div class="ps-pet-photo" style="background: #fceee0">
                        <img src="Assets/Cat/Abyssinian Cat (Nala).jpg" alt="Nala" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Vaccinated</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Nala — Abyssinian</div>
                        <div class="ps-pet-meta">3 months • Dewormed</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Kolkata</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹21,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>

                <!-- FISH -->
                <div class="ps-pet-card" data-category="fish" data-pet-id="19">
                    <div class="ps-pet-photo" style="background: #e0f2ff">
                        <img src="Assets/Fish/Goldfish (Goldie).jpg" alt="Goldie" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Freshwater</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Goldie — Goldfish</div>
                        <div class="ps-pet-meta">2 months • Healthy</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Mumbai</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹500</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="fish" data-pet-id="20">
                    <div class="ps-pet-photo" style="background: #e8f5e9">
                        <img src="Assets/Fish/Clownfish (Nemo).jpg" alt="Nemo" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Saltwater</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Nemo — Clownfish</div>
                        <div class="ps-pet-meta">1 month • Quarantined</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Delhi</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹800</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="fish" data-pet-id="21">
                    <div class="ps-pet-photo" style="background: #fff3e0">
                        <img src="Assets/Fish/Betta Fish (Bubbles).jpg" alt="Bubbles" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Freshwater</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Bubbles — Betta</div>
                        <div class="ps-pet-meta">3 months • Active</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Bengaluru</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹600</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="fish" data-pet-id="22">
                    <div class="ps-pet-photo" style="background: #e0f2ff">
                        <img src="Assets/Fish/Guppy (Finley).jpg" alt="Finley" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Tropical</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Finley — Guppy</div>
                        <div class="ps-pet-meta">2 months • Colorful</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Hyderabad</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹400</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="fish" data-pet-id="23">
                    <div class="ps-pet-photo" style="background: #e8f5e9">
                        <img src="Assets/Fish/Angelfish (Coral).jpg" alt="Coral" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Saltwater</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Coral — Angelfish</div>
                        <div class="ps-pet-meta">4 months • Graceful</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Chennai</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹1,200</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="fish" data-pet-id="24">
                    <div class="ps-pet-photo" style="background: #fff3e0">
                        <img src="Assets/Fish/Tetra Fish (Splash).jpg" alt="Splash" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Freshwater</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Splash — Tetra</div>
                        <div class="ps-pet-meta">1 month • Schooling</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Pune</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹300</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="fish" data-pet-id="25">
                    <div class="ps-pet-photo" style="background: #e0f2ff">
                        <img src="Assets/Fish/Molly Fish (Pearl).jpg" alt="Pearl" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Tropical</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Pearl — Molly</div>
                        <div class="ps-pet-meta">3 months • Peaceful</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Kolkata</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹450</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>

                <!-- BIRDS -->
                <div class="ps-pet-card" data-category="birds" data-pet-id="26">
                    <div class="ps-pet-photo" style="background: #fff8e1">
                        <img src="Assets/Birds/African Grey Parrot (Rio).jpg" alt="Rio" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Talkative</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Rio — African Grey</div>
                        <div class="ps-pet-meta">1 year • Intelligent</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Mumbai</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹45,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="birds" data-pet-id="27">
                    <div class="ps-pet-photo" style="background: #f3e5f5">
                        <img src="Assets/Birds/Macaw Parrot (Sunny).jpg" alt="Sunny" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Colorful</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Sunny — Macaw</div>
                        <div class="ps-pet-meta">2 years • Playful</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Delhi</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹55,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="birds" data-pet-id="28">
                    <div class="ps-pet-photo" style="background: #e8f5e9">
                        <img src="Assets/Birds/Canary (Tweety).jpg" alt="Tweety" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Melodious</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Tweety — Canary</div>
                        <div class="ps-pet-meta">6 months • Singer</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Bengaluru</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹8,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="birds" data-pet-id="29">
                    <div class="ps-pet-photo" style="background: #fff8e1">
                        <img src="Assets/Birds/Cockatiel (Coco).jpg" alt="Coco" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Friendly</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Coco — Cockatiel</div>
                        <div class="ps-pet-meta">8 months • Affectionate</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Hyderabad</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹12,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="birds" data-pet-id="30">
                    <div class="ps-pet-photo" style="background: #f3e5f5">
                        <img src="Assets/Birds/Lovebird (Phoenix).jpg" alt="Phoenix" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Exotic</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Phoenix — Lovebird</div>
                        <div class="ps-pet-meta">4 months • Pair-bonded</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Chennai</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹6,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="birds" data-pet-id="31">
                    <div class="ps-pet-photo" style="background: #e8f5e9">
                        <img src="Assets/Birds/Eagle (Zeus).jpg" alt="Zeus" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Majestic</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Zeus — Eagle</div>
                        <div class="ps-pet-meta">3 years • Trained</div>
                        <div class="ps-pet-stars">★★★★☆</div>
                        <div class="ps-pet-loc">📍 Pune</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹75,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
                </div>
                <div class="ps-pet-card" data-category="birds" data-pet-id="32">
                    <div class="ps-pet-photo" style="background: #fff8e1">
                        <img src="Assets/Birds/Swan (Sky).jpg" alt="Sky" style="width: 100%; height: 100%; object-fit: cover;">
                        <div class="ps-pet-wish">♡</div>
                        <div class="ps-pet-badge">Graceful</div>
                    </div>
                    <div class="ps-pet-body">
                        <div class="ps-pet-name">Sky — Swan</div>
                        <div class="ps-pet-meta">1 year • Elegant</div>
                        <div class="ps-pet-stars">★★★★★</div>
                        <div class="ps-pet-loc">📍 Kolkata</div>
                        <div class="ps-pet-row">
                            <span class="ps-pet-price">₹35,000</span><button class="ps-pet-add">Add to Cart</button>
                        </div>
                    </div>
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

            // Hide items that don't match the current category URL parameter
            const urlCategory = "<?php echo $category; ?>";
            const petCards = document.querySelectorAll('.ps-pet-card');

            if (urlCategory !== 'all') {
                petCards.forEach(card => {
                    if (card.getAttribute('data-category') !== urlCategory) {
                        card.style.display = 'none';
                    }
                });
            }

            // NAVIGATE TO DETAILS PAGE ON CARD CLICK
            petCards.forEach(card => {
                card.style.cursor = 'pointer';
                card.addEventListener('click', function(e) {
                    if (!e.target.closest('.ps-pet-wish') && !e.target.closest('.ps-pet-add') && !e.target.closest('.ps-pet-buy')) {
                        const petId = this.getAttribute('data-pet-id');
                        window.location.href = 'pet_details.php?id=' + petId;
                    }
                });
            });

            // CART & WISHLIST LOGIC
            let cart = JSON.parse(localStorage.getItem('pawsCart')) || [];
            let wishlist = JSON.parse(localStorage.getItem('pawsWishlist')) || [];

            function updateCartCount() {
                const cartCountElement = document.getElementById('cart-count');
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                if (cartCountElement) {
                    cartCountElement.textContent = totalItems;
                    cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
                }
            }

            function updateWishlistIcons() {
                document.querySelectorAll('.ps-pet-wish').forEach(btn => {
                    const petId = btn.closest('.ps-pet-card').getAttribute('data-pet-id');
                    if (wishlist.find(item => item.id === petId)) {
                        btn.innerHTML = '♥';
                        btn.style.color = '#e74c3c';
                    } else {
                        btn.innerHTML = '♡';
                        btn.style.color = 'inherit';
                    }
                });
            }

            document.querySelectorAll('.ps-pet-add').forEach(button => {
                function addToCart(petCard) {
                    const petId = petCard.getAttribute('data-pet-id');
                    const petName = petCard.querySelector('.ps-pet-name').textContent;
                    const petPrice = petCard.querySelector('.ps-pet-price').textContent.replace(/[^0-9]/g, '');
                    const petImage = petCard.querySelector('img').src;

                    const existingPet = cart.find(item => item.id === petId);
                    if (existingPet) {
                        existingPet.quantity += 1;
                    } else {
                        cart.push({
                            id: petId,
                            name: petName,
                            price: parseInt(petPrice),
                            image: petImage,
                            quantity: 1
                        });
                    }
                    localStorage.setItem('pawsCart', JSON.stringify(cart));
                    updateCartCount();
                    return petName;
                }

                button.addEventListener('click', function() {
                    const petName = addToCart(this.closest('.ps-pet-card'));
                    alert(petName + " added to cart!");
                });
            });

            document.querySelectorAll('.ps-pet-wish').forEach(button => {
                button.addEventListener('click', function() {
                    const petCard = this.closest('.ps-pet-card');
                    const petId = petCard.getAttribute('data-pet-id');
                    const petName = petCard.querySelector('.ps-pet-name').textContent;
                    const petPrice = petCard.querySelector('.ps-pet-price').textContent;
                    const petImage = petCard.querySelector('img').src;

                    const existingIndex = wishlist.findIndex(item => item.id === petId);
                    if (existingIndex > -1) {
                        wishlist.splice(existingIndex, 1);
                        alert(petName + " removed from wishlist!");
                    } else {
                        wishlist.push({
                            id: petId,
                            name: petName,
                            price: parseInt(petPrice.replace(/[^0-9]/g, '')), // Parse the string to a raw number
                            image: petImage
                        });
                        alert(petName + " added to wishlist!");
                    }
                    localStorage.setItem('pawsWishlist', JSON.stringify(wishlist));
                    updateWishlistIcons();
                });
            });

            updateCartCount();
            updateWishlistIcons();
        });
    </script>
</body>

</html>