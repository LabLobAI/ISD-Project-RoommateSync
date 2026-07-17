const maxPriceInput = document.getElementById('maxPrice');
const priceLabel = document.getElementById('priceLabel');
const roomTypeInput = document.getElementById('roomType');
const locationInput = document.getElementById('location');
const listingGrid = document.getElementById('listingGrid');

let timer = null;

function money(value) {
    return Number(value).toLocaleString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function loadListings() {
    priceLabel.textContent = maxPriceInput.value;
    listingGrid.innerHTML = '<p>Loading listings...</p>';

    const params = new URLSearchParams({
        api: 'listings',
        max_price: maxPriceInput.value,
        room_type: roomTypeInput.value,
        location: locationInput.value
    });

    try {
        const response = await fetch(`listings.php?${params.toString()}`);
        const data = await response.json();

        if (!data.success) {
            listingGrid.innerHTML = `<div class="alert alert-error">${escapeHtml(data.message)}</div>`;
            return;
        }

        if (data.listings.length === 0) {
            listingGrid.innerHTML = '<p>No listings matched your filters.</p>';
            return;
        }

        listingGrid.innerHTML = data.listings.map(listing => `
            <article class="listing-card">
                ${listing.image_url ? `<img src="${escapeHtml(listing.image_url)}" alt="Room photo">` : ''}
                <div class="listing-card-body">
                    <h3>${escapeHtml(listing.title)}</h3>
                    <p class="price">৳${money(listing.rent)} / month</p>
                    <p class="meta">${escapeHtml(listing.location_text)} · ${escapeHtml(listing.room_type)} · ${listing.bedrooms} bed · ${listing.bathrooms} bath</p>
                    <p class="meta">Landlord: ${escapeHtml(listing.landlord_name)}</p>
                </div>
            </article>
        `).join('');
    } catch (error) {
        listingGrid.innerHTML = '<div class="alert alert-error">Unable to load listings.</div>';
    }
}

function debounceLoad() {
    clearTimeout(timer);
    timer = setTimeout(loadListings, 250);
}

[maxPriceInput, roomTypeInput, locationInput].forEach(input => {
    input.addEventListener('input', debounceLoad);
    input.addEventListener('change', loadListings);
});

loadListings();
