<?php
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

    // Get current user - in a real application, this would come from a session
    $current_user = $_SESSION["username"] ?? ""; // Assume username is stored in session

    // For testing purposes, if no session is available, use a default user
    if (empty($current_user)) {
        $current_user = "TroyBoy78"; // Use an existing user from the database for testing
    }

    // Validate inputs
    if (empty($comment_content)) {
        $error_message = "Comment content cannot be empty.";
    } else {
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
                <br>
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
                    <img src="images/icon.png" alt="" id="post-img">
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
                    <button class="like-btn" onclick="changeHeart(this)" data-post-id="<?php echo $post_id; ?>">
                        <?php
                        // Check if the current user has liked this post
                        $like_check_sql = "SELECT * FROM post_likes WHERE post_id = ? AND user_id = ?";
                        $like_check_stmt = $conn->prepare($like_check_sql);
                        // Use a placeholder user ID if no session is available
                        $user_id = $_SESSION["user_id"] ?? 1;
                        $like_check_stmt->bind_param("ii", $post_id, $user_id);
                        $like_check_stmt->execute();
                        $like_result = $like_check_stmt->get_result();

                        // Display filled heart if user has liked, empty heart otherwise
                        if ($like_result->num_rows > 0) {
                            echo "♥ Like";
                        } else {
                            echo "♡ Like";
                        }
                        $like_check_stmt->close();
                        ?>
                    </button>
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
                                <div class="comment-checkbox">□</div>
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
            <h2 class="profile-header">Profile:</h2>
            <div class="profile-card">
                <img src="images/icon.png" alt="">
                <div class="profile-username">
                    <?php echo isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"]) : "Guest"; ?>
                </div>
                <div class="profile-bio">
                    <?php
                    if (isset($_SESSION["username"])) {
                        $user_bio_sql = "SELECT bio FROM profile WHERE username = ?";
                        $user_bio_stmt = $conn->prepare($user_bio_sql);
                        $user_bio_stmt->bind_param("s", $_SESSION["username"]);
                        $user_bio_stmt->execute();
                        $user_bio_result = $user_bio_stmt->get_result();
                        if ($user_bio_row = $user_bio_result->fetch_assoc()) {
                            echo htmlspecialchars($user_bio_row["bio"]);
                        } else {
                            echo "No bio available.";
                        }
                        $user_bio_stmt->close();
                    } else {
                        echo "Please log in to view your profile.";
                    }
                    ?>
                </div>
                <div class="profile-tags">
                    <div><b>Tags:</b></div>
                    <div>
                        <?php
                        // Fetch user tags if user is logged in
                        if (isset($_SESSION["username"])) {
                            $user_tags_sql = "SELECT t.name FROM tags t 
                                             JOIN user_tags ut ON t.id = ut.tag_id 
                                             JOIN userInfo u ON ut.user_id = u.id 
                                             WHERE u.username = ?";
                            $user_tags_stmt = $conn->prepare($user_tags_sql);
                            $user_tags_stmt->bind_param("s", $_SESSION["username"]);
                            $user_tags_stmt->execute();
                            $user_tags_result = $user_tags_stmt->get_result();

                            while ($tag = $user_tags_result->fetch_assoc()) {
                                $tag_id = htmlspecialchars(strtolower($tag["name"])) . "-tag";
                                echo '<span class="tag" id="' . $tag_id . '">' . htmlspecialchars($tag["name"]) . '</span>';
                            }
                            $user_tags_stmt->close();
                        } else {
                            // Show some default tags for guests
                            echo '<span class="tag" id="sports-tag">Sports</span>';
                            echo '<span class="tag" id="food-tag">Food</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
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