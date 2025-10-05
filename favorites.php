<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "foodie");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT f.food_id, f.name, f.description, f.price, f.quantity, f.dietary, f.image_path 
                        FROM favorites fav
                        JOIN foods f ON fav.food_id = f.food_id
                        WHERE fav.user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favorites - Cravings Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .food-card { border-radius: 15px; box-shadow: 0px 4px 15px rgba(0,0,0,0.1); }
        .food-img { height: 200px; object-fit: cover; width: 100%; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Cravings Site</a>
        <div class="d-flex">
            <a href="index.php" class="btn btn-outline-success me-2">Dashboard</a> <!-- Outline green -->
            <a href="orders.php" class="btn btn-outline-primary me-2">My Orders</a>
            <a href="favorites.php" class="btn btn-outline-warning me-2 active">Favorites</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>


<div class="container mt-4">
    <h2 class="mb-4">My Favorites ❤️</h2>
    <div class="row g-4">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card food-card">
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
                                <a href="buy.php?food_id=<?= $row['food_id'] ?>" class="btn btn-primary w-100 mb-2">Buy Now</a>
                            <?php else: ?>
                                <button class="btn btn-danger w-100 mb-2" disabled>Out of Stock</button>
                            <?php endif; ?>
                            <a href="favorites_action.php?action=remove&food_id=<?= $row['food_id'] ?>" class="btn btn-outline-danger w-100">Remove from Favorites</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted">You don’t have any favorites yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
