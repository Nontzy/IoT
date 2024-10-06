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
$id = $_GET['id']; // Get the address ID from the query string

// Delete the address from the database
$sql = "DELETE FROM user_locations WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $user_id);

if ($stmt->execute()) {
    $_SESSION['status_message'] = 'Address successfully deleted';
    header("Location: profile.php");
    exit();
} else {
    $_SESSION['status_message'] = 'Error deleting address: ' . $stmt->error;
}
$stmt->close();
$conn->close();
?>
