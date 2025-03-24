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
      <form id="signupForm" class="auth-form">
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

        // Check if passwords match (shittiest security ever lmao)
        if (password !== confirm) {
          e.preventDefault();
          alert("Passwords don't match. Please re-type.");
        }
      });
    });
  </script>
</body>

</html>