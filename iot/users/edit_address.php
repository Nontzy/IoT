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

// Fetch the current address for the given ID
$sql = "SELECT address FROM user_locations WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($address);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_address = $_POST['address'];

    // Update address in the database
    $sql = "UPDATE user_locations SET address = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $new_address, $id, $user_id);

    if ($stmt->execute()) {
        $_SESSION['status_message'] = 'Address updated successfully';
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['status_message'] = 'Error updating address: ' . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Address</title>
    <link rel="stylesheet" href="form.css">
</head>
<body>
    <header>
        <h1>IOT-based Fire Detection System</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="edit-address-form">
            <h2>Edit Address</h2>
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

            <form action="edit_address.php?id=<?php echo $id; ?>" method="post">
                <label for="address">Address:</label>
                <textarea id="address" name="address" rows="4" cols="50" required><?php echo htmlspecialchars($address); ?></textarea><br>
                <button type="submit">Update Address</button>
            </form>
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
