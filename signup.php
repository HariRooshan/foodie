<?php
session_start();
$error = "";
$success = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password_pattern = "/^(?=.*[0-9])(?=.*[\W_]).{6,}$/";

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $conn = new mysqli("localhost", "root", "", "foodie");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered. Please login.";
        } else {
            if (!preg_match($password_pattern, $password)) {
                $error = "Password must be at least 6 characters long and include at least one number and one special character.";
            } else {
            // Insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, phone, address) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $name, $email, $hashed_password, $phone, $address);

            if ($insert_stmt->execute()) {
                // Redirect to login page
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
            $insert_stmt->close();
        } }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Cravings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #ffecd2, #fcb69f);
            font-family: 'Segoe UI', sans-serif;
        }
        .signup-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90vh;
        }
        .signup-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0px 6px 20px rgba(0,0,0,0.15);
        }
        .brand-title {
            text-align: center;
            font-size: 2rem;
            font-weight: bold;
            color: #e74c3c;
        }
        .welcome-quote {
            text-align: center;
            font-size: 1.1rem;
            margin-bottom: 20zpx;
            color: #555;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Cravings Site</a>
            <div class="d-flex">
                <a href="admin.php" class="btn btn-outline-dark me-2">Admin</a>
                <a href="vendor.php" class="btn btn-outline-primary me-2">Vendor</a>
                <a href="login.php" class="btn btn-success">Login</a>
            </div>
        </div>
    </nav>

    <!-- Signup Card -->
    <div class="signup-container">
        <div class="signup-card">
            <div class="brand-title">Join Cravings Site🍴</div>
            <p class="welcome-quote">"Your journey to delicious food starts here."</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Enter your full name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="Enter your phone number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" placeholder="Enter your address"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter password">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Confirm password">
                </div>
                <button type="submit" class="btn btn-danger w-100">Sign Up</button>
            </form>

            <div class="text-center mt-3">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>
</body>
</html>
