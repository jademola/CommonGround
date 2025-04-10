<?php
include "sessions.php";

// ini_set('display_errors', 1);
// error_reporting(E_ALL);


if (isset($_GET['id'])) {
    $displayUser = $_GET['id'];
} else if (isset($_SESSION['username'])) {
    $displayUser = $_SESSION['username'];
} else {
    header("Location: login.php");
    exit();
}

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
            <!-- Notification Alert Bar -->
            <div class="notification-box">
                <a href="activity.php"><?php echo $_SESSION['notification_count']; ?> new Notifications</a>
            </div>
        </aside>

        <!-- Main: View Profile -->
        <main class="userProfile">
            <h2 class="userProfile-header"><?php echo "@" . $displayUser ?></h2>
            <div class="profile-header">
                <div class="user-avatar">
                    <?php
                    echo '<img src="getProfileImage.php?username=' . $displayUser . '" alt="Profile Image" id="profile-picture">';
                    ?>
                </div>
                <div class="profileBio-content">
                    <div>
                        <div id="username-display"><b>Username: <?php echo $displayUser ?></b></div>
                    </div>
                    <div>
                        <?php
                        $sql = "SELECT bio
                             FROM profile 
                             WHERE username = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $displayUser);  // "s" specifies the type (string)
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

                    <div class="profile-tags">
                        <p><b>User Tags:</b></p>


                        <?php
                        $sql = "SELECT tags.name, tags.id 
                        FROM tags JOIN profile_tags
                        ON  tags.id = profile_tags.id
                        WHERE username = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $displayUser);  // "s" specifies the type (string)
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while ($row = $result->fetch_assoc()) {
                            $tag_id = htmlspecialchars(strtolower($row["name"])) . "-tag";
                            echo '<span class="tag" id="' . $tag_id . '">' . htmlspecialchars($row["name"]) . '</span>';
                        }

                        if ($result->num_rows === 0) {
                            echo "No tags yet selected";
                        }


                        $stmt->close();
                        ?>
                    </div>

                    <!-- Edit Profile Button -->
                    <?php
                    if ($_SESSION['loggedIn'] = true && $displayUser == $_SESSION['username']) {
                        echo '<div id="profile-buttons">
                        <a href="editprofile.php">
                            <button class="edit-profile-btn">Edit Profile</button>
                        </a>
                        <a href="userHistory.php">
                            <button class="edit-profile-btn">Comment History</button>
                        </a>
                    </div>';
                    }
                    ?>
                </div>

            </div>

        </main>
    </div>

</body>

</html>