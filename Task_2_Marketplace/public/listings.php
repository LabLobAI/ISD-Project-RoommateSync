<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

if (isset($_GET['api']) && $_GET['api'] === 'listings') {
    try {
        $pdo = db();

        $maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== ''
            ? clean_float($_GET['max_price'], 99999)
            : 99999.00;

        $roomType = clean_string($_GET['room_type'] ?? '', 20);
        $location = clean_string($_GET['location'] ?? '', 120);

        $sql = "
            SELECT
                l.id,
                l.title,
                l.description,
                l.location_text,
                l.rent,
                l.room_type,
                l.bedrooms,
                l.bathrooms,
                l.image_url,
                u.full_name AS landlord_name
            FROM listings l
            INNER JOIN users u ON u.id = l.landlord_id
            WHERE l.status = 'AVAILABLE'
              AND l.rent <= :max_price
        ";

        $params = ['max_price' => $maxPrice];

        if ($roomType !== '' && in_array($roomType, ['private', 'shared'], true)) {
            $sql .= " AND l.room_type = :room_type";
            $params['room_type'] = $roomType;
        }

        if ($location !== '') {
            $sql .= " AND l.location_text LIKE :location";
            $params['location'] = '%' . $location . '%';
        }

        $sql .= " ORDER BY l.rent ASC, l.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        json_response([
            'success' => true,
            'filters' => [
                'max_price' => $maxPrice,
                'room_type' => $roomType,
                'location' => $location,
            ],
            'listings' => $stmt->fetchAll(),
        ]);
    } catch (Throwable $e) {
        json_response(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rental Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <strong>Rental Marketplace</strong>
</nav>
<main class="container layout">
    <aside class="filter-panel">
        <h2>Search Filters</h2>
        <div class="form-group">
            <label for="maxPrice">Maximum Price: <span id="priceLabel">20000</span></label>
            <input type="range" id="maxPrice" min="5000" max="30000" step="500" value="20000">
        </div>
        <div class="form-group">
            <label for="roomType">Room Type</label>
            <select id="roomType">
                <option value="">Any</option>
                <option value="private">Private</option>
                <option value="shared">Shared</option>
            </select>
        </div>
        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" placeholder="Example: Dhanmondi">
        </div>
    </aside>
    <section>
        <h1>Available Listings</h1>
        <div id="listingGrid" class="listings-grid"></div>
    </section>
</main>
<script src="assets/js/listings.js"></script>
</body>
</html>
