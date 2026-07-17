<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../core/database.php';
require_once __DIR__ . '/../../../core/helpers.php';
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../core/layout.php';

if (isset($_GET['api']) && $_GET['api'] === 'calculate') {
    try {
        $data = read_json_body();
        if (!$data) {
            $data = $_POST;
        }

        $totalBill = clean_float($data['total_bill'] ?? 0);
        $billName = clean_string($data['bill_name'] ?? 'Shared Bill', 120);
        $roommates = $data['roommates'] ?? [];
        $save = filter_var($data['save'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($totalBill <= 0) {
            json_response(['success' => false, 'message' => 'Total bill must be greater than zero.'], 422);
        }

        if (!is_array($roommates) || count($roommates) < 1) {
            json_response(['success' => false, 'message' => 'At least one roommate is required.'], 422);
        }

        $cleanRoommates = [];
        $combinedIncome = 0.0;

        foreach ($roommates as $index => $roommate) {
            if (!is_array($roommate)) {
                continue;
            }
            $name = clean_string($roommate['name'] ?? 'Roommate ' . ($index + 1), 120);
            $income = clean_float($roommate['income'] ?? 0);
            if ($income <= 0) {
                continue;
            }
            $cleanRoommates[] = ['name' => $name, 'income' => $income];
            $combinedIncome += $income;
        }

        if ($combinedIncome <= 0 || count($cleanRoommates) === 0) {
            json_response(['success' => false, 'message' => 'Combined income must be greater than zero.'], 422);
        }

        $breakdown = [];
        foreach ($cleanRoommates as $roommate) {
            $percentage = $roommate['income'] / $combinedIncome;
            $share = $percentage * $totalBill;

            $breakdown[] = [
                'name' => $roommate['name'],
                'income' => (float) money($roommate['income']),
                'percentage_share' => (float) money($percentage * 100),
                'contribution' => (float) money($share),
                'contribution_formatted' => money($share),
                'formula' => '(' . money($roommate['income']) . ' / ' . money($combinedIncome) . ') × ' . money($totalBill),
            ];
        }

        $billLogId = null;
        if ($save) {
            $user = auth_require_login();
            $pdo = db();
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO bill_logs (created_by, bill_name, total_bill, combined_income) VALUES (:created_by, :bill_name, :total_bill, :combined_income)");
            $stmt->execute([
                'created_by' => $user['id'],
                'bill_name' => $billName,
                'total_bill' => $totalBill,
                'combined_income' => $combinedIncome,
            ]);
            $billLogId = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO bill_log_roommates (bill_log_id, roommate_name, income, contribution, percentage_share) VALUES (:bill_log_id, :roommate_name, :income, :contribution, :percentage_share)");
            foreach ($breakdown as $row) {
                $stmt->execute([
                    'bill_log_id' => $billLogId,
                    'roommate_name' => $row['name'],
                    'income' => $row['income'],
                    'contribution' => $row['contribution'],
                    'percentage_share' => $row['percentage_share'],
                ]);
            }

            $pdo->commit();
        }

        json_response([
            'success' => true,
            'bill_log_id' => $billLogId,
            'total_bill' => (float) money($totalBill),
            'combined_income' => (float) money($combinedIncome),
            'breakdown' => $breakdown,
        ]);
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        json_response(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

$authUser = auth_require_login();

layout_header('Bill Split Calculator', [
    'description' => 'Split household bills proportionally by income.',
]);
?>

    <div class="page-shell">
        <header class="page-header">
            <div>
                <h1>Fair Household Bill Split</h1>
                <p class="lede">Each person pays based on their income share. Add roommates, enter the bill, and see who owes what.</p>
            </div>
        </header>

        <section class="card">
            <form id="expenseForm" novalidate>
                <div class="form-group">
                    <label for="billName">Bill Name</label>
                    <input type="text" id="billName" value="Internet Bill">
                </div>

                <div class="form-group">
                    <label for="totalBill">Total Bill Amount</label>
                    <input type="number" id="totalBill" min="0" step="0.01" value="200">
                </div>

                <h3>Roommates</h3>
                <div id="roommateRows"></div>
                <button type="button" id="addRoommate" class="secondary-button">Add Roommate</button>

                <div class="form-actions">
                    <button type="button" id="saveBill" class="primary-button">Save Bill Log</button>
                </div>
            </form>

            <div id="expenseResult"></div>
        </section>
    </div>

<script src="<?= rm_url('assets/js/expenses.js') ?>"></script>
<?php
layout_footer();