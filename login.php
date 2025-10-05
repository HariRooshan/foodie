<?php
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
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

    // Count currently active users
    $active_check = $conn->query("SELECT COUNT(*) AS active_users FROM users WHERE is_active = TRUE");
    $row = $active_check->fetch_assoc();
    $active_users = $row['active_users'];

    if ($active_users >= 10) {
        $error = "Maximum active users reached. Please try again later.";
    } else {
        // Prepare query to find user by email
        $stmt = $conn->prepare("SELECT user_id, name, email, password_hash, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Set session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];

                // Mark user as active
                $update = $conn->prepare("UPDATE users SET is_active = TRUE WHERE user_id = ?");
                $update->bind_param("i", $user['user_id']);
                $update->execute();
                $update->close();

                // Redirect to homepage
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }
        $stmt->close();
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login - Cravings</title>
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
                <a href="admin.php" class="btn btn-outline-light me-2">Admin</a>
                <a href="vendor.php" class="btn btn-outline-primary me-2">Vendor</a>
                <a href="signup.php" class="btn btn-success">Sign Up</a>
            </div>
        </div>
    </nav>

    <!-- Login Card -->
    <div class="login-container">
        <div class="login-card">
            <div class="brand-title">Welcome to Cravings Site🍴</div>
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

            <div class="text-center mt-3">
                Don’t have an account? <a href="signup.php">Sign Up</a>
            </div>
        </div>
    </div>
</body>
</html>
