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
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?= rm_url('index.php') ?>">RoommateSync</a>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="<?= rm_url('index.php') ?>">Dashboard</a>
            <a href="<?= rm_url('modules/marketplace/public/listings.php') ?>">Marketplace</a>
            <a href="<?= rm_url('modules/bill-split/public/expenses.php') ?>">Bill Split</a>
            <a href="<?= rm_url('modules/booking/public/booking.php') ?>">Booking</a>
            <a href="<?= rm_url('modules/listing-upload/public/create_listing.php') ?>">List a Room</a>
            <a href="<?= rm_url('modules/social/frontend/connect.php') ?>">Connect</a>
            <a href="<?= rm_url('modules/social/frontend/chat.php') ?>">Chat</a>
            <a href="<?= rm_url('modules/social/frontend/review_form.php') ?>">Reviews</a>
        </nav>
        <div class="header-auth">
            <?php if ($user): ?>
                <div class="user-menu">
                    <span class="user-name"><?= h($user['full_name']) ?></span>
                    <span class="user-role badge badge-<?= h(auth_user_role()) ?>"><?= h(ucfirst(auth_user_role())) ?></span>
                    <a class="ghost-link" href="<?= rm_url('auth/logout.php') ?>">Sign out</a>
                </div>
            <?php else: ?>
                <a class="ghost-link" href="<?= rm_url('auth/login.php') ?>">Sign in</a>
                <a class="primary-link" href="<?= rm_url('auth/register.php') ?>">Join</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="site-main">
<?php
}

function layout_footer(): void
{
?>
</main>
<footer class="site-footer">
    <div class="footer-inner">
        <p class="footer-copyright">&copy; <?= date('Y') ?> RoommateSync. Shared housing made simple.</p>
        <nav class="footer-nav" aria-label="Footer links">
            <a href="<?= rm_url('index.php') ?>">Home</a>
            <a href="<?= rm_url('modules/marketplace/public/listings.php') ?>">Marketplace</a>
            <a href="<?= rm_url('modules/bill-split/public/expenses.php') ?>">Bill Split</a>
            <a href="<?= rm_url('modules/booking/public/booking.php') ?>">Booking</a>
            <a href="<?= rm_url('modules/listing-upload/public/create_listing.php') ?>">List a Room</a>
            <a href="<?= rm_url('modules/social/frontend/connect.php') ?>">Social</a>
        </nav>
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