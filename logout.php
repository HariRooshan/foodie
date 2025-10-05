<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // DB connection
    $conn = new mysqli("localhost", "root", "", "foodie");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Mark user as inactive
    $stmt = $conn->prepare("UPDATE users SET is_active = FALSE WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Destroy session
    session_unset();
    session_destroy();
}

// Redirect to login
header("Location: login.php");
exit();
?>
