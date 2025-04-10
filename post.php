<?php
// Handle AJAX requests first before any HTML output
if (isset($_POST['action']) && $_POST['action'] === 'addComment') {
    header('Content-Type: application/json');
    
    include "sessions.php";
    require_once 'db_connect.php';
    
    if (!isset($_SESSION['username'])) {
        echo json_encode(['success' => false, 'message' => 'Must be logged in to comment']);
        exit;
    }

    $post_id = $_POST['post_id'];
    $comment_content = $_POST['comment_content'];
    $username = $_SESSION['username'];
    
    if (empty($comment_content)) {
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
        exit;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO comments (author, content, post_id, date) VALUES (?, ?, ?, CURRENT_DATE())");
        $stmt->bind_param("ssi", $username, $comment_content, $post_id);
        $success = $stmt->execute();
        
        if ($success) {
            $response = [
                'success' => true,
                'comment' => [
                    'author' => $username,
                    'content' => $comment_content,
                    'date' => date("m/d/y")
                ]
            ];
        } else {
            $response = ['success' => false, 'message' => 'Error adding comment'];
        }
        $stmt->close();
        
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    $conn->close();
    exit;
}

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
                    <a href="activity.php"><?php echo $_SESSION['notification_count']; ?> new Notifications</a>
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
                    echo '<img src="getProfileImage.php?username='  . $post['author'] . '"alt="Profile Image" id="user-profile-img">';
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
                            $tag_stmt->bind_param("i", $post['id']);
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
                    $comment_stmt->bind_param("i", $post['id']);
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
                </div>

                <!-- Add Comment Form -->
                <form class="comment-form" id="commentForm">
                    <textarea name="comment_content" id="commentContent" placeholder="Write a comment..." required></textarea>
                    <button type="submit" class="post-comment-btn">Post Comment</button>
                </form>
            </div>
        </main>
        <aside class="profile-sidebar">
            <?php include "profilesidebar.php"; ?>
        </aside>
    </div>

    <script>
    document.getElementById('commentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!<?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>) {
            window.location.href = 'login.php';
            return;
        }

        const formData = new FormData();
        formData.append('action', 'addComment');
        formData.append('post_id', '<?php echo $post['id']; ?>');
        formData.append('comment_content', document.getElementById('commentContent').value);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create new comment element
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment';
                commentDiv.innerHTML = `
                    <div class="comment-user">${data.comment.author}:</div>
                    <div class="comment-body">${data.comment.content}</div>
                    <div class="comment-date">${data.comment.date}</div>
                `;
                
                // Add new comment to top of comments section
                const commentsSection = document.querySelector('.comments-section');
                const firstComment = commentsSection.querySelector('.comment');
                if (firstComment) {
                    commentsSection.insertBefore(commentDiv, firstComment);
                } else {
                    commentsSection.appendChild(commentDiv);
                }
                
                // Clear the form
                document.getElementById('commentContent').value = '';
                
                // Remove "no comments" message if it exists
                const noComments = document.querySelector('.no-comments');
                if (noComments) {
                    noComments.remove();
                }
            } else {
                alert(data.message || 'Error adding comment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error adding comment. Please try again.');
        });
    });
    </script>
</body>
</html>

<?php $conn->close(); ?>