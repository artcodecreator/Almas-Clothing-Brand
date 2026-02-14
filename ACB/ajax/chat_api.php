<?php
session_start();
include("../includes/db.php");

header('Content-Type: application/json');

// Check if user is logged in (either user or admin)
$user_id = $_SESSION['user_id'] ?? 0;
$admin_id = $_SESSION['admin_id'] ?? 0;

if (!$user_id && !$admin_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'send') {
    $message = trim($_POST['message'] ?? '');
    if (!$message) {
        echo json_encode(['status' => 'error', 'message' => 'Empty message']);
        exit;
    }

    if ($admin_id) {
        // Admin sending to a user
        $target_user_id = intval($_POST['user_id']);
        $sender = 'admin';
        $user_id = $target_user_id; // For the query
    } else {
        // User sending to admin
        $sender = 'user';
        // user_id is already set from session
    }

    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, sender, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $sender, $message);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }

} elseif ($action === 'fetch') {
    // If admin, we need to know which user's chat to fetch
    if ($admin_id) {
        $target_user_id = intval($_GET['user_id'] ?? 0);
        if (!$target_user_id) {
            echo json_encode(['status' => 'error', 'message' => 'User ID required']);
            exit;
        }
        $user_id = $target_user_id;
        
        // Mark user messages as read
        $conn->query("UPDATE chat_messages SET is_read = 1 WHERE user_id = $user_id AND sender = 'user'");
    }

    $last_id = intval($_GET['last_id'] ?? 0);
    
    $stmt = $conn->prepare("SELECT * FROM chat_messages WHERE user_id = ? AND id > ? ORDER BY id ASC");
    $stmt->bind_param("ii", $user_id, $last_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'messages' => $messages]);

} elseif ($action === 'list_users' && $admin_id) {
    // Admin only: List users with recent chats
    // Get distinct users who have chatted, ordered by latest message
    $sql = "
        SELECT u.id, u.name, u.profile_image,
        (SELECT message FROM chat_messages WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as last_message,
        (SELECT created_at FROM chat_messages WHERE user_id = u.id ORDER BY id DESC LIMIT 1) as last_time,
        (SELECT COUNT(*) FROM chat_messages WHERE user_id = u.id AND sender = 'user' AND is_read = 0) as unread_count
        FROM users u
        WHERE EXISTS (SELECT 1 FROM chat_messages WHERE user_id = u.id)
        ORDER BY last_time DESC
    ";
    
    $result = $conn->query($sql);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'users' => $users]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
?>
