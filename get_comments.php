<?php
/*
Helper class for post:
1. Fetches comments on the post
2. Formats the comments for JSON response and returns them
*/

// Start session
session_start();

// Include database connection
require_once 'db_connect.php';

// Get post ID from query parameter
$post_id = filter_input(INPUT_GET, 'post_id', FILTER_SANITIZE_NUMBER_INT);

// Validate
if (!$post_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid post ID']);
    exit;
}

// Fetch comments for the post
$sql = "SELECT c.id, c.content, c.date, c.author 
        FROM comments c 
        WHERE c.post_id = ? 
        ORDER BY c.date DESC";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

// Format comments for JSON response
$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = [
        'id' => $row['id'],
        'author' => htmlspecialchars($row['author']),
        'content' => htmlspecialchars($row['content']),
        'date' => date("m/d/y g:ia", strtotime($row['date']))
    ];
}

// Return comments as JSON
header('Content-Type: application/json');
echo json_encode(['comments' => $comments]);

// Close resources
$stmt->close();
$conn->close();
?>