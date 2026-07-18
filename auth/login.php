<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/layout.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    $result = auth_login($email, $password, $remember);
    if ($result['success']) {
        header('Location: ' . rm_url('index.php') . '?signed_in=1');
        exit;
    }

    $errors[] = $result['message'] ?? 'Login failed.';
}

layout_header('Sign in - RoommateSync', [
    'description' => 'Sign in to your RoommateSync account.',
    'noindex' => true,
]);
?>

    <div class="shell auth-shell">
        <section class="auth-card">
            <h1>Sign in</h1>
            <p style="color:#565959;margin-top:4px;font-size:14px;">Access your dashboard, listings, and connections.</p>

            <?php if ($errors): ?>
                <div class="flash-message error"><?= h(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <label>
                    Email
                    <input type="email" name="email" required autocomplete="email">
                </label>
                <label>
                    Password
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
                <label class="checkbox-row">
                    <input type="checkbox" name="remember" checked>
                    Keep me signed in
                </label>
                <button type="submit" class="primary-button">Sign in</button>
            </form>

            <div style="text-align:center;margin-top:16px;padding-top:16px;border-top:1px solid #e7e7e7;">
                <p style="color:#565959;font-size:13px;margin:0;">New to RoommateSync?</p>
                <a href="<?= rm_url('auth/register.php') ?>" style="font-size:14px;">Create your account</a>
            </div>
        </section>
    </div>

<?php
layout_footer();
