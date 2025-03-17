<?php
session_start();

if ($_SESSION['loggedIn']) {
  header("Location: profile.php");
  exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $_POST['loginUsername'];
  $password = $_POST['loginPassword'];

  // TODO: Add database authentication using MySQL
  if ($username && $password) {
    $_SESSION['loggedIn'] = true;
    $_SESSION['username'] = $username;
    header("Location: profile.php");
    exit();
  }
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
  <!-- Navigation (if we want main nav on login page) -->
  <nav class="nav">
    <a href="login.php" class="nav-item">New Post</a>
    <a href="activity.html" class="nav-item">Activity</a>
    <a href="index.php" class="nav-item">Home</a>
    <a href="login.php" class="nav-item">Profile</a>
    <a href="search.html" class="nav-item">Search</a>
  </nav>

  <!-- Header -->
  <header class="header">
    <h1 class="site-name">Common Ground</h1>
    <!-- Replace this link -->
    <a href="#" class="logout-btn">Need Help?</a>
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
      <form id="loginForm" class="auth-form" method="post">
        <label for="loginUsername">Username or Email:</label>
        <input
          type="text"
          id="loginUsername"
          name="loginUsername"
          placeholder="Enter username or email"
          required>

        <label for="loginPassword">Password:</label>
        <input
          type="password"
          id="loginPassword"
          name="loginPassword"
          placeholder="Enter password"
          required>

        <button type="submit">Login</button>
      </form>

      <p>
        Don’t have an account?
        <a href="signup.html">Sign Up</a>
      </p>
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