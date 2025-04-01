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
                        <option value="posts" selected>Choose Table</option>
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

        </main>
    </div>

</body>

</html>