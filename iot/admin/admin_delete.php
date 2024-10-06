<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

include 'db_config.php'; // Include the database configuration

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $admin_id = $_GET['id'];

    // Prepare delete statement
    $sql = "DELETE FROM admin_users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        $_SESSION['status_message'] = "Admin deleted successfully.";
    } else {
        $_SESSION['status_message'] = "Failed to delete admin.";
    }
    
    $stmt->close();
}

$conn->close();
header("Location: admin_profile.php");
exit();
?>
