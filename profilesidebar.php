<?php
/*
Helper class for profile/edit profile:

*/
// Session start and DB start handled in main file
// Just in case
if (!isset($conn)) {
    include_once "db_connect.php";
}
?>

<h2 class="profile-header">Profile:</h2>
<div class="profile-card">
    <img src="images/icon.png" alt="Profile icon">
    <div class="profile-username">
        <?php echo isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"]) : "Guest"; ?>
    </div>
    <div class="profile-bio">
        <?php
        if (isset($_SESSION["username"])) {
            $bio_sql = "SELECT bio FROM profile WHERE username = ?";
            $bio_stmt = $conn->prepare($bio_sql);
            $bio_stmt->bind_param("s", $_SESSION["username"]);
            $bio_stmt->execute();
            $bio_result = $bio_stmt->get_result();
            
            if ($bio_row = $bio_result->fetch_assoc()) {
                echo htmlspecialchars($bio_row["bio"]);
            } else {
                echo "No bio found for this user.";
            }
            $bio_stmt->close();
        } else {
            echo "Please log in to view your profile.";
        }
        ?>
    </div>
    <?php if (isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] === true): ?>
        <div class="side-profile-tags">
            <div><b>Tags:</b></div>
            <div id="side-profile-tags-whitespace">
                <?php
                $tags_sql = "SELECT t.name 
                           FROM tags t
                           JOIN profile_tags pt ON pt.id = t.id
                           WHERE pt.username = ?";
                $tags_stmt = $conn->prepare($tags_sql);
                $tags_stmt->bind_param("s", $_SESSION["username"]);
                $tags_stmt->execute();
                $tags_result = $tags_stmt->get_result();

                $has_tags = false;
                while ($tag = $tags_result->fetch_assoc()) {
                    $has_tags = true;
                    $tag_id = htmlspecialchars(strtolower($tag["name"])) . "-tag";
                    echo '<span class="tag" id="' . $tag_id . '">' . htmlspecialchars($tag["name"]) . '</span>';
                }
                
                if (!$has_tags) {
                    echo "No tags yet selected";
                }
                $tags_stmt->close();
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