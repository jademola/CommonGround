<?php
session_start();
if (!isset($_SESSION['loggedIn'])) {
    $_SESSION['loggedIn'] = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Common Ground</title>
    <link rel="stylesheet" href="styles.css">

    <!-- Temporary, to be replaced once DB implemented-->
    <style>
     #family-tag { background-color: #ffe89d; }
     #travel-tag { background-color: #afff94; }
     #ubco-tag { background-color: #9da9ff; }
     #food-tag { background-color: #dfc8a8; }
     #sports-tag { background-color: #8dbae1; }
    </style>
</head>
<body>
    <nav class="nav">
        <?php if ($_SESSION['loggedIn']): ?>
            <a href="newpost.html" class="nav-item">New Post</a>
            <a href="activity.html" class="nav-item">Activity</a>
            <a href="index.php" class="nav-item">Home</a>
            <a href="profile.html" class="nav-item">Profile</a>
            <a href="search.html" class="nav-item">Search</a>
        <?php else: ?>
            <a href="login.php" class="nav-item">New Post</a>
            <a href="activity.html" class="nav-item">Activity</a>
            <a href="index.php" class="nav-item">Home</a>
            <a href="login.php" class="nav-item">Profile</a>
            <a href="search.html" class="nav-item">Search</a>
        <?php endif; ?>
    </nav>

    <header class="header">
        <h1 class="site-name">Common Ground</h1>
        <?php if ($_SESSION['loggedIn']): ?>
            <a href="logout.php" class="logout-btn">Logout</a>
        <?php else: ?>
            <div id="signin-buttons">
                <a href="login.php" class="login-btn">Login</a>
                <a href="signup.html" class="login-btn">Sign Up</a>
            </div>
        <?php endif; ?>
    </header>

    <div class="main-content">
        <!-- Sidebar content remains the same for both states -->
        <aside class="sidebar">
            <h2 class="sidebar-header">Activity:</h2>
            <ul class="popular-list">
                <li>Popular:</li>
                <li>1. The Secret to Building a Successful Morning Routine</li>
                <li>2. The Best Books You've Never Heard of: A Reading List for the Curious</li>
                <li>3. The Most Beautiful Places You've Never Seen: Iceland</li>
            </ul>
            <div class="notification-box">
                7 new Notifications
            </div>
        </aside>

        <!-- Main feed content -->
        <main class="feed">
            <h2 class="feed-header">Your Feed:</h2>
            <div class="post">
                <div class="post-header">
                    <img src="images/icon.png" alt="" id="post-img">
                    <div class="user-info">
                        <div><?php echo $_SESSION['loggedIn'] ? '<b>Username</b>' : 'Username'; ?></div>
                        <div><?php echo $_SESSION['loggedIn'] ? '<b>Bio: </b>' : 'Bio: '; ?>Lorem ipsum</div>
                        <div class="post-tags">
                            <span class="tag" id="travel-tag">Travel</span>
                            <span class="tag" id="family-tag">Family</span>
                        </div>
                    </div>
                    <div class="timestamp">4:10pm, January 29th, 2025</div>
                </div>
                <!-- Rest of the post content remains the same -->
            </div>
        </main>

        <!-- Profile sidebar with conditional rendering -->
        <aside class="profile-sidebar">
            <h2 class="profile-header">Profile:</h2>
            <div class="profile-card">
                <img src="images/icon.png" alt="">
                <div class="profile-username">Username</div>
                <div class="profile-bio">
                    This is the text in the bio shown below
                </div>
                <?php if ($_SESSION['loggedIn']): ?>
                    <div class="side-profile-tags">
                        <div><b>Tags:</b></div>
                        <div id="side-profile-tags-whitespace">
                            <span class="tag" id="sports-tag">Sports</span>
                            <span class="tag" id="food-tag">Food</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="profile-buttons">
                        <button onclick="window.location.href='login.php'">Login</button>
                        <button onclick="window.location.href='signup.html'">Sign-up</button>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>

    <script>
        function changeHeart(button) {
            if (button.textContent.includes('♡')) {
                button.textContent = '♥ Like';
            } else {
                button.textContent = '♡ Like';
            }
        }
    </script>
</body>
</html>