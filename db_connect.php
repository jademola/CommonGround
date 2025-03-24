<?php
/*
db_connect.php:
Initializes database connection:
*/

// Database connection details
//$servername = "localhost";   // Hostname
//$username = "root";          // XAMPP MySQL default username
//$password = "";              // XAMPP MySQL default password (blank)
//$dbname = "CommonGround";    // The name of your database

$servername = "localhost";           // Still localhost, correct
$username = "tima01";                // Your CWL
$password = "tima01";                // Unless you’ve changed it
$dbname = "tima01";                  // Your DB name (same as CWL)


// Try to connect
try {
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection 
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    throw new Exception("Database connection failed");
}

// set charset to true UTF-8 encoding 
$conn->set_charset("utf8mb4");

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage()); 
    die("Database connection error");
}
// end of class
?>

