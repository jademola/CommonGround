<?php
session_start();
if (!isset($_SESSION['loggedIn'])) {
    $_SESSION['loggedIn'] = false;
}
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
?>
<header class="header">
    <h1 class="site-name">Common Ground</h1>

    <nav class="nav">
        <?php if ($_SESSION['loggedIn']): ?>
            <a href="newpost.php" class="nav-item">New Post</a>

            <a href="activity.php" class="nav-item">Activity</a>
            <a href="index.php" class="nav-item">Home</a>
            <a href="profile.php" class="nav-item">Profile</a>
            <a href="search.php" class="nav-item">Search</a>
        <?php else: ?>
            <a href="login.php" class="nav-item">New Post</a>
            <a href="login.php" class="nav-item">Activity</a>
            <a href="index.php" class="nav-item">Home</a>
            <a href="login.php" class="nav-item">Profile</a>
            <a href="search.php" class="nav-item">Search</a>
        <?php endif; ?>
    </nav>

    <?php if ($_SESSION['userType'] === "admin"): ?>
        <a href="admin.php" class="header-btn">Admin</a>
        <a href="logout.php" class="header-btn">Logout</a>
    <?php elseif ($_SESSION['loggedIn']): ?>
        <a href="logout.php" class="header-btn">Logout</a>

    <?php else: ?>
        <div id="signin-buttons">
            <a href="login.php" class="header-btn">Login</a>
            <a href="signup.php" class="header-btn">Sign Up</a>
        </div>
    <?php endif; ?>
</header>