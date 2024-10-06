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
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
$address = $_POST['address'];

// Insert the location and address into the user_locations table
$sql = "INSERT INTO user_locations (user_id, latitude, longitude, address) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("idds", $user_id, $latitude, $longitude, $address);

if ($stmt->execute()) {
    echo "Location and address saved successfully";
} else {
    echo "Error saving location and address: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
