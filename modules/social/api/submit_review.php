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

$required = ['reviewer_id', 'reviewee_id', 'cleanliness_score', 'communication_score'];
foreach ($required as $key) {
    if (!isset($input[$key])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $key"]);
        exit;
    }
}

$reviewer = (int) $input['reviewer_id'];
$reviewee = (int) $input['reviewee_id'];
$clean = (int) $input['cleanliness_score'];
$comm = (int) $input['communication_score'];
$feedback = isset($input['written_feedback']) ? substr((string) $input['written_feedback'], 0, 1000) : null;

if ($clean < 1 || $clean > 5 || $comm < 1 || $comm > 5) {
    http_response_code(400);
    echo json_encode(['error' => 'Scores must be between 1 and 5']);
    exit;
}

// Verify the current authenticated user matches reviewer_id
$currentUser = auth_user();
if (!$currentUser || (int) $currentUser['id'] !== $reviewer) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: reviewer_id must match current user']);
    exit;
}

// Check if users have an accepted connection (double opt-in)
try {
    $db = db();

    $stmt = $db->prepare("SELECT 1 FROM connection_requests WHERE status = 'ACCEPTED' AND ((sender_id = :r AND receiver_id = :e) OR (sender_id = :e AND receiver_id = :r)) LIMIT 1");
    $stmt->execute([':r' => $reviewer, ':e' => $reviewee]);
    if (!$stmt->fetchColumn()) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized Review: connection required']);
        exit;
    }

    $ins = $db->prepare('INSERT INTO user_reviews (reviewer_id, reviewee_id, cleanliness_score, communication_score, written_feedback) VALUES (:reviewer, :reviewee, :clean, :comm, :feedback)');
    $ins->execute([
        ':reviewer' => $reviewer,
        ':reviewee' => $reviewee,
        ':clean' => $clean,
        ':comm' => $comm,
        ':feedback' => $feedback,
    ]);

    http_response_code(201);
    echo json_encode(['success' => true, 'review_id' => (int) $db->lastInsertId()]);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}