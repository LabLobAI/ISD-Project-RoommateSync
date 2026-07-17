<?php

declare(strict_types=1);

require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/layout.php';

$stats = [
    'listings' => 0,
    'users' => 0,
    'bookings' => 0,
];

try {
    $pdo = db();
    $stats['listings'] = (int) $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'AVAILABLE'")->fetchColumn();
    $stats['users'] = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['bookings'] = (int) $pdo->query("SELECT COUNT(*) FROM appointments WHERE booking_status <> 'CANCELLED'")->fetchColumn();
} catch (Throwable $e) {
    // stats stay at 0
}

$modules = [
    [
        'title' => 'Rental Marketplace',
        'summary' => 'Browse and filter listings by price, room type, and location.',
        'path' => 'modules/marketplace/public/listings.php',
        'icon' => '&#127968;',
    ],
    [
        'title' => 'Bill Split Calculator',
        'summary' => 'Split household bills proportionally by income.',
        'path' => 'modules/bill-split/public/expenses.php',
        'icon' => '&#128176;',
    ],
    [
        'title' => 'Property Viewing',
        'summary' => 'Pick a date, see available slots, and prevent double booking.',
        'path' => 'modules/booking/public/booking.php',
        'icon' => '&#128197;',
    ],
    [
        'title' => 'List a Room',
        'summary' => 'Create a listing, upload a photo, and publish to the marketplace.',
        'path' => 'modules/listing-upload/public/create_listing.php',
        'icon' => '&#128221;',
    ],
    [
        'title' => 'Peer Review',
        'summary' => 'Submit and aggregate user reviews for matched roommates.',
        'path' => 'modules/social/frontend/review_form.php',
        'icon' => '&#11088;',
    ],
    [
        'title' => 'Chat',
        'summary' => 'Polling-based chat between accepted connections.',
        'path' => 'modules/social/frontend/chat.php',
        'icon' => '&#128172;',
    ],
    [
        'title' => 'Connect',
        'summary' => 'Double opt-in matching that unlocks chat once both sides accept.',
        'path' => 'modules/social/frontend/connect.php',
        'icon' => '&#129309;',
    ],
];

$authUser = auth_user();
$flashMessage = '';
if (isset($_GET['logged_out'])) {
    $flashMessage = 'You are signed out.';
} elseif (isset($_GET['registered'])) {
    $flashMessage = 'Account created. You are now signed in.';
} elseif (isset($_GET['signed_in'])) {
    $flashMessage = 'You are signed in.';
}

layout_header('RoommateSync', [
    'description' => 'Find a room, split costs, and manage house life in one place.',
]);

?>
    <div class="shell">
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">RoommateSync</p>
                <h1>Find a room, split costs, manage house life.</h1>
                <p class="lede">Browse rentals, split bills, schedule viewings, and connect with roommates &mdash; all in one place.</p>

                <?php if ($flashMessage !== ''): ?>
                    <div class="flash-message"><?= e($flashMessage) ?></div>
                <?php endif; ?>

                <?php if ($authUser): ?>
                    <div class="auth-summary">
                        <div>
                            <p class="section-kicker">Signed in</p>
                            <strong><?= e($authUser['full_name']) ?></strong>
                            <p class="muted"><?= e($authUser['email']) ?> &middot; <?= e($authUser['city']) ?></p>
                        </div>
                        <a class="ghost-link" href="<?= rm_url('auth/logout.php') ?>">Sign out</a>
                    </div>
                <?php else: ?>
                    <div class="auth-cta-row">
                        <a class="primary-link" href="<?= rm_url('auth/login.php') ?>">Sign in</a>
                        <a class="ghost-link" href="<?= rm_url('auth/register.php') ?>">Create account</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Quick Stats</h2>
            </div>
            <div class="card-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="metric">
                    <span>Available Listings</span>
                    <strong><?= $stats['listings'] ?></strong>
                </div>
                <div class="metric">
                    <span>Registered Users</span>
                    <strong><?= $stats['users'] ?></strong>
                </div>
                <div class="metric">
                    <span>Viewings Booked</span>
                    <strong><?= $stats['bookings'] ?></strong>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2>Modules</h2>
            </div>
            <div class="card-grid">
                <?php foreach ($modules as $module): ?>
                    <a class="module-card" href="<?= rm_url($module['path']) ?>">
                        <div class="module-icon"><?= $module['icon'] ?></div>
                        <h3><?= e($module['title']) ?></h3>
                        <p><?= e($module['summary']) ?></p>
                        <span class="card-arrow">&rarr; Open</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
<?php

layout_footer();
