<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../core/database.php';
require_once __DIR__ . '/../../../core/auth.php';

$a = isset($_GET['user_a']) ? (int) $_GET['user_a'] : 0;
$b = isset($_GET['user_b']) ? (int) $_GET['user_b'] : 0;
$since = isset($_GET['since_id']) ? (int) $_GET['since_id'] : 0;

if ($a <= 0 || $b <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid user_a/user_b']);
    exit;
}

// Verify the current authenticated user is one of the participants
$currentUser = auth_user();
if (!$currentUser || ((int) $currentUser['id'] !== $a && (int) $currentUser['id'] !== $b)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: must be a participant in this conversation']);
    exit;
}

try {
    $db = db();

    $stmt = $db->prepare("SELECT 1 FROM connection_requests WHERE status = 'ACCEPTED' AND ((sender_id = :a AND receiver_id = :b) OR (sender_id = :b AND receiver_id = :a)) LIMIT 1");
    $stmt->execute([':a' => $a, ':b' => $b]);
    if (!$stmt->fetchColumn()) {
        http_response_code(403);
        echo json_encode(['error' => 'Chat Locked']);
        exit;
    }

    $sql = 'SELECT message_id, sender_id, receiver_id, message_text, sent_at FROM messages WHERE ((sender_id = :a AND receiver_id = :b) OR (sender_id = :b AND receiver_id = :a))';
    if ($since > 0) {
        $sql .= ' AND message_id > :since';
    }
    $sql .= ' ORDER BY sent_at ASC';

    $query = $db->prepare($sql);
    $params = [':a' => $a, ':b' => $b];
    if ($since > 0) {
        $params[':since'] = $since;
    }
    $query->execute($params);

    echo json_encode(['messages' => $query->fetchAll()]);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}