<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../core/database.php';
require_once __DIR__ . '/../../../core/auth.php';

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid user_id']);
    exit;
}

// Optional: verify current user is authenticated
$currentUser = auth_user();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

try {
    $db = db();

    $stmt = $db->prepare('SELECT COUNT(*) AS total, AVG(cleanliness_score) AS avg_clean, AVG(communication_score) AS avg_comm, SUM(cleanliness_score) AS sum_clean, SUM(communication_score) AS sum_comm FROM user_reviews WHERE reviewee_id = :uid');
    $stmt->execute([':uid' => $userId]);
    $row = $stmt->fetch();

    $total = (int) $row['total'];
    $avgClean = $row['avg_clean'] !== null ? (float) $row['avg_clean'] : null;
    $avgComm = $row['avg_comm'] !== null ? (float) $row['avg_comm'] : null;

    $aggregated = null;
    if ($total > 0) {
        $sumClean = (float) $row['sum_clean'];
        $sumComm = (float) $row['sum_comm'];
        $aggregated = (($sumClean + $sumComm) / (2 * $total));
    }

    echo json_encode([
        'user_id' => $userId,
        'total_reviews' => $total,
        'avg_cleanliness' => $avgClean,
        'avg_communication' => $avgComm,
        'aggregated_score' => $aggregated,
    ]);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}