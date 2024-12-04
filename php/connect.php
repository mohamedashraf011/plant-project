<?php
$servername = "localhost"; // Database server (change if using a remote DB)
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "plant_website"; // Database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
