const billNameInput = document.getElementById('billName');
const totalBillInput = document.getElementById('totalBill');
const roommateRows = document.getElementById('roommateRows');
const addRoommateButton = document.getElementById('addRoommate');
const saveBillButton = document.getElementById('saveBill');
const expenseResult = document.getElementById('expenseResult');

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function addRoommate(name = '', income = '') {
    const row = document.createElement('div');
    row.className = 'form-group';
    row.style.display = 'grid';
    row.style.gridTemplateColumns = '1fr 1fr auto';
    row.style.gap = '10px';
    row.style.alignItems = 'end';
    row.innerHTML = `
        <input type="text" class="roommate-name" placeholder="Name" value="${name}">
        <input type="number" class="roommate-income" placeholder="Monthly income" min="0" step="0.01" value="${income}">
        <button type="button" class="secondary-button remove-roommate" style="min-height:46px;">Remove</button>
    `;
    roommateRows.appendChild(row);

    row.querySelector('.remove-roommate').addEventListener('click', () => {
        row.remove();
        calculate(false);
    });
    row.querySelectorAll('input').forEach(input => input.addEventListener('input', () => calculate(false)));
}

function getPayload(save = false) {
    const roommates = [...document.querySelectorAll('.form-group .roommate-name')].map((input, index) => ({
        name: input.value || `Roommate ${index + 1}`,
        income: Number(input.closest('.form-group').querySelector('.roommate-income').value || 0)
    }));

    return {
        bill_name: billNameInput.value || 'Shared Bill',
        total_bill: Number(totalBillInput.value || 0),
        roommates,
        save
    };
}

async function calculate(save = false) {
    const roommates = getPayload(save).roommates;
    if (roommates.length === 0 || getPayload().total_bill <= 0) {
        return;
    }

    try {
        const response = await fetch('expenses.php?api=calculate', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(getPayload(save))
        });
        const data = await response.json();

        if (!data.success) {
            expenseResult.innerHTML = `<div class="alert alert-error">${data.message}</div>`;
            return;
        }

        expenseResult.innerHTML = `
            <div class="alert alert-success">
                Total Bill: ৳${Number(data.total_bill).toLocaleString()} · Combined Income: ৳${Number(data.combined_income).toLocaleString()}
                ${data.bill_log_id ? ` · Saved as Bill #${data.bill_log_id}` : ''}
            </div>
            <table style="width:100%; border-collapse:collapse; margin-top:12px;">
                <thead><tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                    <th style="padding:10px; text-align:left; color:var(--muted);">Name</th>
                    <th style="padding:10px; text-align:right; color:var(--muted);">Income</th>
                    <th style="padding:10px; text-align:right; color:var(--muted);">Share %</th>
                    <th style="padding:10px; text-align:right; color:var(--muted);">Contribution</th>
                </tr></thead>
                <tbody>
                    ${data.breakdown.map(row => `
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                            <td style="padding:10px;">${escapeHtml(row.name)}</td>
                            <td style="padding:10px; text-align:right;">৳${Number(row.income).toLocaleString()}</td>
                            <td style="padding:10px; text-align:right;">${row.percentage_share}%</td>
                            <td style="padding:10px; text-align:right; font-weight:700; color:var(--accent);">৳${Number(row.contribution).toLocaleString()}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    } catch (error) {
        expenseResult.innerHTML = '<div class="alert alert-error">Unable to calculate split.</div>';
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
