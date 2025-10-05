<?php
session_start();

// If already logged in, redirect
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard_admin.php");
    exit();
}

$error = "";

// Handle login form submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // DB connection
    $conn = new mysqli("localhost", "root", "", "foodie");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Find admin by email
    $stmt = $conn->prepare("SELECT admin_id, name, email, password_hash FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Set session
            $_SESSION['admin_id'] = $user['admin_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];

            // Optional: update last login time
            $update = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE admin_id = ?");
            $update->bind_param("i", $user['admin_id']);
            $update->execute();
            $update->close();

            // Redirect to dashboard
            header("Location: dashboard_admin.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Admin not found.";
    }

    $stmt->close();
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login - Cravings Site</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #ffecd2, #fcb69f);
            font-family: 'Segoe UI', sans-serif;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90vh;
        }
        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0px 6px 20px rgba(0,0,0,0.15);
            max-width: 400px;
            width: 100%;
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
            margin-bottom: 20px;
            color: #555;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Cravings Site</a>
            <div class="d-flex">
                <a href="vendor.php" class="btn btn-outline-light me-2">Vendors</a>
                <a href="login.php" class="btn btn-outline-primary me-2">Customer</a>
            </div>
        </div>
    </nav>

    <!-- Login Card -->
    <div class="login-container">
        <div class="login-card">
            <div class="brand-title">Hello, Admin!</div>
            <p class="welcome-quote">"Good food is happiness on a plate."</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" class="form-control" required placeholder="Enter your email">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn btn-danger w-100">Login</button>
            </form>

            </div>
    </div>
</body>
</html>
