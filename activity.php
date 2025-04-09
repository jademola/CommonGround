<?php
include "db_connect.php";

session_start();
// include "notifications.php";
if (!isset($_SESSION['loggedIn'])) {
    $_SESSION['loggedIn'] = false;
    header("Location: login.php");
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
        <!-- Sidebar content remains the same for both states -->
        <aside class="sidebar">
            <!-- Top three posts (by likes, in order) -->
            <?php include "popularsidebar.php"; ?>

            <div class="notification-box">
                <a href="activity.php"><?php echo $_SESSION['notification_count']; ?> new Notifications</a>

            </div>
        </aside>

        <!-- Main feed content -->
        <main class="feed">
            <h2 class="feed-header">Your Notifications:</h2>
            <div class="notifications">
                <?php
                // Get notifications for likes and comments on user's posts
                $sql = "SELECT 
                            'like' as type,
                            pl.author as actor,
                            p.id as post_id,
                            p.title as post_title,
                            pl.date as date
                        FROM post_likes pl
                        JOIN post p ON p.id = pl.post_id
                        WHERE p.author = ?
                        UNION ALL
                        SELECT 
                            'comment' as type,
                            c.author as actor,
                            p.id as post_id,
                            p.title as post_title,
                            c.date as date
                        FROM comments c
                        JOIN post p ON p.id = c.post_id
                        WHERE p.author = ?
                        ORDER BY date DESC";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $_SESSION['username'], $_SESSION['username']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $action = $row['type'] === 'like' ? 'liked' : 'commented on';
                ?>
                        <div class="post">
                            <div class="post-header">
                                <div class="user-info">
                                    <a class="authorLink" href="profile.php?id=<?php echo $row['actor']; ?>">
                                        <b><?php echo $row['actor']; ?></b>
                                    </a>
                                    <?php echo " " . $action . " your post: "; ?>
                                    <a class="authorLink" href="post.php?id=<?php echo $row['post_id']; ?>">
                                        <b><?php echo $row['post_title']; ?></b>
                                    </a>
                                </div>
                                <div class="timestamp"><?php echo date("F j, Y", strtotime($row['date'])); ?></div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<div class='post'>No notifications yet.</div>";
                }
                $stmt->close();
                ?>
            </div>
        </main>

        <!-- Profile sidebar with conditional rendering -->
        <aside class="profile-sidebar">
            <?php include "profilesidebar.php" ?>
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