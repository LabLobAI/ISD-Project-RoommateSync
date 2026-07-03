<?php
// send_message.php
// Expects JSON POST: { sender_id, receiver_id, message_text }
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$required = ['sender_id','receiver_id','message_text'];
foreach ($required as $k) {
    if (!isset($input[$k])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $k"]);
        exit;
    }
}

$sender = (int)$input['sender_id'];
$receiver = (int)$input['receiver_id'];
$text = trim((string)$input['message_text']);
if ($text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Empty message']);
    exit;
}
if (mb_strlen($text) > 1000) {
    $text = mb_substr($text, 0, 1000);
}

try {
    $db = get_db();

    // Verify ACCEPTED connection exists
    $stmt = $db->prepare("SELECT 1 FROM connection_requests WHERE status = 'ACCEPTED' AND ((sender_id = :a AND receiver_id = :b) OR (sender_id = :b AND receiver_id = :a)) LIMIT 1");
    $stmt->execute([':a' => $sender, ':b' => $receiver]);
    $exists = $stmt->fetchColumn();
    if (!$exists) {
        http_response_code(403);
        echo json_encode(['error' => 'Chat Locked']);
        exit;
    }

    // Sanitize the message to avoid HTML/script injection when rendered
    $safe = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $ins = $db->prepare("INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (:s, :r, :t)");
    $ins->execute([':s' => $sender, ':r' => $receiver, ':t' => $safe]);

    $id = (int)$db->lastInsertId();
    http_response_code(201);
    echo json_encode(['success' => true, 'message_id' => $id, 'sent_at' => date('c')]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}
