<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../core/database.php';
require_once __DIR__ . '/../../../core/auth.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

foreach (['sender_id', 'receiver_id', 'message_text'] as $key) {
    if (!isset($input[$key])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $key"]);
        exit;
    }
}

$sender = (int) $input['sender_id'];
$receiver = (int) $input['receiver_id'];
$text = trim((string) $input['message_text']);

if ($text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Empty message']);
    exit;
}

if (mb_strlen($text) > 1000) {
    $text = mb_substr($text, 0, 1000);
}

// Verify the current authenticated user matches sender_id
$currentUser = auth_user();
if (!$currentUser || (int) $currentUser['id'] !== $sender) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: sender_id must match current user']);
    exit;
}

try {
    $db = db();

    $stmt = $db->prepare("SELECT 1 FROM connection_requests WHERE status = 'ACCEPTED' AND ((sender_id = :a AND receiver_id = :b) OR (sender_id = :b AND receiver_id = :a)) LIMIT 1");
    $stmt->execute([':a' => $sender, ':b' => $receiver]);
    if (!$stmt->fetchColumn()) {
        http_response_code(403);
        echo json_encode(['error' => 'Chat Locked']);
        exit;
    }

    $safe = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $ins = $db->prepare('INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (:s, :r, :t)');
    $ins->execute([':s' => $sender, ':r' => $receiver, ':t' => $safe]);

    http_response_code(201);
    echo json_encode(['success' => true, 'message_id' => (int) $db->lastInsertId(), 'sent_at' => date('c')]);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}