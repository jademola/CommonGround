<?php
session_start();
// include "notifications.php";
// Include the database connection
require_once 'db_connect.php';

// Initialize message variables
$error_message = "";
$success_message = "";

// Process comment form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_comment"])) {
    // Get form data
    $post_id = $_POST["post_id"];
    $comment_content = $_POST["comment_content"];


    // Validate inputs
    if (empty($comment_content) || empty($post_id)) {
        $error_message = "Comment content cannot be empty.";
    } else if (!isset($_SESSION["username"]) || empty($_SESSION['username'])) {
        $error_message = "Must be logged in to comment.";
    } else {
        $current_user = $_SESSION["username"] ?? ""; // Assume username is stored in session

        // Prepare and execute SQL query to insert comment
        $stmt = $conn->prepare("INSERT INTO comments (author, content, post_id, date) VALUES (?, ?, ?, CURRENT_DATE())");
        $stmt->bind_param("ssi", $current_user, $comment_content, $post_id);

        if ($stmt->execute()) {
            // Comment added successfully
            $success_message = "Comment added successfully!";

            // Redirect to avoid form resubmission
            header("Location: index.php");
            exit();
        } else {
            // Error occurred
            $error_message = "Error adding comment: " . $conn->error;
        }

        $stmt->close();
    }
}

// Fetch posts with their comments
$posts_sql = "SELECT p.*, u.username 
        FROM post p 
        JOIN userInfo u ON p.author = u.username 
        ORDER BY p.date DESC";
$posts_result = $conn->query($posts_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Common Ground</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include "header.php"; ?>

    <div class="main-content">
        <aside class="sidebar">
            <!-- Top three posts (by likes, in order) -->
            <div class="sidebar-section">
                <?php include "popularsidebar.php" ?>
                <br>
                <div class="notification-box">
                    <a href="activity.php"><?php echo $_SESSION['notification_count']; ?> new Notifications</a>
                </div>
        </aside>

        <main class="feed">
            <h2 class="feed-header">Your Feed:</h2>
            <h3 class="sortby">Sorted by date</h3>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($posts_result && $posts_result->num_rows > 0): ?>
                <?php while ($post = $posts_result->fetch_assoc()): ?>
                    <div class="post">
                        <div class="post-header">
                            <?php
                            echo '<img src="getProfileImage.php?id='  . $post['author'] . '"alt="Profile Image" id="user-profile-img">';
                             ?>
                            <div class="user-info">
                                <div><?php echo htmlspecialchars($post["author"]); ?></div>
                                <div class="post-tags">
                                    <?php
                                    // Fetch tags for this post
                                    $tag_sql = "SELECT t.name, t.id FROM tags t 
                                        JOIN post_tags pt ON t.id = pt.tag_id 
                                        WHERE pt.post_id = ?";
                                    $tag_stmt = $conn->prepare($tag_sql);
                                    $tag_stmt->bind_param("i", $post["id"]);
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
                        <div class="post-title">
                            <a id="titleLink" href="post.php?id=<?php echo $post['id']; ?>">
                                <?php echo htmlspecialchars($post["title"]); ?>
                            </a>
                        </div>
                        <div class="post-content">
                            <?php echo htmlspecialchars($post["content"]); ?>
                        </div>
                        <div class="post-footer">
                            <button class="like-btn" onclick="changeHeart(this)" data-post-id="<?php echo $post['id']; ?>">♡ Like</button>
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
                            $comment_stmt->bind_param("i", $post["id"]);
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
                            <form class="comment-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <textarea name="comment_content" placeholder="Write a comment..." required></textarea>
                                <button type="submit" name="submit_comment" class="post-comment-btn">Post Comment</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-posts">No posts found.</div>
            <?php endif; ?>
        </main>

        <aside class="profile-sidebar">
            <?php include "profilesidebar.php"; ?>
        </aside>
    </div>

    <script>
        function changeHeart(button) {
            if (button.textContent.includes('♡')) {
                button.textContent = '♥ Like';
            } else {
                button.textContent = '♡ Like';
            }
        }
    </script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>