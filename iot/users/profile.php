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

// Fetch user email and phone
$sql = "SELECT email, phone FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($email, $phone);
$stmt->fetch();
$stmt->close();

// Fetch user addresses from user_locations
$sql = "SELECT id, address FROM user_locations WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($id, $address);
$addresses = [];
while ($stmt->fetch()) {
    $addresses[] = ['id' => $id, 'address' => htmlspecialchars($address)];
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="icon" href="../res/icons/bonfire.png" type="image/png">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
        <div class="logo-header">
            <a href="dashboard.php" class="logo-header">
                <img src="../res/icons/bonfire.png" alt="Bonfire Icon" class="logo" type="image/png">
            </a>
            <h1>IOT-based Fire Detection System</h1>
        </div>
        
        <nav>
            <ul>
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php" class="logout-link">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="profile-menu">
            <h2>Your Profile</h2>
            <hr>

            <!-- Display status message if available -->
            <?php if (isset($_SESSION['status_message'])): ?>
                <div id="status-message" class="status-message">
                    <?php
                    echo htmlspecialchars($_SESSION['status_message']);
                    unset($_SESSION['status_message']);
                    ?>
                </div>
            <?php endif; ?>

            <p><strong>Email:</strong> <u><?php echo htmlspecialchars($email); ?></u></p>
            <p><strong>Phone Number:</strong> <u><?php echo htmlspecialchars($phone); ?></u></p>
            
            <!-- Update phone number -->
            <form action="update_profile.php" method="post">
                <label for="phone">Update Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                <button type="submit">Update</button>
            </form>

            <hr>
            <!-- Display user addresses -->
            <h3>Addresses:</h3>
            <?php if (!empty($addresses)): ?>
                <ul>
                    <?php foreach ($addresses as $address): ?>
                        <li>
                            <?php echo htmlspecialchars($address['address']); ?>
                            <a href="edit_address.php?id=<?php echo $address['id']; ?>" class="edit-link">Edit</a>
                            <a href="delete_address.php?id=<?php echo $address['id']; ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this address?');">Delete</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No addresses found.</p>
            <?php endif; ?>

        </div>
    </main>
    <footer>
        <p>&copy; 2024 Fire Detection System. All rights reserved.</p>
    </footer>

    <script>
        // Check if the status message element exists
        var statusMessage = document.getElementById('status-message');
        if (statusMessage) {
            // Set a timeout to remove the status message after 3 seconds
            setTimeout(function() {
                statusMessage.style.opacity = '0';
                setTimeout(function() {
                    statusMessage.style.display = 'none';
                }, 500); // Allow some time for fade-out effect
            }, 3000); // 3 seconds
        }
    </script>
</body>
</html>
