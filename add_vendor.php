<?php
session_start();

// Allow only logged-in admins
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "foodie");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields (Name, Email, Password).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check for duplicate email
        $check = $conn->prepare("SELECT vendor_id FROM vendors WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "A vendor with this email already exists.";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new vendor
            $stmt = $conn->prepare("INSERT INTO vendors (name, email, password_hash, phone, address, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param("sssss", $name, $email, $password_hash, $phone, $address);

            if ($stmt->execute()) {
                $success = "Vendor added successfully!";
            } else {
                $error = "Error adding vendor: " . $conn->error;
            }

            $stmt->close();
        }
        $check->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Vendor - Cravings Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            margin-top: 60px;
            max-width: 650px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #dc3545;
            color: white;
            font-size: 1.3rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header text-center">
             Add New Vendor
        </div>
        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Vendor Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>

                <button type="submit" class="btn btn-danger w-100">Add Vendor</button>
                <a href="dashboard_admin.php" class="btn btn-secondary w-100 mt-2">Back to Dashboard</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
