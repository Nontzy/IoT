<?php
session_start();

include 'db_config.php'; // Include the database configuration

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$new_phone = $_POST['phone'];

$sql = "UPDATE users SET phone = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $new_phone, $user_id);

if ($stmt->execute()) {
    $_SESSION['status_message'] = "Profile updated successfully.";
} else {
    $_SESSION['status_message'] = "Error updating profile: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirect to the profile page
header("Location: profile.php");
exit();
?>
