<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "foodie");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id    = $_SESSION['user_id'];
$food_id    = intval($_GET['food_id']);
$payment_id = $_GET['payment_id'];
$name       = $_GET['name'];
$address    = $_GET['address'];
$phone      = $_GET['phone'];
$quantity   = intval($_GET['quantity']);

// Fetch food details + vendor_id + available quantity
$stmt = $conn->prepare("SELECT vendor_id, name, price, quantity FROM foods WHERE food_id=?");
$stmt->bind_param("i", $food_id);
$stmt->execute();
$food = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$food) die("Food not found.");

// Check if enough stock is available
if ($quantity > $food['quantity']) die("Sorry, only " . $food['quantity'] . " item(s) available.");

// Calculate total amount
$total_amount = $food['price'] * $quantity;
$vendor_id = $food['vendor_id'];

// Insert order with quantity
$stmt2 = $conn->prepare("INSERT INTO orders (user_id, vendor_id, order_status, total_amount, quantity) 
                         VALUES (?, ?, 'pending', ?, ?)");
$stmt2->bind_param("iidi", $user_id, $vendor_id, $total_amount, $quantity);
$stmt2->execute();
$order_id = $stmt2->insert_id;
$stmt2->close();

// Deduct quantity from foods table
$stmt3 = $conn->prepare("UPDATE foods SET quantity = quantity - ? WHERE food_id=?");
$stmt3->bind_param("ii", $quantity, $food_id);
$stmt3->execute();
$stmt3->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Invoice - Order #<?= $order_id ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f8f9fa; }
.invoice-card { max-width: 700px; margin: 50px auto; background: white; border-radius: 10px; }
.invoice-header { background: #0d6efd; color: white; padding: 20px; border-radius: 10px 10px 0 0; }
.invoice-body { padding: 20px; }
.invoice-footer { text-align: center; padding: 15px; font-size: 0.9rem; color: #777; }
.table th, .table td { vertical-align: middle; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
<div class="container">
    <a class="navbar-brand fw-bold" href="#">Cravings Site</a>
    <div class="d-flex">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="orders.php" class="btn btn-outline-primary me-2">My Orders</a>
            <a href="favorites.php" class="btn btn-outline-warning me-2">Favorites</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary">Login</a>
        <?php endif; ?>
    </div>
</div>
</nav>

<div class="card invoice-card shadow">
<div class="invoice-header">
    <h2 class="mb-0">Cravings Site - Invoice</h2>
    <p class="mb-0">Order #<?= $order_id ?> | Payment ID: <?= htmlspecialchars($payment_id) ?></p>
</div>
<div class="invoice-body">
    <h5>Billing Details</h5>
    <p><strong>Name:</strong> <?= htmlspecialchars($name) ?><br>
       <strong>Phone:</strong> <?= htmlspecialchars($phone) ?><br>
       <strong>Address:</strong> <?= nl2br(htmlspecialchars($address)) ?></p>

    <h5>Order Summary</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Quantity</th>
                <th class="text-end">Unit Price</th>
                <th class="text-end">Total Price</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($food['name']) ?></td>
                <td class="text-center"><?= $quantity ?></td>
                <td class="text-end">₹<?= number_format($food['price'], 2) ?></td>
                <td class="text-end">₹<?= number_format($total_amount, 2) ?></td>
            </tr>
            <tr>
                <th colspan="3">Total</th>
                <th class="text-end">₹<?= number_format($total_amount, 2) ?></th>
            </tr>
        </tbody>
    </table>

    <div class="alert alert-success text-center">
        <strong>Payment Successful!</strong> Your order has been placed.
    </div>
</div>
<div class="invoice-footer">
    <div class="d-flex justify-content-center mb-2">
        <button id="downloadPdf" class="btn btn-primary me-2">Download Invoice as PDF</button>
        <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
    </div>
    <p>Thank you for ordering with Cravings! 🍴</p>
</div>
</div>
</body>
<!-- html2pdf (bundled) for client-side PDF generation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<script>
document.getElementById('downloadPdf').addEventListener('click', function () {
    var btn = this;
    btn.disabled = true;
    btn.innerText = 'Preparing PDF...';

    // Select the invoice card element
    var element = document.querySelector('.invoice-card');

    var opt = {
        margin:       10,
        filename:     'invoice_order_<?= $order_id ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    // Generate the PDF
    html2pdf().set(opt).from(element).save().then(function () {
        btn.disabled = false;
        btn.innerText = 'Download Invoice as PDF';
    }).catch(function (err) {
        console.error(err);
        alert('Failed to generate PDF. Please try again.');
        btn.disabled = false;
        btn.innerText = 'Download Invoice as PDF';
    });
});
</script>
</html>
</html>
