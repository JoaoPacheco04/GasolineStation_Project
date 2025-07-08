<?php
$servername = "localhost";
$username = "root"; // default in XAMPP
$password = ""; // default in XAMPP
$dbname = "Gasolina_Station";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>