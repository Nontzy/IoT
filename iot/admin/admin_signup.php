<?php
session_start();

include 'db_config.php'; // Include the database configuration

$email = $_POST['email'];
$phone = $_POST['phone']; // Include phone
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Check if passwords match
if ($password !== $confirm_password) {
    header("Location: admin_signup.html?message=Passwords do not match&type=error");
    exit();
}

// Create a connection to the database
$conn = new mysqli($servername, $username, $db_password, $dbname); // Use $db_password

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if email already exists
$sql = "SELECT id FROM admin_users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Email already exists
    header("Location: admin_signup.html?message=Email already exists&type=error");
    exit();
}

// Insert new admin into the database with role set to 'admin'
$sql = "INSERT INTO admin_users (email, phone, password, role) VALUES (?, ?, ?, 'admin')"; // Set role to 'admin'
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $email, $phone, $password); // Bind phone and password

if ($stmt->execute()) {
    // Admin account created successfully
    header("Location: admin_profile.php?message=Admin account created successfully&type=success");
} else {
    // Error occurred during account creation
    header("Location: admin_signup.html?message=Error creating account&type=error");
}

$stmt->close();
$conn->close();
?>
