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

layout_header('Chat', [
    'description' => 'Polling chat between accepted connections.',
    'noindex' => true,
]);
?>

    <div class="page-shell">
        <header class="page-header">
            <div>
                <p class="eyebrow">Social</p>
                <h1>Chat</h1>
                <p class="lede">A polling chat window that unlocks only for accepted connections.</p>
            </div>
            <nav class="header-links" aria-label="Social navigation">
                <a class="ghost-link" href="<?= rm_url('index.php') ?>">Home</a>
                <a class="ghost-link" href="<?= rm_url('modules/social/frontend/review_form.php') ?>">Review</a>
                <a class="ghost-link" href="<?= rm_url('modules/social/frontend/connect.php') ?>">Connect</a>
            </nav>
        </header>

        <div class="page-content">
            <article class="card">
                <h2 class="card-title">Messages</h2>

                <div class="form-group">
                    <label for="peerId">Chat with</label>
                    <select id="peerId">
                        <?php foreach ($people as $person): ?>
                            <option value="<?= (int) $person['id'] ?>" <?= (int) $person['id'] === (int) $defaultPeerId ? 'selected' : '' ?>>
                                <?= h($person['full_name']) ?> (<?= h($person['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="chatStatus" class="status-box">Loading messages...</div>
                <div id="messageStream" class="stream"></div>
            </article>

            <aside class="card">
                <h2 class="card-title">Send Message</h2>

                <form id="messageForm">
                    <input type="hidden" id="senderId" value="<?= (int) $currentUserId ?>">
                    <div class="form-group">
                        <label for="messageText">Message</label>
                        <textarea id="messageText" placeholder="Write a short note to your matched roommate..."></textarea>
                    </div>
                    <div class="action-row">
                        <button class="primary-button" type="submit">Send</button>
                    </div>
                </form>
            </aside>
        </div>
    </div>

<script>
const peerSelect = document.getElementById('peerId');
const senderId = Number(document.getElementById('senderId').value);
const statusBox = document.getElementById('chatStatus');
const stream = document.getElementById('messageStream');
const messageForm = document.getElementById('messageForm');
const messageText = document.getElementById('messageText');

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function renderMessages(messages) {
    if (!messages.length) {
        stream.innerHTML = '<div class="status-box">No messages yet. Start the conversation.</div>';
        return;
    }

    stream.innerHTML = messages.map((message) => {
        const mine = Number(message.sender_id) === senderId;
        return `
            <div class="stream-item" style="margin-left:${mine ? '12%' : '0'}; margin-right:${mine ? '0' : '12%'}; background:${mine ? 'rgba(56, 189, 248, 0.1)' : 'rgba(255, 255, 255, 0.04)'};">
                <div class="stream-meta">
                    <span>${mine ? 'You' : 'Them'}</span>
                    <span>${escapeHtml(message.sent_at)}</span>
                </div>
                <div>${escapeHtml(message.message_text)}</div>
            </div>
        `;
    }).join('');
}

async function loadMessages() {
    const peerId = Number(peerSelect.value);
    const url = `../api/fetch_messages.php?user_a=${encodeURIComponent(senderId)}&user_b=${encodeURIComponent(peerId)}&since_id=0`;
    const response = await fetch(url);
    const data = await response.json();

    if (!response.ok) {
        statusBox.textContent = data.error || 'Chat unavailable.';
        stream.innerHTML = '';
        return;
    }

    statusBox.textContent = 'Chat is open.';
    renderMessages(Array.isArray(data.messages) ? data.messages : []);
}

messageForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    const payload = {
        sender_id: senderId,
        receiver_id: Number(peerSelect.value),
        message_text: messageText.value
    };

    const response = await fetch('../api/send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });
    const data = await response.json();

    if (!response.ok) {
        statusBox.textContent = data.error || 'Message failed.';
        return;
    }

    messageText.value = '';
    statusBox.textContent = 'Message sent.';
    loadMessages();
});

peerSelect.addEventListener('change', loadMessages);

loadMessages();
setInterval(loadMessages, 4000);
</script>
<?php
layout_footer();