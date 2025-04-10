<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include "sessions.php";
include "notifications.php";
include "db_connect.php";

$errorMessages = [
  'title' => '',
  'content' => '',
  'tags' => ''
];

$formValues = [
  'title' => '',
  'content' => '',
  'tags' => ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $formValues['title'] = trim($_POST['postTitle']);
  $formValues['content'] = trim($_POST['postContent']);
  $formValues['tags'] = trim($_POST['postTags']);
  $isValid = true;

  // Validate title length (from DB: varchar(50))
  if (strlen($formValues['title']) < 1|| strlen($formValues['title']) > 50) {
    $errorMessages['title'] = "Title must be between 1 and 50 characters.";
    $isValid = false;
  }

  // Validate content length (from DB: text)
  if (strlen($formValues['content']) < 20) {
    $errorMessages['content'] = "Content must be longer.";
    $isValid = false;
  }

  if (strlen($formValues['content']) > 65535) {
    $errorMessages['content'] = "Content is too long";
    $isValid = false;
  }

  // Validate tags
  if (!empty($formValues['tags'])) {
    $tagArray = array_map('trim', explode(',', $formValues['tags']));
    $tagArray = array_filter($tagArray); // Remove empty tags
    
    $placeholders = implode(',', array_fill(0, count($tagArray), '?'));
    $sql = "SELECT name FROM tags WHERE name IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($tagArray)), ...$tagArray);
    $stmt->execute();
    $result = $stmt->get_result();

    $validTags = [];
    while ($row = $result->fetch_assoc()) {
      $validTags[] = $row['name'];
    }

    $invalidTags = array_diff($tagArray, $validTags);
    if (!empty($invalidTags)) {
      $errorMessages['tags'] = "Invalid tags: " . implode(', ', $invalidTags);
      $isValid = false;
    }
    $stmt->close();
  }

  // If all validations pass, insert the post into the database
  if ($isValid) {
    $author = $_SESSION['username'];
    $sql = "INSERT INTO post (title, content, author, date) VALUES (?, ?, ?, CURRENT_DATE())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $formValues['title'], $formValues['content'], $author);

    if ($stmt->execute()) {
      $postId = $stmt->insert_id;

      // Insert tags into post_tags table
      if (!empty($validTags)) {
        $tagSql = "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)";
        $tagStmt = $conn->prepare($tagSql);

        foreach ($validTags as $tag) {
          $tagIdSql = "SELECT id FROM tags WHERE name = ?";
          $tagIdStmt = $conn->prepare($tagIdSql);
          $tagIdStmt->bind_param("s", $tag);
          $tagIdStmt->execute();
          $tagIdResult = $tagIdStmt->get_result();
          if ($tagIdRow = $tagIdResult->fetch_assoc()) {
            $tagId = $tagIdRow['id'];
            $tagStmt->bind_param("ii", $postId, $tagId);
            $tagStmt->execute();
          }
          $tagIdStmt->close();
        }

        $tagStmt->close();
      }

      $stmt->close();
      header("Location: post.php?id=$postId");
      exit();
    } else {
      $errorMessages['content'] = "Failed to upload post. Please try again.";
    }
  }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Common Ground - New Post</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>

  <?php include "header.php" ?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Popular Post Sidebar -->
    <aside class="sidebar">
      <?php include "popularsidebar.php"; ?>
      <!-- Notification Alert Bar -->
      <div class="notification-box">
        <a href="activity.php"><?php echo $_SESSION['notification_count']; ?> new Notifications</a>
      </div>
    </aside>

    <!-- New Post Section -->
    <main class="feed">
      <h2 class="feed-header">Create a New Post</h2>

      <form id="newPostForm" class="new-post-form" action="newpost.php" method="post">
        <div id="titleContent">
          <!-- Title -->
          <label for="postTitle">Title:</label>
          <input
            type="text"
            id="postTitle"
            name="postTitle"
            placeholder="Enter your post title..."
            value="<?php echo htmlspecialchars($formValues['title']); ?>"
            required>
          <?php if (!empty($errorMessages['title'])): ?>
            <div class="error-message"><?php echo $errorMessages['title']; ?></div>
          <?php endif; ?>
        </div>

        <div id="blogContent">
          <!-- Content -->
          <label for="postContent">Content:</label>
          <textarea
            id="postContent"
            name="postContent"
            rows="6"
            placeholder="Write your blog post here..."
            required><?php echo htmlspecialchars($formValues['content']); ?></textarea>
          <?php if (!empty($errorMessages['content'])): ?>
            <div class="error-message"><?php echo $errorMessages['content']; ?></div>
          <?php endif; ?>
        </div>

        <div id="tagContent">
          <!-- Tags -->
          <label for="postTags">Tags (comma-separated):</label>
          <input
            type="text"
            id="postTags"
            name="postTags"
            placeholder="e.g. travel, food"
            value="<?php echo htmlspecialchars($formValues['tags']); ?>"
          >
          <?php if (!empty($errorMessages['tags'])): ?>
            <div class="error-message"><?php echo $errorMessages['tags']; ?></div>
          <?php endif; ?>
        </div>

        <!-- Publish Button -->
        <button type="submit" id="submitBlog">Publish</button>
      </form>
    </main>
  </div>

  <!-- Very basic client-side validation -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const form = document.getElementById('newPostForm');
      form.addEventListener('submit', (e) => {
        const titleValue = document.getElementById('postTitle').value.trim();
        const contentValue = document.getElementById('postContent').value.trim();

        // Minimal check
        if (!titleValue || !contentValue) {
          e.preventDefault();
          alert('Please fill out both the title and content fields.');
        }
        // Add more checks when more fields added
      });
    });
  </script>
</body>

</html>