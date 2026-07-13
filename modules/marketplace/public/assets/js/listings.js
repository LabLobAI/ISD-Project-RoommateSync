const maxPriceInput = document.getElementById('maxPrice');
const priceLabel = document.getElementById('priceLabel');
const roomTypeInput = document.getElementById('roomType');
const locationInput = document.getElementById('location');
const listingGrid = document.getElementById('listingGrid');

let timer = null;

function money(value) {
    return Number(value).toLocaleString();
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
            listingGrid.innerHTML = `<div class="message error">${data.message}</div>`;
            return;
        }

        if (data.listings.length === 0) {
            listingGrid.innerHTML = '<p>No listings matched your filters.</p>';
            return;
        }

        listingGrid.innerHTML = data.listings.map(listing => `
            <article class="card listing-card">
                ${listing.image_url ? `<img src="${listing.image_url}" alt="Room photo">` : ''}
                <h3>${listing.title}</h3>
                <p>${listing.description || ''}</p>
                <p><strong>${listing.location_text}</strong></p>
                <p><span class="badge">৳${money(listing.rent)}</span> <span class="badge">${listing.room_type}</span></p>
                <p>Bedrooms: ${listing.bedrooms} · Bathrooms: ${listing.bathrooms}</p>
                <p>Landlord: ${listing.landlord_name}</p>
            </article>
        `).join('');
    } catch (error) {
        listingGrid.innerHTML = '<div class="message error">Unable to load listings.</div>';
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
