<?php
// connect_request.php
// Expects JSON POST: { sender_id, receiver_id }
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

foreach (['sender_id','receiver_id'] as $k) {
    if (!isset($input[$k])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $k"]);
        exit;
    }
}

$sender = (int)$input['sender_id'];
$receiver = (int)$input['receiver_id'];
if ($sender <= 0 || $receiver <= 0 || $sender === $receiver) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user ids']);
    exit;
}

try {
    $db = get_db();

    // Check if the reverse request exists (user B -> user A)
    $stmt = $db->prepare('SELECT status FROM connection_requests WHERE sender_id = :b AND receiver_id = :a LIMIT 1');
    $stmt->execute([':b' => $receiver, ':a' => $sender]);
    $row = $stmt->fetch();

    if (!$row) {
        // No reverse row: create a PENDING request (sender -> receiver)
        try {
            $ins = $db->prepare('INSERT INTO connection_requests (sender_id, receiver_id, status) VALUES (:s, :r, "PENDING")');
            $ins->execute([':s' => $sender, ':r' => $receiver]);
            http_response_code(201);
            echo json_encode(['status' => 'PENDING']);
            exit;
        } catch (PDOException $e) {
            // Handle potential unique constraint race
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
        // The other user already requested us — accept and update that row
        $upd = $db->prepare('UPDATE connection_requests SET status = "ACCEPTED" WHERE sender_id = :b AND receiver_id = :a');
        $upd->execute([':b' => $receiver, ':a' => $sender]);

        // Optionally insert a reciprocal accepted row for clarity (not strictly required)
        try {
            $ins2 = $db->prepare('INSERT INTO connection_requests (sender_id, receiver_id, status) VALUES (:s, :r, "ACCEPTED")');
            $ins2->execute([':s' => $sender, ':r' => $receiver]);
        } catch (PDOException $e) {
            // ignore duplicate key if already inserted concurrently
        }

        // Trigger placeholder: system notification could be emitted here
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

    // Fallback
    http_response_code(400);
    echo json_encode(['error' => 'Unhandled state']);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}
