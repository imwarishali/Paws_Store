<?php
require_once 'config.php';
require_once 'db.php';

$category = isset($_GET['category']) ? $_GET['category'] : 'all';

$pageTitle = "Featured Pets";
if ($category === 'dogs') $pageTitle = "Dogs";
if ($category === 'cats') $pageTitle = "Cats";
if ($category === 'fish') $pageTitle = "Fish";
if ($category === 'birds') $pageTitle = "Birds";

// Pagination & Caching
$limit = ITEMS_PER_PAGE;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;
$cache_key = "featured_pets_page_{$page}";
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
        error_log("Database error in featured_pets.php: " . $e->getMessage());
        $db_pets = [];
    }
}

// Get total count for pagination
try {
    $total_result = $pdo->query("SELECT COUNT(*) as count FROM pets");
    $total_count = $total_result->fetch(PDO::FETCH_ASSOC)['count'];
    $total_pages = ceil($total_count / $limit);
} catch (PDOException $e) {
    error_log("Database error in featured_pets.php: " . $e->getMessage());
    $total_pages = 1;
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?> — Paws Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap">
    </noscript>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .featured-header-section {
            background: linear-gradient(135deg, #2c1a0e 0%, #5c3d2e 100%);
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        }

        .featured-header-section .ps-section-title {
            color: #ffffff !important;
            font-size: 42px !important;
            margin: 0 0 10px 0 !important;
            letter-spacing: 0.5px;
            font-weight: 700;
        }

        .featured-header-section .ps-section-sub {
            color: #f0f0f0 !important;
            margin: 0 !important;
            font-size: 16px !important;
            opacity: 0.9;
            line-height: 1.6;
        }

        .featured-header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 0;
            flex-wrap: wrap;
            gap: 20px;
        }

        .featured-filters {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .ps-filter-select {
            padding: 11px 16px !important;
            border-radius: 20px !important;
            border: none !important;
            background: white !important;
            color: #2c1a0e !important;
            font-weight: 600;
            font-size: 14px !important;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .ps-filter-select:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.25) !important;
        }

        .ps-filter-select:focus {
            outline: none !important;
            border: 2px solid #b5860d !important;
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.3) !important;
        }

        .ps-section.ps-featured {
            padding: 30px 20px !important;
        }

        .ps-pets-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .ps-pet-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e0e0e0;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .ps-pet-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #b5860d, #d4af37);
            transform: translateX(-100%);
            transition: transform 0.4s ease;
            z-index: 1;
        }

        .ps-pet-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
            border-color: #b5860d;
        }

        .ps-pet-card:hover::before {
            transform: translateX(0);
        }

        .ps-pet-photo {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .ps-pet-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .ps-pet-card:hover .ps-pet-photo img {
            transform: scale(1.08);
        }

        .ps-pet-wish {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 2px solid #f5f2eb;
        }

        .ps-pet-wish:hover {
            transform: scale(1.15);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
            color: #e74c3c;
        }

        .ps-pet-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(255, 255, 255, 0.95);
            color: #4caf50;
            padding: 6px 14px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
            border: 1px solid #e8f5e9;
            width: fit-content;
            white-space: nowrap;
        }

        .ps-pet-body {
            padding: 18px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .ps-pet-name {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 700;
            color: #2c1a0e;
            margin-bottom: 6px;
            letter-spacing: 0.3px;
        }

        .ps-pet-meta,
        .ps-pet-loc {
            font-size: 12px;
            color: #666;
            margin: 4px 0;
            font-weight: 500;
        }

        .ps-pet-stars {
            color: #b5860d;
            font-size: 13px;
            margin: 6px 0;
        }

        .ps-pet-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-top: auto;
            padding-top: 12px;
            border-top: 1px solid #f0f0f0;
        }

        .ps-pet-price {
            font-size: 18px;
            font-weight: 700;
            color: #b5860d;
        }

        .ps-pet-add {
            flex: 1;
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: white;
            border: none;
            padding: 10px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.25);
        }

        .ps-pet-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(181, 134, 13, 0.35);
        }

        .ps-pet-add.added-to-cart {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.25);
        }

        .ps-pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 40px;
            padding: 20px 0;
            flex-wrap: wrap;
        }

        .ps-pagination a,
        .ps-pagination span {
            padding: 10px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            border: 1px solid #e8e0d4;
        }

        .ps-pagination a {
            color: #2c1a0e;
            background: white;
            cursor: pointer;
        }

        .ps-pagination a:hover {
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: white;
            border-color: #b5860d;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.25);
        }

        .ps-pagination span.current {
            background: linear-gradient(135deg, #b5860d 0%, #d4af37 100%);
            color: white;
            border-color: #b5860d;
            box-shadow: 0 4px 12px rgba(181, 134, 13, 0.25);
        }

        @media (max-width: 1024px) {
            .ps-pets-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
            }

            .featured-header-section .ps-section-title {
                font-size: 36px;
            }
        }

        @media (max-width: 768px) {
            .featured-header-section {
                padding: 30px 20px;
                margin-bottom: 25px;
                border-radius: 16px;
            }

            .featured-header-section .ps-section-title {
                font-size: 28px;
                margin-bottom: 8px;
            }

            .featured-header-section .ps-section-sub {
                font-size: 14px;
            }

            .featured-header-content {
                flex-direction: column;
                align-items: stretch;
                gap: 15px;
            }

            .featured-filters {
                flex-direction: column;
            }

            .ps-filter-select {
                width: 100% !important;
            }

            .ps-pets-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 16px;
            }

            .ps-section.ps-featured {
                padding: 20px 10px !important;
            }

            .ps-pet-card {
                border-radius: 12px;
            }

            .ps-pet-photo {
                height: 150px;
            }

            .ps-pet-body {
                padding: 14px;
            }

            .ps-pet-name {
                font-size: 14px;
                margin-bottom: 4px;
            }

            .ps-pet-price {
                font-size: 16px;
            }

            .ps-pet-add {
                font-size: 12px;
                padding: 8px 10px;
            }

            .ps-pagination a,
            .ps-pagination span {
                padding: 8px 12px;
                font-size: 13px;
            }
        }
    </style>


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
        <div class="featured-header-section">
            <div class="featured-header-content">
                <div>
                    <div class="ps-section-title"><?php echo $pageTitle; ?></div>
                    <div class="ps-section-sub">Find your perfect companion. All pets are vaccinated, dewormed & vet-checked.</div>
                </div>
                <div class="featured-filters">
                    <select id="location-filter" class="ps-filter-select">
                        <option value="all">Location: All</option>
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
                    <select id="price-filter" class="ps-filter-select">
                        <option value="all">Price Range: All</option>
                        <option value="under-10k">Under ₹10,000</option>
                        <option value="10k-20k">₹10,000 - ₹20,000</option>
                        <option value="above-20k">Above ₹20,000</option>
                    </select>
                    <select id="price-sort" class="ps-filter-select">
                        <option value="default">Sort by: Default</option>
                        <option value="low-high">Price: Low to High</option>
                        <option value="high-low">Price: High to Low</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="ps-section ps-featured">
            <div class="ps-pets-grid" id="pets-grid">
                <!-- DOGS -->
                <div class="ps-pet-card" data-category="dogs" data-pet-id="1">
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
                <div class="ps-pet-card" data-category="dogs" data-pet-id="4">
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
                <div class="ps-pet-card" data-category="dogs" data-pet-id="5">
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
                <div class="ps-pet-card" data-category="dogs" data-pet-id="7">
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
                <div class="ps-pet-card" data-category="dogs" data-pet-id="8">
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
                <div class="ps-pet-card" data-category="dogs" data-pet-id="9">
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
                <div class="ps-pet-card" data-category="dogs" data-pet-id="10">
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
                <div class="ps-pet-card" data-category="dogs" data-pet-id="11">
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

                <!-- CATS -->
                <div class="ps-pet-card" data-category="cats" data-pet-id="12">
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
                <div class="ps-pet-card" data-category="cats" data-pet-id="13">
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
                <div class="ps-pet-card" data-category="cats" data-pet-id="14">
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
                <div class="ps-pet-card" data-category="cats" data-pet-id="15">
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
                <div class="ps-pet-card" data-category="cats" data-pet-id="16">
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
                <div class="ps-pet-card" data-category="cats" data-pet-id="17">
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
                <div class="ps-pet-card" data-category="cats" data-pet-id="18">
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

                <!-- FISH -->
                <div class="ps-pet-card" data-category="fish" data-pet-id="19">
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
                <div class="ps-pet-card" data-category="fish" data-pet-id="20">
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
                <div class="ps-pet-card" data-category="fish" data-pet-id="21">
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
                <div class="ps-pet-card" data-category="fish" data-pet-id="22">
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
                <div class="ps-pet-card" data-category="fish" data-pet-id="23">
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
                <div class="ps-pet-card" data-category="fish" data-pet-id="24">
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
                <div class="ps-pet-card" data-category="fish" data-pet-id="25">
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

                <!-- BIRDS -->
                <div class="ps-pet-card" data-category="birds" data-pet-id="26">
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

            <!-- Mix (shuffle) the pets randomly on every page load -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const grid = document.getElementById('pets-grid');
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

            function applyFilters() {
                const locationFilter = document.getElementById('location-filter');
                const priceFilter = document.getElementById('price-filter');
                const locationVal = locationFilter ? locationFilter.value : 'all';
                const priceVal = priceFilter ? priceFilter.value : 'all';

                petCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category');
                    const petLoc = card.querySelector('.ps-pet-loc').textContent;
                    const petPrice = parseInt(card.querySelector('.ps-pet-price').textContent.replace(/[^0-9]/g, '')) || 0;

                    let matchCategory = (urlCategory === 'all' || cardCategory === urlCategory);

                    let matchLocation = true;
                    if (locationVal !== 'all') {
                        matchLocation = petLoc.includes(locationVal);
                    }

                    let matchPrice = true;
                    if (priceVal === 'under-10k') {
                        matchPrice = petPrice < 10000;
                    } else if (priceVal === '10k-20k') {
                        matchPrice = petPrice >= 10000 && petPrice <= 20000;
                    } else if (priceVal === 'above-20k') {
                        matchPrice = petPrice > 20000;
                    }

                    if (matchCategory && matchLocation && matchPrice) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            const locationFilter = document.getElementById('location-filter');
            const priceFilter = document.getElementById('price-filter');
            if (locationFilter) locationFilter.addEventListener('change', applyFilters);
            if (priceFilter) priceFilter.addEventListener('change', applyFilters);

            // Initial filter run
            applyFilters();

            // PRICE SORTING DROPDOWN
            const priceSortSelect = document.getElementById('price-sort');
            const petsGrid = document.getElementById('pets-grid');

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

            // CART & WISHLIST LOGIC
            let wishlist = JSON.parse(localStorage.getItem(wishKey)) || [];

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

            fetch('cart_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'get'
                })
            }).then(r => r.json()).then(d => {
                if (d.status === 'success') updateCartCount(d.cart_count);
            });

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
                button.addEventListener('click', function() {
                    // Check if user is logged in
                    if (currentUserId === 'guest') {
                        alert('🔐 Login to add to cart\n\nPlease log in to your account to add items to your cart.');
                        window.location.href = 'auth/login.php?redirect=featured_pets.php';
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
                        showToast(petName + " removed from wishlist!", '🤍');
                    } else {
                        wishlist.push({
                            id: petId,
                            name: petName,
                            price: parseInt(petPrice.replace(/[^0-9]/g, '')), // Parse the string to a raw number
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

            updateWishlistIcons();
        });
    </script>
</body>

</html>