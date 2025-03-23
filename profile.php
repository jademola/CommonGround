<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Common Ground</title>
    <link rel="stylesheet" href="styles.css">
    <?php include "db_connect.php"; ?>


    <!-- Temporary, to be replaced once DB implemented -->
    <style>
        #funny-tag {
            background-color: #bad6eb;
        }

        #family-tag {
            background-color: #9bd195;
        }

        #username-display {
            margin-bottom: 5px;
        }

        #popular-box-title {
            padding-top: 5px;
            text-align: center;
        }
    </style>
</head>

<body>

    <?php include "header.php" ?>


    <!-- Main Content -->
    <div class="main-content" id="mainFeed">

        <!-- Popular Post Sidebar -->
        <aside class="sidebar">
           <?php include "popularsidebar.php"; ?> 
    </br>
            <!-- Notification Alert Bar -->
            <div class="notification-box">
                7 new Notifications!
            </div>
        </aside>

        <!-- Main: View Profile -->
        <main class="userProfile">
            <h2 class="userProfile-header"><?php $username ?></h2>
            <div class="profile-header">
                <div class="user-avatar">
                    <img src="images/profile1.jpg" alt="" id="#user-profile-img">
                </div>
                <div class="profileBio-content">

                    <!-- Username Display - Replace with DB -->
                    <div>
                        <div id="username-display"><b>Username: </b>Sofi-Grace207</div>
                    </div>

                    <b>Bio: </b>Hey, I’m Sophia Grace! I’m 20 and obsessed with traveling
                    the world. I love exploring new places and experiencing the beauty of
                    different cultures, whether I’m staying in a cozy boutique hotel or
                    finding the best local spots. On this blog, I share my travel stories,
                    tips for simple and memorable adventures, and how to enjoy life’s little
                    luxuries without losing sight of the bigger picture. Come along with me
                    as I discover new places and live a life full of balance and gratitude. 🌍✨

                    <!-- Tags - Replace with DB-->
                    <div class="profile-tags">
                        <p><b>User Tags:</b></p>
                        <span class="tag" id="funny-tag">Travel</span>
                        <span class="tag" id="family-tag">Outdoors</span>
                    </div>
                </div>

            </div>
            <!-- Edit Profile Button -->
            <div id="test">
                <button class="edit-profile-btn">Edit Profile</button>
            </div>
        </main>
    </div>

</body>

</html>