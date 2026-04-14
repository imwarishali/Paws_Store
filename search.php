<?php
session_start();
require_once 'db.php';

// Get all unique values for filters
$categories = [];
$locations = [];
$ageRanges = [];
$priceRanges = [];

try {
    $stmt = $pdo->query("SELECT DISTINCT category FROM pets ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->query("SELECT DISTINCT location FROM pets ORDER BY location");
    $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Silently fail
}

// Handle search and filters
$filters = [];
$query = "SELECT * FROM pets WHERE 1=1";

if (!empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $query .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
    $filters[] = $search;
    $filters[] = $search;
    $filters[] = $search;
}

if (!empty($_GET['category']) && $_GET['category'] !== 'all') {
    $query .= " AND category = ?";
    $filters[] = $_GET['category'];
}

if (!empty($_GET['location']) && $_GET['location'] !== 'all') {
    $query .= " AND location = ?";
    $filters[] = $_GET['location'];
}

if (!empty($_GET['price_min'])) {
    $query .= " AND price >= ?";
    $filters[] = (int)$_GET['price_min'];
}

if (!empty($_GET['price_max'])) {
    $query .= " AND price <= ?";
    $filters[] = (int)$_GET['price_max'];
}

if (!empty($_GET['age'])) {
    $query .= " AND age_months = ?";
    $filters[] = (int)$_GET['age'];
}

// Sorting
$sort = $_GET['sort'] ?? 'name';
$order = 'ASC';

if ($sort === 'price_low') {
    $query .= " ORDER BY price ASC";
} elseif ($sort === 'price_high') {
    $query .= " ORDER BY price DESC";
} elseif ($sort === 'newest') {
    $query .= " ORDER BY created_at DESC";
} else {
    $query .= " ORDER BY name ASC";
}

$stmt = $pdo->prepare($query);
$results = [];
if (!empty($filters)) {
    $stmt->execute($filters);
} else {
    $stmt->execute();
}
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Advanced Search — Paws Store</title>
    <meta name="description" content="Find your perfect pet with advanced filters and search options">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ps-search-page {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .ps-search-page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .ps-search-page-header h1 {
            font-family: 'Playfair Display';
            font-size: 42px;
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .ps-search-results-container {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
        }

        .ps-search-sidebar {
            background: white;
            padding: 25px;
            border-radius: 12px;
            height: fit-content;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 150px;
        }

        .ps-filter-group {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e0e0e0;
        }

        .ps-filter-group:last-child {
            border-bottom: none;
        }

        .ps-filter-group h3 {
            font-weight: 600;
            color: #2c1a0e;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .ps-filter-check {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
        }

        .ps-filter-check input {
            margin-right: 10px;
            cursor: pointer;
        }

        .ps-filter-check label {
            cursor: pointer;
            margin: 0;
            font-size: 14px;
            color: #555;
        }

        .ps-filter-range {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .ps-filter-range input {
            padding: 8px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 13px;
        }

        .ps-search-results {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .ps-results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
            flex-wrap: wrap;
        }

        .ps-results-count {
            color: #666;
            font-size: 15px;
        }

        .ps-results-sort {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .ps-results-sort select {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
        }

        .ps-search-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .ps-search-no-results {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .ps-search-no-results h3 {
            color: #2c1a0e;
            margin-bottom: 10px;
        }

        .ps-clear-filters {
            background: #e0e0e0;
            color: #2c1a0e;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
            width: 100%;
            font-size: 14px;
        }

        .ps-clear-filters:hover {
            background: #d0d0d0;
        }

        @media (max-width: 968px) {
            .ps-search-results-container {
                grid-template-columns: 1fr;
            }

            .ps-search-sidebar {
                position: static;
            }

            .ps-search-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
</head>

<body>
    <?php include 'assets/header.php'; ?>

    <main>
        <div class="ps-search-page">
            <!-- Page Header -->
            <div class="ps-search-page-header">
                <h1>Find Your Perfect Pet</h1>
                <p style="color: #666; font-size: 16px;">Use advanced filters to discover your ideal companion</p>
            </div>

            <!-- Search Results Container -->
            <div class="ps-search-results-container">
                <!-- Sidebar Filters -->
                <div class="ps-search-sidebar">
                    <form method="GET" action="">
                        <!-- Search -->
                        <div class="ps-filter-group">
                            <h3>Search</h3>
                            <input type="text" name="search" placeholder="Pet name, breed..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 14px;">
                        </div>

                        <!-- Category -->
                        <div class="ps-filter-group">
                            <h3>Category</h3>
                            <select name="category" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px;">
                                <option value="all">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($_GET['category'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Location -->
                        <div class="ps-filter-group">
                            <h3>Location</h3>
                            <select name="location" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px;">
                                <option value="all">All Locations</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo htmlspecialchars($loc); ?>" <?php echo ($_GET['location'] ?? '') === $loc ? 'selected' : ''; ?>><?php echo htmlspecialchars($loc); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="ps-filter-group">
                            <h3>Price Range</h3>
                            <div class="ps-filter-range">
                                <input type="number" name="price_min" placeholder="Min ₹" value="<?php echo htmlspecialchars($_GET['price_min'] ?? ''); ?>" min="0">
                                <input type="number" name="price_max" placeholder="Max ₹" value="<?php echo htmlspecialchars($_GET['price_max'] ?? ''); ?>" min="0">
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="ps-filter-group">
                            <h3>Sort By</h3>
                            <select name="sort" style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 6px;">
                                <option value="name" <?php echo ($_GET['sort'] ?? '') === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                                <option value="price_low" <?php echo ($_GET['sort'] ?? '') === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo ($_GET['sort'] ?? '') === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="newest" <?php echo ($_GET['sort'] ?? '') === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            </select>
                        </div>

                        <!-- Action Buttons -->
                        <button type="submit" class="ps-search-btn" style="width: 100%; margin-bottom: 10px;">Search</button>
                        <a href="search.php" class="ps-clear-filters">Clear Filters</a>
                    </form>
                </div>

                <!-- Results -->
                <div class="ps-search-results">
                    <div class="ps-results-header">
                        <span class="ps-results-count"><?php echo count($results); ?> pet(s) found</span>
                    </div>

                    <?php if (!empty($results)): ?>
                        <div class="ps-search-grid">
                            <?php foreach ($results as $pet): ?>
                                <div class="ps-pet-card">
                                    <div class="ps-pet-photo" style="background: #f5ecd8">
                                        <img src="<?php echo htmlspecialchars($pet['image']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                                        <div class="ps-pet-wish">♡</div>
                                        <div class="ps-pet-badge">Vaccinated</div>
                                    </div>
                                    <div class="ps-pet-body">
                                        <div class="ps-pet-name"><?php echo htmlspecialchars($pet['name']); ?></div>
                                        <div class="ps-pet-meta"><?php echo htmlspecialchars($pet['age_months'] ?? ''); ?> months • <?php echo htmlspecialchars($pet['status'] ?? 'Available'); ?></div>
                                        <div class="ps-pet-stars">★★★★★</div>
                                        <div class="ps-pet-loc">📍 <?php echo htmlspecialchars($pet['location'] ?? ''); ?></div>
                                        <div class="ps-pet-row">
                                            <span class="ps-pet-price">₹<?php echo number_format($pet['price']); ?></span>
                                            <a href="pet_details.php?id=<?php echo $pet['id']; ?>" class="ps-pet-add" style="text-decoration: none;">View</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="ps-search-no-results">
                            <h3>🐾 No pets found</h3>
                            <p>Try adjusting your search filters or browse all pets instead</p>
                            <a href="index.php#pets" style="color: #b5860d; text-decoration: none; margin-top: 15px; display: inline-block;">← Back to All Pets</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'assets/footer.php'; ?>
</body>

</html>