<?php
session_start();

// Pet Data (Mock Database mapped directly to your assets)
$petDB = [
<<<<<<< HEAD
    1 => ['name' => 'Max — Labrador', 'price' => 15000, 'image' => 'Assets/Dog/Labrador (Max).jpg', 'category' => 'Dogs', 'desc' => 'Max is a highly energetic and loyal Labrador. He loves playing fetch, swimming, and is incredibly gentle with kids.'],
    4 => ['name' => 'Charlie — Pug', 'price' => 10000, 'image' => 'Assets/Dog/Pug (Charlie).jpg', 'category' => 'Dogs', 'desc' => 'Charlie is an adorable little Pug with a big personality. He enjoys short walks and long naps on the couch.'],
    5 => ['name' => 'Rocky — German Shepherd', 'price' => 18000, 'image' => 'Assets/Dog/Golden Retriever (Bella).jpg', 'category' => 'Dogs', 'desc' => 'Rocky is a highly intelligent and protective German Shepherd. He is eager to learn and makes a fantastic guard dog and loyal companion.'],
    7 => ['name' => 'Daisy — Bulldog', 'price' => 16000, 'image' => 'Assets/Dog/Bulldog(Daisy).jpg', 'category' => 'Dogs', 'desc' => 'Daisy is a calm and affectionate Bulldog. She is low-energy, loves cuddles, and will happily spend the afternoon snoring by your feet.'],
    8 => ['name' => 'Teddy — Shih Tzu', 'price' => 14000, 'image' => 'Assets/Dog/Shih_Tzu(Teddy).jpg', 'category' => 'Dogs', 'desc' => 'Teddy is a charming Shih Tzu with a beautiful, silky coat. He is very social, great with other pets, and loves being pampered.'],
    9 => ['name' => 'Coco — Pomeranian', 'price' => 22000, 'image' => 'Assets/Dog/Pomeranian (Coco).jpg', 'category' => 'Dogs', 'desc' => 'Coco is a fluffy, spirited Pomeranian who loves to be the center of attention. She is alert, playful, and incredibly loyal.'],
    10 => ['name' => 'Bruno — Rottweiler', 'price' => 19000, 'image' => 'Assets/Dog/Rottweiler Puppy (Bruno).jpg', 'category' => 'Dogs', 'desc' => 'Bruno is a strong, confident Rottweiler pup. With proper training, he will grow into a gentle giant and a fiercely loyal protector.'],
    11 => ['name' => 'Milo — Husky', 'price' => 25000, 'image' => 'Assets/Dog/Siberian Husky Puppy (Milo).jpg', 'category' => 'Dogs', 'desc' => 'Milo is a stunning Siberian Husky with striking blue eyes. He is very active, loves the outdoors, and needs plenty of exercise.'],
    12 => ['name' => 'Luna — British Shorthair', 'price' => 18500, 'image' => 'Assets/Cat/British Shorthair (Luna).jpg', 'category' => 'Cats', 'desc' => 'Luna is a dignified British Shorthair with a plush, dense coat. She is independent but loves quiet companionship and cozy spots.'],
    13 => ['name' => 'Whiskers — Persian', 'price' => 22000, 'image' => 'Assets/Cat/Persian Cat (Whiskers).jpg', 'category' => 'Cats', 'desc' => 'Whiskers is a glamorous Persian cat with a sweet, gentle nature. She requires regular grooming and prefers a calm, relaxed environment.'],
    14 => ['name' => 'Shadow — Maine Coon', 'price' => 20000, 'image' => 'Assets/Cat/Maine Coon (Shadow).jpg', 'category' => 'Cats', 'desc' => 'Shadow is a magnificent Maine Coon, known as the gentle giant of the cat world. He is highly sociable, playful, and loves human interaction.'],
    15 => ['name' => 'Misty — Ragdoll', 'price' => 24000, 'image' => 'Assets/Cat/Ragdoll (Misty).jpg', 'category' => 'Cats', 'desc' => 'Misty is a beautiful Ragdoll cat who loves to go limp in your arms when held. She is incredibly affectionate and follows her humans everywhere.'],
    16 => ['name' => 'Tiger — Bengal', 'price' => 19000, 'image' => 'Assets/Cat/Bengal Cat (Tiger).jpg', 'category' => 'Cats', 'desc' => 'Tiger is a highly active and intelligent Bengal cat. With a beautiful leopard-like coat, he loves climbing, playing, and exploring his surroundings.'],
    17 => ['name' => 'Smudge — Siamese', 'price' => 17000, 'image' => 'Assets/Cat/Siamese Cat (Smudge).jpg', 'category' => 'Cats', 'desc' => 'Smudge is a vocal and striking Siamese cat. She forms very strong bonds with her owners and will happily talk to you all day long.'],
    18 => ['name' => 'Nala — Abyssinian', 'price' => 21000, 'image' => 'Assets/Cat/Abyssinian Cat (Nala).jpg', 'category' => 'Cats', 'desc' => 'Nala is a curious and highly energetic Abyssinian. She is always on the move, loves puzzle toys, and enjoys perching on high shelves.'],
    19 => ['name' => 'Goldie — Goldfish', 'price' => 500, 'image' => 'Assets/Fish/Goldfish (Goldie).jpg', 'category' => 'Fish', 'desc' => 'Goldie is a vibrant, classic Goldfish. Easy to care for and mesmerizing to watch, perfect for both beginners and experienced aquarists.'],
    20 => ['name' => 'Nemo — Clownfish', 'price' => 800, 'image' => 'Assets/Fish/Clownfish (Nemo).jpg', 'category' => 'Fish', 'desc' => 'Nemo is a stunning saltwater Clownfish. Known for their unique waddling swimming style, he is a hardy and beautiful addition to any marine tank.'],
    21 => ['name' => 'Bubbles — Betta', 'price' => 600, 'image' => 'Assets/Fish/Betta Fish (Bubbles).jpg', 'category' => 'Fish', 'desc' => 'Bubbles is a gorgeous Betta fish with flowing, colorful fins. He thrives in a peaceful environment and adds a splash of vibrant color to any room.'],
    22 => ['name' => 'Finley — Guppy', 'price' => 400, 'image' => 'Assets/Fish/Guppy (Finley).jpg', 'category' => 'Fish', 'desc' => 'Finley is an active and colorful tropical Guppy. He loves swimming around the tank and does very well in a lively community aquarium.'],
    23 => ['name' => 'Coral — Angelfish', 'price' => 1200, 'image' => 'Assets/Fish/Angelfish (Coral).jpg', 'category' => 'Fish', 'desc' => 'Coral is a graceful Angelfish with elegant, trailing fins. She adds a touch of sophistication to any freshwater tank.'],
    24 => ['name' => 'Splash — Tetra', 'price' => 300, 'image' => 'Assets/Fish/Tetra Fish (Splash).jpg', 'category' => 'Fish', 'desc' => 'Splash is a lively schooling Tetra. With brilliant neon colors, this little fish is highly active and looks best when kept with a small group.'],
    25 => ['name' => 'Pearl — Molly', 'price' => 450, 'image' => 'Assets/Fish/Molly Fish (Pearl).jpg', 'category' => 'Fish', 'desc' => 'Pearl is a peaceful and hardy Molly fish. She is a great community tank resident, adaptable to various water conditions, and easy to care for.'],
    26 => ['name' => 'Rio — African Grey', 'price' => 45000, 'image' => 'Assets/Birds/African Grey Parrot (Rio).jpg', 'category' => 'Birds', 'desc' => 'Rio is a highly intelligent African Grey parrot. He has an exceptional vocabulary, loves interacting with humans, and needs plenty of mental stimulation.'],
    27 => ['name' => 'Sunny — Macaw', 'price' => 55000, 'image' => 'Assets/Birds/Macaw Parrot (Sunny).jpg', 'category' => 'Birds', 'desc' => 'Sunny is a brilliantly colored Macaw. Known for his loud, cheerful personality, he is incredibly smart and requires a spacious environment to thrive.'],
    28 => ['name' => 'Tweety — Canary', 'price' => 8000, 'image' => 'Assets/Birds/Canary (Tweety).jpg', 'category' => 'Birds', 'desc' => 'Tweety is a cheerful Canary with a beautiful singing voice. He will fill your home with melodious songs and is very easy to care for.'],
    29 => ['name' => 'Coco — Cockatiel', 'price' => 12000, 'image' => 'Assets/Birds/Cockatiel (Coco).jpg', 'category' => 'Birds', 'desc' => 'Coco is an affectionate and gentle Cockatiel. He loves sitting on shoulders, whistling tunes, and being an active part of the family.'],
    30 => ['name' => 'Phoenix — Lovebird', 'price' => 6000, 'image' => 'Assets/Birds/Lovebird (Phoenix).jpg', 'category' => 'Birds', 'desc' => 'Phoenix is a vibrant, social Lovebird. Known for forming deep bonds, he is playful, active, and enjoys having plenty of toys in his cage.'],
    31 => ['name' => 'Zeus — Eagle', 'price' => 75000, 'image' => 'Assets/Birds/Eagle (Zeus).jpg', 'category' => 'Birds', 'desc' => 'Zeus is a majestic Eagle with a commanding presence. Handled by experienced falconers only, he requires specialized care, licensing, and training.'],
    32 => ['name' => 'Sky — Swan', 'price' => 35000, 'image' => 'Assets/Birds/Swan (Sky).jpg', 'category' => 'Birds', 'desc' => 'Sky is a graceful Swan, perfect for large, private ponds or estates. She is elegant, majestic, and adds serene beauty to any natural water setting.']
=======
    1 => ['name' => 'Max — Labrador', 'price' => 15000, 'image' => 'Assets/Dog/Labrador (Max).jpg', 'category' => 'Dogs'],
    4 => ['name' => 'Charlie — Pug', 'price' => 10000, 'image' => 'Assets/Dog/Pug (Charlie).jpg', 'category' => 'Dogs'],
    5 => ['name' => 'Rocky — German Shepherd', 'price' => 18000, 'image' => 'Assets/Dog/Golden Retriever (Bella).jpg', 'category' => 'Dogs'],
    7 => ['name' => 'Daisy — Bulldog', 'price' => 16000, 'image' => 'Assets/Dog/Bulldog(Daisy).jpg', 'category' => 'Dogs'],
    8 => ['name' => 'Teddy — Shih Tzu', 'price' => 14000, 'image' => 'Assets/Dog/Shih_Tzu(Teddy).jpg', 'category' => 'Dogs'],
    9 => ['name' => 'Coco — Pomeranian', 'price' => 22000, 'image' => 'Assets/Dog/Pomeranian (Coco).jpg', 'category' => 'Dogs'],
    10 => ['name' => 'Bruno — Rottweiler', 'price' => 19000, 'image' => 'Assets/Dog/Rottweiler Puppy (Bruno).jpg', 'category' => 'Dogs'],
    11 => ['name' => 'Milo — Husky', 'price' => 25000, 'image' => 'Assets/Dog/Siberian Husky Puppy (Milo).jpg', 'category' => 'Dogs'],
    12 => ['name' => 'Luna — British Shorthair', 'price' => 18500, 'image' => 'Assets/Cat/British Shorthair (Luna).jpg', 'category' => 'Cats'],
    13 => ['name' => 'Whiskers — Persian', 'price' => 22000, 'image' => 'Assets/Cat/Persian Cat (Whiskers).jpg', 'category' => 'Cats'],
    14 => ['name' => 'Shadow — Maine Coon', 'price' => 20000, 'image' => 'Assets/Cat/Maine Coon (Shadow).jpg', 'category' => 'Cats'],
    15 => ['name' => 'Misty — Ragdoll', 'price' => 24000, 'image' => 'Assets/Cat/Ragdoll (Misty).jpg', 'category' => 'Cats'],
    16 => ['name' => 'Tiger — Bengal', 'price' => 19000, 'image' => 'Assets/Cat/Bengal Cat (Tiger).jpg', 'category' => 'Cats'],
    17 => ['name' => 'Smudge — Siamese', 'price' => 17000, 'image' => 'Assets/Cat/Siamese Cat (Smudge).jpg', 'category' => 'Cats'],
    18 => ['name' => 'Nala — Abyssinian', 'price' => 21000, 'image' => 'Assets/Cat/Abyssinian Cat (Nala).jpg', 'category' => 'Cats'],
    19 => ['name' => 'Goldie — Goldfish', 'price' => 500, 'image' => 'Assets/Fish/Goldfish (Goldie).jpg', 'category' => 'Fish'],
    20 => ['name' => 'Nemo — Clownfish', 'price' => 800, 'image' => 'Assets/Fish/Clownfish (Nemo).jpg', 'category' => 'Fish'],
    21 => ['name' => 'Bubbles — Betta', 'price' => 600, 'image' => 'Assets/Fish/Betta Fish (Bubbles).jpg', 'category' => 'Fish'],
    22 => ['name' => 'Finley — Guppy', 'price' => 400, 'image' => 'Assets/Fish/Guppy (Finley).jpg', 'category' => 'Fish'],
    23 => ['name' => 'Coral — Angelfish', 'price' => 1200, 'image' => 'Assets/Fish/Angelfish (Coral).jpg', 'category' => 'Fish'],
    24 => ['name' => 'Splash — Tetra', 'price' => 300, 'image' => 'Assets/Fish/Tetra Fish (Splash).jpg', 'category' => 'Fish'],
    25 => ['name' => 'Pearl — Molly', 'price' => 450, 'image' => 'Assets/Fish/Molly Fish (Pearl).jpg', 'category' => 'Fish'],
    26 => ['name' => 'Rio — African Grey', 'price' => 45000, 'image' => 'Assets/Birds/African Grey Parrot (Rio).jpg', 'category' => 'Birds'],
    27 => ['name' => 'Sunny — Macaw', 'price' => 55000, 'image' => 'Assets/Birds/Macaw Parrot (Sunny).jpg', 'category' => 'Birds'],
    28 => ['name' => 'Tweety — Canary', 'price' => 8000, 'image' => 'Assets/Birds/Canary (Tweety).jpg', 'category' => 'Birds'],
    29 => ['name' => 'Coco — Cockatiel', 'price' => 12000, 'image' => 'Assets/Birds/Cockatiel (Coco).jpg', 'category' => 'Birds'],
    30 => ['name' => 'Phoenix — Lovebird', 'price' => 6000, 'image' => 'Assets/Birds/Lovebird (Phoenix).jpg', 'category' => 'Birds'],
    31 => ['name' => 'Zeus — Eagle', 'price' => 75000, 'image' => 'Assets/Birds/Eagle (Zeus).jpg', 'category' => 'Birds'],
    32 => ['name' => 'Sky — Swan', 'price' => 35000, 'image' => 'Assets/Birds/Swan (Sky).jpg', 'category' => 'Birds']
>>>>>>> 6915c4197beae6453652ee6418ca4caff90fc71f
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$pet = $petDB[$id] ?? null;

if (!$pet) {
    die('<h2>Pet not found.</h2><a href="index.php">Return to Homepage</a>');
}

// Set defaults for missing dynamic properties
$pet['status'] = 'Available for Adoption';
$pet['date'] = date('M d, Y', strtotime('-' . rand(1, 14) . ' days'));
<<<<<<< HEAD
=======
$pet['desc'] = "Looking for a loving home! This beautiful " . strtolower($pet['category']) . " is healthy, vet-checked, and ready to become your new best friend. Comes completely vaccinated and dewormed with up-to-date health records. Known for an affectionate personality and great temperament, making for a perfect family companion.";
>>>>>>> 6915c4197beae6453652ee6418ca4caff90fc71f

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($pet['name']); ?> — Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ps-details-container {
            max-width: 1100px;
            margin: 40px auto 80px;
            display: flex;
            gap: 50px;
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            border: 1px solid #e8e0d4;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
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
            max-height: 600px;
        }

        .ps-details-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .ps-details-header {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .ps-meta-badge {
            background: #f5ecd8;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            color: #8b6840;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .ps-details-title {
            font-family: "Playfair Display", serif;
            font-size: 38px;
            color: #2c1a0e;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .ps-details-id {
            font-size: 14px;
            color: #888;
            margin-bottom: 24px;
            font-weight: 500;
            display: flex;
            gap: 15px;
        }

        .ps-details-price {
            font-size: 34px;
            font-weight: 700;
            color: #b5860d;
            margin-bottom: 24px;
        }

        .ps-details-desc {
            font-size: 16px;
            line-height: 1.8;
            color: #555;
            margin-bottom: 35px;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
            padding: 24px 0;
        }

        .ps-details-actions {
            display: flex;
            gap: 15px;
        }

        .ps-btn-add {
            flex: 1;
            background: #b5860d;
            color: white;
            border: none;
            padding: 18px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Nunito', sans-serif;
        }

        .ps-btn-add:hover {
            background: #9a7210;
        }

        .ps-btn-buy {
            flex: 1;
            background: #2c1a0e;
            color: white;
            border: none;
            padding: 18px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            font-family: 'Nunito', sans-serif;
        }

        .ps-btn-buy:hover {
            background: #4a3020;
        }

        .ps-btn-wish {
            width: 60px;
            background: transparent;
            border: 2px solid #e8e0d4;
            border-radius: 8px;
            font-size: 26px;
            color: #666;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }

        .ps-btn-wish:hover {
            background: #fdfaf6;
            border-color: #d8c094;
        }

        .ps-btn-wish.active {
            color: #e74c3c;
            border-color: #e74c3c;
            background: #fdf5f5;
        }

        @media (max-width: 768px) {
            .ps-details-container {
                flex-direction: column;
                padding: 24px;
                margin: 20px;
                gap: 30px;
            }
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
        <div class="ps-details-container">
            <div class="ps-details-left">
                <img src="<?php echo htmlspecialchars($pet['image']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>">
            </div>
            <div class="ps-details-right">
                <div class="ps-details-header">
                    <span class="ps-meta-badge"><?php echo htmlspecialchars($pet['category']); ?></span>
                    <span class="ps-meta-badge status-badge"><?php echo htmlspecialchars($pet['status']); ?></span>
                </div>

                <h1 class="ps-details-title"><?php echo htmlspecialchars($pet['name']); ?></h1>

                <div class="ps-details-id">
                    <span><strong>Pet ID:</strong> #<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></span>
                    <span>|</span>
                    <span><strong>Listed On:</strong> <?php echo $pet['date']; ?></span>
                </div>

                <div class="ps-details-price">₹<?php echo number_format($pet['price']); ?></div>

                <div class="ps-details-desc">
                    <?php echo htmlspecialchars($pet['desc']); ?>
                </div>

                <div class="ps-details-actions">
                    <button class="ps-btn-add" id="add-to-cart">Add to Cart</button>
                    <button class="ps-btn-buy" id="buy-now">Buy Now</button>
                    <button class="ps-btn-wish" id="toggle-wishlist">♡</button>
                </div>
            </div>
        </div>
    </div>

    <footer id="contact">
        <div class="ps-footer" style="margin-top: 0;">
            <div class="ps-footer-bottom" style="text-align: center; color: white;">
                © 2026 Paws Store. Made with 🐾 in India.
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let cart = JSON.parse(localStorage.getItem('pawsCart')) || [];
            let wishlist = JSON.parse(localStorage.getItem('pawsWishlist')) || [];

            const petId = "<?php echo $id; ?>";
            const petName = "<?php echo addslashes($pet['name']); ?>";
            const petPrice = <?php echo $pet['price']; ?>;
            const petImage = "<?php echo addslashes($pet['image']); ?>";

            const cartBtn = document.getElementById('add-to-cart');
            const buyNowBtn = document.getElementById('buy-now');
            const wishBtn = document.getElementById('toggle-wishlist');
            const cartCountElement = document.getElementById('cart-count');

            function updateCartCount() {
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                if (cartCountElement) {
                    cartCountElement.textContent = totalItems;
                    cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
                }
            }

            function updateWishlistIcon() {
                if (wishlist.find(item => item.id === petId)) {
                    wishBtn.classList.add('active');
                    wishBtn.innerHTML = '♥';
                } else {
                    wishBtn.classList.remove('active');
                    wishBtn.innerHTML = '♡';
                }
            }

            function addToCart() {
                const existingPet = cart.find(item => item.id === petId);
                if (existingPet) {
                    existingPet.quantity += 1;
                } else {
                    cart.push({
                        id: petId,
                        name: petName,
                        price: petPrice,
                        image: petImage,
                        quantity: 1
                    });
                }
                localStorage.setItem('pawsCart', JSON.stringify(cart));
                updateCartCount();
            }

            cartBtn.addEventListener('click', function() {
                addToCart();
                alert(petName + " added to cart!");
            });

            buyNowBtn.addEventListener('click', function() {
                addToCart();
                window.location.href = 'cart.php';
            });

            wishBtn.addEventListener('click', function() {
                const existingIndex = wishlist.findIndex(item => item.id === petId);
                if (existingIndex > -1) {
                    wishlist.splice(existingIndex, 1);
                    alert(petName + " removed from wishlist!");
                } else {
                    // We format price with '₹' string specifically for the wishlist screen's format
                    wishlist.push({
                        id: petId,
                        name: petName,
                        price: "₹" + petPrice.toLocaleString('en-IN'),
                        image: petImage
                    });
                    alert(petName + " added to wishlist!");
                }
                localStorage.setItem('pawsWishlist', JSON.stringify(wishlist));
                updateWishlistIcon();
            });

            updateCartCount();
            updateWishlistIcon();
        });
    </script>
</body>

</html>