<?php

include "db_connect.php";
session_start();

if ($_SESSION['loggedIn']) {
  header("Location: profile.php");
  exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['loginUsername'];
  $password = $_POST['loginPassword'];


  // Query username and password 
  $sql = "SELECT username, password, userType FROM userInfo WHERE username = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $username);  // "s" specifies the type (string)
  $stmt->execute();
  $stmt->bind_result($storedUsername, $storedPassword, $storedUserType);

    // Gets returned username, or produces error 
    if ($stmt->fetch()) {
      $errorMessage = "Incorrect username, Please try again.";
      // Verifies password, or produces error
      if ($password == $storedPassword){ 
        $_SESSION['loggedIn'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['userType'] = $storedUserType;
        header("Location: profile.php");
        exit();
      }
      else {
        $errorMessage = "Incorrect password, Please try again.";
      }
    }
    else {
      $errorMessage = "Invalid credentials, Please try again.";
    }
    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Common Ground - Login</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  
  <header class="header">
    <h1 class="site-name">Common Ground</h1>
    <!-- Replace this link -->
    <!-- <a href="#faq.html" class="logout-btn">Need Help?</a> -->
  </header>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Sidebar for consistency in layout -->
    <aside class="sidebar">
      <h2 class="sidebar-header">Quick Links</h2>
      <ul class="popular-list">
        <li>About Us</li>
        <li>Contact Support</li>
        <li>FAQs</li>
      </ul>
      <div class="notification-box">Welcome!</div>
    </aside>

    <main class="feed">
      <h2 class="feed-header">Login</h2>

      <!-- Login Form -->
       <!-- Expand the size to match the V2 document -->
      <form id="loginForm" class="auth-form" method="post">
        <div id="login">
          <label for="loginUsername">Username:</label>
          <input
            type="text"
            id="loginUsername"
            name="loginUsername"
            placeholder="Enter username or email"
            required>
        </div>

        <div id="password">
          <label for="loginPassword">Password:</label>
          <input
            type="password"
            id="loginPassword"
            name="loginPassword"
            placeholder="Enter password"
            required>
        </div>

<!-- Server-Side Validation Error Message -->
        <?php if (isset($errorMessage)): ?>
          <div class="error-message">
            <?php echo $errorMessage; ?>
          </div>
        <?php endif; ?>

        <button type="submit" id="loginButton">Login</button>

        <p>
        Don’t have an account?
        <a href="signup.php">Sign Up</a>
      </p>
      </form>

      
    </main>
  </div>

  <!-- Client-Side Validation -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const loginForm = document.getElementById('loginForm');
      loginForm.addEventListener('submit', (e) => {
        const username = document.getElementById('loginUsername').value.trim();
        const password = document.getElementById('loginPassword').value.trim();

        if (!username || !password) {
          e.preventDefault();
          alert('Please fill out both fields before logging in.');
        }
      });
    });
  </script>
</body>

</html>