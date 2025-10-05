<?php
session_start();
$conn = new mysqli("localhost", "root", "", "foodie");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM foods WHERE name LIKE ?");
    $like = "%" . $search . "%";
    $stmt->bind_param("s", $like);
} else {
    $stmt = $conn->prepare("SELECT * FROM foods");
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cravings Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .site-header {
            text-align: center;
            margin-top: 50px;
            margin-bottom: 30px;
        }
        .site-header h1 {
            font-size: 3rem;
            font-weight: bold;
        }
        .site-header p {
            font-size: 1.25rem;
            color: #555;
        }
        .food-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .food-card:hover {
            transform: translateY(-5px);
        }
        .food-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .favorite-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            border: none;
            background: white;
            border-radius: 50%;
            padding: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">Cravings Site</a>
            <div class="d-flex">
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <a href="dashboard_admin.php" class="btn btn-warning me-2">Admin Dashboard</a>
                    <a href="logout_admin.php" class="btn btn-danger">Logout</a>
                <?php elseif (isset($_SESSION['user_id'])): ?>
                    <a href="index.php" class="btn btn-success me-2">Dashboard</a>
                    <a href="orders.php" class="btn btn-outline-primary me-2">My Orders</a>
                    <a href="favorites.php" class="btn btn-outline-warning me-2">Favorites</a>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <div class="site-header">
        <h1>Cravings Site</h1>
        <p>Your favorite food, delivered fast & hot!</p>
    </div>

    <!-- Search Bar -->
    <div class="container mb-4">
        <form method="get" class="d-flex justify-content-center">
            <input type="text" name="search" class="form-control w-50 me-2" placeholder="Search food..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-outline-success">Search</button>
        </form>
    </div>

    <!-- Food Grid -->
    <div class="container" style="margin-bottom: 2rem;">
        <div class="row g-4">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card food-card position-relative">
                        <!-- Favorite Button -->
                        <a href="favorites_action.php?action=add&food_id=<?= $row['food_id'] ?>" class="favorite-btn" title="Add to Favorites">
    ❤️
</a>

                        <!-- Food Image -->
                        <?php if ($row['image_path']): ?>
                            <img src="<?= htmlspecialchars($row['image_path']) ?>" class="food-img" alt="<?= htmlspecialchars($row['name']) ?>">
                        <?php else: ?>
                            <img src="uploads/placeholder.png" class="food-img" alt="No image">
                        <?php endif; ?>

                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p class="card-text text-muted"><?= htmlspecialchars($row['description']) ?></p>
                            <p>
                                <strong>₹<?= number_format($row['price'], 2) ?></strong> 
                                Quantity: <?= $row['quantity'] ?>
                                <span class="badge bg-<?= $row['dietary'] == 'veg' ? 'success' : 'danger' ?>">
                                    <?= ucfirst($row['dietary']) ?>
                                </span>
                            </p>

                            <?php if ($row['quantity'] > 0): ?>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <!-- User logged in and item is in stock -->
                                    <a href="buy.php?food_id=<?= $row['food_id'] ?>" class="btn btn-primary w-100">Buy Now</a>
                                <?php else: ?>
                                    <!-- Not logged in -->
                                    <a href="login.php" class="btn btn-secondary w-100">Login to Buy</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <!-- Out of stock -->
                                <button class="btn btn-danger w-100" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
