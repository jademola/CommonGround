<?php

// Start session
session_start();

// Set CSRF token if not already set
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include database connection
require_once 'db_connect.php';

// Define the default admin username (change as needed)
$default_admin = 'admin';

// Check if the default admin exists in the adminUsers table
$check_admin_sql = "SELECT username FROM adminUsers WHERE username = ?";
$check_admin_stmt = $conn->prepare($check_admin_sql);
$check_admin_stmt->bind_param("s", $default_admin);
$check_admin_stmt->execute();
$admin_result = $check_admin_stmt->get_result();

if ($admin_result->num_rows === 0) {
    // Default admin is not present; fetch the user info from userInfo table
    $check_user_sql = "SELECT username, password FROM userInfo WHERE username = ?";
    $check_user_stmt = $conn->prepare($check_user_sql);
    $check_user_stmt->bind_param("s", $default_admin);
    $check_user_stmt->execute();
    $user_result = $check_user_stmt->get_result();
    
    if ($user = $user_result->fetch_assoc()) {
        // Insert the default admin into adminUsers
        $add_admin_sql = "INSERT INTO adminUsers (username, password) VALUES (?, ?)";
        $add_admin_stmt = $conn->prepare($add_admin_sql);
        $add_admin_stmt->bind_param("ss", $user['username'], $user['password']);
        $add_admin_stmt->execute();
    }
}

// Check if the user is logged in and has admin privileges
$is_admin = false;
if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
    $username = $_SESSION['username'];
    $admin_check_sql = "SELECT * FROM adminUsers WHERE username = ?";
    $admin_check_stmt = $conn->prepare($admin_check_sql);
    $admin_check_stmt->bind_param("s", $username);
    $admin_check_stmt->execute();
    $is_admin = $admin_check_stmt->get_result()->num_rows > 0;
}


// Handle admin login attempt
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['adminLogin'])) {
    $username = htmlspecialchars(trim($_POST['adminUsername']));
    $password = $_POST['adminPassword'];
    
    $admin_sql = "SELECT a.username, u.password 
                FROM adminUsers a 
                JOIN userInfo u ON a.username = u.username 
                WHERE a.username = ?";
    $admin_stmt = $conn->prepare($admin_sql);
    $admin_stmt->bind_param("s", $username);
    $admin_stmt->execute();
    $admin_result = $admin_stmt->get_result();
    
    if ($admin_row = $admin_result->fetch_assoc()) {
        if (password_verify($password, $admin_row['password'])) {
            $_SESSION['loggedIn'] = true;
            $_SESSION['username'] = $username;
            $is_admin = true;
            
            // Redirect to refresh page and avoid form resubmission
            header("Location: admin.php");
            exit();
        } else {
            $loginError = "Invalid username or password";
        }
    } else {
        $loginError = "Invalid username or password";
    }
}

// Handle actions if admin is logged in
$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $is_admin) {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = "Form submission error. Please try again.";
    } else {
        // Handle delete user
        if (isset($_POST['deleteUser'])) {
            $username = htmlspecialchars(trim($_POST['username']));
            
            try {
                // Begin transaction
                $conn->begin_transaction();
                
                // Delete user's comments
                $delete_comments_sql = "DELETE FROM comments WHERE author = ?";
                $delete_comments_stmt = $conn->prepare($delete_comments_sql);
                $delete_comments_stmt->bind_param("s", $username);
                $delete_comments_stmt->execute();
                
                // Delete user's likes
                $delete_likes_sql = "DELETE FROM post_likes WHERE username = ?";
                $delete_likes_stmt = $conn->prepare($delete_likes_sql);
                $delete_likes_stmt->bind_param("s", $username);
                $delete_likes_stmt->execute();
                
                // Delete user's profile tags
                $delete_profile_tags_sql = "DELETE FROM profile_tags WHERE username = ?";
                $delete_profile_tags_stmt = $conn->prepare($delete_profile_tags_sql);
                $delete_profile_tags_stmt->bind_param("s", $username);
                $delete_profile_tags_stmt->execute();
                
                // Get user's posts
                $get_posts_sql = "SELECT id FROM post WHERE author = ?";
                $get_posts_stmt = $conn->prepare($get_posts_sql);
                $get_posts_stmt->bind_param("s", $username);
                $get_posts_stmt->execute();
                $posts_result = $get_posts_stmt->get_result();
                
                // For each post, delete associated records
                while ($post = $posts_result->fetch_assoc()) {
                    $post_id = $post['id'];
                    
                    // Delete post tags
                    $delete_post_tags_sql = "DELETE FROM post_tags WHERE post_id = ?";
                    $delete_post_tags_stmt = $conn->prepare($delete_post_tags_sql);
                    $delete_post_tags_stmt->bind_param("i", $post_id);
                    $delete_post_tags_stmt->execute();
                    
                    // Delete post comments
                    $delete_post_comments_sql = "DELETE FROM comments WHERE post_id = ?";
                    $delete_post_comments_stmt = $conn->prepare($delete_post_comments_sql);
                    $delete_post_comments_stmt->bind_param("i", $post_id);
                    $delete_post_comments_stmt->execute();
                    
                    // Delete post likes
                    $delete_post_likes_sql = "DELETE FROM post_likes WHERE post_id = ?";
                    $delete_post_likes_stmt = $conn->prepare($delete_post_likes_sql);
                    $delete_post_likes_stmt->bind_param("i", $post_id);
                    $delete_post_likes_stmt->execute();
                }
                
                // Delete user's posts
                $delete_posts_sql = "DELETE FROM post WHERE author = ?";
                $delete_posts_stmt = $conn->prepare($delete_posts_sql);
                $delete_posts_stmt->bind_param("s", $username);
                $delete_posts_stmt->execute();
                
                // Delete user profile
                $delete_profile_sql = "DELETE FROM profile WHERE username = ?";
                $delete_profile_stmt = $conn->prepare($delete_profile_sql);
                $delete_profile_stmt->bind_param("s", $username);
                $delete_profile_stmt->execute();
                
                // Delete user from admin if applicable
                $delete_admin_sql = "DELETE FROM adminUsers WHERE username = ?";
                $delete_admin_stmt = $conn->prepare($delete_admin_sql);
                $delete_admin_stmt->bind_param("s", $username);
                $delete_admin_stmt->execute();
                
                // Delete user
                $delete_user_sql = "DELETE FROM userInfo WHERE username = ?";
                $delete_user_stmt = $conn->prepare($delete_user_sql);
                $delete_user_stmt->bind_param("s", $username);
                $delete_user_stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $successMessage = "User '{$username}' deleted successfully";
            } catch (Exception $e) {
                // Rollback in case of error
                $conn->rollback();
                $errorMessage = "Error deleting user: " . $e->getMessage();
            }
        }
        
        // Handle delete post
        else if (isset($_POST['deletePost'])) {
            $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
            
            try {
                // Begin transaction
                $conn->begin_transaction();
                
                // Delete post tags
                $delete_post_tags_sql = "DELETE FROM post_tags WHERE post_id = ?";
                $delete_post_tags_stmt = $conn->prepare($delete_post_tags_sql);
                $delete_post_tags_stmt->bind_param("i", $post_id);
                $delete_post_tags_stmt->execute();
                
                // Delete post comments
                $delete_post_comments_sql = "DELETE FROM comments WHERE post_id = ?";
                $delete_post_comments_stmt = $conn->prepare($delete_post_comments_sql);
                $delete_post_comments_stmt->bind_param("i", $post_id);
                $delete_post_comments_stmt->execute();
                
                // Delete post likes
                $delete_post_likes_sql = "DELETE FROM post_likes WHERE post_id = ?";
                $delete_post_likes_stmt = $conn->prepare($delete_post_likes_sql);
                $delete_post_likes_stmt->bind_param("i", $post_id);
                $delete_post_likes_stmt->execute();
                
                // Delete post
                $delete_post_sql = "DELETE FROM post WHERE id = ?";
                $delete_post_stmt = $conn->prepare($delete_post_sql);
                $delete_post_stmt->bind_param("i", $post_id);
                $delete_post_stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $successMessage = "Post deleted successfully";
            } catch (Exception $e) {
                // Rollback in case of error
                $conn->rollback();
                $errorMessage = "Error deleting post: " . $e->getMessage();
            }
        }
        
        // Handle delete comment
        else if (isset($_POST['deleteComment'])) {
            $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_SANITIZE_NUMBER_INT);
            
            try {
                // Delete comment
                $delete_comment_sql = "DELETE FROM comments WHERE id = ?";
                $delete_comment_stmt = $conn->prepare($delete_comment_sql);
                $delete_comment_stmt->bind_param("i", $comment_id);
                $delete_comment_stmt->execute();
                
                $successMessage = "Comment deleted successfully";
            } catch (Exception $e) {
                $errorMessage = "Error deleting comment: " . $e->getMessage();
            }
        }
        
        // Handle make admin
        else if (isset($_POST['makeAdmin'])) {
            $username = htmlspecialchars(trim($_POST['username']));
            
            try {
                // First check if user exists in userInfo
                $check_user_sql = "SELECT * FROM userInfo WHERE username = ?";
                $check_user_stmt = $conn->prepare($check_user_sql);
                $check_user_stmt->bind_param("s", $username);
                $check_user_stmt->execute();
                $user_result = $check_user_stmt->get_result();
                
                if ($user = $user_result->fetch_assoc()) {
                    // Check if already admin
                    $check_admin_sql = "SELECT * FROM adminUsers WHERE username = ?";
                    $check_admin_stmt = $conn->prepare($check_admin_sql);
                    $check_admin_stmt->bind_param("s", $username);
                    $check_admin_stmt->execute();
                    
                    if ($check_admin_stmt->get_result()->num_rows > 0) {
                        $errorMessage = "User '{$username}' is already an admin";
                    } else {
                        // Insert into admin table
                        $insert_admin_sql = "INSERT INTO adminUsers (username, password) VALUES (?, ?)";
                        $insert_admin_stmt = $conn->prepare($insert_admin_sql);
                        $insert_admin_stmt->bind_param("ss", $username, $user['password']);
                        $insert_admin_stmt->execute();
                        
                        $successMessage = "User '{$username}' is now an admin";
                    }
                } else {
                    $errorMessage = "User '{$username}' not found";
                }
            } catch (Exception $e) {
                $errorMessage = "Error making user admin: " . $e->getMessage();
            }
        }
    }
}

// Get users data (for admin view)
if ($is_admin) {
    // Get list of users
    $users_sql = "SELECT username, email FROM userInfo ORDER BY username";
    $users_result = $conn->query($users_sql);

    // Get list of posts
    $posts_sql = "SELECT p.id, p.title, p.author, DATE_FORMAT(p.date, '%Y-%m-%d') as post_date,
                 (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
                 FROM post p
                 ORDER BY p.date DESC LIMIT 20";
    $posts_result = $conn->query($posts_sql);

    // Get list of recent comments
    $comments_sql = "SELECT c.id, c.author, LEFT(c.content, 50) as content_preview, 
                    p.id as post_id, p.title as post_title
                    FROM comments c
                    JOIN post p ON c.post_id = p.id
                    ORDER BY c.date DESC LIMIT 20";
    $comments_result = $conn->query($comments_sql);

    // Get admin users
    $admin_users_sql = "SELECT a.username, u.email 
                       FROM adminUsers a
                       JOIN userInfo u ON a.username = u.username
                       ORDER BY a.username";
    $admin_users_result = $conn->query($admin_users_sql);

    // Get stats
    $total_users = $conn->query("SELECT COUNT(*) as count FROM userInfo")->fetch_assoc()['count'];
    $total_posts = $conn->query("SELECT COUNT(*) as count FROM post")->fetch_assoc()['count'];
    $total_comments = $conn->query("SELECT COUNT(*) as count FROM comments")->fetch_assoc()['count'];
    $total_likes = $conn->query("SELECT COUNT(*) as count FROM post_likes")->fetch_assoc()['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="admin-container">
        <?php if (!$is_admin): ?>
            <!-- Admin Login Form -->
            <div class="admin-login card">
                <h2>Admin Login</h2>
                
                <?php if (isset($loginError)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($loginError); ?></div>
                <?php endif; ?>
                
                <form method="post" action="admin.php" class="auth-form">
                    <div class="form-group">
                        <label for="adminUsername">Username:</label>
                        <input 
                            type="text" 
                            id="adminUsername" 
                            name="adminUsername" 
                            class="form-control"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="adminPassword">Password:</label>
                        <input 
                            type="password" 
                            id="adminPassword" 
                            name="adminPassword" 
                            class="form-control"
                            required
                        >
                    </div>
                    
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button type="submit" name="adminLogin" id="loginButton">Login</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Admin Dashboard -->
            <div class="feed-header">
                <h1>Admin Dashboard</h1>
                <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
            </div>
            
            <?php if (!empty($successMessage)): ?>
                <div class="success-message"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
            
            <div class="nav">
                <a href="#" class="nav-item tab-button active" onclick="showTab('overview'); return false;">Overview</a>
                <a href="#" class="nav-item tab-button" onclick="showTab('users'); return false;">Users</a>
                <a href="#" class="nav-item tab-button" onclick="showTab('posts'); return false;">Posts</a>
                <a href="#" class="nav-item tab-button" onclick="showTab('comments'); return false;">Comments</a>
                <a href="#" class="nav-item tab-button" onclick="showTab('settings'); return false;">Settings</a>
            </div>
            
            <!-- Overview Tab -->
            <div id="overview-tab" class="tab-content active">
                <div class="stats-container">
                    <div class="stat-box card">
                        <div>Total Users</div>
                        <div class="stat-value"><?php echo $total_users; ?></div>
                    </div>
                    <div class="stat-box card">
                        <div>Total Posts</div>
                        <div class="stat-value"><?php echo $total_posts; ?></div>
                    </div>
                    <div class="stat-box card">
                        <div>Total Comments</div>
                        <div class="stat-value"><?php echo $total_comments; ?></div>
                    </div>
                    <div class="stat-box card">
                        <div>Total Likes</div>
                        <div class="stat-value"><?php echo $total_likes; ?></div>
                    </div>
                </div>
                
                <div class="admin-section card">
                    <h2>Admin Dashboard</h2>
                    <p>This is your admin dashboard. Use the tabs above to manage users, posts, and comments.</p>
                    <ul>
                        <li>The <strong>Users</strong> tab allows you to manage user accounts, delete users, or grant admin privileges.</li>
                        <li>The <strong>Posts</strong> tab shows all posts and allows you to delete them if needed.</li>
                        <li>The <strong>Comments</strong> tab shows recent comments and allows you to moderate them.</li>
                        <li>The <strong>Settings</strong> tab shows current admins and allows you to add new ones.</li>
                    </ul>
                </div>
            </div>
            
            <!-- Users Tab -->
            <div id="users-tab" class="tab-content">
                <div class="admin-section card">
                    <h2>User Management</h2>
                    <input type="text" id="userSearch" class="admin-search" placeholder="Search users..." onkeyup="searchTable('userSearch', 'userTable')">
                    
                    <table class="admin-table" id="userTable">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <div class="admin-actions">
                                            <a href="viewprofile.php?username=<?php echo urlencode($user['username']); ?>" class="admin-btn admin-btn-view">View</a>
                                            
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" name="makeAdmin" class="admin-btn admin-btn-admin" onclick="return confirm('Make this user an admin?');">Make Admin</button>
                                            </form>
                                            
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" name="deleteUser" class="admin-btn admin-btn-delete" onclick="return confirm('Are you sure you want to delete this user? This cannot be undone.');">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Posts Tab -->
            <div id="posts-tab" class="tab-content">
                <div class="admin-section card">
                    <h2>Post Management</h2>
                    <input type="text" id="postSearch" class="admin-search" placeholder="Search posts..." onkeyup="searchTable('postSearch', 'postTable')">
                    
                    <table class="admin-table" id="postTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Comments</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($post = $posts_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $post['id']; ?></td>
                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td><?php echo htmlspecialchars($post['author']); ?></td>
                                    <td><?php echo htmlspecialchars($post['post_date']); ?></td>
                                    <td><?php echo $post['comment_count']; ?></td>
                                    <td>
                                        <div class="admin-actions">
                                            <a href="post.php?id=<?php echo $post['id']; ?>" class="admin-btn admin-btn-view">View</a>
                                            
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" name="deletePost" class="admin-btn admin-btn-delete" onclick="return confirm('Are you sure you want to delete this post? This cannot be undone.');">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Comments Tab -->
            <div id="comments-tab" class="tab-content">
                <div class="admin-section card">
                    <h2>Comment Management</h2>
                    <input type="text" id="commentSearch" class="admin-search" placeholder="Search comments..." onkeyup="searchTable('commentSearch', 'commentTable')">
                    
                    <table class="admin-table" id="commentTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Author</th>
                                <th>Content</th>
                                <th>Post</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($comment = $comments_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $comment['id']; ?></td>
                                    <td><?php echo htmlspecialchars($comment['author']); ?></td>
                                    <td><?php echo htmlspecialchars($comment['content_preview']); ?>...</td>
                                    <td>
                                        <a href="post.php?id=<?php echo $comment['post_id']; ?>">
                                            <?php echo htmlspecialchars($comment['post_title']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <button type="submit" name="deleteComment" class="admin-btn admin-btn-delete" onclick="return confirm('Are you sure you want to delete this comment?');">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Settings Tab -->
            <div id="settings-tab" class="tab-content">
                <div class="admin-section card">
                    <h2>Admin Users</h2>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($admin = $admin_users_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td><?php echo htmlspecialchars($admin['email']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="admin-section card">
                    <h2>Make User an Admin</h2>
                    <form method="post" class="auth-form">
                        <div class="form-group">
                            <label for="newAdminUser">Username:</label>
                            <input type="text" id="newAdminUser" name="username" class="form-control" required>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit" name="makeAdmin" id="submitBlog">Make Admin</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Show the selected tab and hide others
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show the selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Update active button
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(button => {
                button.classList.remove('active');
            });
            
            // Find the clicked button and make it active
            event.target.classList.add('active');
        }
        
        // Search table functionality
        function searchTable(inputId, tableId) {
            const input = document.getElementById(inputId);
            const filter = input.value.toLowerCase();
            const table = document.getElementById(tableId);
            const rows = table.getElementsByTagName('tr');
            
            // Loop through all table rows, starting from index 1 to skip the header
            for (let i = 1; i < rows.length; i++) {
                let found = false;
                const cells = rows[i].getElementsByTagName('td');
                
                // Check each cell in the row
                for (let j = 0; j < cells.length; j++) {
                    const cellText = cells[j].textContent || cells[j].innerText;
                    
                    if (cellText.toLowerCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                
                // Show/hide based on search result
                rows[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>

<?php
// Close database connection
$conn->close();
?>