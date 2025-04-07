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
    <link rel="stylesheet" href = "styles.css">
    <?php include "db_connect.php"; ?>

    <style> 
    .authorLink {
    color: black;
    }
    </style>
</head>

<body>

<?php include "header.php" ?>

    <!-- Main Content -->     
    <div class="main-content" id="mainFeed">

    <!-- Popular sidebar --> 
    <aside class="sidebar">
           <?php include "popularsidebar.php"; ?> 
        </br>
            <!-- Notification Alert Bar -->
            <div class="notification-box">
                                    <a href="activity.php"><?php echo $_SESSION['notification_count']; ?> new Notifications</a>

            </div>
        </aside>

        <!-- Main: Search -->
        <main class="feed">

        <!-- Search-Bar -->
        <div class="main-search">
                <form id="searchBox" action="search.php" method="post">
                    <input type="text" id="filteredSearch" name="filteredSearch" placeholder="Search by Author, Title or Tags">

                    <select name="searchType" id="searchType">
                        <option value="users" selected>Choose Table</option>
                        <option value="posts">Posts</option>
                        <option value="users">Author</option>
                        <option value="tags">Tags</option>
                    </select>

                    <input
                        type="image"
                        value="submit"
                        name="submitted"
                        id="searchButton"
                        alt="Search Button"
                        src="images/search_icon.png" />
                </form>
            </div>

            <!-- Search Results --> 

            <!-- Results title --> 
            <div class="Search-result-listing">
                <?php
                if (isset($_POST['searchType'])) {
                    echo "<h3 class=tableTitle>" . htmlspecialchars($_POST['searchType']) . "</h3>";
                } else {
                    echo "<h3 class=tableTitle>Author</h3>";
                }
                ?> 

                <!-- Resulting post(s) --> 
                <?php
                    $searchTerm = isset($_POST['filteredSearch']) ? $_POST['filteredSearch'] : '';

                    // While author (Default) search is selected 
                    if (!isset($_POST['searchType']) || $_POST['searchType'] === 'users') {
                        $sql = "SELECT u.username, p.title, p.id, p.date 
                            FROM post p 
                            JOIN userInfo u ON u.username = p.author";
                    if (!empty($searchTerm)) {
                        $sql .= " WHERE u.username LIKE ?";
                    }
                    $sql .= " ORDER BY u.username ASC";

                    $stmt = $conn->prepare($sql);
                    if (!empty($searchTerm)) {
                        $searchPattern = "%$searchTerm%";
                        $stmt->bind_param("s", $searchPattern);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
            ?>
                        <div class="post">
                        <div class="post-header">
                            <div class="user-info">
                            <a class= "authorLink" href="profile.php?id=<?php echo $row['username']; ?>"><b><?php echo $row['username']?></b></a></br>
                            <a class= "authorLink" href="post.php?id=<?php echo $row['id']; ?>"><b>Title: </b><?php echo $row['title']?></a>
                                <div class="post-tags">
                                <?php
                                    // Fetch tags for this post
                                    $tag_sql = "SELECT t.name, t.id FROM tags t 
                                        JOIN post_tags pt ON t.id = pt.tag_id 
                                        WHERE pt.post_id = ?";
                                    $tag_stmt = $conn->prepare($tag_sql);
                                    $tag_stmt->bind_param("i", $row["id"]);
                                    $tag_stmt->execute();
                                    $tag_result = $tag_stmt->get_result();

                                    while ($tag = $tag_result->fetch_assoc()) {
                                        $tag_id = htmlspecialchars(strtolower($tag["name"])) . "-tag";
                                        echo '<span class="tag" id="' . $tag_id . '">' . htmlspecialchars($tag["name"]) . '</span>';
                                    }
                                    $tag_stmt->close();
                                    ?>
                                </div>
                            </div>
                            <div class="timestamp"> <?php echo $row["date"] ?></div>
                        </div>
                        </div>
                <?php
                    }
                }

                // While tags search is selected 
                if ($_POST['searchType'] === 'tags') {
                    $sql = "SELECT p.author, p.title, p.id, p.date 
                            FROM post p 
                            JOIN post_tags pt ON pt.post_id = p.id
                            JOIN tags t ON t.id = pt.tag_id";
                if (!empty($searchTerm)) {
                    $sql .= " WHERE t.name LIKE ?";
                }
                $sql .= " ORDER BY t.name ASC";

                $stmt = $conn->prepare($sql);
                if (!empty($searchTerm)) {
                    $searchPattern = "%$searchTerm%";
                    $stmt->bind_param("s", $searchPattern);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    ?>
                                <div class="post">
                                <div class="post-header">
                                    <div class="user-info">
                                    <a class= "authorLink" href="profile.php?id=<?php echo $row['author']; ?>"><b><?php echo $row['author']?></b></a></br>
                                    <a class= "authorLink" href="post.php?id=<?php echo $row['id']; ?>"><b>Title: </b><?php echo $row['title']?></a>
                                        <div class="post-tags">
                                        <?php
                                            // Fetch tags for this post
                                            $tag_sql = "SELECT t.name, t.id FROM tags t 
                                                JOIN post_tags pt ON t.id = pt.tag_id 
                                                WHERE pt.post_id = ?";
                                            $tag_stmt = $conn->prepare($tag_sql);
                                            $tag_stmt->bind_param("i", $row["id"]);
                                            $tag_stmt->execute();
                                            $tag_result = $tag_stmt->get_result();
        
                                            while ($tag = $tag_result->fetch_assoc()) {
                                                $tag_id = htmlspecialchars(strtolower($tag["name"])) . "-tag";
                                                echo '<span class="tag" id="' . $tag_id . '">' . htmlspecialchars($tag["name"]) . '</span>';
                                            }
                                            $tag_stmt->close();
                                            ?>
                                        </div>
                                    </div>
                                    <div class="timestamp"> <?php echo $row["date"] ?></div>
                                </div>
                                </div>
                        <?php
                            }
                        }

                        if ($_POST['searchType'] === 'posts') {
                            $sql = "SELECT u.username, p.title, p.id, p.date 
                                    FROM post p 
                                    JOIN userInfo u ON u.username = p.author";
                        if (!empty($searchTerm)) {
                            $sql .= " WHERE p.title LIKE ?";
                        }
                        $sql .= " ORDER BY p.title ASC";
        
                        $stmt = $conn->prepare($sql);
                        if (!empty($searchTerm)) {
                            $searchPattern = "%$searchTerm%";
                            $stmt->bind_param("s", $searchPattern);
                        }
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            ?>
                                        <div class="post">
                                        <div class="post-header">
                                            <div class="user-info">
                                            <a class= "authorLink" href="profile.php?id=<?php echo $row['username']; ?>"><b><?php echo $row['username']?></b></a></br>
                                            <a class= "authorLink" href="post.php?id=<?php echo $row['id']; ?>"><b>Title: </b><?php echo $row['title']?></a>
                                                <div class="post-tags">
                                                <?php
                                                    // Fetch tags for this post
                                                    $tag_sql = "SELECT t.name, t.id FROM tags t 
                                                        JOIN post_tags pt ON t.id = pt.tag_id 
                                                        WHERE pt.post_id = ?";
                                                    $tag_stmt = $conn->prepare($tag_sql);
                                                    $tag_stmt->bind_param("i", $row["id"]);
                                                    $tag_stmt->execute();
                                                    $tag_result = $tag_stmt->get_result();
                
                                                    while ($tag = $tag_result->fetch_assoc()) {
                                                        $tag_id = htmlspecialchars(strtolower($tag["name"])) . "-tag";
                                                        echo '<span class="tag" id="' . $tag_id . '">' . htmlspecialchars($tag["name"]) . '</span>';
                                                    }
                                                    $tag_stmt->close();
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="timestamp"> <?php echo $row["date"] ?></div>
                                        </div>
                                        </div>
                                <?php
                                    }
                                }
                ?>

                

        </div> 
        </main>
        
        <aside class="profile-sidebar">
            <?php include "profilesidebar.php"; ?>
        </aside>
    </div>
</body>
</html>