<?php
/*
Handles posts:
1. Session checks
2. Get post information
3. Non asynch fallback option if AJAX somehow doesn't function properly
4. Display post content 
5. Retrieve and display comments tied to post
6. Sanitize and validate new comments beofre comitting to DB
7. AJAX functionality with 10s polling
*/
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include the database connection
require_once 'db_connect.php';

// Check if post ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // If no post ID is provided, redirect to index
    header("Location: index.php");
    exit();
}

// Get the post ID from the URL
$post_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

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

// Process comment form submission (non-AJAX fallback)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_comment"])) {
    // Get form data
    if (!isset($_SESSION["loggedIn"]) || $_SESSION["loggedIn"] !== true) {
        $error_message = "You must be logged in to comment.";
    } else {

        $comment_content = htmlspecialchars(trim($_POST["comment_content"]));
        $current_user = $_SESSION["username"];

        // For testing purposes, if no session is available, use a default user
     //   if (empty($current_user)) {
     //       $current_user = "TroyBoy78"; // Use an existing user from the database for testing
      //  }

        // Validate inputs
        if (empty($comment_content)) {
            $error_message = "Comment content cannot be empty.";
        } else {
            // Prepare and execute SQL query to insert comment
            $stmt = $conn->prepare("INSERT INTO comments (author, content, post_id, date) VALUES (?, ?, ?, NOW())");
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
                    <?php echo isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] ? "7 new Notifications" : "Sign in to see notifications"; ?>
                </div>
            </div>
        </aside>

        <main class="feed">
            <!-- Breadcrumb Navigation -->
            <div class="breadcrumb">
                <a href="index.php">Home</a> &gt; 
                <?php
                // Fetch first tag for breadcrumb
                $tag_sql = "SELECT t.name FROM tags t 
                    JOIN post_tags pt ON t.id = pt.tag_id 
                    WHERE pt.post_id = ? LIMIT 1";
                $tag_stmt = $conn->prepare($tag_sql);
                $tag_stmt->bind_param("i", $post_id);
                $tag_stmt->execute();
                $tag_result = $tag_stmt->get_result();
                if ($tag_row = $tag_result->fetch_assoc()) {
                    echo '<a href="search.php?tag=' . urlencode($tag_row['name']) . '">' . htmlspecialchars($tag_row['name']) . '</a> &gt; ';
                }
                $tag_stmt->close();
                ?>
                <span><?php echo htmlspecialchars($post["title"]); ?></span>
            </div>

            <h2 class="feed-header" id="results-return"><a href="index.php">&lt; Return to Feed</a></h2>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <div class="post" id="post-<?php echo $post_id; ?>">
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
                    <?php echo nl2br(htmlspecialchars($post["content"])); ?>
                </div>
                <div class="post-footer">
                    <button class="like-btn" onclick="likePost(this, <?php echo $post_id; ?>)" 
                            data-post-id="<?php echo $post_id; ?>">♡ Like</button>
                </div>

                <!-- Comments Section -->
                <div class="comments-section" id="comments-section">
                    <h3>Comments:</h3>

                    <div id="comments-list">
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
                                <div class="comment" id="comment-<?php echo $comment['id']; ?>">
                                    <div class="comment-user"><?php echo htmlspecialchars($comment["author"]); ?>:</div>
                                    <div class="comment-body"><?php echo htmlspecialchars($comment["content"]); ?></div>
                                    <div class="comment-date"><?php echo date("m/d/y g:ia", strtotime($comment["date"])); ?></div>
                                </div>
                        <?php
                            }
                        } else {
                            echo '<div class="no-comments">No comments yet. Be the first to comment!</div>';
                        }
                        $comment_stmt->close();
                        ?>
                    </div>

                    <!-- Add Comment Form -->
                    <?php if (isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"]): ?>
                        <!-- AJAX Comment Form -->
                        <form id="ajax-comment-form" class="comment-form">
                            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <textarea id="comment-content" name="comment_content" placeholder="Write a comment..." required></textarea>
                            <button type="submit" class="post-comment-btn">Post Comment</button>
                        </form>
                    <?php else: ?>
                        <!-- Non-AJAX form for fallback -->
                        <div class="login-to-comment">
                            <a href="login.php">Log in</a> to post a comment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>

        <aside class="profile-sidebar">
            <?php include "profilesidebar.php"; ?>
        </aside>
    </div>

    <script>
        // Function to add a new comment to the page
        function addCommentToDOM(comment) {
            const commentsList = document.getElementById('comments-list');
            const noCommentsMessage = document.querySelector('.no-comments');
            
            // Remove "no comments" message if it exists
            if (noCommentsMessage) {
                noCommentsMessage.remove();
            }
            
            // Create new comment element
            const commentDiv = document.createElement('div');
            commentDiv.className = 'comment';
            commentDiv.id = 'comment-' + comment.id;
            
            commentDiv.innerHTML = `
                <div class="comment-user">${comment.author}:</div>
                <div class="comment-body">${comment.content}</div>
                <div class="comment-date">${comment.date}</div>
            `;
            
            // Add to the top of the comments list
            commentsList.insertBefore(commentDiv, commentsList.firstChild);
        }
        
        // Function to load comments via AJAX
        function loadComments() {
            const postId = <?php echo $post_id; ?>;
            
            fetch('get_comments.php?post_id=' + postId)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error loading comments:', data.error);
                        return;
                    }
                    
                    // Get existing comment IDs
                    const existingComments = document.querySelectorAll('.comment');
                    const existingIds = Array.from(existingComments).map(comment => {
                        return parseInt(comment.id.replace('comment-', ''));
                    });
                    
                    // Add new comments that aren't already displayed
                    let newCommentsAdded = false;
                    
                    data.comments.forEach(comment => {
                        if (!existingIds.includes(parseInt(comment.id))) {
                            addCommentToDOM(comment);
                            newCommentsAdded = true;
                        }
                    });
                    
                    // If no comments exist yet but we got some from server
                    if (existingComments.length === 0 && data.comments.length > 0) {
                        const noCommentsMessage = document.querySelector('.no-comments');
                        if (noCommentsMessage) {
                            noCommentsMessage.remove();
                        }
                        
                        data.comments.forEach(comment => {
                            addCommentToDOM(comment);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
        
        // Function to handle post likes
        function likePost(button, postId) {
            // Visual feedback immediately
            if (button.textContent.includes('♡')) {
                button.textContent = '♥ Like';
            } else {
                button.textContent = '♡ Like';
            }
            
            // AJAX implementation for likes
            fetch('like_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'post_id=' + postId
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert if unsuccessful
                    if (button.textContent.includes('♥')) {
                        button.textContent = '♡ Like';
                    } else {
                        button.textContent = '♥ Like';
                    }
                    
                    // If there's a message about not being logged in, redirect to login
                    if (data.message && data.message.includes('logged in')) {
                        window.location.href = 'login.php';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        // Event listener for comment form submission
        document.addEventListener('DOMContentLoaded', function() {
            const commentForm = document.getElementById('ajax-comment-form');
            if (commentForm) {
                commentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const postId = this.querySelector('input[name="post_id"]').value;
                    const csrfToken = this.querySelector('input[name="csrf_token"]').value;
                    const content = document.getElementById('comment-content').value;
                    
                    if (!content.trim()) {
                        alert('Comment cannot be empty');
                        return;
                    }
                    
                    // Submit comment via AJAX
                    fetch('post_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `post_id=${postId}&comment_content=${encodeURIComponent(content)}&csrf_token=${csrfToken}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Add the new comment to the page
                            addCommentToDOM(data.comment);
                            
                            // Clear the comment form
                            document.getElementById('comment-content').value = '';
                        } else {
                            alert(data.message || 'Error posting comment');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while posting your comment');
                    });
                });
            }
            
            // Polling for new comments (every 10 seconds)
            setInterval(loadComments, 10000);
        });
    </script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>