<?php
require_once 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unaweza tu kuona chat ukiwa umeingia.']);
    exit;
}

$userId = $_SESSION['user_id'];
$conn = db_connect();

$messages = [];
$stmt = $conn->prepare('SELECT sender, content FROM messages WHERE user_id = ? ORDER BY created_at ASC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($sender, $content);
while ($stmt->fetch()) {
    $messages[] = ['sender' => $sender, 'content' => $content];
}
$stmt->close();

$unreadCount = 0;
$stmt2 = $conn->prepare('SELECT COUNT(*) FROM messages WHERE user_id = ? AND sender = "admin" AND is_read_by_user = 0');
$stmt2->bind_param('i', $userId);
$stmt2->execute();
$stmt2->bind_result($unreadCount);
$stmt2->fetch();
$stmt2->close();

$conn->close();

echo json_encode(['success' => true, 'messages' => $messages, 'unreadCount' => $unreadCount]);
