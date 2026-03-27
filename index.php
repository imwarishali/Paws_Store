<?php
session_start();
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Paws Store — Homepage Mockup</title>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap"
    rel="stylesheet" />
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
      <div class="fk-cat-item active" data-category="all">
        <div class="fk-cat-img">🏠</div>
        <a href="#home">Home</a>
      </div>
      <div class="fk-cat-item" data-category="dogs">
        <div class="fk-cat-img">🛒</div>
        <a href="#categories">Shop</a>
      </div>
      <div class="fk-cat-item" data-category="cats">
        <div class="fk-cat-img">🐾</div>
        <a href="#pets">Pets</a>
      </div>
      <div class="fk-cat-item" data-category="fish">
        <div class="fk-cat-img">ℹ️</div>
        <a href="#about">About Us</a>
      </div>
      <div class="fk-cat-item" data-category="birds">
        <div class="fk-cat-img">📞</div>
        <a href="#contact">Contact Us</a>
      </div>
    </div>
  </div>

  <!-- Carousel Banner -->
  <div class="ps-carousel-wrapper">
    <div class="ps-carousel-container" id="ps-carousel-container">
      <div class="ps-carousel-track" id="ps-carousel-track">
        <!-- Slide 1 -->
        <div class="ps-slide">
          <img src="Assets/Dog/Golden Retriever (Bella).jpg" alt="Dogs">
          <div class="ps-slide-content">
            <h2>Bring Home Joy</h2>
            <p>Find your perfect canine companion from our verified breeders across India.</p>
            <a href="category.php?type=dogs"><button class="ps-slide-btn">Shop Dogs</button></a>
          </div>
        </div>
        <!-- Slide 2 -->
        <div class="ps-slide">
          <img src="Assets/Cat/Maine Coon (Shadow).jpg" alt="Cats">
          <div class="ps-slide-content">
            <h2>Purr-fect Friends</h2>
            <p>Explore our beautiful and healthy cat breeds available for adoption today.</p>
            <a href="category.php?type=cats"><button class="ps-slide-btn">Shop Cats</button></a>
          </div>
        </div>
        <!-- Slide 3 -->
        <div class="ps-slide">
          <img src="Assets/Birds/Macaw Parrot (Sunny).jpg" alt="Birds">
          <div class="ps-slide-content">
            <h2>Feathered Wonders</h2>
            <p>Add some color to your life with our exotic, friendly, and talkative birds.</p>
            <a href="category.php?type=birds"><button class="ps-slide-btn">Shop Birds</button></a>
          </div>
        </div>
        <!-- Slide 4 -->
        <div class="ps-slide">
          <img src="Assets/Fish/Goldfish (Goldie).jpg" alt="Fish">
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
      <img src="Assets/pet_group.png" alt="Pet Group">
    </div>
  </div>

  <!-- Ticker -->
  <div class="ps-ticker">
    🐾 FREE VET CONSULTATION ON YOUR FIRST PURCHASE &nbsp;&bull;&nbsp; SAFE
    DELIVERY PAN INDIA &nbsp;&bull;&nbsp; ALL PETS VACCINATED &amp; DEWORMED
    &nbsp;&bull;&nbsp; VERIFIED BREEDERS ONLY
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
          <div class="ps-cat-img"><img src="Assets/Dog/Labrador (Max).jpg" alt="Dogs"></div>
          <div class="ps-cat-name">Dogs</div>
        </div>
      </a>
      <a href="category.php?type=cats" style="text-decoration: none; color: inherit;">
        <div class="ps-cat-card">
          <div class="ps-cat-img"><img src="Assets/Cat/British Shorthair (Luna).jpg" alt="Cats"></div>
          <div class="ps-cat-name">Cats</div>
        </div>
      </a>
      <a href="category.php?type=fish" style="text-decoration: none; color: inherit;">
        <div class="ps-cat-card">
          <div class="ps-cat-img"><img src="Assets/Fish/Goldfish (Goldie).jpg" alt="Fish"></div>
          <div class="ps-cat-name">Fish</div>
        </div>
      </a>
      <a href="category.php?type=birds" style="text-decoration: none; color: inherit;">
        <div class="ps-cat-card">
          <div class="ps-cat-img"><img src="Assets/Birds/African Grey Parrot (Rio).jpg" alt="Birds"></div>
          <div class="ps-cat-name">Birds</div>
        </div>
      </a>
    </div>
  </div>

  <!-- Featured Pets -->
  <div class="ps-section ps-featured" style="padding-top: 52px" id="pets">
    <div class="ps-section-title" id="show-all-pets" style="cursor: pointer;">Suggested for You</div>
    <div class="ps-section-sub">
      All pets are vaccinated, dewormed &amp; vet-checked
    </div>
    <div class="ps-pets-grid">
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
      <img src="Assets/pet_group.png" alt="Pet Group">
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

  <!-- Newsletter -->
  <div class="ps-newsletter">
    <div class="ps-nl-text">
      <h3>🐾 Get pet care tips &amp; exclusive offers</h3>
      <p>Join 10,000+ pet lovers — no spam, only love!</p>
    </div>
    <div class="ps-nl-form">
      <input
        class="ps-nl-input"
        type="email"
        placeholder="Enter your email address" />
      <button class="ps-nl-btn" id="subscribe-btn">Subscribe</button>
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
        </div>
        <div class="ps-footer-col">
          <h4>Support</h4>
          <a href="FAQ.php">FAQ</a>
          <a href="order_history.php">Track Order</a>
        </div>
        <div class="ps-footer-col">
          <h4>Contact</h4>
          <div class="ps-footer-contact">📞 +91 97988 89456</div>
          <div class="ps-footer-contact">✉️ support@pawsstore.in</div>
        </div>
      </div>
      <div class="ps-footer-bottom">
        © 2025 Paws Store. Made with 🐾 in India.
      </div>
    </div>
  </footer>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const categoryCards = document.querySelectorAll('.ps-cat-card');
      const petCards = document.querySelectorAll('.ps-pet-card');
      const showAllButton = document.getElementById('show-all-pets');
      const addToCartButtons = document.querySelectorAll('.ps-pet-add');
      const searchInput = document.getElementById('search-input');
      const searchBtn = document.getElementById('search-btn');

      // SHRINK CATEGORY NAV ON SCROLL
      window.addEventListener('scroll', function() {
        if (window.scrollY > 20) {
          document.body.classList.add('nav-scrolled');
        } else {
          document.body.classList.remove('nav-scrolled');
        }
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

      // CART SYSTEM
      let cart = JSON.parse(localStorage.getItem('pawsCart')) || [];

      function updateCartCount() {
        const cartCountElement = document.getElementById('cart-count');
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

        cartCountElement.textContent = totalItems;
        cartCountElement.style.display = totalItems > 0 ? 'flex' : 'none';
        localStorage.setItem('pawsCart', JSON.stringify(cart));
      }

      addToCartButtons.forEach(button => {
        function addToCart(petCard) {
          const petId = petCard.getAttribute('data-pet-id');
          const petName = petCard.querySelector('.ps-pet-name').textContent;
          const petPrice = petCard.querySelector('.ps-pet-price').textContent;
          const petImage = petCard.querySelector('img').src;

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
          updateCartCount();
          return petName;
        }

        button.addEventListener('click', function() {
          const petName = addToCart(this.closest('.ps-pet-card'));
          alert(petName + " added to cart!");
        });
      });

      // WISHLIST SYSTEM
      let wishlist = JSON.parse(localStorage.getItem('pawsWishlist')) || [];
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
              price: petPrice,
              image: petImage
            });
            alert(petName + " added to wishlist!");
          }
          localStorage.setItem('pawsWishlist', JSON.stringify(wishlist));
          updateWishlistIcons();
        });
      });

      // CATEGORY FILTER
      function filterPets(category) {
        petCards.forEach(card => {
          const cardCategory = card.getAttribute('data-category');
          if (category === 'all' || cardCategory === category) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        });
      }

      categoryCards.forEach(card => {
        card.addEventListener('click', function() {
          const category = this.getAttribute('data-category');

          filterPets(category);

          categoryCards.forEach(c => c.classList.remove('active'));
          this.classList.add('active');
        });
      });

      // SEARCH FUNCTIONALITY
      function searchPets(query) {
        const searchTerm = query.toLowerCase().trim();

        if (searchTerm === '') {
          // If search is empty, show all pets
          filterPets('all');
          categoryCards.forEach(c => c.classList.remove('active'));
          return;
        }

        // Filter pets based on search term
        petCards.forEach(card => {
          const petName = card.querySelector('.ps-pet-name').textContent.toLowerCase();
          const petCategory = card.getAttribute('data-category').toLowerCase();

          // Check if search term matches pet name or category
          if (petName.includes(searchTerm) || petCategory.includes(searchTerm)) {
            card.style.display = 'block';
          } else {
            card.style.display = 'none';
          }
        });
        // Remove active state from category cards
        categoryCards.forEach(c => c.classList.remove('active'));
        // Scroll to pets section
        document.getElementById('pets').scrollIntoView({
          behavior: 'smooth'
        });
      }

      // Search event listeners
      searchInput.addEventListener('input', function() {
        searchPets(this.value);
      });

      searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
          searchPets(this.value);
        }
      });

      searchBtn.addEventListener('click', function() {
        searchPets(searchInput.value);
      });

      // SHOW ALL PETS
      showAllButton.addEventListener('click', function() {
        filterPets('all');
        categoryCards.forEach(c => c.classList.remove('active'));
      });

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
          autoSlideInterval = setInterval(nextSlide, 3000);
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

      // NEWSLETTER SUBSCRIPTION
      const subscribeBtn = document.getElementById('subscribe-btn');
      const newsletterInput = document.querySelector('.ps-nl-input');

      if (subscribeBtn && newsletterInput) {
        subscribeBtn.addEventListener('click', function() {
          const email = newsletterInput.value;
          if (email && email.includes('@') && email.includes('.')) { // Basic email validation
            alert('Thank you for subscribing to our newsletter! You will receive pet care tips and exclusive offers.');
            newsletterInput.value = ''; // Clear the input field
          } else {
            alert('Please enter a valid email address.');
          }
        });
      }

      // INIT
      updateCartCount();
      updateWishlistIcons();

    });
  </script>
</body>

</html>