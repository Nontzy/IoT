<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.html");
    exit();
}

include 'db_config.php'; // Include the database configuration

$admin_id = $_SESSION['admin_id'];
$sql = "SELECT email FROM admin_users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

// Fetch detected locations including user email
$sqlLocations = "SELECT id, latitude, longitude, address, temperature, user_email FROM detected_locations"; 
$result = $conn->query($sqlLocations);

if (!$result) {
    die("Error executing query: " . $conn->error);
}

// Prepare data for displaying in the table
$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = $row;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="../res/icons/fire-flames.png" type="image/png">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
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
        <section class="hero">
            <h2>Ensuring Safety with Precision and Vigilance</h2>
            <p>"Our system empowers you to monitor, detect, and protect — securing lives, multiple alerts at the same time."</p>
            <b><p id="selectedLocation">Selected Location: None</p></b>
        </section>

        <section class="status">
            <h3>System Status:</h3>
            <p id="fireStatus">No fire detected.</p>
        </section>

        <div class="map-container">
            <div id="map"></div>
        </div>

        <section class="hero">
            <h2>Detected Locations</h2>
            <table id="locationList">
                <thead>
                    <tr>
                        <th>Location #</th>
                        <th>Coordinates</th>
                        <th>Address</th>
                        <th>Temperature</th>
                        <th>User Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['latitude'] . ', ' . $row['longitude']); ?></td>
                            <td><?php echo htmlspecialchars($row['address']); ?></td>
                            <td><?php echo htmlspecialchars($row['temperature']); ?> °C</td>
                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td>
                                <a href="details.php?user_id=<?php echo $row['id']; ?>">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
            <hr>
        <div class="log-container">
            <h2>Detected Locations Log</h2>
            <table id="detectedLocationsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Address</th>
                        <th>Temperature (°C)</th>
                        <th>User Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $location): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($location['id']); ?></td>
                            <td><?php echo htmlspecialchars($location['latitude']); ?></td>
                            <td><?php echo htmlspecialchars($location['longitude']); ?></td>
                            <td><?php echo htmlspecialchars($location['address']); ?></td>
                            <td><?php echo htmlspecialchars($location['temperature']); ?></td>
                            <td><?php echo htmlspecialchars($location['user_email']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>

    <footer>
        <p>&copy; 2024 IOT-based Fire Detection System. All rights reserved.</p>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Initialize the map
        const map = L.map('map').setView([14.4497, 120.9826], 16); // Set to your desired initial coordinates
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Populate the detected locations table
        const locations = <?php echo json_encode($locations); ?>; // Pass PHP data to JavaScript
        const detectedLocationsTable = document.getElementById('detectedLocationsTable').getElementsByTagName('tbody')[0];

        locations.forEach(location => {
            const row = document.createElement('tr');

            // Create cells for each property
            Object.keys(location).forEach(key => {
                const cell = document.createElement('td');
                cell.textContent = location[key];
                row.appendChild(cell);
            });

            // Append the row to the table body
            detectedLocationsTable.appendChild(row);
        });
    </script>
    <script src="script.js"></script>
</body>
</html>
