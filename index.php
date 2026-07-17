<?php

declare(strict_types=1);

require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/layout.php';

$modules = [
    [
        'title' => 'Rental Marketplace',
        'summary' => 'Browse and filter listings by price, room type, and location.',
        'path' => 'modules/marketplace/public/listings.php',
    ],
    [
        'title' => 'Bill Split Calculator',
        'summary' => 'Split household bills proportionally by income.',
        'path' => 'modules/bill-split/public/expenses.php',
    ],
    [
        'title' => 'Property Viewing Booking',
        'summary' => 'Pick a date, see available slots, and prevent double booking.',
        'path' => 'modules/booking/public/booking.php',
    ],
    [
        'title' => 'Landlord Listing Upload',
        'summary' => 'Create a listing, validate the image, and save to the database.',
        'path' => 'modules/listing-upload/public/create_listing.php',
    ],
    [
        'title' => 'Peer Review',
        'summary' => 'Submit and aggregate user reviews for matched roommates.',
        'path' => 'modules/social/frontend/review_form.php',
    ],
    [
        'title' => 'Chat',
        'summary' => 'Polling-based chat between accepted connections.',
        'path' => 'modules/social/frontend/chat.php',
    ],
    [
        'title' => 'Connect',
        'summary' => 'Double opt-in matching that unlocks chat once both sides accept.',
        'path' => 'modules/social/frontend/connect.php',
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
                <p class="lede">Browse rentals, split bills, schedule viewings, and connect with roommates — all in one place.</p>

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
            <div class="card-grid">
                <?php foreach ($modules as $module): ?>
                    <a class="module-card" href="<?= rm_url($module['path']) ?>">
                        <h3><?= e($module['title']) ?></h3>
                        <p><?= e($module['summary']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
<?php

layout_footer();
