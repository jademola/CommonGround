<?php


session_start(); 

// Set CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'db_connect.php';

// Check if filtering by tag
$tag_filter = isset($_GET['tag']) ? filter_input(INPUT_GET, 'tag', FILTER_SANITIZE_NUMBER_INT) : null;

// Pagination setup
$posts_per_page = 5; // Number of posts per page
$current_page = isset($_GET['page']) ? max(1, filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT)) : 1;
$offset = ($current_page - 1) * $posts_per_page;

// Base SQL for posts
$posts_sql_base = "
    SELECT p.id, p.title, p.date, p.content, p.author 
    FROM post p
";

// Modify query if filtering by tag
if ($tag_filter) {
    $posts_sql = $posts_sql_base . "
        JOIN post_tags pt ON p.id = pt.post_id
        WHERE pt.tag_id = ?
        ORDER BY p.date DESC
        LIMIT ? OFFSET ?
    ";
    $posts_stmt = $conn->prepare($posts_sql);
    $posts_stmt->bind_param("iii", $tag_filter, $posts_per_page, $offset);
} else {
    $posts_sql = $posts_sql_base . "
        ORDER BY p.date DESC
        LIMIT ? OFFSET ?
    ";
    $posts_stmt = $conn->prepare($posts_sql);
    $posts_stmt->bind_param("ii", $posts_per_page, $offset);
}

$posts_stmt->execute();
$posts_result = $posts_stmt->get_result();

// Get tag name if filtering
$tag_name = "";
if ($tag_filter) {
    $tag_name_sql = "SELECT name FROM tags WHERE id = ?";
    $tag_name_stmt = $conn->prepare($tag_name_sql);
    $tag_name_stmt->bind_param("i", $tag_filter);
    $tag_name_stmt->execute();
    $tag_result = $tag_name_stmt->get_result();
    if ($tag_row = $tag_result->fetch_assoc()) {
        $tag_name = htmlspecialchars($tag_row['name']);
    }
    $tag_name_stmt->close();
}

// Get total posts for pagination
if ($tag_filter) {
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM post p
        JOIN post_tags pt ON p.id = pt.post_id
        WHERE pt.tag_id = ?
    ";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $tag_filter);
} else {
    $count_sql = "SELECT COUNT(*) as total FROM post";
    $count_stmt = $conn->prepare($count_sql);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_posts = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $posts_per_page);
$count_stmt->close();

// Functions for post data
function getLikeCount($conn, $postId) {
    $sql = "SELECT COUNT(*) as count FROM post_likes WHERE post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    return $count;
}

function getCommentCount($conn, $postId) {
    $sql = "SELECT COUNT(*) as count FROM comments WHERE post_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    $stmt->close();
    return $count;
}

function userHasLiked($conn, $postId, $username) {
    if (!$username) return false;
    
    $sql = "SELECT COUNT(*) as liked FROM post_likes WHERE post_id = ? AND username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $postId, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $hasLiked = $result->fetch_assoc()['liked'] > 0;
    $stmt->close();
    return $hasLiked;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?php echo $tag_name ? "Posts tagged with $tag_name - " : ""; ?>Common Ground - Feed</title>
  <link rel="stylesheet" href="styles.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

<!-- Header / Navigation -->
<?php include "header.php"; ?>

<div class="main-content">

  <!-- Left Sidebar -->
  <aside class="sidebar">
    <?php include "popularsidebar.php"; ?>
    
    <!-- Tag filtering section -->
    <div class="sidebar-section">
      <h3>Popular Tags</h3>
      <div class="tag-cloud">
        <?php
        // Get popular tags
        $tag_cloud_sql = "
          SELECT t.id, t.name, COUNT(pt.post_id) as post_count 
          FROM tags t
          JOIN post_tags pt ON t.id = pt.tag_id
          GROUP BY t.id
          ORDER BY post_count DESC, t.name
          LIMIT 10
        ";
        $tag_cloud_result = $conn->query($tag_cloud_sql);
        
        if ($tag_cloud_result && $tag_cloud_result->num_rows > 0) {
          while ($tag = $tag_cloud_result->fetch_assoc()) {
            echo '<a href="index.php?tag=' . $tag['id'] . '" class="tag';
            // Highlight active tag
            if ($tag_filter && $tag_filter == $tag['id']) {
                echo ' active-tag';
            }
            echo '">';
            echo htmlspecialchars($tag['name']);
            echo ' <span class="tag-count">(' . $tag['post_count'] . ')</span>';
            echo '</a>';
          }
        } else {
          echo '<p>No tags found.</p>';
        }
        ?>
      </div>
    </div>
    
    <?php if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true): ?>
    <div class="sidebar-section">
      <a href="createpost.php" class="btn btn-primary btn-block">Create New Post</a>
    </div>
    <?php endif; ?>
  </aside>

  <!-- Center Feed -->
  <main class="feed">
    <?php if ($tag_filter && $tag_name): ?>
      <h2 class="feed-header">Posts tagged with: <?php echo $tag_name; ?></h2>
      <a href="index.php" class="clear-filter">Show all posts</a>
    <?php else: ?>
      <h2 class="feed-header">Your Feed:</h2>
    <?php endif; ?>
    
    <h3 class="sortby">Sorted by date</h3>

    <!-- Display posts -->
    <?php if ($posts_result && $posts_result->num_rows > 0): ?>
      <?php while ($post = $posts_result->fetch_assoc()): ?>
        <div class="post" id="post-<?php echo $post['id']; ?>">

          <!-- Post Header -->
          <div class="post-header">
            <img src="images/icon.png" alt="Post Icon" id="post-img">
            <div class="user-info">
              <div>
                <a href="viewprofile.php?username=<?php echo urlencode($post["author"]); ?>">
                  <?php echo htmlspecialchars($post["author"]); ?>
                </a>
              </div>
              <div>
                <!-- Fetch & display tags for this post -->
                <?php
                $tag_sql = "
                  SELECT t.id, t.name
                  FROM tags t
                  JOIN post_tags pt ON t.id = pt.tag_id
                  WHERE pt.post_id = ?
                ";
                $tag_stmt = $conn->prepare($tag_sql);
                $tag_stmt->bind_param("i", $post["id"]);
                $tag_stmt->execute();
                $tag_result = $tag_stmt->get_result();

                while ($tag = $tag_result->fetch_assoc()) {
                  $tag_lower = strtolower($tag["name"]);
                  echo '<a href="index.php?tag=' . $tag['id'] . '" class="tag" id="' 
                       . htmlspecialchars($tag_lower) . '-tag">'
                       . htmlspecialchars($tag["name"])
                       . '</a>';
                }
                $tag_stmt->close();
                ?>
              </div>
            </div>
            <div class="timestamp">
              <?php echo date("g:ia, F jS, Y", strtotime($post["date"])); ?>
            </div>
          </div> <!-- end post-header -->

          <!-- Post Title -->
          <div class="post-title">
            <a id="titleLink" href="post.php?id=<?php echo $post['id']; ?>">
              <?php echo htmlspecialchars($post["title"]); ?>
            </a>
          </div>

          <!-- Post Content -->
          <div class="post-content">
            <?php
            $content = $post["content"];
            $excerpt = mb_substr($content, 0, 200);
            if (mb_strlen($content) > 200) {
                $excerpt .= "...";
            }
            echo nl2br(htmlspecialchars($excerpt));
            ?>
            <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more">Read More</a>
          </div>

          <!-- Post Footer (Like, etc.) -->
          <div class="post-footer">
            <?php 
            $like_count = getLikeCount($conn, $post['id']);
            $has_liked = isset($_SESSION['username']) ? userHasLiked($conn, $post['id'], $_SESSION['username']) : false;
            ?>
            <button class="like-btn <?php echo $has_liked ? 'liked' : ''; ?>"
                    onclick="likePost(this, <?php echo $post['id']; ?>)"
                    data-post-id="<?php echo $post['id']; ?>"
                    data-csrf="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
              <?php echo $has_liked ? '♥' : '♡'; ?> Like
              <span class="like-count">(<?php echo $like_count; ?>)</span>
            </button>
            
            <?php $comment_count = getCommentCount($conn, $post['id']); ?>
            <span class="comment-count">
              <?php echo $comment_count; ?> <?php echo $comment_count == 1 ? 'comment' : 'comments'; ?>
            </span>
          </div>

          <!-- Comments Section -->
          <div class="comments-section">
            <div><strong>Comments:</strong></div>
            <div id="comments-list-<?php echo $post['id']; ?>">
              <?php
              // Fetch comments for this post (limit to 3 most recent for feed view)
              $comment_sql = "
                SELECT c.id, c.author, c.content, c.date
                FROM comments c
                WHERE c.post_id = ?
                ORDER BY c.date DESC
                LIMIT 3
              ";
              $comment_stmt = $conn->prepare($comment_sql);
              $comment_stmt->bind_param("i", $post["id"]);
              $comment_stmt->execute();
              $comment_result = $comment_stmt->get_result();

              if ($comment_result->num_rows > 0) {
                while ($comment = $comment_result->fetch_assoc()) {
                  ?>
                  <div class="comment" id="comment-<?php echo $comment['id']; ?>">
                    <div class="comment-user">
                      <a href="viewprofile.php?username=<?php echo urlencode($comment["author"]); ?>">
                        <?php echo htmlspecialchars($comment["author"]); ?>
                      </a>:
                    </div>
                    <div class="comment-body">
                      <?php echo nl2br(htmlspecialchars($comment["content"])); ?>
                    </div>
                    <div class="comment-date">
                      <?php echo date("m/d/y g:ia", strtotime($comment["date"])); ?>
                    </div>
                  </div>
                  <?php
                }
                
                // Show "View all comments" link if there are more comments
                if ($comment_count > 3) {
                  echo '<div class="view-all-comments">';
                  echo '<a href="post.php?id=' . $post['id'] . '#comments">View all ' . $comment_count . ' comments</a>';
                  echo '</div>';
                }
              } else {
                echo '<div class="no-comments">No comments yet. Be the first to comment!</div>';
              }
              $comment_stmt->close();
              ?>
            </div> <!-- end #comments-list -->

            <!-- Add Comment Form (AJAX) -->
            <form class="ajax-comment-form" data-post-id="<?php echo $post['id']; ?>">
              <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
              <?php if (!empty($_SESSION['csrf_token'])): ?>
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <?php endif; ?>
              <textarea name="comment_content" placeholder="<?php echo isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] ? 'Write a comment...' : 'Please log in to comment'; ?>" required <?php echo isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] ? '' : 'disabled'; ?>></textarea>
              <button type="submit" class="post-comment-btn" <?php echo isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] ? '' : 'disabled'; ?>>Post Comment</button>
            </form>
            
            <?php if (!isset($_SESSION['loggedIn']) || !$_SESSION['loggedIn']): ?>
            <div class="login-to-comment">
              <a href="login.php">Log in</a> to post a comment.
            </div>
            <?php endif; ?>
          </div> <!-- end comments-section -->
        </div> <!-- end post -->
      <?php endwhile; ?>
      
      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <?php if ($current_page > 1): ?>
          <a href="?page=<?php echo $current_page - 1; ?><?php echo $tag_filter ? '&tag=' . $tag_filter : ''; ?>" class="page-link">&laquo; Previous</a>
        <?php endif; ?>
        
        <?php 
        // Show limited page numbers with ellipsis
        $start_page = max(1, min($current_page - 2, $total_pages - 4));
        $end_page = min($total_pages, max($current_page + 2, 5));
        
        if ($start_page > 1): ?>
          <a href="?page=1<?php echo $tag_filter ? '&tag=' . $tag_filter : ''; ?>" class="page-link">1</a>
          <?php if ($start_page > 2): ?>
            <span class="page-ellipsis">...</span>
          <?php endif; ?>
        <?php endif; ?>
        
        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
          <?php if ($i == $current_page): ?>
            <span class="page-link current"><?php echo $i; ?></span>
          <?php else: ?>
            <a href="?page=<?php echo $i; ?><?php echo $tag_filter ? '&tag=' . $tag_filter : ''; ?>" class="page-link"><?php echo $i; ?></a>
          <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($end_page < $total_pages): ?>
          <?php if ($end_page < $total_pages - 1): ?>
            <span class="page-ellipsis">...</span>
          <?php endif; ?>
          <a href="?page=<?php echo $total_pages; ?><?php echo $tag_filter ? '&tag=' . $tag_filter : ''; ?>" class="page-link"><?php echo $total_pages; ?></a>
        <?php endif; ?>
        
        <?php if ($current_page < $total_pages): ?>
          <a href="?page=<?php echo $current_page + 1; ?><?php echo $tag_filter ? '&tag=' . $tag_filter : ''; ?>" class="page-link">Next &raquo;</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      
    <?php else: ?>
      <div class="no-posts">
        <?php if (isset($tag_filter)): ?>
          No posts found with this tag. <a href="index.php">View all posts</a>
        <?php else: ?>
          No posts found.
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </main>

  <!-- Right Sidebar -->
  <aside class="profile-sidebar">
    <?php include "profilesidebar.php"; ?>
  </aside>

</div> <!-- end .main-content -->


<script>
function likePost(button, postId) {
  // Get CSRF token
  const csrfToken = button.dataset.csrf;
  
  const isLiked = button.textContent.includes('♥');
  const likeCountElement = button.querySelector('.like-count');
  let likeCount = parseInt(likeCountElement.textContent.match(/\d+/)[0]);
  
  if (isLiked) {
    button.innerHTML = '♡ Like <span class="like-count">(' + (likeCount - 1) + ')</span>';
    button.classList.remove('liked');
  } else {
    button.innerHTML = '♥ Like <span class="like-count">(' + (likeCount + 1) + ')</span>';
    button.classList.add('liked');
  }

  // Send AJAX request
  fetch('like_post.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'post_id=' + encodeURIComponent(postId) + 
          '&csrf_token=' + encodeURIComponent(csrfToken)
  })
  .then(response => response.json())
  .then(data => {
    if (!data.success) {
      // revert if unsuccessful
      if (isLiked) {
        button.innerHTML = '♥ Like <span class="like-count">(' + likeCount + ')</span>';
        button.classList.add('liked');
      } else {
        button.innerHTML = '♡ Like <span class="like-count">(' + likeCount + ')</span>';
        button.classList.remove('liked');
      }
      
      // if message says "must be logged in", redirect
      if (data.message && data.message.includes('logged in')) {
        window.location.href = 'login.php';
      } else if (data.message) {
        alert(data.message);
      }
    }
  })
  .catch(error => {
    console.error('Like error:', error);
    // revert if error
    if (isLiked) {
      button.innerHTML = '♥ Like <span class="like-count">(' + likeCount + ')</span>';
      button.classList.add('liked');
    } else {
      button.innerHTML = '♡ Like <span class="like-count">(' + likeCount + ')</span>';
      button.classList.remove('liked');
    }
  });
}

// 2) Add new comment to DOM
function addCommentToDOM(postId, comment) {
  const commentsList = document.getElementById('comments-list-' + postId);
  if (!commentsList) return;

  // Remove "No comments yet" if it's there
  const noComments = commentsList.querySelector('.no-comments');
  if (noComments) {
    noComments.remove();
  }

  // Create the new comment div
  const commentDiv = document.createElement('div');
  commentDiv.className = 'comment';
  commentDiv.id = 'comment-' + comment.id;
  
  // Escape HTML to prevent XSS
  const escapeHTML = (str) => {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
  };
  
  // Format the date
  const date = new Date(comment.date);
  const formattedDate = date.toLocaleDateString('en-US', {
    month: '2-digit',
    day: '2-digit',
    year: '2-digit'
  }) + ' ' + date.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
    hour12: true
  });
  
  commentDiv.innerHTML = `
    <div class="comment-user">
      <a href="viewprofile.php?username=${escapeHTML(comment.author)}">
        ${escapeHTML(comment.author)}
      </a>:
    </div>
    <div class="comment-body">${escapeHTML(comment.content).replace(/\n/g, '<br>')}</div>
    <div class="comment-date">${formattedDate}</div>
  `;
  
  // Insert at top
  commentsList.insertBefore(commentDiv, commentsList.firstChild);
  
  // Update comment count
  const countElem = document.querySelector(`.post[id="post-${postId}"] .comment-count`);
  if (countElem) {
    const currentCount = parseInt(countElem.textContent);
    const newCount = currentCount + 1;
    countElem.textContent = `${newCount} ${newCount === 1 ? 'comment' : 'comments'}`;
  }
  
  // Add "View all comments" link if this is the 4th comment
  const comments = commentsList.querySelectorAll('.comment');
  if (comments.length === 4) {
    const existingViewAll = commentsList.querySelector('.view-all-comments');
    if (!existingViewAll) {
      const viewAllDiv = document.createElement('div');
      viewAllDiv.className = 'view-all-comments';
      viewAllDiv.innerHTML = `<a href="post.php?id=${postId}#comments">View all ${comments.length} comments</a>`;
      commentsList.appendChild(viewAllDiv);
    }
  } else if (comments.length > 4) {
    // Update the count in existing "View all" link
    const viewAllLink = commentsList.querySelector('.view-all-comments a');
    if (viewAllLink) {
      viewAllLink.textContent = `View all ${comments.length} comments`;
    }
  }
}

// 3) Handle comment form submissions via AJAX to post_comment.php
document.addEventListener('DOMContentLoaded', () => {
  const commentForms = document.querySelectorAll('.ajax-comment-form');

  commentForms.forEach(form => {
    form.addEventListener('submit', e => {
      e.preventDefault();
      
      // Check if logged in
      const textarea = form.querySelector('textarea[name="comment_content"]');
      if (textarea.disabled) {
        window.location.href = 'login.php';
        return;
      }

      // Gather data
      const formData = new FormData(form);
      const postId = form.dataset.postId;

      // Send to post_comment.php
      fetch('post_comment.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Insert comment at top
          addCommentToDOM(postId, data.comment);
          // Clear the textarea
          form.querySelector('textarea[name="comment_content"]').value = '';
        } else {
          alert(data.message || 'Error adding comment.');
          // If says "You must be logged in," redirect to login
          if (data.message && data.message.includes('logged in')) {
            window.location.href = 'login.php';
          }
        }
      })
      .catch(error => {
        console.error('Comment error:', error);
        alert('An error occurred while posting your comment.');
      });
    });
  });
});
</script>

</body>
</html>

<?php
$conn->close();
?>