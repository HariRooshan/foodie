<?php
session_start();

// Redirect if not logged in as vendor
if (!isset($_SESSION['vendor_id'])) {
    header("Location: vendor.php");
    exit();
}

$vendor_id = $_SESSION['vendor_id'];
$conn = new mysqli("localhost", "root", "", "foodie");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Add Food
if (isset($_POST['add_food'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $dietary = $_POST['dietary'];
    $quantity = intval($_POST['quantity']);
    $available = isset($_POST['available']) ? 1 : 0;

    // Handle image upload
    $image_path = "";
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/foods/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_path = $target_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    $stmt = $conn->prepare("INSERT INTO foods (vendor_id, name, description, price, available, dietary, image_path, quantity) VALUES (?, ?, ?, ?, ?, ?, ?,?)");
    $stmt->bind_param("issdissi", 
    $vendor_id, 
    $name, 
    $description, 
    $price, 
    $available, 
    $dietary, 
    $image_path, 
    $quantity
);

    $stmt->execute();
    $stmt->close();
    header("Location: dashboard_vendor.php");
    exit();
}

// Handle Update Food
if (isset($_POST['update_food'])) {
    $food_id = intval($_POST['food_id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $dietary = $_POST['dietary'];
    $available = isset($_POST['available']) ? 1 : 0;
    $quantity = intval($_POST['quantity']);

    $image_path = $_POST['old_image'];
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "uploads/foods/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_path = $target_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    }

    $stmt = $conn->prepare("UPDATE foods SET name=?, description=?, price=?, available=?, dietary=?, image_path=?, quantity=? WHERE food_id=? AND vendor_id=?");
    $stmt->bind_param("ssdissiii", $name, $description, $price, $available, $dietary, $image_path, $quantity, $food_id, $vendor_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard_vendor.php");
    exit();
}

// Handle Delete Food
if (isset($_POST['delete_food'])) {
    $food_id = intval($_POST['food_id']);
    $stmt = $conn->prepare("DELETE FROM foods WHERE food_id=? AND vendor_id=?");
    $stmt->bind_param("ii", $food_id, $vendor_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard_vendor.php");
    exit();
}

// Fetch vendor foods
$foods = $conn->query("SELECT * FROM foods WHERE vendor_id = $vendor_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vendor Dashboard - Cravings Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #fafafa;
        }
        .dashboard-container {
            padding: 20px;
        }
        .food-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        .food-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand">🍽️ Cravings Site Vendor Dashboard</span>
        <a href="logout_vendor.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<div class="container dashboard-container">
    <h2 class="mb-4">Manage Your Menu</h2>

    <!-- Add Food Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Add New Food Item</div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-2"><input type="text" name="name" class="form-control" placeholder="Food Name" required></div>
                <div class="mb-2"><textarea name="description" class="form-control" placeholder="Description"></textarea></div>
                <div class="mb-2"><input type="number" step="0.01" name="price" class="form-control" placeholder="Price" required></div>
                <div class="mb-2"><input type="number" name="quantity" class="form-control" placeholder="Quantity" required></div>
                <div class="mb-2">
                    <select name="dietary" class="form-control">
                        <option value="veg">Veg</option>
                        <option value="non-veg">Non-Veg</option>
                    </select>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="available" checked>
                    <label class="form-check-label">Available</label>
                </div>
                <div class="mb-2"><input type="file" name="image" class="form-control"></div>
                <button type="submit" name="add_food" class="btn btn-success">Add Food</button>
            </form>
        </div>
    </div>

    <!-- Vendor Foods -->
    <h3>Your Menu</h3>
    <div class="row">
        <?php while ($food = $foods->fetch_assoc()): ?>
            <div class="col-md-4 mb-3">
                <div class="card food-card">
                    <?php if ($food['image_path']): ?>
                        <img src="<?= $food['image_path'] ?>" class="card-img-top" style="height:200px;object-fit:cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($food['name']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($food['description']) ?></p>
                        <p><b>₹<?= number_format($food['price'],2) ?></b> | <?= ucfirst($food['dietary']) ?></p>
                        <p>Status: <?= $food['available'] ? '<span class="text-success">Available</span>' : '<span class="text-danger">Unavailable</span>' ?></p>
                        
                        <!-- Update Form -->
                        <form method="POST" enctype="multipart/form-data" class="mb-2">
                            <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                            <input type="hidden" name="old_image" value="<?= $food['image_path'] ?>">
                            <input type="text" name="name" class="form-control mb-1" value="<?= htmlspecialchars($food['name']) ?>" required>
                            <textarea name="description" class="form-control mb-1"><?= htmlspecialchars($food['description']) ?></textarea>
                            <input type="number" step="0.01" name="price" class="form-control mb-1" value="<?= $food['price'] ?>" required>
                            <input type="number" name="quantity" class="form-control mb-1" value="<?= $food['quantity'] ?>" required>
                            <select name="dietary" class="form-control mb-1">
                                <option value="veg" <?= $food['dietary']=="veg" ? "selected":"" ?>>Veg</option>
                                <option value="non-veg" <?= $food['dietary']=="non-veg" ? "selected":"" ?>>Non-Veg</option>
                            </select>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="available" <?= $food['available'] ? "checked":"" ?>>
                                <label class="form-check-label">Available</label>
                            </div>
                            <input type="file" name="image" class="form-control mb-1">
                            <button type="submit" name="update_food" class="btn btn-warning btn-sm">Update</button>
                        </form>

                        <!-- Delete Form -->
                        <form method="POST">
                            <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                            <button type="submit" name="delete_food" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>
