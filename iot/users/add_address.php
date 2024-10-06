<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include 'db_config.php'; // Include the database configuration

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$new_address = $_POST['address'];

$sql = "INSERT INTO user_addresses (user_id, address) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $new_address);

if ($stmt->execute()) {
    $_SESSION['status_message'] = "Address added successfully.";
} else {
    $_SESSION['status_message'] = "Error adding address: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: profile.php");
exit();
?>
