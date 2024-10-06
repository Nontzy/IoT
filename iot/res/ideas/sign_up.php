<?php
include 'db_config.php'; // Include the database configuration
require 'vendor/autoload.php'; // Load Twilio SDK

use Twilio\Rest\Client;

// Get the form inputs
$email = $_POST['email'];
$phone = $_POST['phone'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Generate a 6-digit verification code
$verification_code = rand(100000, 999999);

// Insert the user info and verification code into a temporary table
$sql = "INSERT INTO users_temp (email, phone, password, verification_code) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $email, $phone, $password, $verification_code);

if ($stmt->execute()) {
    // Send the verification code via SMS using Twilio
    $sid = 'your_twilio_sid';
    $token = 'your_twilio_token';
    $twilio_phone_number = 'your_twilio_phone_number';

    $client = new Client($sid, $token);
    $client->messages->create(
        $phone, // the phone number the code is sent to
        array(
            'from' => $twilio_phone_number,
            'body' => "Your verification code is: $verification_code"
        )
    );

    // Redirect the user to the verification page
    header('Location: verify.php');
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="form.css">
</head>
<body>
    <header>
        <h1>Fire Detection System</h1>
    </header>
    <main>
        <h2>Sign Up</h2>
        <form action="signup.php" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.html">Login here</a>.</p>
    </main>
</body>
</html>
