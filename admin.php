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
    <?php require_once "db_connect.php";
    $sql = "SELECT userType FROM userInfo WHERE username = ?";
    $admin = "admin";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);  // "s" specifies the type (string)
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any rows were returned
    while ($row = $result->fetch_assoc()) {
        if ($row['adminType'] !== $admin) {
            echo "Unauthorized user";
            header("Location: index.php");
            exit();
        }
    }
    $stmt->close();
    ?>
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
            <?php include "admin_sidebar.php"; ?>
            </br>
            <!-- Notification Alert Bar -->
            <div class="notification-box">
                7 new Notifications!
            </div>
        </aside>

        <!-- Main: View Profile -->
        <main class="adminDashboard">
            <h2 class="adminDashboard-header">Administrator Dashboard</h2>
            <div class="admin-search">
                <form id="searchBox" action="admin.php" method="post">
                    <input type="text" id="filteredSearch" name="filteredSearch" placeholder="Search comments, users or posts">

                    <select name="searchType" id="searchType">
                        <option value="users" selected>Choose Table</option>
                        <option value="posts">Posts</option>
                        <option value="users">Users</option>
                        <option value="comments">Comments</option>
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

            <div class="adminTable">
                <?php
                if (isset($_POST['searchType'])) {
                    echo "<h3 class=tableTitle>" . htmlspecialchars($_POST['searchType']) . "</h3>";
                } else {
                    echo "<h3 class=tableTitle >USERS</h3>";
                }
                ?>
                <table>
                    <thead>
                        <tr>
                            <?php
                            if (!isset($_POST['searchType']) || $_POST['searchType'] === 'users') {
                                echo '<th>Username</th>';
                                echo '<th>Email</th>';
                                echo '<th># of Posts</th>';
                            } elseif ($_POST['searchType'] === 'posts') {
                                echo '<th>Date</th>';
                                echo '<th>Post ID</th>';
                                echo '<th>Title</th>';
                                echo '<th># of Comments</th>';
                                echo '<th># of Likes</th>';
                                echo '<th>Username</th>';
                            } elseif ($_POST['searchType'] === 'comments') {
                                echo '<th>Date</th>';
                                echo '<th>Post ID</th>';
                                echo '<th>Title</th>';
                                echo '<th>Content</th>';
                                echo '<th>Author</th>';
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $searchTerm = isset($_POST['filteredSearch']) ? $_POST['filteredSearch'] : '';

                        if (!isset($_POST['searchType']) || $_POST['searchType'] === 'users') {
                            $sql = "SELECT u.username, u.email, COUNT(DISTINCT p.id) as post_count 
                                   FROM userInfo u 
                                   LEFT JOIN post p ON u.username = p.author";
                            if (!empty($searchTerm)) {
                                $sql .= " WHERE u.username LIKE ? OR u.email LIKE ?";
                            }
                            $sql .= " GROUP BY u.username ORDER BY post_count DESC";

                            $stmt = $conn->prepare($sql);
                            if (!empty($searchTerm)) {
                                $searchPattern = "%$searchTerm%";
                                $stmt->bind_param("ss", $searchPattern, $searchPattern);
                            }
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while ($row = $result->fetch_assoc()) {
                                echo "<tr id= \"adminRow\">";
                                echo "<td>" . $row['username'] . "</td>";
                                echo "<td>" . $row['email'] . "</td>";
                                echo "<td>" . $row['post_count'] . "</td>";
                                echo "</tr>";
                            }
                            $stmt->close();
                        } elseif ($_POST['searchType'] === 'posts') {
                            $sql = "SELECT p.id, p.title, p.date, p.author,
                                   COUNT(DISTINCT c.id) as comment_count,
                                   COUNT(DISTINCT pl.like_id) as like_count
                                   FROM post p 
                                   LEFT JOIN comments c ON p.id = c.post_id
                                   LEFT JOIN post_likes pl ON p.id = pl.post_id";
                            if (!empty($searchTerm)) {
                                $sql .= " WHERE p.title LIKE ? OR p.author LIKE ?";
                            }
                            $sql .= " GROUP BY p.id ORDER BY p.date DESC, p.id ASC";

                            $stmt = $conn->prepare($sql);
                            if (!empty($searchTerm)) {
                                $searchPattern = "%$searchTerm%";
                                $stmt->bind_param("ss", $searchPattern, $searchPattern);
                            }
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while ($row = $result->fetch_assoc()) {
                                echo "<tr id= \"adminRow\">";
                                echo "<td>" . $row['date'] . "</td>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['title'] . "</td>";
                                echo "<td>" . $row['comment_count'] . "</td>";
                                echo "<td>" . $row['like_count'] . "</td>";
                                echo "<td>" . $row['author'] . "</td>";
                                echo "</tr>";
                            }
                            $stmt->close();
                        } elseif ($_POST['searchType'] === 'comments') {
                            $sql = "SELECT c.id, p.title, c.content, c.author, c.date 
                                   FROM comments c
                                   JOIN post p ON c.post_id = p.id";
                            if (!empty($searchTerm)) {
                                $sql .= " WHERE c.content LIKE ? OR c.author LIKE ? OR p.title LIKE ?";
                            }

                            $stmt = $conn->prepare($sql);
                            if (!empty($searchTerm)) {
                                $searchPattern = "%$searchTerm%";
                                $stmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
                            }
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while ($row = $result->fetch_assoc()) {
                                echo "<tr id= \"adminRow\">";
                                echo "<td>" . $row['date'] . "</td>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['title'] . "</td>";
                                echo "<td>" . $row['content'] . "</td>";
                                echo "<td>" . $row['author'] . "</td>";
                                echo "</tr>";
                            }
                            $stmt->close();
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>

</body>

</html>