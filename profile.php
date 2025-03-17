<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Common Ground</title>
    <link rel="stylesheet" href = "styles.css">

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

    <!--Navigation Bar-->
    <nav class="nav">
        <a href="newpost.html" class="nav-item">New Post</a>
        <a href="activity.html" class="nav-item">Activity</a>
        <a href="index.php" class="nav-item">Home</a>
        <a href="profileLoggedIn.html" class="nav-item">Profile</a>
        <a href="search.html" class="nav-item">Search</a>
    </nav>
    <!-- Header -->
    <header class="header">
        <h1 class="site-name">Common Ground</h1>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Popular Post Sidebar -->
        <aside class="sidebar">
            <h2 class="sidebar-header">Activity:</h2>
            <ul class="popular-list">
                <li id="popular-box-title">Popular:</li>
                <li class="popularPost">1. The Secret to Building a Successful Morning Routine</li>
                <li class="popularPost">2. The Best Books You’ve Never Heard of: A Reading List for the Curious</li>
                <li class="popularPost">3. The Most Beautiful Places You’ve Never Seen: Iceland</li>
            </ul>
            <!-- Notification Alert Bar -->
            <div class="notification-box">
                7 new Notifications!
            </div>
        </aside>

        <!-- Main: View Profile -->
        <main class="userProfile">
            <h2 class="userProfile-header">@Sofi-Grace207</h2>
                <div class="profile-header">
                    <div class ="user-avatar">
                    <img src="images/profile1.jpg" alt="" id="#user-profile-img">
                    </div>
                     <div class="profileBio-content">
                   
                        <!-- Username Display - Replace with DB -->
                        <div>
                            <div id = "username-display"><b>Username: </b>Sofi-Grace207</div>
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