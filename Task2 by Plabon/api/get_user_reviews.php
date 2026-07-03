<?php
// get_user_reviews.php?user_id=123
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($userId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid user_id']);
    exit;
}

try {
    $db = get_db();

    $stmt = $db->prepare('SELECT COUNT(*) AS total, AVG(cleanliness_score) AS avg_clean, AVG(communication_score) AS avg_comm, SUM(cleanliness_score) AS sum_clean, SUM(communication_score) AS sum_comm FROM user_reviews WHERE reviewee_id = :uid');
    $stmt->execute([':uid' => $userId]);
    $row = $stmt->fetch();

    $total = (int)$row['total'];
    $avg_clean = $row['avg_clean'] !== null ? (float)$row['avg_clean'] : null;
    $avg_comm = $row['avg_comm'] !== null ? (float)$row['avg_comm'] : null;

    if ($total > 0) {
        $sum_clean = (float)$row['sum_clean'];
        $sum_comm = (float)$row['sum_comm'];
        $aggregated = (($sum_clean + $sum_comm) / (2 * $total));
    } else {
        $aggregated = null;
    }

    echo json_encode([
        'user_id' => $userId,
        'total_reviews' => $total,
        'avg_cleanliness' => $avg_clean,
        'avg_communication' => $avg_comm,
        'aggregated_score' => $aggregated
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
    exit;
}
