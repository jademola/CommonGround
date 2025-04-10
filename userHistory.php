<?php
// Handle AJAX requests first before anything else
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    include "sessions.php";
    require_once 'db_connect.php';
    
    if ($_POST['action'] === 'deleteComment') {
        if (!isset($_SESSION['username'])) {
            echo json_encode(['success' => false, 'message' => 'Must be logged in']);
            exit;
        }

        $comment_id = $_POST['comment_id'];
        $username = $_SESSION['username'];
        
        try {
            // Only allow users to delete their own comments
            $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND author = ?");
            $stmt->bind_param("is", $comment_id, $username);
            $success = $stmt->execute();
            
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Comment deleted' : 'Failed to delete comment'
            ]);
            
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }
    exit;
}

// Regular page load
include "sessions.php";
include "notifications.php";
require_once 'db_connect.php';

// Get user's comment history
$sql = "SELECT c.*, p.title as post_title, p.id as post_id 
        FROM comments c
        JOIN post p ON c.post_id = p.id
        WHERE c.author = ?
        ORDER BY c.date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$comments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment History - Common Ground</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include "header.php"; ?>

    <div class="main-content">
        <aside class="sidebar">
            <div class="sidebar-section">
                <?php include "popularsidebar.php" ?>
                <div class="notification-box">
                    <a href="activity.php"><?php echo $_SESSION['notification_count']; ?> new Notifications</a>
                </div>
            </div>
        </aside>

        <main class="feed">
            <h2 class="feed-header">Your Comment History</h2>

            <?php if ($comments->num_rows > 0): ?>
                <?php while ($comment = $comments->fetch_assoc()): ?>
                    <div class="post" id="comment-<?php echo $comment['id']; ?>">
                        <div class="post-header">
                            <div class="user-info">
                                You commented on: 
                                <a href="post.php?id=<?php echo $comment['post_id']; ?>">
                                    <?php echo htmlspecialchars($comment['post_title']); ?>
                                </a>
                            </div>
                            <div class="timestamp"><?php echo date("g:ia, F jS, Y", strtotime($comment["date"])); ?></div>
                        </div>
                        <div class="comment">
                            <div class="comment-body"><?php echo htmlspecialchars($comment['content']); ?></div>
                            <button class="delete-btn" onclick="deleteComment(<?php echo $comment['id']; ?>)">Delete</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-comments">You haven't made any comments yet.</div>
            <?php endif; ?>
        </main>

        <aside class="profile-sidebar">
            <?php include "profilesidebar.php"; ?>
        </aside>
    </div>

    <script>
    function deleteComment(commentId) {
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }

        const commentElement = document.getElementById('comment-' + commentId);
        if (!commentElement) {
            return; // Exit if element doesn't exist
        }

        const formData = new FormData();
        formData.append('action', 'deleteComment');
        formData.append('comment_id', commentId);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the element first
                commentElement.remove();
                
                // Check for remaining comments after element is removed
                const remainingComments = document.querySelectorAll('.post');
                if (remainingComments.length === 0) {
                    const feed = document.querySelector('.feed');
                    const noCommentsDiv = document.createElement('div');
                    noCommentsDiv.className = 'no-comments';
                    noCommentsDiv.textContent = "You haven't made any comments yet.";
                    feed.appendChild(noCommentsDiv);
                }
            } else {
                // Only show error if deletion failed
                console.error('Failed to delete comment:', data.message);
                alert(data.message || 'Error deleting comment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Only show error if there was a network/server error
            alert('Network error while deleting comment. Please try again.');
        });
    }
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
