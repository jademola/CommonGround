<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Common Ground - New Post</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <?php include "header.php"?>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Popular Post Sidebar -->
    <aside class="sidebar">
      <h2 class="sidebar-header">Popular Topics</h2>
      <ul class="popular-list">
        <li id="popular-box-title">Popular:</li>
          <li class="popularPost">1. The Secret to Building a Successful Morning Routine</li>
          <li class="popularPost">2. The Best Books You’ve Never Heard of: A Reading List for the Curious</li>
          <li class="popularPost">3. The Most Beautiful Places You’ve Never Seen: Iceland</li>
          </ul>
      <!-- Notifications Alert Bar -->
      <div class="notification-box">No new notifications</div>
    </aside>

    <!-- New Post Section -->
    <main class="feed">
      <h2 class="feed-header">Create a New Post</h2>
      
      <form id="newPostForm" class="new-post-form">
        <div id="titleContent">
          <!-- Title -->
          <label for="postTitle">Title:</label>
          <input 
            type="text" 
            id="postTitle" 
            name="postTitle" 
            placeholder="Enter your post title..."
            required
          >
        </div id=tit>

        <div id="blogContent">
          <!-- Content -->
          <label for="postContent">Content:</label>
          <textarea 
            id="postContent" 
            name="postContent" 
            rows="6"  
            placeholder="Write your blog post here..." 
            required
          ></textarea>
        </div>

        <div id="tagContent">
          <!-- Tags -->
          <label for="postTags">Tags (comma-separated):</label>
          <input 
            type="text" 
            id="postTags" 
            name="postTags" 
            placeholder="e.g. travel, food"
          >
        </div>

        <div id="uploadImage">
          <!-- Adding image -->
          <label for="postImage">Upload Image (optional):</label>
          <input 
            type="file" 
            id="postImage" 
            name="postImage"
            accept="image/*"
          >
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
