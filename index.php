<?php
include "db_connect";

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

    <?php include "header.php" ?>

    <div class="main-content">
        <!-- Sidebar content remains the same for both states -->
        <aside class="sidebar">
                <!-- Top three posts (by likes, in order) -->  
                <?php include "popularsidebar.php"; ?> 

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
                        <button onclick="window.location.href='signup.php'">Sign-up</button>
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