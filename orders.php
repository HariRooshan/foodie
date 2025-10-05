<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "foodie");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];

// Fetch orders joined with foods and vendors
$sql = "SELECT 
            o.order_id,
            o.order_status,
            o.total_amount,
            o.quantity,
            f.name AS food_name,
            f.price AS unit_price,
            v.name AS vendor_name,
            o.created_at
        FROM orders o
        JOIN foods f ON o.vendor_id = f.vendor_id
        JOIN vendors v ON f.vendor_id = v.vendor_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Orders - Cravings Site</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Cravings Site</a>
        <div class="d-flex">
            <a href="index.php" class="btn btn-outline-success me-2">Dashboard</a> <!-- Outline green -->
            <a href="orders.php" class="btn btn-outline-primary me-2 active">My Orders</a>
            <a href="favorites.php" class="btn btn-outline-warning me-2">Favorites</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">My Orders</h2>
    <div class="card shadow-sm p-4">
        <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Food</th>
                    <th>Vendor</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Ordered On</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): 
                    $total_price = $row['unit_price'] * $row['quantity'];
                ?>
                <tr>
                    <td><?= $row['order_id'] ?></td>
                    <td><?= htmlspecialchars($row['food_name']) ?></td>
                    <td><?= htmlspecialchars($row['vendor_name']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td>₹<?= number_format($row['unit_price'], 2) ?></td>
                    <td>₹<?= number_format($total_price, 2) ?></td>
                    <td>
                        <span class="badge 
                            <?php 
                                switch($row['order_status']) {
                                    case 'completed': echo 'bg-success'; break;
                                    case 'cancelled': echo 'bg-danger'; break;
                                    case 'pending': echo 'bg-warning text-dark'; break;
                                    default: echo 'bg-info';
                                }
                            ?>">
                            <?= ucfirst($row['order_status']) ?>
                        </span>
                    </td>
                    <td><?= date("d M Y, h:i A", strtotime($row['created_at'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="text-muted">You haven't placed any orders yet.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
