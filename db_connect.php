<?php

// Database connection details
$servername = "localhost";   // Hostname
$username = "root";          // XAMPP MySQL default username
$password = "";              // XAMPP MySQL default password (blank)
$dbname = "CommonGround";    // The name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection 
if ($conn->connect_error) {
    // If there is an error, display a message 
    die("Connection failed: " . $conn->connect_error);
} 
?>

