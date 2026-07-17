<?php

declare(strict_types=1);

require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/layout.php';

$modules = [
    [
        'group' => 'Modules',
        'title' => 'Rental Marketplace',
        'status' => 'Ready',
        'summary' => 'Browse and filter listings without reloading the page.',
        'path' => 'modules/marketplace/public/listings.php',
    ],
    [
        'group' => 'Modules',
        'title' => 'Bill Split Calculator',
        'status' => 'Ready',
        'summary' => 'Split household bills proportionally by income and save logs.',
        'path' => 'modules/bill-split/public/expenses.php',
    ],
    [
        'group' => 'Modules',
        'title' => 'Property Viewing Booking',
        'status' => 'Ready',
        'summary' => 'Pick a date, see available slots, and prevent double booking.',
        'path' => 'modules/booking/public/booking.php',
    ],
    [
        'group' => 'Modules',
        'title' => 'Landlord Listing Upload',
        'status' => 'Ready',
        'summary' => 'Create a listing, validate the image, and save to MySQL.',
        'path' => 'modules/listing-upload/public/create_listing.php',
    ],
    [
        'group' => 'Modules',
        'title' => 'Peer Review',
        'status' => 'Ready',
        'summary' => 'Submit and aggregate user reviews behind a simple verification gate.',
        'path' => 'modules/social/frontend/review_form.php',
    ],
    [
        'group' => 'Modules',
        'title' => 'Chat',
        'status' => 'Ready',
        'summary' => 'Polling-based chat between accepted connections.',
        'path' => 'modules/social/frontend/chat.php',
    ],
    [
        'group' => 'Modules',
        'title' => 'Double Opt-In Connect',
        'status' => 'Ready',
        'summary' => 'Connection requests unlock chat after a mutual match.',
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

$statusCounts = [
    'Ready' => 0,
    'Prototype' => 0,
    'Integrated' => 0,
];

foreach ($modules as $module) {
    $statusCounts[$module['status']]++;
}

layout_header('RoommateSync - Unified Project', [
    'description' => 'Find a room, split costs, and manage house life in one place.',
]);

?>
    <div class="shell">
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow">RoommateSync</p>
                <h1>Find a room, split costs, and manage house life in one place.</h1>
                <p class="lede">A single landing page for the project, with sign in, sign up, and a module dashboard for the rental marketplace, bill split, booking, and social flows.</p>

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

                <div class="hero-stats">
                    <div class="stat-card">
                        <span class="stat-value"><?= count($modules) ?></span>
                        <span class="stat-label">modules available</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?= $statusCounts['Ready'] ?></span>
                        <span class="stat-label">core flows ready</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-value"><?= $statusCounts['Prototype'] ?></span>
                        <span class="stat-label">prototype modules</span>
                    </div>
                </div>
            </div>

            <aside class="hero-panel">
                <h2>Getting started</h2>
                <ul>
                    <li>Import <strong>Database/schema.sql</strong> and <strong>Database/seed.sql</strong> for the main app.</li>
                    <li>Create an account or sign in from this landing page.</li>
                    <li>Use the module cards below to jump into each feature area.</li>
                </ul>
            </aside>
        </section>

        <section class="section">
            <div class="section-header">
                <div>
                    <p class="section-kicker">Module Map</p>
                    <h2>All components in one place</h2>
                </div>
                <p class="section-note">The main roommate rental flows are ready. Social review, chat, and connect now work through the consolidated API layer.</p>
            </div>

            <div class="card-grid">
                <?php foreach ($modules as $module): ?>
                    <article class="module-card">
                        <div class="card-top">
                            <span class="badge badge-<?= strtolower($module['status']) ?>"><?= e($module['status']) ?></span>
                            <span class="group-label"><?= e($module['group']) ?></span>
                        </div>
                        <h3><?= e($module['title']) ?></h3>
                        <p><?= e($module['summary']) ?></p>
                        <a href="<?= rm_url($module['path']) ?>">Open module</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section two-column">
            <article class="info-card">
                <p class="section-kicker">What is already implemented</p>
                <ul>
                    <li>Search, filter, and render listings from the shared MySQL database.</li>
                    <li>Bill splitting with backend validation and optional persistence.</li>
                    <li>Booking flow with slot conflict checks and booking state management.</li>
                    <li>Landlord listing creation with file validation and image upload.</li>
                </ul>
            </article>

            <article class="info-card">
                <p class="section-kicker">What still needs tightening</p>
                <ul>
                    <li>Shared authentication and role-based access control across all module actions.</li>
                    <li>Common layout/header/footer across all PHP entry points.</li>
                    <li>Unified error handling, logging, and test coverage.</li>
                    <li>Folder-level aliases for cleaner URLs if you want even shorter paths.</li>
                </ul>
            </article>
        </section>

        <section class="section">
            <div class="section-header">
                <div>
                    <p class="section-kicker">Launchpad</p>
                    <h2>Go to the live project modules</h2>
                </div>
                <p class="section-note">Use the dashboard after signing in, or open any module directly for development and testing.</p>
            </div>
            <div class="launch-grid">
                <a class="launch-card" href="<?= rm_url('modules/marketplace/public/listings.php') ?>">
                    <span>Marketplace</span>
                    <strong>Browse listings</strong>
                </a>
                <a class="launch-card" href="<?= rm_url('modules/bill-split/public/expenses.php') ?>">
                    <span>Bill split</span>
                    <strong>Split shared costs</strong>
                </a>
                <a class="launch-card" href="<?= rm_url('modules/booking/public/booking.php') ?>">
                    <span>Booking</span>
                    <strong>Reserve viewings</strong>
                </a>
                <a class="launch-card" href="<?= rm_url('modules/listing-upload/public/create_listing.php') ?>">
                    <span>Listing upload</span>
                    <strong>Create a room listing</strong>
                </a>
            </div>
        </section>
    </div>
<?php

layout_footer();