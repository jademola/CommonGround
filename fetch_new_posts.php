<?php
include "sessions.php";
require_once 'db_connect.php';

header('Content-Type: application/json');

$last_post_id = isset($_GET['last_post_id']) ? (int)$_GET['last_post_id'] : 0;

// Fetch posts newer than last_post_id
$posts_sql = "SELECT p.*, u.username 
              FROM post p 
              JOIN userInfo u ON p.author = u.username 
              WHERE p.id > ?
              ORDER BY p.date DESC, p.id DESC";

$stmt = $conn->prepare($posts_sql);
$stmt->bind_param("i", $last_post_id);
$stmt->execute();
$result = $stmt->get_result();

$posts = [];
while ($post = $result->fetch_assoc()) {
    // Fetch tags for this post
    $tag_sql = "SELECT t.name, t.id FROM tags t 
                JOIN post_tags pt ON t.id = pt.tag_id 
                WHERE pt.post_id = ?";
    $tag_stmt = $conn->prepare($tag_sql);
    $tag_stmt->bind_param("i", $post["id"]);
    $tag_stmt->execute();
    $tag_result = $tag_stmt->get_result();
    
    $tags = [];
    while ($tag = $tag_result->fetch_assoc()) {
        $tags[] = [
            'name' => $tag['name'],
            'id' => $tag['id']
        ];
    }
    $tag_stmt->close();
    
    $post['tags'] = $tags;
    $post['formatted_date'] = date("g:ia, F jS, Y", strtotime($post["date"]));
    $posts[] = $post;
}

$stmt->close();
$conn->close();

echo json_encode(['posts' => $posts]);
