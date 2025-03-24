<?php
/*
like_post.php:
Helper class for post:
1. Checks if user is logged in
2. Fetches post ID
3. Checks if user has already liked the post, then changes the status 
*/
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to like posts']);
    exit;
}

// Get post ID from POST data
$post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
$username = $_SESSION['username'];

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

try {
    // Check if user already liked the post
    $check_sql = "SELECT * FROM post_likes WHERE post_id = ? AND username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $post_id, $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // User already liked the post, so remove the like
        $delete_sql = "DELETE FROM post_likes WHERE post_id = ? AND username = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("is", $post_id, $username);
        $success = $delete_stmt->execute();
        $delete_stmt->close();
        
        echo json_encode(['success' => $success, 'liked' => false]);
    } else {
        // User hasn't liked the post, so add a like
        $insert_sql = "INSERT INTO post_likes (post_id, username) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("is", $post_id, $username);
        $success = $insert_stmt->execute();
        $insert_stmt->close();
        
        echo json_encode(['success' => $success, 'liked' => true]);
    }

    $check_stmt->close();
} catch (Exception $e) {
    error_log("Like error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}

$conn->close();
// end of class
?>