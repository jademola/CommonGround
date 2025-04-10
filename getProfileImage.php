<?php
include 'db_connect.php'; 

ini_set('display_errors', 1);
error_reporting(E_ALL); 

include "sessions.php";

// If page is selected and an id has been passed through URL, it will use the username passed, 
// otherwise, will display the session user
if (isset($_GET['username']) && !empty($_GET['username'])) {
    $displayUser = $_GET['username']; 
}
else {
    $displayUser = $_SESSION['username'];
}


$sql = "SELECT contentType, image 
        FROM userImages WHERE username = ?";

$stmt = mysqli_prepare($conn, $sql);

    // Bind the username as parameter
    mysqli_stmt_bind_param($stmt, "s", $displayUser);

    // Execute the query
    mysqli_stmt_execute($stmt);

    // Bind the result columns to variables
    mysqli_stmt_bind_result($stmt, $contentType, $imageData);

    // Fetch the image data
    if (mysqli_stmt_fetch($stmt)) {
        // Check if the image data is not empty
        if (!empty($imageData)) {
            // Set the content type header based on the MIME type of the image
            ob_clean();
            header("Content-Type: " . $contentType);
    
            // Output the binary image data
            echo $imageData;
        } else {
            echo "No image found for this user.";
        }
    } else {
        echo "Error: Could not fetch image data.";
    }
    
    // Close the statement and database connection
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
?>