<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION["user"])) {
    header("Location: auth/login.php");
    exit();
}

$host = 'localhost';
$dbname = 'pet_store';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the logged-in user is an admin
    $user_id = $_SESSION["user"]["id"] ?? 0;
    $admin_stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
    $admin_stmt->execute([$user_id]);
    $user_data = $admin_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_data || empty($user_data['is_admin'])) {
        header("Location: index.php"); // Redirect non-admins to the homepage
        exit();
    }

    // Handle Edit Pet submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_pet'])) {
        $stmt = $pdo->prepare("UPDATE pets SET name = ?, category = ?, price = ?, image = ?, description = ?, status = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['price'],
            $_POST['image'],
            $_POST['description'],
            $_POST['status'],
            $_POST['pet_id']
        ]);
        $success_message = "Pet '{$_POST['name']}' updated successfully!";
    }

    // Handle Add New Pet form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_pet'])) {
        $stmt = $pdo->prepare("INSERT INTO pets (name, category, price, image, description, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['category'],
            $_POST['price'],
            $_POST['image'],
            $_POST['description'],
            $_POST['status']
        ]);
        $success_message = "Pet '{$_POST['name']}' added successfully!";
    }

    // Handle Delete Pet submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pet'])) {
        $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
        $stmt->execute([$_POST['pet_id']]);
        $success_message = "Pet deleted successfully!";
    }

    // Fetch pet details if in Edit mode
    $edit_pet = null;
    if (isset($_GET['edit_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
        $stmt->execute([$_GET['edit_id']]);
        $edit_pet = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch all pets from the database
    $stmt = $pdo->query("SELECT * FROM pets ORDER BY id DESC");
    $all_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Pets</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e8e0d4;
            margin-bottom: 40px;
        }

        .admin-header h1,
        .admin-header h2 {
            font-family: 'Playfair Display', serif;
            color: #2c1a0e;
            margin-bottom: 20px;
        }

        .msg-success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c1a0e;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
        }

        .submit-btn {
            background: #b5860d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background: #9a7210;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background-color: #f9f9f9;
            color: #2c1a0e;
            font-weight: 700;
        }

        .pet-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
        }

        .edit-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-right: 8px;
            transition: background 0.3s;
        }

        .edit-btn:hover {
            background: #218838;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .delete-btn:hover {
            background: #c82333;
        }
    </style>
</head>

<body>
    <nav class="fk-nav-header">
        <div class="fk-nav-top">
            <a href="index.php" class="fk-logo">🐾 Paws Store Admin</a>
            <div class="fk-nav-right" style="margin-left: auto;">
                <a href="admin_orders.php" class="fk-cart-btn" style="margin-right: 15px;">
                    <span class="fk-cart-icon">📦</span> Manage Orders
                </a>
                <a href="index.php" class="fk-cart-btn">
                    <span class="fk-cart-icon">🏠</span> Store Home
                </a>
            </div>
        </div>
    </nav>

    <div class="ps-wrap">
        <!-- Add New Pet Section -->
        <div class="admin-container">
            <div class="admin-header">
                <h2 style="display: inline-block;"><?php echo $edit_pet ? 'Edit Pet #' . $edit_pet['id'] : 'Add New Pet'; ?></h2>
                <?php if ($edit_pet): ?>
                    <a href="admin_pets.php" style="color: #dc3545; text-decoration: none; font-weight: bold; margin-left: 15px; font-size: 14px;">(Cancel Edit)</a>
                <?php endif; ?>
            </div>
            <?php if (isset($success_message)): ?>
                <div class="msg-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form method="POST">
                <?php if ($edit_pet): ?>
                    <input type="hidden" name="pet_id" value="<?php echo $edit_pet['id']; ?>">
                <?php endif; ?>
                <div class="form-grid">
                    <div class="form-group"><label>Pet Name & Breed</label><input type="text" name="name" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['name']) : ''; ?>" required placeholder="e.g., Max — Labrador"></div>
                    <div class="form-group"><label>Category</label>
                        <select name="category" required>
                            <option value="Dogs" <?php echo ($edit_pet && $edit_pet['category'] === 'Dogs') ? 'selected' : ''; ?>>Dogs</option>
                            <option value="Cats" <?php echo ($edit_pet && $edit_pet['category'] === 'Cats') ? 'selected' : ''; ?>>Cats</option>
                            <option value="Fish" <?php echo ($edit_pet && $edit_pet['category'] === 'Fish') ? 'selected' : ''; ?>>Fish</option>
                            <option value="Birds" <?php echo ($edit_pet && $edit_pet['category'] === 'Birds') ? 'selected' : ''; ?>>Birds</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Price (₹)</label><input type="number" name="price" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['price']) : ''; ?>" required min="0"></div>
                    <div class="form-group"><label>Status</label><input type="text" name="status" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['status']) : 'Available for Adoption'; ?>" required></div>
                    <div class="form-group full-width"><label>Image Path</label><input type="text" name="image" value="<?php echo $edit_pet ? htmlspecialchars($edit_pet['image']) : ''; ?>" required placeholder="e.g., Assets/Dog/Labrador (Max).jpg"></div>
                    <div class="form-group full-width"><label>Description</label><textarea name="description" rows="3" required><?php echo $edit_pet ? htmlspecialchars($edit_pet['description']) : ''; ?></textarea></div>
                </div>

                <?php if ($edit_pet): ?>
                    <button type="submit" name="edit_pet" class="submit-btn" style="background: #28a745;">Update Pet Details</button>
                <?php else: ?>
                    <button type="submit" name="add_pet" class="submit-btn">+ Add Pet to Store</button>
                <?php endif; ?>
            </form>
        </div>

        <!-- Manage Existing Pets Section -->
        <div class="admin-container">
            <div class="admin-header">
                <h2>Current Pets Database</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_pets as $pet): ?>
                        <tr>
                            <td>#<?php echo $pet['id']; ?></td>
                            <td><img src="<?php echo htmlspecialchars($pet['image']); ?>" class="pet-thumb" alt="pet"></td>
                            <td><strong><?php echo htmlspecialchars($pet['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($pet['category']); ?></td>
                            <td>₹<?php echo number_format($pet['price']); ?></td>
                            <td><span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold;"><?php echo htmlspecialchars($pet['status']); ?></span></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this pet?');" style="display: flex; align-items: center; margin: 0;">
                                    <a href="admin_pets.php?edit_id=<?php echo $pet['id']; ?>" class="edit-btn">Edit</a>
                                    <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                                    <button type="submit" name="delete_pet" class="delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>