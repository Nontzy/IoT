<?php
session_start();

include 'db_config.php'; // Include the database configuration

$email = $_POST['email'];
$password = $_POST['password'];

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute SQL statement to fetch user with admin role
$sql = "SELECT id, password, role FROM admin_users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($id, $stored_password, $role);
$stmt->fetch();

if ($stmt->num_rows === 0) {
    // No user found with the given email
    header("Location: admin_login.html?error=invalid_email");
} else {
    // Check if the provided password matches the stored password (without hashing)
    if ($password === $stored_password) {
        // Verify the user is an admin
        if ($role === 'admin') {
            $_SESSION['admin_id'] = $id; // Store admin session
            header("Location: admin_dashboard.php"); // Redirect to admin dashboard
            exit();
        } else {
            header("Location: admin_login.html?error=not_admin"); // Not an admin
        }
    } else {
        header("Location: admin_login.html?error=invalid_password"); // Invalid password
    }
}

$stmt->close();
$conn->close();
?>
