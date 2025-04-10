
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL); 
include "sessions.php";
include "db_connect.php";

$errorMessage = null;

if ($_SESSION['loggedIn']) {
  header("Location: profile.php");
  exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['signupUsername'];
  $password = $_POST['signupPassword'];
  $email = $_POST['signupEmail'];

  $check_sql = "SELECT username FROM userInfo WHERE username = ? OR email = ?";
  $check_stmt = $conn->prepare($check_sql);
  $check_stmt->bind_param("ss", $username, $email);
  $check_stmt->execute();
  $check_stmt->store_result();

  if ($check_stmt->num_rows == 0) {
    // Query username and password 
    $sql = "INSERT INTO userInfo (username, email, password) 
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);  // "sss" specifies the type (string)
    $stmt->execute();

    $sqlP = "INSERT INTO profile (username) 
    VALUES (?)";

    $stmtC = $conn->prepare($sqlP);
    $stmtC->bind_param("s", $username);  // "s" specifies the type (string)
    $stmtC->execute();
    $stmtC->close();
    
    
    // Profile Image upload 
    if ($_FILES["image"]["size"]>0) {

    $imagedata = file_get_contents($_FILES['image']['tmp_name']); 

      $fileType = $_FILES['image']['type'];
      
      $sqlI = "INSERT INTO userImages (username, contentType, image) 
          VALUES (?, ?, ?)";

      $stmtB = mysqli_stmt_init($conn);
      
      mysqli_stmt_prepare($stmtB, $sqlI);

      mysqli_stmt_bind_param($stmtB, "ssb", $username, $fileType, $data);

      mysqli_stmt_send_long_data($stmtB, 2, $imagedata);

      // This sends the binary data to the third variable location in the // prepared statement (starting from 0).
      $resultB = mysqli_stmt_execute($stmtB) or die(mysqli_stmt_error($stmtB)); // run the statement
      mysqli_stmt_close($stmtB); // and dispose of the statement.
    }
    else {
      $defaultImagePath = './images/icon.png';
      $imagedata = file_get_contents($defaultImagePath);
      $fileType = 'image/png';

      $sqlI = "INSERT INTO userImages (username, contentType, image) 
        VALUES (?, ?, ?)";

      $stmtB = mysqli_stmt_init($conn);
      mysqli_stmt_prepare($stmtB, $sqlI);
      mysqli_stmt_bind_param($stmtB, "ssb", $username, $fileType, $data);
      mysqli_stmt_send_long_data($stmtB, 2, $imagedata);
      mysqli_stmt_execute($stmtB);
      mysqli_stmt_close($stmtB);
    }

    if ($stmt->affected_rows > 0) {
      $_SESSION['loggedIn'] = true;
      $_SESSION['username'] = $username;
      header("Location: profile.php");
      exit();
    }
    else {
      header("Location: signup.php");
      exit();
    }

    $stmt->close();
    $stmtB->close();

  }
  else {
    $errorMessage = "Username in use, Please try again.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Common Ground - Sign Up</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <!-- Navigation Bar -->
  <!-- Should be moved as well with the login page -->
  <?php include "header.php" ?>

  <div class="main-content">
    <!-- Sidebar for consistency -->
    <aside class="sidebar">
      <h2 class="sidebar-header">Join Us</h2>
      <ul class="popular-list">
        <li>Community Guidelines</li>
        <li>Terms of Service</li>
        <li>Privacy Policy</li>
      </ul>
      <div class="notification-box">Be part of our community!</div>
    </aside>

    <main class="feed">
      <h2 class="feed-header">Sign Up</h2>

      <!-- Sign Up Form -->
      <form id="signupForm" class="auth-form" method="post" enctype="multipart/form-data">
        <div id="signUsername">
          <label for="signupUsername">Username:</label>
          <input
            type="text"
            id="signupUsername"
            name="signupUsername"
            placeholder="Choose a username"
            required>
        </div>

        <div id="signEmail">
          <label for="signupEmail">Email:</label>
          <input
            type="email"
            id="signupEmail"
            name="signupEmail"
            placeholder="Enter your email address"
            required>
        </div>

        <div id="signPassword">
          <label for="signupPassword">Password:</label>
          <input
            type="password"
            id="signupPassword"
            name="signupPassword"
            placeholder="Create a password"
            required>
        </div>

        <div id="confirmPass">
          <label for="confirmPassword">Confirm Password:</label>
          <input
            type="password"
            id="confirmPassword"
            name="confirmPassword"
            placeholder="Re-type your password"
            required>
        </div>

        <div id="uploadImage">
          <!-- Adding image -->
          <label for="postImage">Upload Profile Image:</label>
          <input 
            type="file" 
            id="image" 
            name="image"
            accept="image/*"
          >
        </div>


        <!-- Server-Side Validation Error Message -->
        <?php if (isset($errorMessage)): ?>
          <div class="error-message">
            <?php echo $errorMessage; ?>
          </div>
        <?php endif; ?>

        <button type="submit" id="signupButton">Sign Up</button>
        <p>
          Already have an account?
          <a href="login.php">Login</a>
        </p>
      </form>


    </main>
  </div>

  <!-- Client-Side Validation -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const signupForm = document.getElementById('signupForm');

      signupForm.addEventListener('submit', (e) => {
        const password = document.getElementById('signupPassword').value;
        const confirm = document.getElementById('confirmPassword').value;

        // Check if passwords match
        if (password !== confirm) {
          e.preventDefault();
          alert("Passwords don't match. Please re-type.");
        }
      });
    });
  </script>
</body>

</html>