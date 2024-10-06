<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

include 'db_config.php'; // Include the database configuration

$user_id = $_SESSION['user_id'];
$sql = "SELECT email, phone FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($email, $phone);
$stmt->fetch();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="../res/icons/bonfire.png" type="image/png">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <!-- chart JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <section class="hero">
            <h2>Stay Safe with Real-time Fire Detection</h2>
            <p>Our IoT-based system detects fires and sends immediate alerts to keep your home safe.</p>
            <b><p id="selectedLocation">Selected Location: None</p></b>
        </section>

        <section class="status">
            <h3>System Status:</h3>
            <p id="fireStatus">No fire detected.</p>
        </section>

       
        <div class="map-container">
            <div id="map"></div>
            <button id="confirmLocation" disabled>Confirm Location</button>
        </div>

        <hr>
        
        <div class="location-container">
            <h2>Selected Locations</h2>
            <table id="locationList">
                <thead>
                    <tr>
                        <th>Location #</th>
                        <th>Coordinates</th>
                        <th>Address</th>
                        <th>Temperature</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Location rows will be inserted here dynamically -->
                </tbody>
            </table>
        </div>

        <!-- Graph for temperature history -->
        <div class="chart-container">
            <h2>Temperature History</h2>
            <canvas id="temperatureChart"></canvas>
        </div>
    </main>

    
    <footer>
        <p>&copy; 2024 IOT-base Fire Detection System. All rights reserved.</p>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="script.js"></script>

</body>
</html>
