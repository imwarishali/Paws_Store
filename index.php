<?php

require_once 'config.php';
require_once 'db.php';

// Pagination & Caching
$limit = ITEMS_PER_PAGE;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$cache_key = "pets_page_{$page}";
$cache_time = CACHE_DURATION;

// Try to get from session cache
if (isset($_SESSION[$cache_key]) && isset($_SESSION["cache_time_{$page}"]) && (time() - $_SESSION["cache_time_{$page}"]) < $cache_time) {
  $db_pets = $_SESSION[$cache_key];
} else {
  try {
    // FIX: Use prepared statement to prevent SQL injection
    $stmt = $pdo->prepare("SELECT * FROM pets ORDER BY id ASC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    $db_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION[$cache_key] = $db_pets;
    $_SESSION["cache_time_{$page}"] = time();
  } catch (PDOException $e) {
    error_log("Database error in index.php: " . $e->getMessage());
    $db_pets = [];
  }
}

// Get total count for pagination
try {
  $total_result = $pdo->query("SELECT COUNT(*) as count FROM pets");
  $total_count = $total_result->fetch(PDO::FETCH_ASSOC)['count'];
  $total_pages = ceil($total_count / $limit);
} catch (PDOException $e) {
  error_log("Database error in index.php: " . $e->getMessage());
  $total_pages = 1;
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Paws Store - Buy healthy puppies, kittens, fish & birds online across India. Verified breeders, 100% vaccinated pets, home delivery. Shop now!" />
  <meta name="keywords" content="buy pets online, puppies, kittens, fish, birds, pet store India, healthy pets, vaccinated pets, pet delivery" />
  <meta name="author" content="Paws Store" />
  <meta property="og:title" content="Paws Store — India's Most Trusted Online Pet Store" />
  <meta property="og:description" content="Find your perfect pet! Verified breeders, healthy puppies, kittens & more. Free delivery across India." />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://pawsstore.in" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Paws Store — Buy Pets Online India" />
  <meta name="twitter:description" content="Verified pet breeders, healthy vaccinated pets, fast delivery." />
  <title>Paws Store — India's Most Trusted Online Pet Store | Buy Healthy Pets</title>
  <link rel="canonical" href="https://pawsstore.in" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap">
  </noscript>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>
  <nav class="fk-nav-header">
    <div class="fk-nav-top">
      <a href="#home" class="fk-logo">🐾 Paws Store</a>

      <div class="fk-search-container">
        <span class="fk-search-icon" id="search-btn" style="cursor: pointer;">🔍</span>
        <input type="text" class="fk-search-input" id="search-input" placeholder="Search for Pets, Breeds and More">
      </div>

      <div class="fk-nav-right">

        <div class="fk-dropdown-wrapper">
          <button class="fk-login-btn">
            <span class="fk-icon-user">👤</span>
            <?php echo isset($_SESSION["user"]) ? "Account" : "Login"; ?>
            <span class="fk-chevron">⌄</span>
          </button>

          <div class="fk-dropdown-menu">
            <?php if (!isset($_SESSION["user"])): ?>
              <div class="fk-new-customer">
                <span>Existing user?</span>
                <a href="auth/login.php" class="fk-signup-link">Log In</a>
              </div>
              <div class="fk-new-customer">
                <span>New customer?</span>
                <a href="auth/register.php" class="fk-signup-link">Sign Up</a>
              </div>
              <hr class="fk-divider">
            <?php endif; ?>

            <a href="profile.php" class="fk-menu-item"><span class="fk-menu-icon">👤</span> My Profile</a>
            <a href="order_history.php" class="fk-menu-item"><span class="fk-menu-icon">📦</span> Orders</a>
            <a href="wishlist.php" class="fk-menu-item"><span class="fk-menu-icon">🤍</span> Wishlist</a>

            <?php if (isset($_SESSION["user"])): ?>
              <hr class="fk-divider">
              <a href="auth/logout.php" class="fk-menu-item"><span class="fk-menu-icon">🚪</span> Logout</a>
            <?php endif; ?>
          </div>
        </div>

        <a href="cart.php" class="fk-cart-btn">
          <span class="fk-cart-icon">🛒</span> Cart
          <span id="cart-count" class="cart-count">0</span>
        </a>
      </div>
    </div>
  </nav>
  <div class="fk-nav-categories">
    <div class="fk-categories-wrapper" style="display: flex; justify-content: center;">
      <a href="#home" class="fk-cat-item active" data-category="all" style="text-decoration: none;">
        <div class="fk-cat-img">🏠</div>
        Home
      </a>
      <a href="#categories" class="fk-cat-item" data-category="dogs" style="text-decoration: none;">
        <div class="fk-cat-img">🛒</div>
        Shop
      </a>
      <a href="#pets" class="fk-cat-item" data-category="cats" style="text-decoration: none;">
        <div class="fk-cat-img">🐾</div>
        Pets
      </a>
      <a href="other_services.php" class="fk-cat-item" style="text-decoration: none;">
        <div class="fk-cat-img">🏥</div>
        Other Services
      </a>
      <a href="#about" class="fk-cat-item" data-category="fish" style="text-decoration: none;">
        <div class="fk-cat-img">ℹ️</div>
        About Us
      </a>
      <a href="#contact" class="fk-cat-item" data-category="birds" style="text-decoration: none;">
        <div class="fk-cat-img">📞</div>
        Contact Us
      </a>
    </div>
  </div>

  <!-- Promo Marquee Banner -->
  <div class="promo-marquee-wrapper">
    <div class="promo-marquee">
      <span class="marquee-item coupon-item" onclick="copyCoupon('FIRST10', '✨ FIRST10 → 10% OFF on first purchase')">✨ <span class="coupon-code">FIRST10</span> → 10% OFF on first purchase</span>
      <span class="marquee-divider">•</span>
      <span class="marquee-item coupon-item" onclick="copyCoupon('BULK5', '📦 BULK5 → 5% OFF on 2+ pets')">📦 <span class="coupon-code">BULK5</span> → 5% OFF on 2+ pets</span>
      <span class="marquee-divider">•</span>
      <span class="marquee-item coupon-item" onclick="copyCoupon('VET500', '🏥 VET500 → ₹500 OFF on vet consultation')">🏥 <span class="coupon-code">VET500</span> → ₹500 OFF on vet consultation</span>
      <span class="marquee-divider">•</span>
      <span class="marquee-item coupon-item" onclick="copyCoupon('SAVE20', '⭐ SAVE20 → 20% OFF on selected pets')">⭐ <span class="coupon-code">SAVE20</span> → 20% OFF on selected pets</span>
      <span class="marquee-divider">•</span>
      <span class="marquee-item coupon-item" onclick="copyCoupon('CARD15', '💳 CARD15 → 15% OFF with credit/debit card')">💳 <span class="coupon-code">CARD15</span> → 15% OFF with credit/debit card</span>
      <span class="marquee-divider">•</span>
      <span class="marquee-item coupon-item" onclick="copyCoupon('SUMMER25', '🎊 SUMMER25 → 25% OFF summer special collection')">🎊 <span class="coupon-code">SUMMER25</span> → 25% OFF summer special collection</span>
      <span class="marquee-divider">•</span>
      <span class="marquee-item coupon-item" onclick="copyCoupon('FIRST10', '✨ FIRST10 → 10% OFF on first purchase')">✨ <span class="coupon-code">FIRST10</span> → 10% OFF on first purchase</span>
      <span class="marquee-divider">•</span>
      <span class="marquee-item coupon-item" onclick="copyCoupon('BULK5', '📦 BULK5 → 5% OFF on 2+ pets')">📦 <span class="coupon-code">BULK5</span> → 5% OFF on 2+ pets</span>
      <span class="marquee-divider">•</span>
      <span class="marquee-item coupon-item" onclick="copyCoupon('VET500', '🏥 VET500 → ₹500 OFF on vet consultation')">🏥 <span class="coupon-code">VET500</span> → ₹500 OFF on vet consultation</span>
    </div>
  </div>

  <!-- Carousel Banner -->
  <div class="ps-carousel-wrapper">
    <div class="ps-carousel-container" id="ps-carousel-container">
      <div class="ps-carousel-track" id="ps-carousel-track">
        <!-- Slide 1 -->
        <div class="ps-slide">
          <img src="Assets/Dog/Golden Retriever (Bella).jpg" alt="Dogs" loading="lazy" width="800" height="500">
          <div class="ps-slide-content">
            <h2>Bring Home Joy</h2>
            <p>Find your perfect canine companion from our verified breeders across India.</p>
            <a href="category.php?type=dogs"><button class="ps-slide-btn">Shop Dogs</button></a>
          </div>
        </div>
        <!-- Slide 2 -->
        <div class="ps-slide">
          <img src="Assets/Cat/Maine Coon (Shadow).jpg" alt="Cats" loading="lazy" width="800" height="500">
          <div class="ps-slide-content">
            <h2>Purr-fect Friends</h2>
            <p>Explore our beautiful and healthy cat breeds available for adoption today.</p>
            <a href="category.php?type=cats"><button class="ps-slide-btn">Shop Cats</button></a>
          </div>
        </div>
        <!-- Slide 3 -->
        <div class="ps-slide">
          <img src="Assets/Birds/Macaw Parrot (Sunny).jpg" alt="Birds" loading="lazy" width="800" height="500">
          <div class="ps-slide-content">
            <h2>Feathered Wonders</h2>
            <p>Add some color to your life with our exotic, friendly, and talkative birds.</p>
            <a href="category.php?type=birds"><button class="ps-slide-btn">Shop Birds</button></a>
          </div>
        </div>
        <!-- Slide 4 -->
        <div class="ps-slide">
          <img src="Assets/Fish/Goldfish (Goldie).jpg" alt="Fish" loading="lazy" width="800" height="500">
          <div class="ps-slide-content">
            <h2>Aquatic Beauties</h2>
            <p>Dive into our collection of vibrant and healthy freshwater and saltwater fish.</p>
            <a href="category.php?type=fish"><button class="ps-slide-btn">Shop Fish</button></a>
          </div>
        </div>
      </div>
      <button class="ps-carousel-nav prev" id="ps-btn-prev">&#10094;</button>
      <button class="ps-carousel-nav next" id="ps-btn-next">&#10095;</button>
      <div class="ps-carousel-dots" id="ps-carousel-dots">
        <div class="ps-dot active"></div>
        <div class="ps-dot"></div>
        <div class="ps-dot"></div>
        <div class="ps-dot"></div>
      </div>
    </div>
  </div>

  <!-- Hero -->
  <div class="ps-hero" id="home">
    <div class="ps-hero-text">
      <div class="ps-hero-tag">India's #1 Pet Store</div>
      <h1 class="ps-hero-h1">
        Find your perfect<br /><span>companion</span> today
      </h1>
      <p class="ps-hero-sub">
        India's most trusted pet store — verified breeders, healthy pets,
        delivered with love across India.
      </p>
      <div class="ps-hero-btns">
        <a href="#pets"><button class="ps-btn-primary">Browse Pets</button></a>
        <a href="#categories"><button class="ps-btn-secondary">Shop by Category</button></a>
      </div>
      <div class="ps-hero-trust">
        🐾 Trusted by 10,000+ happy pet owners across India
      </div>
    </div>
    <div class="ps-hero-img">
      <img src="Assets/pet_group.png" alt="Pet Group" loading="lazy">
    </div>
  </div>

  <!-- Ticker -->
  <div class="ps-ticker">
    🐾 FREE VET CONSULTATION ON YOUR FIRST PURCHASE &nbsp;&bull;&nbsp; SAFE
    DELIVERY PAN INDIA &nbsp;&bull;&nbsp; ALL PETS VACCINATED &amp; DEWORMED
    &nbsp;&bull;&nbsp; VERIFIED BREEDERS ONLY
  </div>

  <!-- Trust Stats Section -->
  <div class="ps-trust-stats">
    <div class="ps-trust-stat-item">
      <div class="ps-trust-stat-number">10,000+</div>
      <div class="ps-trust-stat-label">Happy Pet Owners</div>
    </div>
    <div class="ps-trust-stat-item">
      <div class="ps-trust-stat-number">500+</div>
      <div class="ps-trust-stat-label">Dog Breeds & Varieties</div>
    </div>
    <div class="ps-trust-stat-item">
      <div class="ps-trust-stat-number">100%</div>
      <div class="ps-trust-stat-label">Verified Breeders</div>
    </div>
    <div class="ps-trust-stat-item">
      <div class="ps-trust-stat-number">24/7</div>
      <div class="ps-trust-stat-label">Customer Support</div>
    </div>
    <div class="ps-trust-stat-item">
      <div class="ps-trust-stat-number">PAN</div>
      <div class="ps-trust-stat-label">India Delivery</div>
    </div>
  </div>

  <!-- Why Choose Us -->
  <div class="ps-section">
    <div class="ps-section-title">Why Choose Paws Store?</div>
    <div class="ps-section-sub">
      We make it easy, safe and joyful to bring a pet home
    </div>
    <div class="ps-why-grid">
      <div class="ps-why-card">
        <div class="ps-why-icon">🏥</div>
        <div class="ps-why-title">Vet Certified</div>
        <div class="ps-why-desc">
          All pets health checked before listing
        </div>
      </div>
      <div class="ps-why-card">
        <div class="ps-why-icon">✅</div>
        <div class="ps-why-title">Verified Breeders</div>
        <div class="ps-why-desc">
          100% trusted &amp; background-checked sellers
        </div>
      </div>
      <div class="ps-why-card">
        <div class="ps-why-icon">🚚</div>
        <div class="ps-why-title">Safe Delivery</div>
        <div class="ps-why-desc">Pan India shipping with live tracking</div>
      </div>
      <div class="ps-why-card">
        <div class="ps-why-icon">💬</div>
        <div class="ps-why-title">24/7 Support</div>
        <div class="ps-why-desc">Expert pet care team always available</div>
      </div>
    </div>
  </div>

  <!-- Categories -->
  <div class="ps-section ps-cats" style="padding-top: 52px" id="categories">
    <div class="ps-section-title">Shop by Category</div>
    <div class="ps-section-sub">Everything your pet needs in one place</div>
    <div class="ps-cats-grid">
      <a href="category.php?type=dogs" style="text-decoration: none; color: inherit;">
        <div class="ps-cat-card">
          <div class="ps-cat-img"><img src="Assets/Dog/Labrador (Max).jpg" alt="Dogs" loading="lazy"></div>
          <div class="ps-cat-name">Dogs</div>
        </div>
      </a>
      <a href="category.php?type=cats" style="text-decoration: none; color: inherit;">
        <div class="ps-cat-card">
          <div class="ps-cat-img"><img src="Assets/Cat/British Shorthair (Luna).jpg" alt="Cats" loading="lazy"></div>
          <div class="ps-cat-name">Cats</div>
        </div>
      </a>
      <a href="category.php?type=fish" style="text-decoration: none; color: inherit;">
        <div class="ps-cat-card">
          <div class="ps-cat-img"><img src="Assets/Fish/Goldfish (Goldie).jpg" alt="Fish" loading="lazy"></div>
          <div class="ps-cat-name">Fish</div>
        </div>
      </a>
      <a href="category.php?type=birds" style="text-decoration: none; color: inherit;">
        <div class="ps-cat-card">
          <div class="ps-cat-img"><img src="Assets/Birds/African Grey Parrot (Rio).jpg" alt="Birds" loading="lazy"></div>
          <div class="ps-cat-name">Birds</div>
        </div>
      </a>
    </div>
  </div>

  <!-- Featured Pets -->
  <div class="ps-section ps-featured" style="padding-top: 52px" id="pets">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
      <div>
        <div class="ps-section-title" id="show-all-pets" style="cursor: pointer;">Suggested for You</div>
        <div class="ps-section-sub" style="margin-bottom: 0;">
          All pets are vaccinated, dewormed &amp; vet-checked
        </div>
      </div>
      <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <select id="location-filter" class="ps-filter-select">
          <option value="all">📍 Location: All</option>
          <option value="Ahmedabad">Ahmedabad</option>
          <option value="Bengaluru">Bengaluru</option>
          <option value="Chandigarh">Chandigarh</option>
          <option value="Chennai">Chennai</option>
          <option value="Delhi">Delhi</option>
          <option value="Hyderabad">Hyderabad</option>
          <option value="Jaipur">Jaipur</option>
          <option value="Kolkata">Kolkata</option>
          <option value="Lucknow">Lucknow</option>
          <option value="Mumbai">Mumbai</option>
          <option value="Pune">Pune</option>
        </select>
        <select id="breed-filter" class="ps-filter-select">
          <option value="all">🐾 Breed: All</option>
          <option value="labrador">Labrador</option>
          <option value="pug">Pug</option>
          <option value="german-shepherd">German Shepherd</option>
          <option value="bulldog">Bulldog</option>
          <option value="shih-tzu">Shih Tzu</option>
          <option value="pomeranian">Pomeranian</option>
          <option value="rottweiler">Rottweiler</option>
          <option value="husky">Husky</option>
          <option value="british-shorthair">British Shorthair</option>
          <option value="persian">Persian</option>
          <option value="maine-coon">Maine Coon</option>
          <option value="ragdoll">Ragdoll</option>
          <option value="bengal">Bengal</option>
          <option value="siamese">Siamese</option>
          <option value="abyssinian">Abyssinian</option>
          <option value="goldfish">Goldfish</option>
          <option value="clownfish">Clownfish</option>
          <option value="betta">Betta</option>
          <option value="guppy">Guppy</option>
          <option value="angelfish">Angelfish</option>
          <option value="tetra">Tetra</option>
          <option value="molly">Molly</option>
          <option value="african-grey">African Grey</option>
        </select>
        <select id="health-filter" class="ps-filter-select">
          <option value="all">💪 Health: All</option>
          <option value="vaccinated">Vaccinated</option>
          <option value="dewormed">Dewormed</option>
          <option value="house-trained">House Trained</option>
          <option value="healthy">Healthy</option>
        </select>
        <select id="age-filter" class="ps-filter-select">
          <option value="all">⏱️ Age: All</option>
          <option value="0-3">0-3 Months</option>
          <option value="3-6">3-6 Months</option>
          <option value="6plus">6+ Months</option>
        </select>
        <select id="price-filter" class="ps-filter-select">
          <option value="all">💰 Price Range: All</option>
          <option value="under-10k">Under ₹10,000</option>
          <option value="10k-20k">₹10,000 - ₹20,000</option>
          <option value="above-20k">Above ₹20,000</option>
        </select>
        <select id="price-sort" class="ps-filter-select">
          <option value="default">↕️ Sort by: Default</option>
          <option value="low-high">Price: Low to High</option>
          <option value="high-low">Price: High to Low</option>
        </select>
        <button id="reset-filters" class="ps-filter-select" style="background: linear-gradient(135deg, #c9a876 0%, #a67c52 100%); color: white; border: none; cursor: pointer; font-weight: 600; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">🔄 Reset</button>
      </div>
    </div>
    <div class="ps-pets-grid">
      <div class="ps-pet-card" data-category="dogs" data-pet-id="1" data-breed="labrador" data-health-status="vaccinated" data-age-months="2">
        <div class="ps-pet-photo" style="background: #f5ecd8">
          <img src="Assets/Dog/Labrador (Max).jpg" alt="Max" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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

      <div class="ps-pet-card" data-category="dogs" data-pet-id="4" data-breed="pug" data-health-status="vaccinated" data-age-months="2">
        <div class="ps-pet-photo" style="background: #f5ecd8">
          <img src="Assets/Dog/Pug (Charlie).jpg" alt="Charlie" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="dogs" data-pet-id="5" data-breed="german-shepherd" data-health-status="house-trained" data-age-months="4">
        <div class="ps-pet-photo" style="background: #fceee0">
          <img src="Assets/Dog/Golden Retriever (Bella).jpg" alt="Bella" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="dogs" data-pet-id="7" data-breed="bulldog" data-health-status="vaccinated" data-age-months="2">
        <div class="ps-pet-photo" style="background: #f5ecd8">
          <img src="Assets/Dog/Bulldog(Daisy).jpg" alt="Daisy" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="dogs" data-pet-id="8" data-breed="shih-tzu" data-health-status="house-trained" data-age-months="3">
        <div class="ps-pet-photo" style="background: #fceee0">
          <img src="Assets/Dog/Shih_Tzu(Teddy).jpg" alt="Teddy" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="dogs" data-pet-id="9" data-breed="pomeranian" data-health-status="dewormed" data-age-months="1">
        <div class="ps-pet-photo" style="background: #eef4f0">
          <img src="Assets/Dog/Pomeranian (Coco).jpg" alt="Coco" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="dogs" data-pet-id="10" data-breed="rottweiler" data-health-status="vaccinated" data-age-months="5">
        <div class="ps-pet-photo" style="background: #f5ecd8">
          <img src="Assets/Dog/Rottweiler Puppy (Bruno).jpg" alt="Bruno" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="dogs" data-pet-id="11" data-breed="husky" data-health-status="house-trained" data-age-months="3">
        <div class="ps-pet-photo" style="background: #fceee0">
          <img src="Assets/Dog/Siberian Husky Puppy (Milo).jpg" alt="Milo" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="cats" data-pet-id="12" data-breed="british-shorthair" data-health-status="vaccinated" data-age-months="3">
        <div class="ps-pet-photo" style="background: #fceee0">
          <img src="Assets/Cat/British Shorthair (Luna).jpg" alt="Luna" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="cats" data-pet-id="13" data-breed="persian" data-health-status="house-trained" data-age-months="4">
        <div class="ps-pet-photo" style="background: #eef4f0">
          <img src="Assets/Cat/Persian Cat (Whiskers).jpg" alt="Whiskers" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="cats" data-pet-id="14" data-breed="maine-coon" data-health-status="dewormed" data-age-months="2">
        <div class="ps-pet-photo" style="background: #f5ecd8">
          <img src="Assets/Cat/Maine Coon (Shadow).jpg" alt="Shadow" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="cats" data-pet-id="15" data-breed="ragdoll" data-health-status="vaccinated" data-age-months="3">
        <div class="ps-pet-photo" style="background: #fceee0">
          <img src="Assets/Cat/Ragdoll (Misty).jpg" alt="Misty" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="cats" data-pet-id="16" data-breed="bengal" data-health-status="house-trained" data-age-months="4">
        <div class="ps-pet-photo" style="background: #eef4f0">
          <img src="Assets/Cat/Bengal Cat (Tiger).jpg" alt="Tiger" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="cats" data-pet-id="17" data-breed="siamese" data-health-status="dewormed" data-age-months="2">
        <div class="ps-pet-photo" style="background: #f5ecd8">
          <img src="Assets/Cat/Siamese Cat (Smudge).jpg" alt="Smudge" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="cats" data-pet-id="18" data-breed="abyssinian" data-health-status="vaccinated" data-age-months="3">
        <div class="ps-pet-photo" style="background: #fceee0">
          <img src="Assets/Cat/Abyssinian Cat (Nala).jpg" alt="Nala" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="fish" data-pet-id="19" data-breed="goldfish" data-health-status="healthy" data-age-months="2">
        <div class="ps-pet-photo" style="background: #e0f2ff">
          <img src="Assets/Fish/Goldfish (Goldie).jpg" alt="Goldie" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="fish" data-pet-id="20" data-breed="clownfish" data-health-status="healthy" data-age-months="1">
        <div class="ps-pet-photo" style="background: #e8f5e9">
          <img src="Assets/Fish/Clownfish (Nemo).jpg" alt="Nemo" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="fish" data-pet-id="21" data-breed="betta" data-health-status="healthy" data-age-months="3">
        <div class="ps-pet-photo" style="background: #fff3e0">
          <img src="Assets/Fish/Betta Fish (Bubbles).jpg" alt="Bubbles" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="fish" data-pet-id="22" data-breed="guppy" data-health-status="healthy" data-age-months="2">
        <div class="ps-pet-photo" style="background: #e0f2ff">
          <img src="Assets/Fish/Guppy (Finley).jpg" alt="Finley" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="fish" data-pet-id="23" data-breed="angelfish" data-health-status="healthy" data-age-months="4">
        <div class="ps-pet-photo" style="background: #e8f5e9">
          <img src="Assets/Fish/Angelfish (Coral).jpg" alt="Coral" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="fish" data-pet-id="24" data-breed="tetra" data-health-status="healthy" data-age-months="1">
        <div class="ps-pet-photo" style="background: #fff3e0">
          <img src="Assets/Fish/Tetra Fish (Splash).jpg" alt="Splash" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="fish" data-pet-id="25" data-breed="molly" data-health-status="healthy" data-age-months="3">
        <div class="ps-pet-photo" style="background: #e0f2ff">
          <img src="Assets/Fish/Molly Fish (Pearl).jpg" alt="Pearl" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
      <div class="ps-pet-card" data-category="birds" data-pet-id="26" data-breed="african-grey" data-health-status="healthy" data-age-months="12">
        <div class="ps-pet-photo" style="background: #fff8e1">
          <img src="Assets/Birds/African Grey Parrot (Rio).jpg" alt="Rio" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
          <img src="Assets/Birds/Macaw Parrot (Sunny).jpg" alt="Sunny" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
          <img src="Assets/Birds/Canary (Tweety).jpg" alt="Tweety" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
          <img src="Assets/Birds/Cockatiel (Coco).jpg" alt="Coco" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
          <img src="Assets/Birds/Lovebird (Phoenix).jpg" alt="Phoenix" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
          <img src="Assets/Birds/Eagle (Zeus).jpg" alt="Zeus" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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
          <img src="Assets/Birds/Swan (Sky).jpg" alt="Sky" loading="lazy" width="300" height="300" style="width: 100%; height: 100%; object-fit: cover;">
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

    <div id="no-pets-msg" style="display: none; text-align: center; padding: 40px; color: #666; background: #fff; border-radius: 16px; border: 0.5px solid #e8e0d4; margin-top: 16px;">
      <h2 style="color: #2c1a0e; margin-bottom: 10px;">🐾 No pets found</h2>
      <p>We couldn't find any pets matching your search criteria. Try a different keyword!</p>
    </div>

    <!-- Mix (shuffle) the pets randomly on every page load -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        const grid = document.querySelector('#pets .ps-pets-grid');
        if (grid) {
          grid.style.display = 'none'; // Prevent layout thrashing
          const fragment = document.createDocumentFragment();
          const children = Array.from(grid.children);
          while (children.length) {
            fragment.appendChild(children.splice(Math.floor(Math.random() * children.length), 1)[0]);
          }
          grid.appendChild(fragment);
          grid.style.display = ''; // Restore visibility
        }
      });
    </script>

    <div style="text-align: center; margin-top: 40px;">
      <a href="featured_pets.php" class="ps-btn-primary" style="text-decoration: none; padding: 14px 40px; font-size: 16px;">View All Featured Pets</a>
    </div>
  </div>

  <!-- How It Works -->
  <div class="ps-how">
    <div class="ps-how-title">How It Works</div>
    <div class="ps-how-sub">Bringing home your new best friend is simple</div>
    <div class="ps-how-steps">
      <div class="ps-step">
        <div class="ps-step-num">1</div>
        <div class="ps-step-title">Browse Pets</div>
        <div class="ps-step-desc">
          Explore hundreds of verified pets across all categories and breeds
        </div>
      </div>
      <div class="ps-step">
        <div class="ps-step-num">2</div>
        <div class="ps-step-title">Place Your Order</div>
        <div class="ps-step-desc">
          Secure checkout with multiple payment options and instant confirmation
        </div>
      </div>
      <div class="ps-step">
        <div class="ps-step-num">3</div>
        <div class="ps-step-title">Pet Arrives Home</div>
        <div class="ps-step-desc">
          Safe door-to-door delivery with live tracking and vet certificate included
        </div>
      </div>
    </div>
  </div>

  <!-- Testimonials -->
  <div class="ps-testi">
    <div class="ps-section-title">Happy Pet Parents</div>
    <div class="ps-section-sub">Real stories from our customers</div>
    <div class="ps-testi-grid">
      <div class="ps-testi-card">
        <div class="ps-testi-stars">★★★★★</div>
        <div class="ps-testi-text">
          "Got my Golden Retriever from Paws Store — healthy, happy and fully vaccinated. The whole process was so smooth!"
        </div>
        <div class="ps-testi-author">Shaik Rayan</div>
        <div class="ps-testi-city">Bangaluru</div>
      </div>
      <div class="ps-testi-card">
        <div class="ps-testi-stars">★★★★★</div>
        <div class="ps-testi-text">
          "Excellent service! My British Shorthair kitten arrived healthy and the breeder was very cooperative throughout."
        </div>
        <div class="ps-testi-author">Syed Uzaif</div>
        <div class="ps-testi-city">Bengaluru</div>
      </div>
      <div class="ps-testi-card">
        <div class="ps-testi-stars">★★★★☆</div>
        <div class="ps-testi-text">
          "Fast delivery and the puppy was exactly as described. Very happy with Paws Store. Will definitely buy again!"
        </div>
        <div class="ps-testi-author">Abdul Ali</div>
        <div class="ps-testi-city">Bengaluru</div>
      </div>
    </div>
  </div>

  <!-- About Us -->
  <div class="ps-about" id="about">
    <div class="ps-about-img">
      <img src="Assets/pet_group.png" alt="Pet Group" loading="lazy">
    </div>
    <div class="ps-about-text">
      <div class="ps-about-title">About Paws Store</div>
      <div class="ps-about-desc">
        At Paws Store, we believe every pet deserves a loving home. Founded
        in 2020, we've helped thousands of pet parents across India find their
        perfect companion with ease and confidence.
      </div>
      <div class="ps-about-desc">
        Our mission is to make pet adoption and shopping safe, joyful and
        accessible for everyone. We partner with verified breeders and provide
        vet-certified pets delivered right to your doorstep.
      </div>
    </div>
  </div>

  <!-- Footer (Outside ps-wrap for full width) -->
  <footer id="contact">
    <div class="ps-footer">
      <div class="ps-footer-grid">
        <div>
          <div class="ps-footer-brand">🐾 Paws Store</div>
          <div class="ps-footer-tagline">
            Bringing joy home,<br />one paw at a time.
          </div>
        </div>
        <div class="ps-footer-col">
          <h4>Quick Links</h4>
          <a href="#home">Home</a>
          <a href="#pets">Pets</a>
          <a href="#categories">Shop</a>
          <a href="#about">About Us</a>
          <a href="testimonials.php">Testimonials</a>
        </div>
        <div class="ps-footer-col">
          <h4>Support</h4>
          <a href="FAQ.php">FAQ</a>
          <a href="order_history.php">Track Order</a>
        </div>
        <div class="ps-footer-col">
          <h4>Contact</h4>
          <div class="ps-footer-contact">📞 <a href="tel:+919798889456" style="color: inherit; text-decoration: none;">+91 97988 89456</a></div>
          <div class="ps-footer-contact">✉️ <a href="mailto:support@pawsstore.in" style="color: inherit; text-decoration: none;">support@pawsstore.in</a></div>
        </div>
        <div class="ps-footer-col">
          <h4>Follow Us</h4>
          <div style="display: flex; gap: 12px; margin-top: 8px;">
            <a href="https://instagram.com/pawsstore" target="_blank" title="Follow on Instagram" style="color: #faf7f2; font-size: 18px; text-decoration: none;">📷</a>
            <a href="https://facebook.com/pawsstore" target="_blank" title="Like on Facebook" style="color: #faf7f2; font-size: 18px; text-decoration: none;">👍</a>
            <a href="https://twitter.com/pawsstore" target="_blank" title="Follow on Twitter" style="color: #faf7f2; font-size: 18px; text-decoration: none;">𝕏</a>
            <a href="https://youtube.com/@pawsstore" target="_blank" title="Subscribe on YouTube" style="color: #faf7f2; font-size: 18px; text-decoration: none;">▶️</a>
          </div>
        </div>
      </div>
      <div class="ps-footer-bottom">
        © 2026 Paws Store. Made with 🐾 in India.
        <div style="margin-top: 10px; font-size: 12px; text-align: center;">
          <a href="privacy-policy.php" style="color: inherit; text-decoration: none; margin: 0 10px;">Privacy Policy</a> |
          <a href="terms-conditions.php" style="color: inherit; text-decoration: none; margin: 0 10px;">Terms & Conditions</a> |
          <a href="refund-policy.php" style="color: inherit; text-decoration: none; margin: 0 10px;">Refund Policy</a> |
          <a href="contact.php" style="color: inherit; text-decoration: none; margin: 0 10px;">Contact Us</a>
        </div>
      </div>
    </div>
  </footer>
  </div>

  <!-- Live Chat Widget (Tawk.to) -->
  <script>
    var Tawk_API = Tawk_API || {},
      Tawk_LoadStart = new Date();
    (function() {
      var s1 = document.createElement("script"),
        s0 = document.getElementsByTagName("script")[0];
      s1.async = true;
      s1.src = 'https://embed.tawk.to/REPLACE_WITH_YOUR_TAWK_ID/1hpojkd1g';
      s1.charset = 'UTF-8';
      s1.setAttribute('crossorigin', '*');
      s0.parentNode.insertBefore(s1, s0);
    })();
  </script>

  <!-- Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=GA_TRACKING_ID"></script>
  <script>
    window.dataLayer = window.dataLayer || [];

    function gtag() {
      dataLayer.push(arguments);
    }
    gtag('js', new Date());
    gtag('config', 'GA_TRACKING_ID');
  </script>

  <!-- Floating Action Buttons -->
  <a href="https://wa.me/919798889456?text=Hi!%20I%20am%20interested%20in%20adopting%20a%20pet%20from%20Paws%20Store." target="_blank" class="float-whatsapp" title="Chat with us on WhatsApp">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" style="width: 35px; height: 35px;">
  </a>

  <button class="float-top" id="back-to-top" title="Go to top">↑</button>

  <!-- Mobile App-like Bottom Navigation -->
  <div class="mobile-bottom-nav">
    <a href="index.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
      <span class="mobile-nav-icon">🏠</span>
      <span>Home</span>
    </a>
    <a href="index.php#categories" class="mobile-nav-item">
      <span class="mobile-nav-icon">🔍</span>
      <span>Shop</span>
    </a>
    <a href="wishlist.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'wishlist.php' ? 'active' : ''; ?>">
      <span class="mobile-nav-icon">🤍</span>
      <span>Wishlist</span>
    </a>
    <a href="cart.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'cart.php' ? 'active' : ''; ?>">
      <span class="mobile-nav-icon">🛒</span>
      <span>Cart</span>
      <span id="mobile-cart-count" class="mobile-cart-badge" style="display: none;">0</span>
    </a>
    <a href="profile.php" class="mobile-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
      <span class="mobile-nav-icon">👤</span>
      <span>Profile</span>
    </a>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const categoryCards = document.querySelectorAll('.ps-cat-card');
      const petCards = document.querySelectorAll('.ps-pet-card');
      const showAllButton = document.getElementById('show-all-pets');
      const addToCartButtons = document.querySelectorAll('.ps-pet-add');
      const searchInput = document.getElementById('search-input');
      const searchBtn = document.getElementById('search-btn');

      // SHRINK CATEGORY NAV & HIGHLIGHT ACTIVE ITEM ON SCROLL
      const navItems = document.querySelectorAll('.fk-cat-item');
      const sections = document.querySelectorAll('#home, #categories, #pets, #about, #contact');

      window.addEventListener('scroll', function() {
        if (window.scrollY > 20) {
          document.body.classList.add('nav-scrolled');
        } else {
          document.body.classList.remove('nav-scrolled');
        }

        let current = 'home';
        sections.forEach(section => {
          if (window.scrollY >= (section.offsetTop - 200)) {
            current = section.getAttribute('id');
          }
        });

        navItems.forEach(item => {
          item.classList.remove('active');
          if (item.getAttribute('href') === '#' + current) {
            item.classList.add('active');
          }
        });
      });

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

      const currentUserId = '<?php echo isset($_SESSION["user"]["id"]) ? $_SESSION["user"]["id"] : "guest"; ?>';
      const cartKey = 'pawsCart_' + currentUserId;
      const wishKey = 'pawsWishlist_' + currentUserId;

      // TRANSFER GUEST DATA TO LOGGED-IN USER
      if (currentUserId !== 'guest') {
        let guestWish = JSON.parse(localStorage.getItem('pawsWishlist_guest'));
        if (guestWish && guestWish.length > 0) {
          let userWish = JSON.parse(localStorage.getItem(wishKey)) || [];
          guestWish.forEach(guestItem => {
            if (!userWish.find(item => item.id === guestItem.id)) userWish.push(guestItem);
          });
          localStorage.setItem(wishKey, JSON.stringify(userWish));
          localStorage.removeItem('pawsWishlist_guest');
        }
      }

      // TOAST NOTIFICATION FUNCTION
      function showToast(message, icon = '✅') {
        let container = document.getElementById('toast-container');
        if (!container) {
          container = document.createElement('div');
          container.id = 'toast-container';
          container.className = 'toast-container';
          document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = 'toast-msg';
        toast.innerHTML = `<span class="toast-icon">${icon}</span> <span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
          toast.classList.remove('show');
          setTimeout(() => toast.remove(), 400);
        }, 3000);
      }

      // COPY COUPON FUNCTION
      window.copyCoupon = function(code, fullText) {
        // Copy code to clipboard
        const textArea = document.createElement('textarea');
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        try {
          document.execCommand('copy');
          // Store in localStorage for cart to auto-fill
          localStorage.setItem('copiedCoupon', code);
          showToast(`Copied: ${code}. Open cart to apply! 🎉`, '📋');
        } catch (err) {
          showToast('Failed to copy coupon code', '❌');
        }
        document.body.removeChild(textArea);
      }

      // PHP SESSION CART SYSTEM
      function updateCartCount(count) {
        const cartCountElement = document.getElementById('cart-count');
        const mobileCartCount = document.getElementById('mobile-cart-count');

        if (cartCountElement) {
          cartCountElement.textContent = count;
          cartCountElement.style.display = count > 0 ? 'flex' : 'none';
        }
        if (mobileCartCount) {
          mobileCartCount.textContent = count;
          mobileCartCount.style.display = count > 0 ? 'flex' : 'none';
        }
      }

      // Fetch initial cart count on page load
      fetch('cart_action.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action: 'get'
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            updateCartCount(data.cart_count);
          }
        });

      addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Check if user is logged in
          if (currentUserId === 'guest') {
            alert('🔐 Login to add to cart\n\nPlease log in to your account to add items to your cart.');
            window.location.href = 'auth/login.php?redirect=index.php';
            return;
          }

          const petCard = this.closest('.ps-pet-card');
          const petId = petCard.getAttribute('data-pet-id');
          const petName = petCard.querySelector('.ps-pet-name').textContent;

          fetch('cart_action.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                action: 'add',
                id: petId,
                quantity: 1
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.status === 'success') {
                updateCartCount(data.cart_count);
              }
            });

          this.textContent = 'Added! ✓';
          this.classList.add('added-to-cart');

          clearTimeout(this.addedTimeout);
          this.addedTimeout = setTimeout(() => {
            this.textContent = 'Add to Cart';
            this.classList.remove('added-to-cart');
          }, 2000);

          showToast(petName + " added to cart!", '🛒');
        });
      });

      // WISHLIST SYSTEM
      let wishlist = JSON.parse(localStorage.getItem(wishKey)) || [];
      const wishButtons = document.querySelectorAll('.ps-pet-wish');

      function updateWishlistIcons() {
        wishButtons.forEach(btn => {
          const petCard = btn.closest('.ps-pet-card');
          const petId = petCard.getAttribute('data-pet-id');
          if (wishlist.find(item => item.id === petId)) {
            btn.innerHTML = '♥';
            btn.style.color = '#e74c3c';
          } else {
            btn.innerHTML = '♡';
            btn.style.color = 'inherit';
          }
        });
      }

      wishButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Check if user is logged in
          if (currentUserId === 'guest') {
            alert('❤️ Login to add to wishlist\n\nPlease log in to your account to add items to your wishlist.');
            window.location.href = 'auth/login.php?redirect=index.php';
            return;
          }

          const petCard = this.closest('.ps-pet-card');
          const petId = petCard.getAttribute('data-pet-id');
          const petName = petCard.querySelector('.ps-pet-name').textContent;
          const petPrice = petCard.querySelector('.ps-pet-price').textContent.replace(/[^0-9]/g, '');
          const petImage = petCard.querySelector('img').src;

          const existingIndex = wishlist.findIndex(item => item.id === petId);
          if (existingIndex > -1) {
            wishlist.splice(existingIndex, 1);
            showToast(petName + " removed from wishlist!", '🤍');
          } else {
            wishlist.push({
              id: petId,
              name: petName,
              price: parseInt(petPrice),
              image: petImage
            });
            showToast(petName + " added to wishlist!", '❤️');
          }
          localStorage.setItem(wishKey, JSON.stringify(wishlist));
          updateWishlistIcons();

          this.classList.add('wish-pop');
          setTimeout(() => this.classList.remove('wish-pop'), 300);
        });
      });

      // UNIFIED FILTER SYSTEM
      let currentCategory = 'all';
      let currentSearchTerm = '';

      const locationFilter = document.getElementById('location-filter');
      const breedFilter = document.getElementById('breed-filter');
      const healthFilter = document.getElementById('health-filter');
      const ageFilter = document.getElementById('age-filter');
      const priceFilter = document.getElementById('price-filter');
      const resetButton = document.getElementById('reset-filters');

      function applyFilters() {
        let visibleCount = 0;
        const noPetsMsg = document.getElementById('no-pets-msg');
        const locationVal = locationFilter ? locationFilter.value : 'all';
        const breedVal = breedFilter ? breedFilter.value : 'all';
        const healthVal = healthFilter ? healthFilter.value : 'all';
        const ageVal = ageFilter ? ageFilter.value : 'all';
        const priceVal = priceFilter ? priceFilter.value : 'all';

        petCards.forEach(card => {
          const cardCategory = card.getAttribute('data-category');
          const cardBreed = card.getAttribute('data-breed');
          const cardHealth = card.getAttribute('data-health-status');
          const cardAge = parseInt(card.getAttribute('data-age-months')) || 0;
          const petName = card.querySelector('.ps-pet-name').textContent.toLowerCase();
          const petLoc = card.querySelector('.ps-pet-loc').textContent;
          const petPrice = parseInt(card.querySelector('.ps-pet-price').textContent.replace(/[^0-9]/g, '')) || 0;

          // Category match
          let matchCategory = (currentCategory === 'all' || cardCategory === currentCategory);

          // Search match
          let matchSearch = true;
          if (currentSearchTerm !== '') {
            const searchTerms = currentSearchTerm.split(/\s+/).filter(term => term.length > 0);
            const searchText = `${petName} ${cardCategory} ${cardBreed}`;
            matchSearch = searchTerms.every(term => searchText.includes(term));
          }

          // Location match
          let matchLocation = true;
          if (locationVal !== 'all') {
            matchLocation = petLoc.includes(locationVal);
          }

          // Breed match
          let matchBreed = true;
          if (breedVal !== 'all') {
            matchBreed = (cardBreed === breedVal);
          }

          // Health status match
          let matchHealth = true;
          if (healthVal !== 'all') {
            matchHealth = (cardHealth === healthVal);
          }

          // Age match
          let matchAge = true;
          if (ageVal !== 'all') {
            if (ageVal === '0-3') {
              matchAge = cardAge <= 3;
            } else if (ageVal === '3-6') {
              matchAge = cardAge > 3 && cardAge <= 6;
            } else if (ageVal === '6plus') {
              matchAge = cardAge > 6;
            }
          }

          // Price match
          let matchPrice = true;
          if (priceVal === 'under-10k') {
            matchPrice = petPrice < 10000;
          } else if (priceVal === '10k-20k') {
            matchPrice = petPrice >= 10000 && petPrice <= 20000;
          } else if (priceVal === 'above-20k') {
            matchPrice = petPrice > 20000;
          }

          if (matchCategory && matchSearch && matchLocation && matchBreed && matchHealth && matchAge && matchPrice) {
            card.style.display = 'block';
            visibleCount++;
          } else {
            card.style.display = 'none';
          }
        });

        if (noPetsMsg) {
          noPetsMsg.style.display = visibleCount === 0 ? 'block' : 'none';
        }
      }

      // Reset filters function
      function resetAllFilters() {
        currentCategory = 'all';
        currentSearchTerm = '';
        if (locationFilter) locationFilter.value = 'all';
        if (breedFilter) breedFilter.value = 'all';
        if (healthFilter) healthFilter.value = 'all';
        if (ageFilter) ageFilter.value = 'all';
        if (priceFilter) priceFilter.value = 'all';
        if (searchInput) searchInput.value = '';
        applyFilters();
      }

      // Filter Event Listeners
      if (locationFilter) locationFilter.addEventListener('change', applyFilters);
      if (breedFilter) breedFilter.addEventListener('change', applyFilters);
      if (healthFilter) healthFilter.addEventListener('change', applyFilters);
      if (ageFilter) ageFilter.addEventListener('change', applyFilters);
      if (priceFilter) priceFilter.addEventListener('change', applyFilters);
      if (resetButton) resetButton.addEventListener('click', resetAllFilters);

      categoryCards.forEach(card => {
        card.addEventListener('click', function() {
          currentCategory = this.getAttribute('data-category');
          currentSearchTerm = '';
          searchInput.value = ''; // Clear search when clicking category

          applyFilters();

          categoryCards.forEach(c => c.classList.remove('active'));
          this.classList.add('active');
        });
      });

      // Search event listeners
      let hasScrolledForSearch = false;

      console.log('[Search Debug] Initializing search:', {
        searchInput: !!searchInput,
        searchBtn: !!searchBtn,
        petCards: petCards.length,
        currentSearchTerm,
        currentCategory
      });

      if (searchInput) {
        searchInput.addEventListener('input', function() {
          const previousSearchTerm = currentSearchTerm;
          currentSearchTerm = this.value.toLowerCase().trim();

          console.log('[Search Debug] Input event:', {
            previousValue: previousSearchTerm,
            currentValue: currentSearchTerm
          });

          // Only scroll to pets section when user TYPES (goes from empty to non-empty)
          // Don't scroll when user is backspacing (clearing the search)
          if (previousSearchTerm === '' && currentSearchTerm !== '') {
            // User just started typing - scroll to results
            document.getElementById('pets').scrollIntoView({
              behavior: 'smooth'
            });
          }

          if (currentSearchTerm !== '') {
            currentCategory = 'all'; // Reset category when searching
            categoryCards.forEach(c => c.classList.remove('active'));
          }
          applyFilters();
        });

        searchInput.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
            currentSearchTerm = this.value.toLowerCase().trim();
            applyFilters();
          }
        });
      }

      if (searchBtn) {
        searchBtn.addEventListener('click', function() {
          console.log('[Search Debug] Search button clicked');
          // Don't scroll automatically on button click - just apply filter
          currentSearchTerm = searchInput.value.toLowerCase().trim();

          if (currentSearchTerm !== '') {
            currentCategory = 'all'; // Reset category when searching
            categoryCards.forEach(c => c.classList.remove('active'));
          }

          applyFilters();
        });
      }

      // SHOW ALL PETS
      showAllButton.addEventListener('click', function() {
        currentCategory = 'all';
        currentSearchTerm = '';
        searchInput.value = '';
        if (locationFilter) locationFilter.value = 'all';
        if (priceFilter) priceFilter.value = 'all';

        applyFilters();
        categoryCards.forEach(c => c.classList.remove('active'));
      });

      // PRICE SORTING DROPDOWN
      const priceSortSelect = document.getElementById('price-sort');
      const petsGrid = document.querySelector('#pets .ps-pets-grid');

      if (priceSortSelect && petsGrid) {
        priceSortSelect.addEventListener('change', function() {
          const cards = Array.from(petsGrid.children);

          if (this.value === 'low-high' || this.value === 'high-low') {
            cards.sort((a, b) => {
              const priceA = parseInt(a.querySelector('.ps-pet-price').textContent.replace(/[^0-9]/g, '')) || 0;
              const priceB = parseInt(b.querySelector('.ps-pet-price').textContent.replace(/[^0-9]/g, '')) || 0;

              if (this.value === 'low-high') {
                return priceA - priceB;
              } else {
                return priceB - priceA;
              }
            });

            cards.forEach(card => petsGrid.appendChild(card));
          }
        });
      }

      // CAROUSEL SYSTEM
      const track = document.getElementById('ps-carousel-track');
      // .children gets element nodes, skipping any HTML comments
      const slides = track ? Array.from(track.children) : [];
      const nextButton = document.getElementById('ps-btn-next');
      const prevButton = document.getElementById('ps-btn-prev');
      const dotsNav = document.getElementById('ps-carousel-dots');
      const dots = dotsNav ? Array.from(dotsNav.children) : [];
      const carouselContainer = document.getElementById('ps-carousel-container');

      if (track && slides.length > 0) {
        let currentIndex = 0;
        let autoSlideInterval;

        function updateCarousel(index) {
          track.style.transform = 'translateX(-' + (index * 100) + '%)';
          dots.forEach(dot => dot.classList.remove('active'));
          if (dots[index]) dots[index].classList.add('active');
          currentIndex = index;
        }

        function nextSlide() {
          const nextIndex = (currentIndex + 1) % slides.length;
          updateCarousel(nextIndex);
        }

        function prevSlide() {
          const prevIndex = (currentIndex - 1 + slides.length) % slides.length;
          updateCarousel(prevIndex);
        }

        if (nextButton) nextButton.addEventListener('click', () => {
          nextSlide();
          resetInterval();
        });
        if (prevButton) prevButton.addEventListener('click', () => {
          prevSlide();
          resetInterval();
        });

        dots.forEach((dot, index) => {
          dot.addEventListener('click', () => {
            updateCarousel(index);
            resetInterval();
          });
        });

        function startInterval() {
          autoSlideInterval = setInterval(nextSlide, 10000);
        }

        function resetInterval() {
          clearInterval(autoSlideInterval);
          startInterval();
        }

        if (carouselContainer) {
          carouselContainer.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
          carouselContainer.addEventListener('mouseleave', startInterval);
        }
        startInterval();
      }

      // BACK TO TOP BUTTON
      const backToTopBtn = document.getElementById('back-to-top');
      if (backToTopBtn) {
        window.addEventListener('scroll', () => {
          if (window.scrollY > 600) {
            backToTopBtn.classList.add('visible');
          } else {
            backToTopBtn.classList.remove('visible');
          }
        });
        backToTopBtn.addEventListener('click', () => {
          window.scrollTo({
            top: 0,
            behavior: 'smooth'
          });
        });
      }

      // INIT
      updateCartCount();
      updateWishlistIcons();

    });
  </script>
</body>

</html>