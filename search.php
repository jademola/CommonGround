<?php 
    session_start();
    $username = $_SESSION['username']; 
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Common Ground</title>
    <link rel="stylesheet" href = "styles.css">
    <?php include "db_connect.php"; ?>

</head>
<body>
<?php include "header.php" ?>
    
    <div class="main-content">
    <aside class="sidebar">
           <?php include "popularsidebar.php"; ?> 
        </br>
            <!-- Notification Alert Bar -->
            <div class="notification-box">
                7 new Notifications!
            </div>
        </aside>

        
        <main class="feed">
            <div class="search-bar">
                <b>Search:</b>
                <div id="bar">

                </div> 
            </div>
        </main>
        
        <aside class="profile-sidebar">
            <?php include "profilesidebar.php"; ?>
        </aside>
    </div>
</body>
</html>