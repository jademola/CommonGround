<?php
include "sessions.php";
include "db_connect.php";

if (isset($_SESSION['username'])) {
    // Query to count total notifications (likes + comments) for user's posts
    $sql = "SELECT 
            (SELECT COUNT(*) 
             FROM post_likes pl 
             JOIN post p ON p.id = pl.post_id 
             WHERE p.author = ?) +
            (SELECT COUNT(*) 
             FROM comments c 
             JOIN post p ON p.id = c.post_id 
             WHERE p.author = ?) as total_notifications";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $_SESSION['username'], $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Store notification count in session
    $_SESSION['notification_count'] = $row['total_notifications'];

    $stmt->close();
} else {
    $_SESSION['notification_count'] = 0;
}

// $conn->close();
?>
