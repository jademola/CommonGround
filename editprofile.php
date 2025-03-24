<?php 
/*
1. Session checks
2. Validate and update email, bio, and tags
3. Displays editable profile form with pre filled data
*/

// Need to start session at beginning
session_start();

// Session security code for later
//ini_set('session.cookie_httponly', 1);
//ini_set('session.cookie_secure', 1); // HTTPS
//ini_set('session.use_only_cookies', 1);
//session_regenerate_id(true); // Regenerate session ID when logging in

// CSRF protection
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Redirect if not logged in
if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header("Location: login.php");
    exit();
}

include "db_connect.php"; 
include "queryFunctions.php";

$errorMessage = null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF protection
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = "Form submission error. Please try again.";
    } else {
        // Inputs need to be sanitized
        $email = filter_var($_POST['userEmail'], FILTER_SANITIZE_EMAIL);
        $bio = htmlspecialchars(trim($_POST['profileBio']));
        $tagsInput = htmlspecialchars(trim($_POST['profileTags']));
        
        // Server side validation
        if (empty($email) || empty($bio)) {
            $errorMessage = "Email and bio fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = "Please enter a valid email address.";
        } else {
            try {
                // Begin transaction
                $conn->begin_transaction();
                
                // Update Bio 
                $bio_sql = "UPDATE profile SET bio = ? WHERE username = ?";
                $bio_stmt = $conn->prepare($bio_sql);
                $bio_stmt->bind_param("ss", $bio, $_SESSION['username']);
                $bio_stmt->execute();
                
                // Update Email 
                $email_sql = "UPDATE userInfo SET email = ? WHERE username = ?";
                $email_stmt = $conn->prepare($email_sql);
                $email_stmt->bind_param("ss", $email, $_SESSION['username']);
                $email_stmt->execute();
                
                // Tag handling
                if (!empty($tagsInput)) {

                    // Process tags (split by comma, trim whitespace)
                    $tagArray = array_map('trim', explode(',', $tagsInput));
                    $tagArray = array_filter($tagArray); // Remove empty elements
                    
                    // First delete existing tags for this user
                    $delete_tags_sql = "DELETE FROM profile_tags WHERE username = ?";
                    $delete_tags_stmt = $conn->prepare($delete_tags_sql);
                    $delete_tags_stmt->bind_param("s", $_SESSION['username']);
                    $delete_tags_stmt->execute();
                    
                    // Then insert new tags
                    if (!empty($tagArray)) {
                        foreach ($tagArray as $tag) {

                            // Check if tag exists in tags table
                            $check_tag_sql = "SELECT id FROM tags WHERE name = ?";
                            $check_tag_stmt = $conn->prepare($check_tag_sql);
                            $check_tag_stmt->bind_param("s", $tag);
                            $check_tag_stmt->execute();
                            $check_tag_result = $check_tag_stmt->get_result();
                            
                            if ($check_tag_row = $check_tag_result->fetch_assoc()) {

                                $tag_id = $check_tag_row['id'];
                            } else {
                                // Insert new tag

                                $insert_tag_sql = "INSERT INTO tags (name) VALUES (?)";
                                $insert_tag_stmt = $conn->prepare($insert_tag_sql);
                                $insert_tag_stmt->bind_param("s", $tag);
                                $insert_tag_stmt->execute();
                                $tag_id = $conn->insert_id;
                            }
                            
                            // Link tag to user
                            $link_tag_sql = "INSERT INTO profile_tags (username, id) VALUES (?, ?)";
                            $link_tag_stmt = $conn->prepare($link_tag_sql);
                            $link_tag_stmt->bind_param("si", $_SESSION['username'], $tag_id);
                            $link_tag_stmt->execute();
                        }
                    }
                }
                
                // Commit transaction
                $conn->commit();
                
                // Redirect to profile page
                header("Location: profile.php");
                exit();
            } catch (Exception $e) {
                // Roll back transaction on error
                $conn->rollback();
                
                // Log error
                error_log("Database error: " . $e->getMessage());
                $errorMessage = "An error occurred while updating profile";
            }
        }
    }
}

// Get current user data
$email = getUserEmailByUsername($_SESSION['username'], $conn);
$bio = getUserBioByUsername($_SESSION['username'], $conn);
$tags = getUserTagsByUsername($_SESSION['username'], $conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Common Ground - Edit Profile</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <?php include "header.php" ?>

  <!-- Main Content -->
  <div class="main-content">
    <aside class="sidebar">
      <h2 class="sidebar-header">Update Profile</h2>
      <ul class="popular-list">
        <li>Profile Settings</li>
        <li>Privacy Settings</li>
        <li>Account Security</li>
      </ul>
      <div class="notification-box">
        Keep your profile up to date!
      </div>
    </aside>

    <!-- Edit Profile Section -->
    <main class="feed">
      <h2 class="feed-header">Update Profile Information</h2>
      
      <!-- Server-Side Validation Error Message -->
      <?php if (isset($errorMessage)): ?>
        <div class="error-message">
          <?php echo htmlspecialchars($errorMessage); ?>
        </div>
      <?php endif; ?>
      
      <form id="editProfileForm" class="auth-form" method="post">
        <div id="emailField">
          <label for="userEmail">Your Email:</label>
          <input 
            type="email" 
            id="userEmail" 
            name="userEmail" 
            placeholder="Enter your email address"
            value="<?php echo htmlspecialchars($email); ?>" 
            required
          >
        </div>

        <div id="bioField">
          <label for="profileBio">Bio:</label>
          <textarea 
            id="profileBio" 
            name="profileBio" 
            placeholder="Tell us about yourself"
            rows="6"  
            required
          ><?php echo htmlspecialchars($bio); ?></textarea>
        </div>

        <div id="tagsField">
          <label for="profileTags">Tags (comma-separated):</label>
          <input 
            type="text" 
            id="profileTags" 
            name="profileTags" 
            placeholder="sports, food, technology"
            value="<?php 
              if (!empty($tags)) {
                echo htmlspecialchars(implode(', ', $tags));
              }
            ?>"
          >
        </div>

        <div id="uploadImage">
          <label for="profileImage">Profile Picture (optional):</label>
          <input 
            type="file" 
            id="profileImage" 
            name="profileImage"
            accept="image/*"
          >
          <small>Image upload functionality coming soon</small>
        </div>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <!-- Update Button -->
        <button type="submit" id="submitBlog">Update Profile</button>
      </form>
    </main>
    
    <!-- Profile Sidebar -->
    <aside class="profile-sidebar">
      <?php include "profilesidebar.php"; ?>
    </aside>
  </div>

  <!-- Client-Side Validation -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('editProfileForm');
      
      form.addEventListener('submit', (e) => {
        const email = document.getElementById('userEmail').value;
        const bio = document.getElementById('profileBio').value.trim();
        
        // Email validation (same regex as signup)
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          e.preventDefault();
          alert("Please enter a valid email address.");
          return;
        }
        
        // Bio validation
        if (!bio) {
          e.preventDefault();
          alert("Please provide a bio for your profile.");
          return;
        }
      });
    });
  </script>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>