<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

include 'db_config.php'; // Include the database configuration

// Fetching the alerted user's ID or location from the previous page
$alertedUserId = $_GET['user_id']; // Assume the user ID is passed in the URL
$sql = "SELECT email, phone, address, latitude, longitude FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $alertedUserId);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($email, $phone, $address, $lat, $lon);
$stmt->fetch();
$stmt->close();

// Define initial location coordinates (replace with your initial pin location)
$initialLat = 14.4497;
$initialLon = 120.9826;

$googleApiKey = 'YOUR_GOOGLE_MAPS_API_KEY'; // Replace with your Google Maps API Key

// Fetch ETA based on traffic using Google Maps Directions API
function fetchEta($origin, $destination, $apiKey) {
    $url = "https://maps.googleapis.com/maps/api/directions/json?origin={$origin}&destination={$destination}&departure_time=now&traffic_model=best_guess&key={$apiKey}";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['routes'][0]['legs'][0]['duration_in_traffic']['text'])) {
        return $data['routes'][0]['legs'][0]['duration_in_traffic']['text'];
    } else {
        return 'Unavailable'; // Handle error or no data
    }
}

// Get ETA for traffic
$origin = "{$initialLat},{$initialLon}";
$destination = "{$lat},{$lon}";
$etaTraffic = fetchEta($origin, $destination, $googleApiKey);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details</title>
    <link rel="icon" href="../res/icons/fire-flames.png" type="image/png">
    <link rel="stylesheet" href="styles.css"> 
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body>
    <header>
            <div class="logo-header">
                <img src="../res/icons/fire-flames.png" alt="fire-flames Logo" class="logo">
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

    <main class="details-container">
        <div class="map-container">
            <div id="map" style="height: 400px;"></div>
        </div>

        <h2>Account Information</h2>
        <p><strong>Email:</strong> <span id="email"><?php echo htmlspecialchars($email); ?></span></p>
        <p><strong>Phone Number:</strong> <span id="phone"><?php echo htmlspecialchars($phone); ?></span></p>
        <p><strong>Address:</strong> <span id="address"><?php echo htmlspecialchars($address); ?></span></p>
        <p><strong>Estimated Time of Arrival for Rescue (with traffic):</strong> <span id="eta"><?php echo $etaTraffic; ?></span></p>
    </main>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Initialize Leaflet map at user's coordinates
        const map = L.map('map').setView([<?php echo $lat; ?>, <?php echo $lon; ?>], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Add a marker for the user's location
        L.marker([<?php echo $lat; ?>, <?php echo $lon; ?>]).addTo(map);
    </script>
</body>
</html>
