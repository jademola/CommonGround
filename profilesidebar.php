<?php 
/*
<aside class="profile-sidebar">
            <h2 class="profile-header">Profile:</h2>
            <div class="profile-card">
                <img src="images/icon.png" alt="">
                <div class="profile-username">
                    <?php echo isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"]) : "Guest"; ?>
                </div>
                <div class="profile-bio">
                    <?php
                    if (isset($_SESSION["username"])) {
                        $user_bio_sql = "SELECT bio FROM profile WHERE username = ?";
                        $user_bio_stmt = $conn->prepare($user_bio_sql);
                        $user_bio_stmt->bind_param("s", $_SESSION["username"]);
                        $user_bio_stmt->execute();
                        $user_bio_result = $user_bio_stmt->get_result();
                        if ($user_bio_row = $user_bio_result->fetch_assoc()) {
                            echo htmlspecialchars($user_bio_row["bio"]);
                        } else {
                            echo "No bio available.";
                        }
                        $user_bio_stmt->close();
                    } else {
                        echo "Please log in to view your profile.";
                    }
                    ?>
                </div>
                <div class="profile-tags">
                    <div><b>Tags:</b></div>
                    <div>
                        <?php
                        // Fetch user tags if user is logged in
                        if (isset($_SESSION["username"])) {
                            $user_tags_sql = "SELECT t.name FROM tags t 
                                             JOIN user_tags ut ON t.id = ut.tag_id 
                                             JOIN userInfo u ON ut.user_id = u.id 
                                             WHERE u.username = ?";
                            $user_tags_stmt = $conn->prepare($user_tags_sql);
                            $user_tags_stmt->bind_param("s", $_SESSION["username"]);
                            $user_tags_stmt->execute();
                            $user_tags_result = $user_tags_stmt->get_result();

                            while ($tag = $user_tags_result->fetch_assoc()) {
                                $tag_id = htmlspecialchars(strtolower($tag["name"])) . "-tag";
                                echo '<span class="tag" id="' . $tag_id . '">' . htmlspecialchars($tag["name"]) . '</span>';
                            }
                            $user_tags_stmt->close();
                        } else {
                            // Show some default tags for guests
                            echo '<span class="tag" id="sports-tag">Sports</span>';
                            echo '<span class="tag" id="food-tag">Food</span>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </aside>
    </div>
*/

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
<h2 class="profile-header">Profile:</h2>
            <div class="profile-card">
                <img src="images/icon.png" alt="">
                <div class="profile-username"><?php echo $_SESSION['username'] ?></div>
                <div class="profile-bio">
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
                                echo "Bio: " . $row['bio'];
                            } else {
                                echo "No bio found for this user.";
                            }

                            $stmt->close();
                        ?>
                </div>
                <?php if ($_SESSION['loggedIn']): ?>
                    <div class="side-profile-tags">
                        <div><b>Tags:</b></div>
                        <div id="side-profile-tags-whitespace">
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
                <?php else: ?>
                    <div class="profile-buttons">
                        <button onclick="window.location.href='login.php'">Login</button>
                        <button onclick="window.location.href='signup.php'">Sign-up</button>
                    </div>
                <?php endif; ?>
            </div>
    </body>