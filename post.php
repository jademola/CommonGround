<?php

include "sessions.php";
// Include the database connection
require_once 'db_connect.php';

// Check if post ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // If no post ID is provided, redirect to index
    header("Location: index.php");
    exit();
}

// Get the post ID from the URL
$post_id = $_GET['id'];

// Fetch the post data
$post_sql = "SELECT p.*, u.username 
             FROM post p 
             JOIN userInfo u ON p.author = u.username 
             WHERE p.id = ?";
$post_stmt = $conn->prepare($post_sql);
$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();

// Check if the post exists
if ($post_result->num_rows == 0) {
    // Post not found, redirect to index
    header("Location: index.php");
    exit();
}

// Get the post data
$post = $post_result->fetch_assoc();
$post_stmt->close();

// Initialize message variables
$error_message = "";
$success_message = "";

// Process comment form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_comment"])) {
    // Get form data
    $comment_content = $_POST["comment_content"];
    if (!isset($_SESSION["username"]) || empty($_SESSION['username'])) {
        $error_message = "Must be logged in to comment.";
    } else if (empty($comment_content)) {
        $error_message = "Comment content cannot be empty.";
    } // Validate inputs
    else {
        $current_user = $_SESSION['username'];
        // Prepare and execute SQL query to insert comment
        $stmt = $conn->prepare("INSERT INTO comments (author, content, post_id, date) VALUES (?, ?, ?, CURRENT_DATE())");
        $stmt->bind_param("ssi", $current_user, $comment_content, $post_id);

        if ($stmt->execute()) {
            // Comment added successfully
            $success_message = "Comment added successfully!";

            // Redirect to avoid form resubmission
            header("Location: post.php?id=" . $post_id);
            exit();
        } else {
            // Error occurred
            $error_message = "Error adding comment: " . $conn->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Common Ground - Post View</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include "header.php"; ?>

    <div class="main-content">
        <aside class="sidebar">
            <!-- Top three posts (by likes, in order) -->
            <div class="sidebar-section">
                <?php include "popularsidebar.php" ?>
                <div class="notification-box">
                    7 new Notifications
                </div>
            </div>
        </aside>

        <main class="feed">
            <h2 class="feed-header" id="results-return"><a href="index.php">&lt; Return to Feed</a></h2>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <div class="post">
                <div class="post-header">
                <?php
                     echo '<img src="getProfileImage.php?id='  . $post['author'] . '"alt="Profile Image" id="user-profile-img">';
                ?>               
                 <div class="user-info">
                        <div><?php echo htmlspecialchars($post["author"]); ?></div>
                        <div>
                            <?php
                            // Fetch tags for this post
                            $tag_sql = "SELECT t.name, t.id FROM tags t 
                                JOIN post_tags pt ON t.id = pt.tag_id 
                                WHERE pt.post_id = ?";
                            $tag_stmt = $conn->prepare($tag_sql);
                            $tag_stmt->bind_param("i", $post_id);
                            $tag_stmt->execute();
                            $tag_result = $tag_stmt->get_result();

                            while ($tag = $tag_result->fetch_assoc()) {
                                $tag_id = htmlspecialchars(strtolower($tag["name"])) . "-tag";
                                echo '<span class="tag" id="' . $tag_id . '">' . htmlspecialchars($tag["name"]) . '</span>';
                            }
                            $tag_stmt->close();
                            ?>
                        </div>
                    </div>
                    <div class="timestamp"><?php echo date("g:ia, F jS, Y", strtotime($post["date"])); ?></div>
                </div>
                <div class="post-title"><?php echo htmlspecialchars($post["title"]); ?></div>
                <div class="post-content">
                    <?php echo htmlspecialchars($post["content"]); ?>
                </div>
                <div class="post-footer">


                </div>

                <!-- Comments Section -->
                <div class="comments-section">
                    <div>Comments:</div>

                    <?php
                    // Fetch comments for this post
                    $comment_sql = "SELECT c.*, u.username FROM comments c 
                        JOIN userInfo u ON c.author = u.username 
                        WHERE c.post_id = ? 
                        ORDER BY c.date DESC";
                    $comment_stmt = $conn->prepare($comment_sql);
                    $comment_stmt->bind_param("i", $post_id);
                    $comment_stmt->execute();
                    $comment_result = $comment_stmt->get_result();

                    if ($comment_result->num_rows > 0) {
                        while ($comment = $comment_result->fetch_assoc()) {
                    ?>
                            <div class="comment">
                                <div class="comment-user"><?php echo htmlspecialchars($comment["author"]); ?>:</div>
                                <div class="comment-body"><?php echo htmlspecialchars($comment["content"]); ?></div>
                                <div class="comment-date"><?php echo date("m/d/y", strtotime($comment["date"])); ?></div>
                            </div>
                    <?php
                        }
                    } else {
                        echo '<div class="no-comments">No comments yet. Be the first to comment!</div>';
                    }
                    $comment_stmt->close();
                    ?>

                    <!-- Add Comment Form -->
                    <form class="comment-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $post_id; ?>">
                        <textarea name="comment_content" placeholder="Write a comment..." required></textarea>
                        <button type="submit" name="submit_comment" class="post-comment-btn">Post Comment</button>
                    </form>
                </div>
            </div>
        </main>
       <aside class="profile-sidebar">
            <?php include "profilesidebar.php"; ?>
        </aside>
    </div>

    <script>
        function changeHeart(button) {
            const postId = button.getAttribute('data-post-id');

            if (button.textContent.includes('♡')) {
                button.textContent = '♥ Like';
                // You can add AJAX call here to update likes in the database
                // Example: fetch('like.php?post_id=' + postId + '&action=like')
            } else {
                button.textContent = '♡ Like';
                // AJAX call to unlike
                // Example: fetch('like.php?post_id=' + postId + '&action=unlike')
            }
        }
    </script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>