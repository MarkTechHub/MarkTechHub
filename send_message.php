<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unaweza tu kutuma ujumbe ukiwa umeingia.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$content = trim($payload['content'] ?? '');

if ($content === '') {
    echo json_encode(['success' => false, 'error' => 'Ujumbe hauwezi kuwa tupu.']);
    exit;
}

$conn = db_connect();
$userId = $_SESSION['user_id'];

$stmt = $conn->prepare('INSERT INTO messages (user_id, sender, content) VALUES (?, "user", ?)');
$stmt->bind_param('is', $userId, $content);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    $userEmail = $_SESSION['user_email'];
    $subject = 'Ujumbe mpya wa chat kutoka ' . $userEmail;
    $body = "Imepokelewa ujumbe mpya katika chat yako kutoka $userEmail: <br><br>" . nl2br(htmlspecialchars($content));
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: chat-notification@marktechhub.com\r\n";

    mail('markmartinmwaitolola@gmail.com', $subject, $body, $headers);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Hakuna taarifa ya kutuma.']);
}
$conn->close();
