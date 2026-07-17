<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/database.php';
require_once __DIR__ . '/../../../core/helpers.php';
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../core/layout.php';

if (isset($_GET['api']) && $_GET['api'] === 'available_slots') {
    try {
        $listingId = (int) ($_GET['listing_id'] ?? 0);
        $date = clean_string($_GET['date'] ?? '', 10);

        if ($listingId <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            json_response(['success' => false, 'message' => 'Valid listing_id and date are required.'], 422);
        }

        $dayStart = $date . ' 00:00:00';
        $dayEnd = (new DateTimeImmutable($dayStart))->modify('+1 day')->format('Y-m-d H:i:s');

        $stmt = db()->prepare("
            SELECT id, start_time, end_time, booking_status
            FROM appointments
            WHERE listing_id = :listing_id
              AND booking_status <> 'CANCELLED'
              AND start_time >= :day_start
              AND start_time < :day_end
            ORDER BY start_time ASC
        ");
        $stmt->execute([
            'listing_id' => $listingId,
            'day_start' => $dayStart,
            'day_end' => $dayEnd,
        ]);

        json_response([
            'success' => true,
            'listing_id' => $listingId,
            'date' => $date,
            'booked' => $stmt->fetchAll(),
        ]);
    } catch (Throwable $e) {
        json_response(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

if (isset($_GET['api']) && $_GET['api'] === 'book_viewing') {
    try {
        $currentUser = auth_user();
        if (!$currentUser) {
            json_response(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        $data = read_json_body();
        if (!$data) {
            $data = $_POST;
        }

        $listingId = (int) ($data['listing_id'] ?? 0);
        $tenantId = (int) $currentUser['id'];
        $startInput = clean_string($data['start_time'] ?? '', 30);

        if ($listingId <= 0 || $tenantId <= 0 || $startInput === '') {
            json_response(['success' => false, 'message' => 'listing_id, tenant_id, and start_time are required.'], 422);
        }

        $start = new DateTimeImmutable($startInput);
        $end = $start->modify('+' . VIEWING_SLOT_MINUTES . ' minutes');

        $startSql = $start->format('Y-m-d H:i:s');
        $endSql = $end->format('Y-m-d H:i:s');

        $pdo = db();
        $pdo->beginTransaction();

        $listingStmt = $pdo->prepare("SELECT id, status FROM listings WHERE id = :id FOR UPDATE");
        $listingStmt->execute(['id' => $listingId]);
        $listing = $listingStmt->fetch();

        if (!$listing || $listing['status'] !== 'AVAILABLE') {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Listing is not available.'], 404);
        }

        $conflictStmt = $pdo->prepare("
            SELECT id
            FROM appointments
            WHERE listing_id = :listing_id
              AND booking_status <> 'CANCELLED'
              AND (:start_time < end_time AND :end_time > start_time)
            LIMIT 1
        ");
        $conflictStmt->execute([
            'listing_id' => $listingId,
            'start_time' => $startSql,
            'end_time' => $endSql,
        ]);

        if ($conflictStmt->fetch()) {
            $pdo->rollBack();
            json_response(['success' => false, 'message' => 'Slot already booked.'], 409);
        }

        $insertStmt = $pdo->prepare("
            INSERT INTO appointments (listing_id, tenant_id, start_time, end_time, booking_status)
            VALUES (:listing_id, :tenant_id, :start_time, :end_time, 'PENDING')
        ");
        $insertStmt->execute([
            'listing_id' => $listingId,
            'tenant_id' => $tenantId,
            'start_time' => $startSql,
            'end_time' => $endSql,
        ]);

        $appointmentId = (int) $pdo->lastInsertId();
        $pdo->commit();

        json_response([
            'success' => true,
            'message' => 'Your viewing booking has been placed successfully.',
            'appointment' => [
                'id' => $appointmentId,
                'listing_id' => $listingId,
                'tenant_id' => $tenantId,
                'start_time' => $startSql,
                'end_time' => $endSql,
                'booking_status' => 'PENDING',
            ],
        ], 201);
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        json_response(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

$listingId = isset($_GET['listing_id']) ? (int) $_GET['listing_id'] : 1;
$authUser = auth_require_login();

layout_header('Book Viewing', [
    'description' => 'Schedule a property viewing with available time slots.',
]);
?>

    <div class="page-shell">
        <header class="page-header">
            <div>
                <h1>Schedule a Property Viewing</h1>
                <p class="lede">Select a date and pick an available 30-minute slot.</p>
            </div>
        </header>

        <section class="card">
            <div class="form-group">
                <label for="listingId">Listing ID</label>
                <input type="number" id="listingId" value="<?= h((string) $listingId) ?>" min="1">
            </div>

            <div class="form-group">
                <label for="bookingDate">Date</label>
                <input type="date" id="bookingDate">
            </div>

            <h3>Available Time Slots</h3>
            <div id="slotGrid" class="slot-grid"></div>
            <div id="bookingMessage"></div>
        </section>
    </div>

<script src="assets/js/booking.js"></script>
<?php
layout_footer();