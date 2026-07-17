const roomImageInput = document.getElementById('roomImage');
const previewPanel = document.getElementById('previewPanel');
const imagePreview = document.getElementById('imagePreview');
const fileInfo = document.getElementById('fileInfo');
const listingForm = document.getElementById('listingForm');
const maxSize = 5 * 1024 * 1024;

roomImageInput.addEventListener('change', () => {
    const file = roomImageInput.files[0];

    previewPanel.hidden = true;
    imagePreview.removeAttribute('src');
    fileInfo.textContent = '';

    if (!file) {
        return;
    }

    if (!['image/jpeg', 'image/png'].includes(file.type)) {
        alert('Only JPG and PNG images are allowed.');
        roomImageInput.value = '';
        return;
    }

    if (file.size > maxSize) {
        alert('Image size must not exceed 5 MB.');
        roomImageInput.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = event => {
        imagePreview.src = event.target.result;
        previewPanel.hidden = false;
    };
    reader.readAsDataURL(file);

    fileInfo.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB`;
});

listingForm.addEventListener('submit', event => {
    const file = roomImageInput.files[0];

    if (!file) {
        event.preventDefault();
        alert('Please select a room image.');
        return;
    }

    if (!['image/jpeg', 'image/png'].includes(file.type) || file.size > maxSize) {
        event.preventDefault();
        alert('Please upload a valid JPG/PNG image under 5 MB.');
    }
});
