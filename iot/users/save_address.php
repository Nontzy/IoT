<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in";
    exit();
}

include 'db_config.php'; // Include your database configuration

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$address = $_POST['address'];

// Insert the address into the user_addresses table
$sql = "INSERT INTO user_addresses (user_id, address) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $address);

if ($stmt->execute()) {
    echo "Address saved successfully";
} else {
    echo "Error saving address: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
