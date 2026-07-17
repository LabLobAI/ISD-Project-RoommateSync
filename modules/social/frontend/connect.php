<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/database.php';
require_once __DIR__ . '/../../../core/helpers.php';
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../core/layout.php';

$currentUserId = auth_user_id();
$peopleStmt = db()->prepare('SELECT id, full_name, email FROM users WHERE id <> :id ORDER BY full_name');
$peopleStmt->execute(['id' => $currentUserId]);
$people = $peopleStmt->fetchAll();
$defaultPeerId = $people[0]['id'] ?? 0;

layout_header('Connect', [
    'description' => 'Double opt-in matching that unlocks chat once both sides accept.',
    'noindex' => true,
]);
?>

    <div class="page-shell">
        <header class="page-header">
            <div>
                <p class="eyebrow">Social</p>
                <h1>Connect</h1>
                <p class="lede">Double opt-in matching that unlocks chat once both sides accept.</p>
            </div>
            <nav class="header-links" aria-label="Social navigation">
                <a class="ghost-link" href="<?= rm_url('index.php') ?>">Home</a>
                <a class="ghost-link" href="<?= rm_url('modules/social/frontend/review_form.php') ?>">Review</a>
                <a class="ghost-link" href="<?= rm_url('modules/social/frontend/chat.php') ?>">Chat</a>
            </nav>
        </header>

        <div class="page-content">
            <article class="card">
                <p class="card-kicker">Request connection</p>
                <h2 class="card-title">Find a Roommate</h2>

                <div class="form-group">
                    <label for="receiverId">Connect with</label>
                    <select id="receiverId">
                        <?php foreach ($people as $person): ?>
                            <option value="<?= (int) $person['id'] ?>" <?= (int) $person['id'] === (int) $defaultPeerId ? 'selected' : '' ?>>
                                <?= h($person['full_name']) ?> (<?= h($person['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="action-row">
                    <button id="sendRequest" class="primary-button" type="button">Send request</button>
                </div>

                <div id="connectStatus" class="status-box">Ready to connect.</div>
            </article>

            <aside class="card">
                <p class="card-kicker">How it works</p>
                <h2 class="card-title">Connection flow</h2>

                <div style="display:grid; gap:12px;">
                    <div class="status-box">1. One person sends a request.</div>
                    <div class="status-box">2. The other person sends one back.</div>
                    <div class="status-box">3. Both rows become accepted and chat unlocks.</div>
                </div>
            </aside>
        </div>
    </div>

<script>
const receiverSelect = document.getElementById('receiverId');
const connectStatus = document.getElementById('connectStatus');
const sendRequest = document.getElementById('sendRequest');
const senderId = <?= (int) $currentUserId ?>;

sendRequest.addEventListener('click', async () => {
    connectStatus.textContent = 'Sending request...';

    const response = await fetch('../api/connect_request.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            sender_id: senderId,
            receiver_id: Number(receiverSelect.value)
        })
    });
    const data = await response.json();

    if (!response.ok) {
        connectStatus.textContent = data.error || 'Connection request failed.';
        return;
    }

    connectStatus.textContent = data.message ? `${data.status}: ${data.message}` : `Connection status: ${data.status}`;
});
</script>
<?php
layout_footer();