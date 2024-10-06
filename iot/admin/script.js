// Your OpenWeatherMap API key
const apiKey = '9399524711f71e10ee83491501f75624';

// Initialize Leaflet map at coordinates 14.4497, 120.9826
const map = L.map('map').setView([14.4497, 120.9826], 16);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

let selectedCoords = null;
let selectedMarker = null; // Store selected marker
let markers = []; // Store multiple markers
let locations = []; // Store location data

// Define an alert temperature level
const alertTemperatureLevel = 30; // Example threshold in degrees Celsius

// Format coordinates to 5 decimal places
function formatCoords(lat, lon) {
    return {
        lat: lat.toFixed(5),
        lon: lon.toFixed(5)
    };
}

// Fetch temperature based on coordinates
function fetchTemperature(lat, lon, index) {
    const apiURL = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`;

    fetch(apiURL)
        .then(response => response.json())
        .then(data => {
            const temp = data.main.temp;
            // Update the temperature in the locations array
            locations[index].temp = temp;
            
            // Check if the temperature exceeds the alert level
            if (temp >= alertTemperatureLevel) {
                pinLocation(index);
            }

            // Update the location list table
            updateLocationList();
        })
        .catch(error => {
            console.error('Error fetching temperature:', error);
            locations[index].temp = 'Error';
            updateLocationList();
        });
}

// Pin the location on the map
function pinLocation(index) {
    const location = locations[index];
    if (!location.marker) {
        location.marker = L.marker([location.lat, location.lon]).addTo(map);
        markers.push(location.marker);
    }
}

// Add an initial marker that cannot be removed
function addInitialPin() {
    const initialCoords = [14.4497, 120.9826];
    const formattedCoords = formatCoords(initialCoords[0], initialCoords[1]);

    // Add initial marker
    const initialMarker = L.marker(initialCoords).addTo(map);
    markers.push(initialMarker);

    // Store the initial location (non-removable)
    locations.push({ lat: formattedCoords.lat, lon: formattedCoords.lon, marker: initialMarker, removable: false });

    // Update location list
    updateLocationList();
}

// Map click event
map.on('click', function(e) {
    const lat = e.latlng.lat;
    const lon = e.latlng.lng;
    selectedCoords = [lat, lon];

    const formattedCoords = formatCoords(lat, lon);

    // If there's an existing selected marker, remove it
    if (selectedMarker) {
        map.removeLayer(selectedMarker);
    }

    // Add a new marker for the selected location
    selectedMarker = L.marker(e.latlng).addTo(map);

    // Display the selected location in the UI
    document.getElementById('selectedLocation').textContent = `Selected Location: Lat ${formattedCoords.lat}, Lon ${formattedCoords.lon}`;

    // Enable the confirm button
    document.getElementById('confirmLocation').disabled = false;

    // Fetch temperature for the clicked location
    fetchTemperature(formattedCoords.lat, formattedCoords.lon, -1); // Pass -1 as it's not stored yet
});

// Function to update the displayed list of locations
function updateLocationList() {
    const locationList = document.getElementById('locationList').getElementsByTagName('tbody')[0];
    locationList.innerHTML = ''; // Clear the current list

    locations.forEach((location, index) => {
        const row = document.createElement('tr');
        
        // Location Number
        const locationNumber = document.createElement('td');
        locationNumber.textContent = index + 1;
        row.appendChild(locationNumber);

        // Coordinates
        const coordinates = document.createElement('td');
        coordinates.textContent = `Lat ${location.lat}, Lon ${location.lon}`;
        row.appendChild(coordinates);

        // Address
        const address = document.createElement('td');
        address.textContent = location.address || 'Fetching address...';
        row.appendChild(address);

        // Temperature
        const temperature = document.createElement('td');
        temperature.textContent = `Temp: ${location.temp || 'Fetching...'}`;
        row.appendChild(temperature);

        // Actions (View and New Page Buttons)
        const actions = document.createElement('td');
        
        // View Button
        const viewButton = document.createElement('button');
        viewButton.textContent = 'View';
        viewButton.className = 'view';
        viewButton.onclick = function() {
            // Zoom to the location on the map
            const latLng = [parseFloat(location.lat), parseFloat(location.lon)];
            map.setView(latLng, 19); // Adjust zoom level as needed
        };
        actions.appendChild(viewButton);

        // New Action Button
        const newPageButton = document.createElement('button');
        newPageButton.textContent = 'Details'; // Set the button label
        newPageButton.className = 'details';
        newPageButton.onclick = function() {
            // Redirect to another page, e.g., 'details.html'
            window.location.href = `details.php?lat=${location.lat}&lon=${location.lon}`; // Pass latitude and longitude as query parameters
        };
        actions.appendChild(newPageButton);

        row.appendChild(actions);
        locationList.appendChild(row);
    });
}


// Function to confirm the selected location
function confirmLocation() {
    if (selectedMarker) {
        const lat = selectedMarker.getLatLng().lat.toFixed(5);
        const lon = selectedMarker.getLatLng().lng.toFixed(5);

        // Store location data (removable pin)
        const location = { lat, lon, marker: selectedMarker, removable: true, temp: 'Fetching...' };
        locations.push(location);
        markers.push(selectedMarker);

        // Fetch temperature and address for the selected location
        fetchTemperature(lat, lon, locations.length - 1); // Pass index of the newly added location
        fetchAddress(lat, lon, locations.length - 1); // Pass index of the newly added location

        // Clear the selected marker and disable the confirm button
        selectedMarker = null;
        document.getElementById('confirmLocation').disabled = true;
    }
}

// Function to fetch address and update user_locations
function fetchAddress(lat, lon, index) {
    const apiURL = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`;

    fetch(apiURL)
        .then(response => response.json())
        .then(data => {
            let address = data.display_name || 'Address not found';

            // Split the address by commas and select the first 5 parts
            const addressParts = address.split(',').slice(0, 5).join(', ');

            // Store the shortened address in the locations array
            locations[index].address = addressParts;

            // Send the location and address to the server
            fetch('save_location.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'latitude': lat,
                    'longitude': lon,
                    'address': addressParts
                })
            })
            .then(response => response.text())
            .then(data => {
                console.log('Location and address saved:', data);
            })
            .catch(error => {
                console.error('Error saving location and address:', error);
            });

            // Update the location list with the address
            updateLocationList();
        })
        .catch(error => {
            console.error('Error fetching address:', error);
            locations[index].address = 'Address not found';
            updateLocationList();
        });
}

// Function to fetch the user's locations from the server on page load
function fetchUserLocations() {
    fetch('fetch_locations.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error fetching locations:', data.error);
                return;
            }

            // Clear existing table
            const locationList = document.getElementById('locationList').getElementsByTagName('tbody')[0];
            locationList.innerHTML = '';

            data.forEach((location, index) => {
                const row = document.createElement('tr');

                // Location Number
                const locationNumber = document.createElement('td');
                locationNumber.textContent = index + 1;
                row.appendChild(locationNumber);

                // Coordinates
                const coordinates = document.createElement('td');
                coordinates.textContent = `Lat ${location.latitude}, Lon ${location.longitude}`;
                row.appendChild(coordinates);

                // Address
                const address = document.createElement('td');
                address.textContent = location.address;
                row.appendChild(address);

                // Temperature
                const temperature = document.createElement('td');
                temperature.textContent = 'Temp: Fetching...'; // Placeholder for temperature
                row.appendChild(temperature);

                // Actions (View Button)
                const actions = document.createElement('td');

                // View Button
                const viewButton = document.createElement('button');
                viewButton.textContent = 'View';
                viewButton.className = 'view';
                viewButton.onclick = function() {
                    // Zoom to the location on the map
                    const latLng = [parseFloat(location.latitude), parseFloat(location.longitude)];
                    map.setView(latLng, 19); // Adjust zoom level as needed
                };
                actions.appendChild(viewButton);

                row.appendChild(actions);
                locationList.appendChild(row);

                // Store in locations array
                locations.push({
                    lat: location.latitude,
                    lon: location.longitude,
                    marker: L.marker([location.latitude, location.longitude]).addTo(map),
                    address: location.address,
                    removable: location.removable,
                    temp: 'Fetching...'
                });
            });

            // Update temperatures after fetching all locations
            locations.forEach((location, index) => {
                fetchTemperature(location.lat, location.lon, index);
            });
        })
        .catch(error => {
            console.error('Error fetching user locations:', error);
        });
}

// Fetch user locations when the page loads
window.onload = function() {
    addInitialPin();
    fetchUserLocations();
};

document.getElementById('confirmLocation').addEventListener('click', confirmLocation);
