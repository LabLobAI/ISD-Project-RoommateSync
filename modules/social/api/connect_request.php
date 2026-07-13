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

foreach (['sender_id', 'receiver_id'] as $key) {
    if (!isset($input[$key])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $key"]);
        exit;
    }
}

$sender = (int) $input['sender_id'];
$receiver = (int) $input['receiver_id'];
if ($sender <= 0 || $receiver <= 0 || $sender === $receiver) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user ids']);
    exit;
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

    $stmt = $db->prepare('SELECT status FROM connection_requests WHERE sender_id = :b AND receiver_id = :a LIMIT 1');
    $stmt->execute([':b' => $receiver, ':a' => $sender]);
    $row = $stmt->fetch();

    if (!$row) {
        try {
            $ins = $db->prepare('INSERT INTO connection_requests (sender_id, receiver_id, status) VALUES (:s, :r, "PENDING")');
            $ins->execute([':s' => $sender, ':r' => $receiver]);
            http_response_code(201);
            echo json_encode(['status' => 'PENDING']);
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                http_response_code(409);
                echo json_encode(['error' => 'Duplicate request']);
                exit;
            }
            throw $e;
        }
    }

    $status = $row['status'];
    if ($status === 'PENDING') {
        $upd = $db->prepare('UPDATE connection_requests SET status = "ACCEPTED" WHERE sender_id = :b AND receiver_id = :a');
        $upd->execute([':b' => $receiver, ':a' => $sender]);

        try {
            $ins2 = $db->prepare('INSERT INTO connection_requests (sender_id, receiver_id, status) VALUES (:s, :r, "ACCEPTED")');
            $ins2->execute([':s' => $sender, ':r' => $receiver]);
        } catch (PDOException $e) {
            // Ignore duplicate key error for the reverse entry
        }

        http_response_code(200);
        echo json_encode(['status' => 'ACCEPTED', 'message' => "It's a Match! Chat Unlocked"]);
        exit;
    }

    if ($status === 'ACCEPTED') {
        http_response_code(200);
        echo json_encode(['status' => 'ACCEPTED', 'message' => 'Already matched']);
        exit;
    }

    if ($status === 'REJECTED') {
        http_response_code(403);
        echo json_encode(['error' => 'Connection was rejected']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Unhandled state']);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}