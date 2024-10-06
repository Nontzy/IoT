<?php
include 'db_config.php'; // Include the database configuration

$email = $_POST['email'];
$phone = $_POST['phone'];
$password = $_POST['password'];

$sql = "INSERT INTO users (email, phone, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $email, $phone, $password);

if ($stmt->execute()) {
    echo "Sign-up successful. <a href='login.html'>Login here</a>.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
