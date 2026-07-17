const listingIdInput = document.getElementById('listingId');
const bookingDateInput = document.getElementById('bookingDate');
const slotGrid = document.getElementById('slotGrid');
const bookingMessage = document.getElementById('bookingMessage');

const today = new Date().toISOString().slice(0, 10);
bookingDateInput.value = today;
bookingDateInput.min = today;

function makeSlots() {
    const slots = [];
    for (let hour = 9; hour < 17; hour++) {
        for (const minute of [0, 30]) {
            const hh = String(hour).padStart(2, '0');
            const mm = String(minute).padStart(2, '0');
            slots.push(`${hh}:${mm}`);
        }
    }
    return slots;
}

function overlaps(slotStart, bookedStart, bookedEnd) {
    const slotEnd = new Date(slotStart.getTime() + 30 * 60000);
    return slotStart < bookedEnd && slotEnd > bookedStart;
}

async function loadSlots(clearMessage = true) {
    if (clearMessage) {
        bookingMessage.innerHTML = '';
    }
    slotGrid.innerHTML = '<p>Loading slots...</p>';

    const params = new URLSearchParams({
        api: 'available_slots',
        listing_id: listingIdInput.value,
        date: bookingDateInput.value
    });

    try {
        const response = await fetch(`booking.php?${params.toString()}`);
        const data = await response.json();

        if (!data.success) {
            slotGrid.innerHTML = `<p>${data.message}</p>`;
            return;
        }

        const booked = data.booked.map(item => ({
            start: new Date(item.start_time.replace(' ', 'T')),
            end: new Date(item.end_time.replace(' ', 'T'))
        }));

        slotGrid.innerHTML = makeSlots().map(time => {
            const slotStart = new Date(`${bookingDateInput.value}T${time}:00`);
            const disabled = booked.some(block => overlaps(slotStart, block.start, block.end));
            return `<button type="button" class="slot-btn ${disabled ? 'booked' : ''}" data-time="${time}" ${disabled ? 'disabled' : ''}>${time}</button>`;
        }).join('');

        document.querySelectorAll('.slot-btn:not(.booked)').forEach(button => {
            button.addEventListener('click', () => bookSlot(button.dataset.time));
        });
    } catch (error) {
        slotGrid.innerHTML = '<p>Unable to load available slots.</p>';
    }
}

async function bookSlot(time) {
    document.querySelectorAll('.slot-btn').forEach(button => button.classList.remove('selected'));
    const selectedButton = document.querySelector(`.slot-btn[data-time="${time}"]`);
    selectedButton.classList.add('selected');

    bookingMessage.innerHTML = '<div class="status-box">Placing your booking...</div>';

    const payload = {
        listing_id: Number(listingIdInput.value),
        start_time: `${bookingDateInput.value} ${time}:00`
    };

    try {
        const response = await fetch('booking.php?api=book_viewing', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await response.json();

        if (response.status === 409) {
            bookingMessage.innerHTML = `<div class="alert alert-error">Sorry, this slot is already booked. Please choose another time.</div>`;
            await loadSlots(false);
            return;
        }

        if (!data.success) {
            bookingMessage.innerHTML = `<div class="alert alert-error">${data.message || 'Booking failed. Please try again.'}</div>`;
            return;
        }

        bookingMessage.innerHTML = `
            <div class="alert alert-success">
                ${data.message}<br>
                Selected time: ${data.appointment.start_time} to ${data.appointment.end_time}
            </div>
        `;
        await loadSlots(false);
    } catch (error) {
        bookingMessage.innerHTML = '<div class="alert alert-error">Something went wrong. Please try again.</div>';
    }
}

[listingIdInput, bookingDateInput].forEach(input => input.addEventListener('change', () => loadSlots(true)));
loadSlots(true);
