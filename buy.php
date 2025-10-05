<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "foodie");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];
$food_id = isset($_GET['food_id']) ? intval($_GET['food_id']) : 0;

// Fetch user details
$stmt = $conn->prepare("SELECT name, address, phone FROM users WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch food details including available quantity
$stmt2 = $conn->prepare("SELECT name, price, quantity FROM foods WHERE food_id=?");
$stmt2->bind_param("i", $food_id);
$stmt2->execute();
$food = $stmt2->get_result()->fetch_assoc();

$stmt->close();
$stmt2->close();
$conn->close();

$razorpay_key_id = "rzp_test_DfkoBiraPtXwPE";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout - <?= htmlspecialchars($food['name']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: #f8f9fa; }
    .checkout-card { max-width: 600px; margin: 50px auto; }
</style>
</head>
<body>
<div class="container">
    <div class="card checkout-card shadow p-4">
        <h3 class="mb-3">Checkout</h3>

        <h5>Food: <?= htmlspecialchars($food['name']) ?></h5>
        <p><strong>Price: ₹<?= number_format($food['price'], 2) ?></strong></p>
        <p><small>Available Stock: <?= $food['quantity'] ?></small></p>

        <div class="mb-3">
            <label class="form-label">Quantity</label>
            <input type="number" id="quantity" class="form-control" value="1" min="1" max="<?= $food['quantity'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" id="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea id="address" class="form-control" required><?= htmlspecialchars($user['address']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" id="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>" required>
        </div>

        <button type="button" id="payButton" class="btn btn-primary w-100">Pay with Razorpay</button>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.getElementById('payButton').onclick = function(e){
    e.preventDefault();

    var name = document.getElementById('name').value.trim();
    var address = document.getElementById('address').value.trim();
    var phone = document.getElementById('phone').value.trim();
    var quantity = parseInt(document.getElementById('quantity').value);
    var maxQty = <?= $food['quantity'] ?>;

    if(!name || !address || !phone){
        alert("Please fill all details");
        return;
    }

    if(quantity < 1){
        alert("Quantity must be at least 1");
        return;
    }

    if(quantity > maxQty){
        alert("Quantity exceeds available stock (" + maxQty + ")");
        return;
    }

    var pricePerItem = <?= $food['price'] ?>;
    var totalAmount = Math.round(pricePerItem * quantity * 100); // in paise

    var options = {
        "key": "<?= $razorpay_key_id ?>",
        "amount": totalAmount,
        "currency": "INR",
        "name": "Cravings Site Food Delivery",
        "description": "Order Payment",
        "handler": function(response){
            var url = "order_success.php?payment_id=" + response.razorpay_payment_id +
                      "&food_id=<?= $food_id ?>" +
                      "&name=" + encodeURIComponent(name) +
                      "&address=" + encodeURIComponent(address) +
                      "&phone=" + encodeURIComponent(phone) +
                      "&quantity=" + quantity;
            window.location.href = url;
        },
        "prefill": {
            "name": name,
            "email": "<?= $_SESSION['email'] ?>",
            "contact": phone
        },
        "theme": { "color": "#3399cc" }
    };

    var rzp1 = new Razorpay(options);
    rzp1.open();
};
</script>
</body>
</html>
