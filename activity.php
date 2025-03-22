<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Common Ground</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        #family-tag {
            background-color: #ffe89d;
        }

        #travel-tag {
            background-color: #afff94;
        }

        #ubco-tag {
            background-color: #9da9ff;
        }

        #food-tag {
            background-color: #dfc8a8;
        }

        #sports-tag {
            background-color: #8dbae1;
        }
    </style>
</head>

<body>
    <?php include "header.php" ?>

    <div class="main-content">
        <aside class="sidebar">
            <h2 class="sidebar-header">Activity:</h2>
            <ul class="popular-list">
                <li id="popular-box-title">Popular:</li>
                <li class="popularPost">1. The Secret to Building a Successful Morning Routine</li>
                <li class="popularPost">2. The Best Books You’ve Never Heard of: A Reading List for the Curious</li>
                <li class="popularPost">3. The Most Beautiful Places You’ve Never Seen: Iceland</li>
            </ul>
            <div class="notification-box">
                7 new Notifications
            </div>
        </aside>

        <main class="feed">
            <h2 class="feed-header">Your Activity:</h2>
            <h3 class="sortby">Sorted by date</h3>
            <div class="post">
                <div class="post-header">
                    <img src="images/icon.png" alt="" id="post-img">
                    <div class="user-info">
                        <div>Username</div>
                        <div>Bio: Lorem ipsum</div>
                        <div>
                            <span class="tag" id="travel-tag">Travel</span>
                            <span class="tag" id="family-tag">Family</span>
                        </div>
                    </div>
                    <div class="timestamp">4:10pm, January 29th, 2025</div>
                </div>
                <div class="post-title">Title: Lorem ipsum dolor sit amet</div>
                <div class="post-content">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis ligula diam, vestibulum non nunc
                    congue, euismod posuere nulla. Sed elit lectus, convallis nec convallis at, dignissim a diam. Mauris
                    nisl risus, fermentum nec sollicitudin eget, euismod eget nisi. Ut et pellentesque dui, non volutpat
                    quam. Duis id tellus est. Nullam at felis ligula
                </div>
                <div class="post-footer">
                    <button class="like-btn" onclick="changeHeart(this)">♡ Like</button>
                    <script>
                        function changeHeart(button) {
                            if (button.textContent.includes('♡')) {
                                button.textContent = '♥ Like';
                            } else {
                                button.textContent = '♡ Like';
                            }
                        }
                    </script>
                </div>
                <div class="comments-section">
                    <div>Comments:</div>
                    <div class="comment">
                        <div class="comment-checkbox">□</div>
                        <div class="comment-user">Username:</div>
                        <div class="comment-body">Comment body</div>
                        <div class="comment-date">01/29/25</div>
                    </div>
                    <button class="post-comment-btn">Post Comment</button>
                </div>
            </div>
        </main>

        <aside class="profile-sidebar">
            <h2 class="profile-header">Profile:</h2>
            <div class="profile-card">
                <img src="images/icon.png" alt="">
                <div class="profile-username">Username</div>
                <div class="profile-bio">
                    This is the text in the bio shown below
                </div>
                <div class="profile-tags">
                    <div>Tags:</div>
                    <div>
                        <span class="tag" id="sports-tag">Sports</span>
                        <span class="tag" id="food-tag">Food</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</body>

</html>