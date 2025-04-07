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
    <link rel="stylesheet" href="styles.css">
    <?php include "db_connect.php"; ?>
</head>
<style>
    .tag {
        background-color: #9abdd6;
    }
</style>

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
                <a href="activity.php">7 new Notifications</a>
            </div>
        </aside>

        <!-- Main: View Profile -->
        <main class="userProfile">
            <h2 class="userProfile-header"><?php echo "@" . $_SESSION['username'] ?></h2>
            <div class="profile-header">
                <div class="user-avatar">
                    <img src="images/profile1.jpg" alt="" id="#user-profile-img">
                </div>
                <div class="profileBio-content">
                    <div>
                        <?php
                        $sql = "SELECT bio
                             FROM profile 
                             WHERE username = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $_SESSION['username']);  // "s" specifies the type (string)
                        $stmt->execute();
                        $result = $stmt->get_result();

                        // Check if any rows were returned
                        if ($row = $result->fetch_assoc()) {
                            // Display the bio
                            echo $row['bio'];
                        } else {
                            echo "No bio found for this user.";
                        }

                        $stmt->close();
                        ?>
                        <!-- Tags - Replace with DB-->
                    </div>
                    <!-- Edit Profile Button -->
                    <div id="test">
                        <a href="editprofile.php">
                            <button class="edit-profile-btn">Edit Profile</button>
                        </a>
                    </div>
                    <div class="profile-tags">
                        <p><b>User Tags:</b></p>

                        <?php
                        $sql = "SELECT tags.name 
                        FROM profile_tags JOIN tags 
                        ON profile_tags.id = tags.id 
                        WHERE username = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $_SESSION['username']);  // "s" specifies the type (string)
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            echo "<span class='tag' id='funny-tag'>" . $row['name'] . "</span>"; // You can modify this as needed
                        }

                        if ($result->num_rows === 0) {
                            echo "No tags yet selected";
                        }
                        $stmt->close();
                        ?>
                    </div>
                </div>

            </div>

        </main>
    </div>

</body>

</html>