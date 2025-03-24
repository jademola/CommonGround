<?php 
     include "db_connect.php"; 


     function getUserBioByUsername($username, $conn) {
        // Define the SQL query
        $sql = "SELECT bio 
                FROM profile  
                WHERE username = ?";
        
        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("s", $username);  // "s" specifies the type (string)
            
            // Execute the query
            $stmt->execute();
            
            // Bind the result variables
            $stmt->bind_result($storedBio);
    
            // Check if any row was returned
            if ($stmt->fetch()) {
                // Return the bio
                return $storedBio;
            } else {
                // If no bio found for the user, return an appropriate message
                return "No bio found for this user.";
            }
            
            // Close the statement
            $stmt->close();
        } else {
            // If the statement failed to prepare, return an error message
            return "Error preparing statement.";
        }
    }

    function getUserEmailByUsername($username, $conn) {
        // Define the SQL query
        $sql = "SELECT email 
                FROM userInfo  
                WHERE username = ?";
        
        // Prepare the statement
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters
            $stmt->bind_param("s", $username);  // "s" specifies the type (string)
            
            // Execute the query
            $stmt->execute();
            
            // Bind the result variables
            $stmt->bind_result($storedEmail);
    
            // Check if any row was returned
            if ($stmt->fetch()) {
                // Return the bio
                return $storedEmail;
            } else {
                // If no bio found for the user, return an appropriate message
                return "No bio found for this user.";
            }
            
            // Close the statement
            $stmt->close();
        } else {
            // If the statement failed to prepare, return an error message
            return "Error preparing statement.";
        }
    }

function getUserTagsByUsername($username, $conn) {
    $sql = "SELECT tags.name 
        FROM profile_tags JOIN tags 
        ON profile_tags.id = tags.id 
        WHERE username = ?"; 
    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters
        $stmt->bind_param("s", $username);  // "s" specifies the type (string)
        
        // Execute the query
        $stmt->execute();
        
        // Get the result set
        $result = $stmt->get_result();
        
        // Initialize an array to store tags
        $tags = [];
        
        // Check if any tags are returned
        if ($result->num_rows > 0) {
            // Fetch tags and store them in the $tags array
            while ($row = $result->fetch_assoc()) {
                $tags[] = $row['name'];  // Add the tag to the array
            }
        } else {
            // If no tags are found, return a default message in the array
            $tags[] = "No tags yet selected";
        }
        
        // Close the statement
        $stmt->close();
        
        // Return the list of tags
        return $tags;
    } else {
        // If the query failed to prepare
        return ["Error preparing the statement."];
    }
}
 
    

    // Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['userEmail'];
    $password = $_POST['profileBio'];
    //$password = $_POST['profileTags'];
  
    
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
          <label for="postTitle">Your Email:</label>
          <input 
            type="text" 
            id="userEmail" 
            name="postTitle" 
            value="<?php echo getUserEmailByUsername($_SESSION['username'], $conn);?>"; 
            required
          >
        </div id=tit>

        <div id="blogContent">
          <!-- Content -->
          <label for="profileBio">Bio:</label>
          <textarea 
            id="profileBio" 
            name="postContent" 
            rows="6"  
            required
          ><?php echo getUserBioByUsername($_SESSION['username'], $conn);?></textarea>
        </div>

        <div id="tagContent">
          <!-- Tags -->
          <label for="postTags">Tags (comma-separated):</label>
          <input 
            type="text" 
            id="profileTags" 
            name="postTags" 
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
