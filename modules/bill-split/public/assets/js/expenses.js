const billNameInput = document.getElementById('billName');
const totalBillInput = document.getElementById('totalBill');
const roommateRows = document.getElementById('roommateRows');
const addRoommateButton = document.getElementById('addRoommate');
const saveBillButton = document.getElementById('saveBill');
const expenseResult = document.getElementById('expenseResult');

function addRoommate(name = '', income = '') {
    const row = document.createElement('div');
    row.className = 'roommate-row';
    row.innerHTML = `
        <input type="text" class="roommate-name" placeholder="Name" value="${name}">
        <input type="number" class="roommate-income" placeholder="Monthly income" min="0" step="0.01" value="${income}">
        <button type="button" class="danger remove-roommate">Remove</button>
    `;
    roommateRows.appendChild(row);

    row.querySelector('.remove-roommate').addEventListener('click', () => {
        row.remove();
        calculate(false);
    });
    row.querySelectorAll('input').forEach(input => input.addEventListener('input', () => calculate(false)));
}

function getPayload(save = false) {
    const roommates = [...document.querySelectorAll('.roommate-row')].map((row, index) => ({
        name: row.querySelector('.roommate-name').value || `Roommate ${index + 1}`,
        income: Number(row.querySelector('.roommate-income').value || 0)
    }));

    return {
        bill_name: billNameInput.value || 'Shared Bill',
        total_bill: Number(totalBillInput.value || 0),
        roommates,
        save
    };
}

async function calculate(save = false) {
    try {
        const response = await fetch('expenses.php?api=calculate', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(getPayload(save))
        });
        const data = await response.json();

        if (!data.success) {
            expenseResult.innerHTML = `<div class="message error">${data.message}</div>`;
            return;
        }

        expenseResult.innerHTML = `
            <div class="message ${data.bill_log_id ? 'success' : ''}">
                Total Bill: ৳${Number(data.total_bill).toLocaleString()} · Combined Income: ৳${Number(data.combined_income).toLocaleString()}
                ${data.bill_log_id ? ` · Saved Bill ID: ${data.bill_log_id}` : ''}
            </div>
            <table class="breakdown-table">
                <thead><tr><th>Name</th><th>Income</th><th>Share %</th><th>Contribution</th><th>Formula</th></tr></thead>
                <tbody>
                    ${data.breakdown.map(row => `
                        <tr>
                            <td>${row.name}</td>
                            <td>৳${Number(row.income).toLocaleString()}</td>
                            <td>${row.percentage_share}%</td>
                            <td><strong>৳${Number(row.contribution).toLocaleString()}</strong></td>
                            <td>${row.formula}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    } catch (error) {
        expenseResult.innerHTML = '<div class="message error">Unable to calculate split.</div>';
    }
}

addRoommateButton.addEventListener('click', () => {
    addRoommate();
    calculate(false);
});
saveBillButton.addEventListener('click', () => calculate(true));
totalBillInput.addEventListener('input', () => calculate(false));
billNameInput.addEventListener('input', () => calculate(false));

addRoommate('Roommate A', 3000);
addRoommate('Roommate B', 5000);
calculate(false);
