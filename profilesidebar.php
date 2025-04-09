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
<h2 class="profile-header">Profile:</h2>
            <div class="profile-card">
            <img src="getProfileImage.php" alt="Profile Image" id="#user-profile-img">
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
                                echo "Login/Sign-up to view your profile information";
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