<?php
include "db_connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<!-- HTML for structuring list -->
<ul class='popular-list'>
<li id='popular-box-title'><strong>STATISTICS:</strong></li>
<?php
// Get User Count
$sql = "SELECT COUNT(*) AS userCount FROM userInfo";
$result = $conn->query($sql);
// Check if there are any rows returned
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
        $userCount = $row['userCount'];
        echo "<li class='popularPost'>Total Registered Users: $userCount</li>";
    }
}
else {
    echo "No results found.";
}

// Get Post Count
$sql = "SELECT COUNT(*) AS postCount FROM post";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
        $postCount = $row['postCount'];
        echo "<li class='popularPost'>Total Posts: $postCount</li>";
    }
}

// Get Average Interactions
$sql = "SELECT 
    ROUND(AVG(likes + comments), 1) AS avgInteractions 
    FROM (
        SELECT 
            p.id,
            COUNT(DISTINCT pl.like_id) as likes,
            COUNT(DISTINCT c.id) as comments
        FROM post p
        LEFT JOIN post_likes pl ON p.id = pl.post_id
        LEFT JOIN comments c ON p.id = c.post_id
        GROUP BY p.id
    ) as interactions";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
        $avgInteractions = $row['avgInteractions'];
        echo "<li class='popularPost'>Average Interactions Per Post: $avgInteractions</li>";
    }
}
?>
<!-- Close unordered list -->
</ul>