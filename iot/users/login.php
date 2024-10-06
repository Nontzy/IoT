<?php
session_start();

include 'db_config.php'; // Include the database configuration

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT id, password FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($id, $stored_password);
$stmt->fetch();

if ($password === $stored_password) {
    $_SESSION['user_id'] = $id;
    header("Location: dashboard.php"); // Redirect to a dashboard or home page
} else {
    echo "Invalid email or password. <a href='login.html'>Try again</a>.";
}

$stmt->close();
$conn->close();
?>
