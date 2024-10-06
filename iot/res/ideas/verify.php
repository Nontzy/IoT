<?php
include 'db_config.php';

$code = $_POST['code'];

// Check if the code matches the one in the temp table
$sql = "SELECT * FROM users_temp WHERE verification_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Move the user from the temporary table to the main users table
    $row = $result->fetch_assoc();
    $sql_insert = "INSERT INTO users (email, phone, password) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sss", $row['email'], $row['phone'], $row['password']);
    $stmt_insert->execute();

    // Delete the temporary record
    $sql_delete = "DELETE FROM users_temp WHERE verification_code = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("s", $code);
    $stmt_delete->execute();

    echo "Verification successful. <a href='login.html'>Login here</a>.";
} else {
    echo "Invalid verification code.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account</title>
</head>
<body>
    <header>
        <h1>Fire Detection System</h1>
    </header>
    <main>
        <h2>Verify Your Account</h2>
        <form action="verify_code.php" method="post">
            <label for="code">Enter the 6-digit verification code sent to your phone:</label>
            <input type="text" id="code" name="code" required>

            <button type="submit">Verify</button>
        </form>
    </main>
</body>
</html>
