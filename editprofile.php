<?php 
    session_start();

    include "db_connect.php"; 
    include "queryFunctions.php";


    // Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['userEmail'];
    $bio = $_POST['profileBio'];
    $oldPass = $_POST['oldPass'];
    $newPass = $_POST['newPassA']; 
    $tag_id = $_POST['tags'];
    $removedTags = $_POST['removedTags']; 

    // Update Email 
    $sqlB = "UPDATE userInfo SET email = ? WHERE username = ?";

    $stmtB = $conn->prepare($sqlB);
    $stmtB->bind_param("ss", $email, $_SESSION['username']);  // "s" specifies the type (string)
    $stmtB->execute();

    // Update Bio 
    $sql = "UPDATE profile SET bio = ? WHERE username = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $bio, $_SESSION['username']);  // "s" specifies the type (string)
    $stmt->execute();
    
    //Update Tags

    //Remove tag
    if (!empty($removedTags)){
      $removedTagsArray = explode(',', $removedTags);

      foreach ($removedTagsArray as $value){
        $sql = "DELETE FROM profile_tags 
        WHERE id = ? AND username = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $value, $_SESSION['username']);  // "s" specifies the type (string)
        $stmt->execute();

      }
    }
    //Add new tag
    if ($tag_id != 0){
      $sqlB = "INSERT INTO profile_tags (id, username) 
      VALUES (?, ?)";

      $stmtB = $conn->prepare($sqlB);
      $stmtB->bind_param("ss", $tag_id, $_SESSION['username']);  // "s" specifies the type (string)
      $stmtB->execute();

    }
    
    // Update Profile Image


    // Update Password:
    // Check if Password has been updated 
    if (isset($oldPass) && isset($newPass) && !empty($oldPass) && !empty($newPass)){

      // Check current password is valid 
    $sql = "SELECT password FROM userInfo WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);  // "s" specifies the type (string)
    $stmt->execute();
    $stmt->bind_result($storedPassword);

      if ($stmt->fetch()) {
        // Verifies password, and updates database
        if ($newPass == $storedPassword){ 
          $sql = "UPDATE userInfo SET password = ? WHERE username = ?";

          $stmt = $conn->prepare($sqlB);
          $stmt->bind_param("ss", $newPass, $_SESSION['username']);  // "s" specifies the type (string)
          $stmt->execute();
        }
        else {
          $errorMessage = "Incorrect current password, please try again";
        }
      }
      else {
        $errorMessage = "Incorrect current password, please try again";
      }
    $stmt->close();
    }
    
    // Re-direct after submission 
    if (isset($errorMessage)) {
      header("Location: editprofile.php");
      exit();
    }
    else {
      header("Location: profile.php");
      exit();
    }
    $stmt->close();
    $stmtB->close(); 
  }
 

    
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Common Ground - Edit Profile</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <style> 

  .edittag {
    background-color: #9abdd6;
    display: inline-block;
    font-family: var(--font-family-main);
    padding: var(--spacing-sm) var(--spacing-md);
    margin-left: var(--spacing-sm);
    border-radius: var(--border-radius-lg);
    cursor: pointer;
  }

  .clicked {
    background-color: gray; 
    text-decoration: line-through; 
  }

    
  </style>

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
      
      <form id="update_profile" class="update_profile" method="post" action="editprofile.php">
        <div id="userEmailUpdate">
          <!-- User Email -->
          <label for="userEmail">Your Email:</label>
          <input 
            type="email" 
            id="userEmail" 
            name="userEmail" 
            value="<?php echo getUserEmailByUsername($_SESSION['username'], $conn);?>"; 
            required
          >
        </div>

        <div id="profileBio">
          <!-- User Bio Content -->
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
          <label for="tagList">User Tags: (Select Tag to Remove)</label>

          <!-- Displays selected tags -->
          <?php
              $sql = "SELECT tags.name, tags.id 
              FROM tags JOIN profile_tags
              ON  tags.id = profile_tags.id
              WHERE username = ?";
              $stmt = $conn->prepare($sql);
              $stmt->bind_param("s", $_SESSION['username']);  // "s" specifies the type (string)
              $stmt->execute();
              $result = $stmt->get_result();

              ?>
              <input type="hidden" name="removedTags" id="removedTags">
              <?php
              while ($row = $result->fetch_assoc()) {
                  $tag_id = htmlspecialchars(strtolower($row["id"])) . "-tag";
                  echo '<span class="edittag" id="' . $tag_id . '">' . htmlspecialchars($row["name"]) . '</span>';                        }

              if ($result->num_rows === 0) {
                  echo "No tags yet selected";
              }


              $stmt->close();
              ?>        
          <select name="tags" id="tagList">
            <option value="0">Add New Tag</option> 
            <?php
            $sql = "SELECT name, id
            FROM tags"; 
            $stmt = $conn->prepare($sql); 
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()){
              echo '<option value=' . $row['id'] . '> ' . $row['name'] . '</option>';
            }

            ?>
          </select>
        </div>

        <div id="uploadImage">
          <!-- Adding image -->
          <label for="postImage">Upload New Profile Image:</label>
          <input 
            type="file" 
            id="postImage" 
            name="postImage"
            accept="image/*"
          >
        </div>

        <div id = "updatePasswordInput"> 
          <!-- Update Password Value -->
           <label for="updatePassword">Update Password: </label> 
           <input
                type="password"
                id="oldPass" 
                name="oldPass" 
                placeholder="Enter your Current Password"
                ></br>

              <div class="input-row">
                <input
                type="password"
                id="newPassA" 
                name="newPassA" 
                placeholder="Enter New Password"
                >

                <input
                type="password"
                id="newPassB" 
                name="newPassB" 
                placeholder="Confirm New Password"
                >
              </div>
          
        </div>

        <?php if (isset($errorMessage)): ?>
          <div class="error-message">
            <?php echo $errorMessage; ?>
          </div>
        <?php endif; ?> 

        <!-- Update Button -->
        <a href="editprofile.php">
            <button type="submit" id="submitBlog">Update</button>
        </a>
      </form>
    </main>
  </div>

  <!-- Very basic client-side validation -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {

      //Changes tag color when selected
      const tagSpans = document.querySelectorAll('.edittag'); 
      let removedTags = [];

        tagSpans.forEach(function(span) {
          span.addEventListener('click', function(event) {
            const targetSpan = event.target; 
            const tagId = targetSpan.getAttribute('id');
            targetSpan.classList.toggle('clicked'); 

            if (removedTags.includes(tagId)) {
            // If it is, remove it from the array
            removedTags = removedTags.filter(id => id !== tagId);
          } else {
            // If it is not, add it to the array
            removedTags.push(tagId);
          }
          document.getElementById('removedTags').value = removedTags.join(',');

          });
        }); 
      
        //When update is selected: 
      const form = document.getElementById('update_profile');
      form.addEventListener('submit', (e) => {
        const emailValue = document.getElementById('userEmail').value.trim();
        const BioValue = document.getElementById('profileBio').value.trim();
        
        // Minimal check for no input value
        if (!emailValue || !BioValue) {
          e.preventDefault(); 
          alert('Please fill out both the title and content fields.');
        }

        // Check old password? - Server side 
        // Check if NEW passwords match
        const password = document.getElementById('newPassA').value;
        const confirm = document.getElementById('newPassB').value;
        if (password !== confirm) {
          e.preventDefault();
          alert("New passwords don't match. Please try again.");
        }
      });
    });
  </script>
</body>
</html>
