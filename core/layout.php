<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/auth.php';

function rm_base_path(): string
{
    return '';
}

function rm_url(string $path): string
{
    return rm_base_path() . '/' . ltrim($path, '/');
}

function layout_header(string $title = 'RoommateSync', array $meta = []): void
{
    $user = auth_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <link rel="stylesheet" href="<?= rm_url('assets/css/style.css') ?>">
    <?php if (!empty($meta['description'])): ?>
        <meta name="description" content="<?= h($meta['description']) ?>">
    <?php endif; ?>
    <?php if (!empty($meta['noindex'])): ?>
        <meta name="robots" content="noindex">
    <?php endif; ?>
</head>
<body>

<!-- Top utility bar -->
<div class="top-bar">
    <div class="top-bar-inner">
        <?php if ($user): ?>
            <a href="<?= rm_url('index.php') ?>">Hello, <?= h($user['full_name']) ?></a>
            <div class="top-bar-divider"></div>
            <a href="<?= rm_url('auth/logout.php') ?>">Sign out</a>
        <?php else: ?>
            <a href="<?= rm_url('auth/login.php') ?>">Sign in</a>
            <div class="top-bar-divider"></div>
            <a href="<?= rm_url('auth/register.php') ?>">Register</a>
        <?php endif; ?>
    </div>
</div>

<!-- Main header -->
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?= rm_url('index.php') ?>">Roommate<span class="brand-accent">Sync</span></a>

        <div class="search-bar">
            <form action="<?= rm_url('modules/marketplace/public/listings.php') ?>" method="get" style="display:flex;flex:1;height:100%;">
                <input type="text" name="search" placeholder="Search rooms, locations...">
                <button type="submit" aria-label="Search">&#128269;</button>
            </form>
        </div>

        <nav class="header-nav" aria-label="Quick links">
            <?php if ($user): ?>
                <a href="<?= rm_url('auth/logout.php') ?>">
                    <span class="nav-small">Hello, <?= h(mb_substr($user['full_name'], 0, 12)) ?></span>
                    <span>Account</span>
                </a>
            <?php else: ?>
                <a href="<?= rm_url('auth/login.php') ?>">
                    <span class="nav-small">Hello, Sign in</span>
                    <span>Account</span>
                </a>
            <?php endif; ?>
            <a href="<?= rm_url('modules/listing-upload/public/create_listing.php') ?>">
                <span class="nav-small">Your</span>
                <span>Listings</span>
            </a>
        </nav>
    </div>
</header>

<!-- Category navigation bar -->
<nav class="category-bar" aria-label="Category navigation">
    <div class="category-bar-inner">
        <a href="<?= rm_url('modules/marketplace/public/listings.php') ?>">All Listings</a>
        <a href="<?= rm_url('modules/marketplace/public/listings.php') ?>?room_type=private">Private Rooms</a>
        <a href="<?= rm_url('modules/marketplace/public/listings.php') ?>?room_type=shared">Shared Rooms</a>
        <a href="<?= rm_url('modules/booking/public/booking.php') ?>">Book Viewing</a>
        <a href="<?= rm_url('modules/bill-split/public/expenses.php') ?>">Bill Split</a>
        <a href="<?= rm_url('modules/social/frontend/connect.php') ?>">Connect</a>
        <a href="<?= rm_url('modules/social/frontend/chat.php') ?>">Chat</a>
        <a href="<?= rm_url('modules/social/frontend/review_form.php') ?>">Reviews</a>
        <a href="<?= rm_url('modules/listing-upload/public/create_listing.php') ?>">List a Room</a>
    </div>
</nav>

<main class="site-main">
<?php
}

function layout_footer(): void
{
?>
</main>

<!-- Back to top -->
<a href="#" class="footer-back-to-top">Back to top</a>

<footer class="site-footer">
    <div class="footer-columns">
        <div class="footer-col">
            <h4>Get to Know Us</h4>
            <ul>
                <li><a href="<?= rm_url('index.php') ?>">About RoommateSync</a></li>
                <li><a href="<?= rm_url('modules/marketplace/public/listings.php') ?>">Browse Listings</a></li>
                <li><a href="<?= rm_url('modules/social/frontend/connect.php') ?>">Connect with Roommates</a></li>
                <li><a href="<?= rm_url('modules/social/frontend/review_form.php') ?>">Peer Reviews</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Make Money</h4>
            <ul>
                <li><a href="<?= rm_url('modules/listing-upload/public/create_listing.php') ?>">List Your Room</a></li>
                <li><a href="<?= rm_url('modules/bill-split/public/expenses.php') ?>">Bill Split Calculator</a></li>
                <li><a href="<?= rm_url('modules/booking/public/booking.php') ?>">Schedule Viewings</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Help & Support</h4>
            <ul>
                <li><a href="<?= rm_url('modules/social/frontend/chat.php') ?>">Chat with Roommates</a></li>
                <li><a href="<?= rm_url('modules/social/frontend/connect.php') ?>">Connection Guide</a></li>
                <li><a href="<?= rm_url('modules/social/frontend/review_form.php') ?>">Write a Review</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Account</h4>
            <ul>
                <li><a href="<?= rm_url('auth/login.php') ?>">Sign In</a></li>
                <li><a href="<?= rm_url('auth/register.php') ?>">Create Account</a></li>
                <li><a href="<?= rm_url('modules/listing-upload/public/create_listing.php') ?>">Post a Listing</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="footer-bottom-logo">Roommate<span class="brand-accent">Sync</span></div>
        <p>&copy; <?= date('Y') ?> RoommateSync. Shared housing made simple.</p>
    </div>
</footer>
</body>
</html>
<?php
}

function layout_flash_messages(): void
{
    if (!empty($_GET['logged_out'])) {
        echo '<div class="flash-message success">' . h('You have been signed out.') . '</div>';
    }
    if (!empty($_GET['registered'])) {
        echo '<div class="flash-message success">' . h('Account created. You are now signed in.') . '</div>';
    }
    if (!empty($_GET['signed_in'])) {
        echo '<div class="flash-message success">' . h('You are signed in.') . '</div>';
    }
    if (!empty($_GET['error'])) {
        echo '<div class="flash-message error">' . h($_GET['error']) . '</div>';
    }
}
