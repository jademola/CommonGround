
<?php
/*
signup.php:
Signup page:
1. Session checks
2. Input sanitization and checking - both server and client side
3. Insert new signup into database , redirect to profile
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

$errorMessage = null;

// Need to check if session actually exists
if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true) {
  header("Location: profile.php");
  exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // CSRF protection 
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $errorMessage = "Form submission error. Please try again.";
  } else {
    // Inputs need to be sanitized
    $username = htmlspecialchars(trim($_POST['signupUsername']));
    $email = filter_var($_POST['signupEmail'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['signupPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Server side validation
    if (empty($username) || empty($email) || empty($password)) {
      $errorMessage = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $errorMessage = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
      $errorMessage = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
      $errorMessage = "Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.";
    } elseif ($password !== $confirmPassword) {
      $errorMessage = "Passwords don't match. Please try again.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
      $errorMessage = "Username must be 3-20 characters and contain only letters, numbers, and underscores.";
    } else {
      try {
        // DB connection here to avoid loading it unnecessarily at start
        include "db_connect.php";
        
        $check_sql = "SELECT username FROM userInfo WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        // Was potentially problematic check
        if ($check_stmt->num_rows > 0) {
          $errorMessage = "Username or email already in use. Please try again.";
        } else {
          // Passwords can't be stored in plain text that is a security risk 
          $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
          
          $sql = "INSERT INTO userInfo (username, email, password) VALUES (?, ?, ?)";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("sss", $username, $email, $hashedPassword);  // fields weren't in order
          $stmt->execute();

          if ($stmt->affected_rows > 0) {

            $stmt->close();
            $_SESSION['loggedIn'] = true;
            $_SESSION['username'] = $username;

            $conn->close();
            // Redirect to profile 
            header("Location: profile.php");
            exit();
          } else {
            $stmt->close();
            $conn->close();
            $errorMessage = "Registration failed";
            //header("Location: index.php");   Not sure about this come back later
            // exit();
          }
        }
      } catch (Exception $e) {
        // Log error
        error_log("Database error: " . $e->getMessage());
        $errorMessage = "An error occurred during registration";
      }
    }   
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
      <form id="signupForm" class="auth-form" method="post">
        <div id="signUsername">
          <label for="signupUsername">Username:</label>
          <input
            type="text"
            id="signupUsername"
            name="signupUsername"
            placeholder="Choose a username"
            value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
            required>
        </div>

        <div id="signEmail">
          <label for="signupEmail">Email:</label>
          <input
            type="email"
            id="signupEmail"
            name="signupEmail"
            placeholder="Enter your email address"
            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
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

        <!-- Server-Side Validation Error Message -->
        <?php if (isset($errorMessage)): ?>
          <div class="error-message">
          <?php echo htmlspecialchars($errorMessage); ?>
          </div>
        <?php endif; ?>

        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

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
        const username = document.getElementById('signupUsername').value;
        const email = document.getElementById('signupEmail').value;
        const password = document.getElementById('signupPassword').value;
        const confirm = document.getElementById('confirmPassword').value;
        
        // Username validation
        if (!/^[a-zA-Z0-9_]{3,20}$/.test(username)) {
          e.preventDefault();
          alert("Username must be 3-20 characters and contain only letters, numbers, and underscores.");
          return;
        }
        
        // Email validation
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
          e.preventDefault();
          alert("Please enter a valid email address.");
          return;
        }
        
        // Password strength check
        if (password.length < 8 || 
          !/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/.test(password)) {
          e.preventDefault();
          alert("Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character.");
          return;
        }
        
        // Password match check
        if (password !== confirm) {
          e.preventDefault();
          alert("Passwords don't match. Please re-type.");
          return;
        }
      });
    });
  </script>
</body>
<!-- End of Class -->
</html>