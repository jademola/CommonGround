<?php
include "sessions.php";
include "notifications.php";
include "db_connect.php";
include "queryFunctions.php";
    
ini_set('display_errors', 1);
error_reporting(E_ALL); 


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
    $stmtB->bind_param("ss", $email, $_SESSION['username']); 
    $stmtB->execute();

    // Update Bio 
    $sql = "UPDATE profile SET bio = ? WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $bio, $_SESSION['username']);
    $stmt->execute();
    
    //Update Tags

    //Remove tag
    if (!empty($removedTags)){
      $removedTagsArray = explode(',', $removedTags);

      foreach ($removedTagsArray as $value){
        $sql = "DELETE FROM profile_tags 
        WHERE id = ? AND username = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $value, $_SESSION['username']);
        $stmt->execute();
      }
    }

    //Add new tag
    if ($tag_id != 0){
      $sqlB = "INSERT INTO profile_tags (id, username) 
      VALUES (?, ?)";
      $stmtB = $conn->prepare($sqlB);
      $stmtB->bind_param("ss", $tag_id, $_SESSION['username']);
      $stmtB->execute();
    }
    
    // Update Profile Image
    if (isset($_FILES['image']) && !empty($_FILES['image']['name'])) {
      
      $imagedata = file_get_contents($_FILES['image']['tmp_name']); 

      $fileType = $_FILES['image']['type'];
      
      /*
      $sql = "UPDATE userImages 
        SET contentType = ?, image = ? 
        WHERE username = ?";
        */

        $sql = "INSERT INTO userImages (contentType, image, username) 
          VALUES (?, ?, ?)";

      $stmt = mysqli_stmt_init($conn);
      
      mysqli_stmt_prepare($stmt, $sql);

      mysqli_stmt_bind_param($stmt, "sbs", $fileType, $data, $_SESSION['username']);

      mysqli_stmt_send_long_data($stmt, 1, $imagedata);

      // This sends the binary data to the third variable location in the // prepared statement (starting from 0).
      $result = mysqli_stmt_execute($stmt) or die(mysqli_stmt_error($stmt)); // run the statement
      mysqli_stmt_close($stmt); // and dispose of the statement.
      }

    // Update Password:
    if (isset($oldPass) && isset($newPass) && !empty($oldPass) && !empty($newPass)){

      // Hash user password 
      $hashedPass = md5($newPass);

      // Check current password is valid 
      $sql = "SELECT password FROM userInfo WHERE username = ?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param("s", $_SESSION['username']);
      $stmt->execute();
      $stmt->store_result();
      $stmt->bind_result($storedPassword);

      if ($stmt->fetch()) {
        if ($oldPass == $storedPassword){ 
          $sqlN = "UPDATE userInfo SET password = ? WHERE username = ?";
          $stmtN = $conn->prepare($sqlN);
          $stmtN->bind_param("ss", $hashedPass, $_SESSION['username']);
          $stmtN->execute();
          $stmtN->close();
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

  <?php include "header.php" ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Popular Post Sidebar -->
    <aside class="sidebar">
      <?php include "popularsidebar.php"; ?>
      <!-- Notification Alert Bar -->
      <div class="notification-box">
        <a href="activity.php"><?php echo $_SESSION['notification_count']; ?> new Notifications</a>
      </div>
    </aside>

    <!-- New Post Section -->
    <main class="feed">
      <h2 class="feed-header">Update Profile Information</h2>
      
      <form id="update_profile" class="update_profile" method="post" action="editprofile.php" enctype="multipart/form-data">
        <div id="userEmailUpdate">
          <!-- User Email -->
          <label for="userEmail">Your Email:</label>
          <input 
            type="email" 
            id="userEmail" 
            name="userEmail" 
            value="<?php echo getUserEmailByUsername($_SESSION['username'], $conn);?>" 
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
            required><?php echo getUserBioByUsername($_SESSION['username'], $conn) ?></textarea>
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
              $stmt->bind_param("s", $_SESSION['username']);
              $stmt->execute();
              $result = $stmt->get_result();
          ?>
              <input type="hidden" name="removedTags" id="removedTags">
          <?php
              while ($row = $result->fetch_assoc()) {
                  $tag_id = htmlspecialchars(strtolower($row["id"])) . "-tag";
                  echo '<span class="edittag" id="' . $tag_id . '">' . htmlspecialchars($row["name"]) . '</span>';
              }

              if ($result->num_rows === 0) {
                  echo "No tags yet selected";
              }

              $stmt->close();
          ?>        
          <select name="tags" id="tagList">
            <option value="0">Add New Tag</option> 
            <?php
            $sql = "SELECT name, id FROM tags"; 
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
          <label for="image">Upload New Profile Image:</label>
          <input 
            type="file" 
            id="image" 
            name="image"
            accept="image/*"
          >
        </div>

        <div id="updatePasswordInput"> 
          <!-- Update Password Value -->
           <label for="updatePassword">Update Password: </label> 
           <input
                type="password"
                id="oldPass" 
                name="oldPass" 
                placeholder="Enter your Current Password"
                ><br>

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
            removedTags = removedTags.filter(id => id !== tagId);
          } else {
            removedTags.push(tagId);
          }
          document.getElementById('removedTags').value = removedTags.join(',');
        });
      }); 
      
      const form = document.getElementById('update_profile');
      form.addEventListener('submit', (e) => {
        const emailValue = document.getElementById('userEmail').value.trim();
        const BioValue = document.getElementById('profileBio').value.trim();
        
        if (!emailValue || !BioValue) {
          e.preventDefault(); 
          alert('Please fill out both the email and bio fields.');
        }

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
