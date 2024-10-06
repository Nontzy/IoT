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

$admin_id = $_SESSION['admin_id'];

// Fetch admin email and phone
$sql = "SELECT email, phone FROM admin_users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($email, $phone);
$stmt->fetch();
$stmt->close();

// Fetch all admin accounts
$sql = "SELECT id, email, phone FROM admin_users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($admin_id, $admin_email, $admin_phone);

$admins = [];
while ($stmt->fetch()) {
    $admins[] = [
        'id' => $admin_id,
        'email' => htmlspecialchars($admin_email),
        'phone' => htmlspecialchars($admin_phone)
    ];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile</title>
    <link rel="icon" href="../res/icons/fire-flames.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo-header">
            <a href="admin_dashboard.php" class="logo-header">
                    <img src="../res/icons/fire-flames.png" alt="fire-flames Icon" class="logo" type="image/png">
                </a>
                <h1>IOT-based Fire Detection System</h1>
        </div>

            <nav>
                <ul>
                    <li><a href="admin_dashboard.php">Home</a></li>
                    <li><a href="admin_profile.php">Profile</a></li>
                    <li><a href="admin_logout.php" class="logout-link">Logout</a></li>
                </ul>
            </nav>
    </header>
    <main>
        <div class="profile-menu">
            <h2>Your Admin Profile</h2>
            <hr>

            <p><strong>Email:</strong> <u><?php echo htmlspecialchars($email); ?></u></p>
            <p><strong>Phone Number:</strong> <u><?php echo htmlspecialchars($phone); ?></u></p>
            
            <!-- Update phone number -->
            <form action="admin_update_profile.php" method="post">
                <label for="phone">Update Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                <button type="submit">Update</button>
            </form>

            <hr>
                <h3>All Admin Accounts:</h3>
            <?php if (!empty($admins)): ?>
                <ul>
                    <?php foreach ($admins as $admin): ?>
                        <li>
                            <strong>Email:</strong> <?php echo $admin['email']; ?> <br>
                            <strong>Phone Number:</strong> <?php echo $admin['phone']; ?> <br>
                            <a href="admin_delete.php?id=<?php echo $admin['id']; ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this admin account?');">Delete</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No admin accounts found.</p>
            <?php endif; ?>

            <!-- Create new admin account button -->
            <div class="create-button-container">
                <a href="admin_signup.php" class="create-button">Create Admin Account</a>
            </div>

        </div>
    </main>
    <footer>
        <p>&copy; 2024 Fire Detection System - Admin. All rights reserved.</p>
    </footer>
</body>
</html>
