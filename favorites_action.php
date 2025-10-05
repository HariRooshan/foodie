<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "foodie");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$food_id = intval($_GET['food_id']);
$action = $_GET['action']; // "add" or "remove"

if ($action == "add") {
    $stmt = $conn->prepare("INSERT IGNORE INTO favorites (user_id, food_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $food_id);
    $stmt->execute();
    $stmt->close();
} elseif ($action == "remove") {
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id=? AND food_id=?");
    $stmt->bind_param("ii", $user_id, $food_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header("Location: favorites.php");
exit();
?>
