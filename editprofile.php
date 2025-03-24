<?php 
    session_start();

    include "db_connect.php"; 
    include "queryFunctions.php";
    

    // Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['userEmail'];
    $bio = $_POST['profileBio'];
    $tags = $_POST['profileTags'];

    // Update Bio 
    $sql = "UPDATE profile SET bio = ? WHERE username = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $bio, $_SESSION['username']);  // "s" specifies the type (string)
    $stmt->execute();

    // Update Email 
    $sqlB = "UPDATE userInfo SET email = ? WHERE username = ?";

    $stmtB = $conn->prepare($sqlB);
    $stmtB->bind_param("ss", $email, $_SESSION['username']);  // "s" specifies the type (string)
    $stmtB->execute();

    /*// Update Tags
    $sqlB = "UPDATE profile_tags SET tag_name = ? WHERE username = ?";

    $stmtB = $conn->prepare($sqlB);
    $stmtB->bind_param("ss", $email, $_SESSION['username']);  // "s" specifies the type (string)
    $stmtB->execute();
    */

    if ($stmt->affected_rows > 0 || $stmtB->affected_rows > 0) {
      header("Location: profile.php");
      exit();
    }
    else {
      header("Location: editprofile.php");
      exit();
    }
    $stmt->close();
  }
 

    
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Common Ground - New Post</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <?php include "header.php"?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Popular Post Sidebar -->
    <aside class="sidebar">
           <?php include "popularsidebar.php"; ?> 
        </br>
            <!-- Notification Alert Bar -->
            <div class="notification-box">
                7 new Notifications!
            </div>
        </aside>

    <!-- New Post Section -->
    <main class="feed">
      <h2 class="feed-header">Update Profile Information</h2>
      
      <form id="newPostForm" class="new-post-form" method="post">
        <div id="titleContent">
          <!-- Title -->
          <label for="userEmail">Your Email:</label>
          <input 
            type="text" 
            id="userEmail" 
            name="userEmail" 
            value="<?php echo getUserEmailByUsername($_SESSION['username'], $conn);?>"; 
            required
          >
        </div id=tit>

        <div id="blogContent">
          <!-- Content -->
          <label for="profileBio">Bio:</label>
          <textarea 
            id="profileBio" 
            name="profileBio" 
            rows="6"  
            required
          ><?php echo getUserBioByUsername($_SESSION['username'], $conn)?></textarea>
        </div>

        <div id="tagContent">
          <!-- Tags -->
          <label for="profileTags">Tags (comma-separated):</label>
          <input 
            type="text" 
            id="profileTags" 
            name="profileTags" 
            value="<?php 
                $tags = getUserTagsByUsername($_SESSION['username'], $conn);

                if (count($tags) > 0) {
                    foreach ($tags as $tag) {
                        echo "$tag, ";
                    }
                } else {
                    echo "No tags available.";
                }?>"; 
          >
        </div>

        <div id="uploadImage">
          <!-- Adding image -->
          <label for="postImage">Upload Image (optional):</label>
          <input 
            type="file" 
            id="postImage" 
            name="postImage"
            accept="image/*"
          >
        </div>

        <!-- Publish Button -->
        <a href="editprofile.php">
            <button type="submit" id="submitBlog">Update</button>
        </a>
      </form>
    </main>
  </div>

  <!-- Very basic client-side validation -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('newPostForm');
      form.addEventListener('submit', (e) => {
        const titleValue = document.getElementById('postTitle').value.trim();
        const contentValue = document.getElementById('postContent').value.trim();
        
        // Minimal check
        if (!titleValue || !contentValue) {
          e.preventDefault(); 
          alert('Please fill out both the title and content fields.');
        }
        // Add more checks when more fields added
      });
    });
  </script>
</body>
</html>
