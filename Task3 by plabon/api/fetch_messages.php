<?php
// fetch_messages.php?user_a=1&user_b=2&since_id=0
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$a = isset($_GET['user_a']) ? (int)$_GET['user_a'] : 0;
$b = isset($_GET['user_b']) ? (int)$_GET['user_b'] : 0;
$since = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;
if ($a <= 0 || $b <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid user_a/user_b']);
    exit;
}

try {
    $db = get_db();

    // Verify ACCEPTED connection exists between users
    $stmt = $db->prepare("SELECT 1 FROM connection_requests WHERE status = 'ACCEPTED' AND ((sender_id = :a AND receiver_id = :b) OR (sender_id = :b AND receiver_id = :a)) LIMIT 1");
    $stmt->execute([':a' => $a, ':b' => $b]);
    $exists = $stmt->fetchColumn();
    if (!$exists) {
        http_response_code(403);
        echo json_encode(['error' => 'Chat Locked']);
        exit;
    }

    $sql = 'SELECT message_id, sender_id, receiver_id, message_text, sent_at FROM messages WHERE ((sender_id = :a AND receiver_id = :b) OR (sender_id = :b AND receiver_id = :a))';
    if ($since > 0) {
        $sql .= ' AND message_id > :since';
    }
    $sql .= ' ORDER BY sent_at ASC';

    $q = $db->prepare($sql);
    $params = [':a' => $a, ':b' => $b];
    if ($since > 0) $params[':since'] = $since;
    $q->execute($params);
    $messages = $q->fetchAll();

    echo json_encode(['messages' => $messages]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}
