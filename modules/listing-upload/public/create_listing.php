<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/database.php';
require_once __DIR__ . '/../../../core/helpers.php';
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../core/layout.php';

$authUser = auth_require_login();
if (!auth_is_landlord((int) $authUser['id'])) {
    header('Location: ' . rm_url('index.php') . '?error=' . urlencode('Only landlords can create listings.'));
    exit;
}

$errors = [];
$successMessage = '';
$createdListing = null;

$houseRuleLabels = [
    'no_smoking' => 'No smoking',
    'no_pets' => 'No pets',
    'quiet_hours' => 'Quiet hours after 10 PM',
    'visitors_allowed' => 'Visitors allowed with notice',
    'cooking_allowed' => 'Cooking allowed',
];

function handle_room_image_upload(array $file): string
{
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new RuntimeException('Invalid image upload request.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please try again.');
    }

    $maxSize = 5 * 1024 * 1024; // 5 MB
    if ($file['size'] > $maxSize) {
        throw new RuntimeException('Image size must not exceed 5 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    if (!array_key_exists($mimeType, $allowedTypes)) {
        throw new RuntimeException('Only JPG and PNG images are allowed.');
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        throw new RuntimeException('Unable to create upload directory.');
    }

    $extension = $allowedTypes[$mimeType];
    $safeFileName = uniqid('room_', true) . '.' . $extension;
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $safeFileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Unable to move uploaded image to server folder.');
    }

    // Store browser-accessible relative path, not binary image data.
    $publicBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    return $publicBase . '/uploads/' . $safeFileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim((string) post_value('title'));
    $description = trim((string) post_value('description'));
    $rent = filter_var(post_value('rent'), FILTER_VALIDATE_FLOAT);
    $location = trim((string) post_value('location_text'));
    $roomType = (string) post_value('room_type', 'private');
    $bedrooms = filter_var(post_value('bedrooms', 1), FILTER_VALIDATE_INT);
    $bathrooms = filter_var(post_value('bathrooms', 1), FILTER_VALIDATE_FLOAT);
    $landlordId = (int) $authUser['id'];
    $selectedRules = $_POST['house_rules'] ?? [];

    if ($title === '' || mb_strlen($title) > 180) {
        $errors[] = 'Title is required and must be within 180 characters.';
    }

    if ($description === '') {
        $errors[] = 'Description is required.';
    }

    if ($rent === false || $rent <= 0 || $rent > 999999.99) {
        $errors[] = 'Monthly rent must be a valid positive amount.';
    }

    if ($location === '' || mb_strlen($location) > 180) {
        $errors[] = 'Location is required and must be within 180 characters.';
    }

    if (!in_array($roomType, ['private', 'shared'], true)) {
        $errors[] = 'Room type must be either private or shared.';
    }

    if ($bedrooms === false || $bedrooms < 1 || $bedrooms > 10) {
        $errors[] = 'Bedrooms must be between 1 and 10.';
    }

    if ($bathrooms === false || $bathrooms < 0.5 || $bathrooms > 10) {
        $errors[] = 'Bathrooms must be between 0.5 and 10.';
    }

    $validRules = array_values(array_intersect($selectedRules, array_keys($houseRuleLabels)));
    $houseRulesText = $validRules
        ? 'House Rules: ' . implode(', ', array_map(fn($key) => $houseRuleLabels[$key], $validRules))
        : 'House Rules: Not specified';

    if (!isset($_FILES['room_image'])) {
        $errors[] = 'Room image is required.';
    }

    if (!$errors) {
        try {
            $imagePath = handle_room_image_upload($_FILES['room_image']);
            $finalDescription = $description . "\n\n" . $houseRulesText;

            $stmt = db()->prepare(
                'INSERT INTO listings
                    (landlord_id, title, description, location_text, rent, room_type, bedrooms, bathrooms, status, image_url)
                 VALUES
                    (:landlord_id, :title, :description, :location_text, :rent, :room_type, :bedrooms, :bathrooms, :status, :image_url)'
            );

            $stmt->execute([
                ':landlord_id' => $landlordId,
                ':title' => $title,
                ':description' => $finalDescription,
                ':location_text' => $location,
                ':rent' => $rent,
                ':room_type' => $roomType,
                ':bedrooms' => $bedrooms,
                ':bathrooms' => $bathrooms,
                ':status' => 'AVAILABLE',
                ':image_url' => $imagePath,
            ]);

            $createdListing = [
                'id' => db()->lastInsertId(),
                'title' => $title,
                'location_text' => $location,
                'rent' => (float) $rent,
                'room_type' => $roomType,
                'bedrooms' => (int) $bedrooms,
                'bathrooms' => (float) $bathrooms,
                'image_url' => $imagePath,
            ];

            $successMessage = 'Room listing has been created successfully and saved to the database.';

            $_POST = [];
        } catch (Throwable $exception) {
            $errors[] = $exception->getMessage();
        }
    }
}

layout_header('Create Rental Listing', [
    'description' => 'Add room details, validate the uploaded image, and save the listing path into the common roommate_rental MySQL database.',
]);
?>

    <div class="page-shell">
        <header class="page-header">
            <div>
                <h1>Create Rental Listing</h1>
                <p class="lede">Add room details, upload a photo, and publish your listing.</p>
            </div>
        </header>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <strong>Please fix the following:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= h($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <?= h($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($createdListing): ?>
            <section class="card created-card">
                <img src="<?= h($createdListing['image_url']) ?>" alt="Created room image">
                <div>
                    <h2><?= h($createdListing['title']) ?></h2>
                    <p><?= h($createdListing['location_text']) ?></p>
                    <p><strong>৳<?= money($createdListing['rent']) ?></strong> / month</p>
                    <p><?= h(ucfirst($createdListing['room_type'])) ?> room &middot; <?= h((string) $createdListing['bedrooms']) ?> bed &middot; <?= h((string) $createdListing['bathrooms']) ?> bath</p>
                </div>
            </section>
        <?php endif; ?>

        <section class="card">
            <form method="post" enctype="multipart/form-data" id="listingForm">
                <label>
                    Listing Title
                    <input type="text" name="title" maxlength="180" required value="<?= h((string) post_value('title')) ?>" placeholder="Sunny private room near campus">
                </label>

                <div class="grid-2">
                    <label>
                        Location / Address
                        <input type="text" name="location_text" maxlength="180" required value="<?= h((string) post_value('location_text')) ?>" placeholder="Dhanmondi, Dhaka">
                    </label>

                    <label>
                        Monthly Rent
                        <input type="number" name="rent" min="1" step="0.01" required value="<?= h((string) post_value('rent')) ?>" placeholder="15000">
                    </label>
                </div>

                <div class="grid-3">
                    <label>
                        Room Type
                        <select name="room_type" required>
                            <option value="private" <?= post_value('room_type', 'private') === 'private' ? 'selected' : '' ?>>Private</option>
                            <option value="shared" <?= post_value('room_type') === 'shared' ? 'selected' : '' ?>>Shared</option>
                        </select>
                    </label>

                    <label>
                        Bedrooms
                        <input type="number" name="bedrooms" min="1" max="10" required value="<?= h((string) post_value('bedrooms', 1)) ?>">
                    </label>

                    <label>
                        Bathrooms
                        <input type="number" name="bathrooms" min="0.5" max="10" step="0.5" required value="<?= h((string) post_value('bathrooms', 1)) ?>">
                    </label>
                </div>

                <label>
                    Description
                    <textarea name="description" rows="4" required placeholder="Room facilities, nearby locations, other details..."><?= h((string) post_value('description')) ?></textarea>
                </label>

                <fieldset>
                    <legend>House Rules</legend>
                    <div class="checkbox-grid">
                        <?php foreach ($houseRuleLabels as $key => $label): ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="house_rules[]" value="<?= h($key) ?>" <?= in_array($key, $_POST['house_rules'] ?? [], true) ? 'checked' : '' ?>>
                                <?= h($label) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>

                <label>
                    Room Image <span class="small-note">JPG/PNG, max 5 MB</span>
                    <input type="file" name="room_image" id="roomImage" accept="image/jpeg,image/png" required>
                </label>

                <div class="preview-panel" id="previewPanel" hidden>
                    <img id="imagePreview" alt="Room preview">
                    <p id="fileInfo"></p>
                </div>

                <button type="submit" class="primary-button">Create Listing</button>
            </form>
        </section>
    </div>

<script src="assets/js/create_listing.js"></script>
<?php
layout_footer();