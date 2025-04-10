<?php
// Handle AJAX requests
if (isset($_POST['action']) && $_POST['action'] === 'getNotificationCount') {
    header('Content-Type: application/json');
    
    include "sessions.php";
    require_once 'db_connect.php';
    
    if (!isset($_SESSION['username'])) {
        echo json_encode(['success' => false, 'count' => 0]);
        exit;
    }

    try {
        $sql = "SELECT 
                (SELECT COUNT(*) 
                 FROM post_likes pl 
                 JOIN post p ON p.id = pl.post_id 
                 WHERE p.author = ?) +
                (SELECT COUNT(*) 
                 FROM comments c 
                 JOIN post p ON p.id = c.post_id 
                 WHERE p.author = ?) as total_notifications";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $_SESSION['username'], $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $_SESSION['notification_count'] = $row['total_notifications'];
        
        echo json_encode([
            'success' => true, 
            'count' => $row['total_notifications']
        ]);
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit;
}

// Regular page load - initialize notifications
if (isset($_SESSION['username'])) {
    // Initial count set to 0 - will be updated by AJAX
    $_SESSION['notification_count'] = $_SESSION['notification_count'] ?? 0;
} else {
    $_SESSION['notification_count'] = 0;
}
?>

<script>
function updateNotificationCount() {
    const formData = new FormData();
    formData.append('action', 'getNotificationCount');

    fetch('notifications.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationElements = document.querySelectorAll('.notification-box a');
            notificationElements.forEach(element => {
                element.textContent = data.count + ' new Notifications';
            });
        }
    })
    .catch(error => console.error('Error:', error));
}

// Update notifications every 30 seconds
setInterval(updateNotificationCount, 30000);

// Initial update
document.addEventListener('DOMContentLoaded', updateNotificationCount);
</script>
