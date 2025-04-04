<?php
include "db_connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<!-- HTML for structuring list -->
<h2 class="sidebar-header">Activity:</h2>
<ul class='popular-list'>
<li id='popular-box-title'><strong>Popular:</strong></li>
<?php
// Top 3 Most Popular Post Query
$sql = "SELECT post.id, post.title, post.author, COUNT(like_id) AS total_likes
FROM post_likes JOIN post
ON post_likes.post_id = post.id
GROUP BY post.id
ORDER BY total_likes DESC
LIMIT 3";
$result = $conn->query($sql);
// Check if there are any rows returned
$count = 0;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){
        $post_id = $row['id'];
        $title = $row['title'];
        $author = $row['author'];
        $count = $count + 1;
        echo "<li class='popularPost'><a href=\"post.php?id=" . $post_id . "\"><strong>" . $count . ". " . $title . "</strong>- " . $author . "</a></li>";
    }
}
else {
    echo "No results found.";
}
?>
<!-- Close unordered list -->
</ul>