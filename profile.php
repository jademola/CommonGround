<?php
session_start();
/*
1. Checks user auth, includes sidebar 
2. Fetches and displays user bio and tags 
3. Renders profile page with editable section that redirects to "edit profile"
*/

if (!isset($_SESSION['loggedIn']) || $_SESSION['loggedIn'] !== true) {
    header("Location: login.php");
    exit();
}
require_once "db_connect.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Common Ground - Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <?php include "header.php" ?>

    <!-- Main Content -->
    <div class="main-content" id="mainFeed">
        <!-- Popular Post Sidebar -->
        <aside class="sidebar">
            <?php include "popularsidebar.php"; ?>
            <br>
            <!-- Notification Alert Bar -->
            <div class="notification-box">
                7 new Notifications!
            </div>
        </aside>

        <!-- Main: View Profile -->
        <main class="userProfile">
            <h2 class="userProfile-header"><?php echo "@" . htmlspecialchars($_SESSION['username']); ?></h2>
            <div class="profile-header">
                <div class="user-avatar">
                    <img src="images/profile1.jpg" alt="User profile image" id="user-profile-img">
                </div>
                <div class="profileBio-content">
                    <div>
                        <div id="username-display"><b>Username: <?php echo htmlspecialchars($_SESSION['username']); ?></b></div>
                    </div>
                    <div>
                        <?php
                        $bio_sql = "SELECT bio FROM profile WHERE username = ?";
                        $bio_stmt = $conn->prepare($bio_sql);
                        $bio_stmt->bind_param("s", $_SESSION['username']);
                        $bio_stmt->execute();
                        $bio_result = $bio_stmt->get_result();

                        if ($bio_row = $bio_result->fetch_assoc()) {
                            echo "Bio: " . htmlspecialchars($bio_row['bio']);
                        } else {
                            echo "No bio found for this user.";
                        }

                        $bio_stmt->close();
                        ?>
                    </div>
                    <!-- Edit Profile Button -->
                    <div id="edit-profile-container">
                        <a href="editprofile.php">
                            <button class="edit-profile-btn">Edit Profile</button>
                        </a>
                    </div>
                    <div class="profile-tags">
                        <p><b>User Tags:</b></p>
                        <?php
                        $tags_sql = "SELECT t.name 
                                   FROM tags t
                                   JOIN profile_tags pt ON pt.id = t.id
                                   WHERE pt.username = ?";
                        $tags_stmt = $conn->prepare($tags_sql);
                        $tags_stmt->bind_param("s", $_SESSION['username']);
                        $tags_stmt->execute();
                        $tags_result = $tags_stmt->get_result();

                        $has_tags = false;
                        while ($tag = $tags_result->fetch_assoc()) {
                            $has_tags = true;
                            $tag_id = htmlspecialchars(strtolower($tag['name'])) . "-tag";
                            echo '<span class="tag" id="' . $tag_id . '">' . htmlspecialchars($tag['name']) . '</span>';
                        }

                        if (!$has_tags) {
                            echo "No tags yet selected";
                        }
                        $tags_stmt->close();
                        ?>
                    </div>
                </div>
            </div>
        </main>

        <!-- Profile Sidebar -->
        <aside class="profile-sidebar">
            <?php include "profilesidebar.php"; ?>
        </aside>
    </div>
</body>
</html>
<?php
// Close the database connection
$conn->close();
?>