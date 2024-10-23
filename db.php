<?php
$servername = "localhost"; // Database server (usually "localhost")
$username = "uhgjpkjnkkwnq"; // Database username
$password = "shumail@123"; // Database password (leave empty if using XAMPP default)
$dbname = "dbmdju4fgdana6"; // The name of your database

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
