<?php

// Connect to the database if not already connected
if (!isset($conn)) {
    require_once "db_connect.php";
}
?>

<div class="sidebar-section">
    <h2 class="sidebar-header">Activity:</h2>
    <ul class='popular-list'>
        <li id='popular-box-title'><strong>Popular:</strong></li>
        <?php
        // Top 3 Most Popular Post Query
        $sql = "SELECT p.id, p.title, p.author, COUNT(pl.like_id) AS total_likes 
                FROM post p
                JOIN post_likes pl ON p.id = pl.post_id 
                GROUP BY p.id, p.title, p.author
                ORDER BY total_likes DESC 
                LIMIT 3";
                
        $result = $conn->query($sql);

        // Check if there are any rows returned
        $count = 0;
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $post_id = $row['id'];
                $title = htmlspecialchars($row['title']);
                $author = htmlspecialchars($row['author']);
                $total_likes = $row['total_likes'];
                $count++;
                
                echo "<li class='popularPost'>";
                echo "<a href='post.php?id=" . $post_id . "'>";
                echo "<strong>" . $count . ". " . $title . "</strong> - " . $author;
                echo "</a>";
                echo "<span class='like-count'>(" . $total_likes . " likes)</span>";
                echo "</li>";
            }
        } else {
            // If no popular posts are found, show recent posts instead
            $recent_sql = "SELECT id, title, author FROM post ORDER BY date DESC LIMIT 3";
            $recent_result = $conn->query($recent_sql);
            
            if ($recent_result && $recent_result->num_rows > 0) {
                echo "<li><em>Most recent posts:</em></li>";
                
                while ($row = $recent_result->fetch_assoc()) {
                    $post_id = $row['id'];
                    $title = htmlspecialchars($row['title']);
                    $author = htmlspecialchars($row['author']);
                    $count++;
                    
                    echo "<li class='popularPost'>";
                    echo "<a href='post.php?id=" . $post_id . "'>";
                    echo "<strong>" . $count . ". " . $title . "</strong> - " . $author;
                    echo "</a>";
                    echo "</li>";
                }
            } else {
                echo "<li>No posts found.</li>";
            }
        }
        ?>
    </ul>
</div>