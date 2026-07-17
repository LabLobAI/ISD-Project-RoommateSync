<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/database.php';
require_once __DIR__ . '/../../../core/helpers.php';
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../core/layout.php';

$currentUserId = auth_user_id();
$currentUser = auth_user();

$peopleStmt = db()->prepare('SELECT id, full_name, email FROM users WHERE id <> :id ORDER BY full_name');
$peopleStmt->execute(['id' => $currentUserId]);
$people = $peopleStmt->fetchAll();
$selectedRevieweeId = isset($_GET['reviewee_id']) ? max(1, (int) $_GET['reviewee_id']) : ($people[0]['id'] ?? 0);

layout_header('Peer Review', [
    'description' => 'Submit a review for a matched roommate and see the aggregated score update live.',
    'noindex' => true,
]);
?>

    <div class="page-shell">
        <header class="page-header">
            <div>
                <p class="eyebrow">Social</p>
                <h1>Peer Review</h1>
                <p class="lede">Submit a review for a matched roommate and see the aggregated score update live.</p>
            </div>
            <nav class="header-links" aria-label="Social navigation">
                <a class="ghost-link" href="<?= rm_url('index.php') ?>">Home</a>
                <a class="ghost-link" href="<?= rm_url('modules/social/frontend/chat.php') ?>">Chat</a>
                <a class="ghost-link" href="<?= rm_url('modules/social/frontend/connect.php') ?>">Connect</a>
            </nav>
        </header>

        <div class="page-content">
            <article class="card">
                <h2 class="card-title">Write a Review</h2>

                <form id="reviewForm">
                    <input type="hidden" id="reviewerId" value="<?= (int) $currentUserId ?>">
                    <div class="form-group">
                        <label for="revieweeId">Reviewee</label>
                        <select id="revieweeId" required>
                            <?php foreach ($people as $person): ?>
                                <option value="<?= (int) $person['id'] ?>" <?= (int) $person['id'] === (int) $selectedRevieweeId ? 'selected' : '' ?>>
                                    <?= h($person['full_name']) ?> (<?= h($person['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cleanliness">Cleanliness score</label>
                        <input id="cleanliness" type="range" min="1" max="5" value="5">
                    </div>

                    <div class="form-group">
                        <label for="communication">Communication score</label>
                        <input id="communication" type="range" min="1" max="5" value="4">
                    </div>

                    <div class="form-group">
                        <label for="feedback">Written feedback</label>
                        <textarea id="feedback" placeholder="Short, useful feedback for the roommate..."></textarea>
                    </div>

                    <div class="action-row">
                        <button class="primary-button" type="submit">Submit review</button>
                    </div>
                </form>

                <div id="reviewStatus" class="status-box">Ready to send.</div>
            </article>

            <aside class="card">
                <h2 class="card-title" id="summaryName">Review Summary</h2>

                <div class="review-metrics">
                    <div class="metric">
                        <span>Total reviews</span>
                        <strong id="totalReviews">0</strong>
                    </div>
                    <div class="metric">
                        <span>Average cleanliness</span>
                        <strong id="avgCleanliness">-</strong>
                    </div>
                    <div class="metric">
                        <span>Average communication</span>
                        <strong id="avgCommunication">-</strong>
                    </div>
                </div>

                <div class="status-box" id="aggregateScore">No reviews yet.</div>
            </aside>
        </div>
    </div>

<script>
const reviewForm = document.getElementById('reviewForm');
const reviewStatus = document.getElementById('reviewStatus');
const revieweeSelect = document.getElementById('revieweeId');
const summaryName = document.getElementById('summaryName');
const totalReviews = document.getElementById('totalReviews');
const avgCleanliness = document.getElementById('avgCleanliness');
const avgCommunication = document.getElementById('avgCommunication');
const aggregateScore = document.getElementById('aggregateScore');

function selectedName() {
    return revieweeSelect.options[revieweeSelect.selectedIndex]?.textContent || 'Roommate';
}

async function loadSummary(userId) {
    summaryName.textContent = selectedName();
    const response = await fetch(`../api/get_user_reviews.php?user_id=${encodeURIComponent(userId)}`);
    const data = await response.json();

    if (!response.ok) {
        aggregateScore.textContent = data.error || 'Unable to load review summary.';
        return;
    }

    totalReviews.textContent = data.total_reviews ?? 0;
    avgCleanliness.textContent = data.avg_cleanliness ? Number(data.avg_cleanliness).toFixed(2) : '-';
    avgCommunication.textContent = data.avg_communication ? Number(data.avg_communication).toFixed(2) : '-';
    aggregateScore.textContent = data.aggregated_score ? `Overall score: ${Number(data.aggregated_score).toFixed(2)} / 5` : 'No reviews yet.';
}

revieweeSelect.addEventListener('change', () => loadSummary(revieweeSelect.value));

reviewForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    reviewStatus.textContent = 'Submitting review...';

    const payload = {
        reviewer_id: Number(document.getElementById('reviewerId').value),
        reviewee_id: Number(revieweeSelect.value),
        cleanliness_score: Number(document.getElementById('cleanliness').value),
        communication_score: Number(document.getElementById('communication').value),
        written_feedback: document.getElementById('feedback').value
    };

    const response = await fetch('../api/submit_review.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });
    const data = await response.json();

    if (!response.ok) {
        reviewStatus.textContent = data.error || 'Review submission failed.';
        return;
    }

    reviewStatus.textContent = `Review saved. Review ID ${data.review_id}.`;
    loadSummary(revieweeSelect.value);
});

loadSummary(revieweeSelect.value);
</script>
<?php
layout_footer();