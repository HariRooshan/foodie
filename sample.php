<?php
// DB connection
$conn = new mysqli("localhost", "root", "", "foodie");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Vendor details
$name = "Pizza Vendor";
$email = "admin@cravings.com";
$password = "Abcdef@05";
$phone = "9876543210";
$address = "123 Pizza Street, Food City";

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Insert vendor
$stmt = $conn->prepare("INSERT INTO admins (email, password_hash) VALUES (?, ?)");
$stmt->bind_param("ss", $email, $password_hash);

if ($stmt->execute()) {
    echo "Vendor inserted successfully!";
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
