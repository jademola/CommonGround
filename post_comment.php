<?php
/*
post_comment.php:
Helper class for post:
1. Checks if user is logged in
2. Gets form data, validates inputs
3. Executes SQL query to insert comment
*/
// Start session
session_start();

// CSRF protection 
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to comment']);
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Get form data
$post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
$comment_content = htmlspecialchars(trim($_POST['comment_content']));
$username = $_SESSION['username'];

// Validate inputs
if (empty($comment_content) || empty($post_id)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Comment content cannot be empty']);
    exit;
}

try {
    // Prepare and execute SQL query to insert comment
    $stmt = $conn->prepare("INSERT INTO comments (author, content, post_id, date) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("ssi", $username, $comment_content, $post_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Get the new comment ID
        $comment_id = $conn->insert_id;
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'comment' => [
                'id' => $comment_id,
                'author' => htmlspecialchars($username),
                'content' => $comment_content,
                'date' => date("m/d/y g:ia")
            ]
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error adding comment']);
    }

    $stmt->close();
} catch (Exception $e) {
    error_log("Comment error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}

$conn->close();
// end of class
?>