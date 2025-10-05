<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];

    // DB connection
    $conn = new mysqli("localhost", "root", "", "foodie");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Mark vendor as inactive
    // $stmt = $conn->prepare("UPDATE vendors SET is_active = FALSE WHERE vendor_id = ?");
    // $stmt->bind_param("i", $vendor_id);
    // $stmt->execute();
    // $stmt->close();
    $conn->close();

    // Destroy session
    session_unset();
    session_destroy();
}

// Redirect to login
header("Location: admin.php");
exit();
?>
