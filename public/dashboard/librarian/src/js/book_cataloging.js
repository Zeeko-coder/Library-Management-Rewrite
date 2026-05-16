document.addEventListener('DOMContentLoaded', function () {
    // Modals
    const viewModal = document.getElementById('viewBookModal');
    const editModal = document.getElementById('editBookModal');

    // Buttons
    const viewBtns = document.querySelectorAll('.btn-view');
    const editBtns = document.querySelectorAll('.btn-edit');
    const closeBtns = document.querySelectorAll('.close-modal');


    // Open View Modal with Data
    viewBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const data = btn.dataset;

            document.getElementById('viewTitle').textContent = data.title;
            document.getElementById('viewAuthor').textContent = 'By ' + data.author;
            document.getElementById('viewID').textContent = '#' + data.id;
            document.getElementById('viewCategory').textContent = data.category;
            document.getElementById('viewCopies').textContent = data.copies;
            document.getElementById('viewDate').textContent = data.date;
            document.getElementById('viewYear').textContent = data.year || 'N/A';
            document.getElementById('viewDescription').textContent = data.description || 'No description available.';

            const statusBadge = document.getElementById('viewStatus');
            statusBadge.textContent = data.status;
            statusBadge.className = 'badge badge-' + data.status.toLowerCase().replace(' ', '-');

            // Handle Image
            const viewCover = document.getElementById('viewBookCover');
            if (data.image) {
                viewCover.innerHTML = `<img src="${data.image}" style="width: 100%; height: 100%; object-fit: cover;">`;
            } else {
                viewCover.innerHTML = `<i class="fas fa-book"></i>`;
            }

            viewModal.style.display = 'flex';
        });
    });

    // Open Edit Modal with Data
    editBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const data = btn.dataset;

            document.getElementById('editBookID').value = data.id;
            document.getElementById('editTitle').value = data.title;
            document.getElementById('editAuthor').value = data.author;
            document.getElementById('editCategory').value = data.category;
            document.getElementById('editCopies').value = data.copies;
            document.getElementById('editStatus').value = data.status;
            document.getElementById('editYear').value = data.year || '';
            document.getElementById('editDescription').value = data.description || '';

            // Handle Image Preview
            const editPreview = document.getElementById('editImagePreview');
            if (data.image) {
                editPreview.innerHTML = `<img src="${data.image}" style="width: 100%; height: 100%; object-fit: cover;">`;
            } else {
                editPreview.innerHTML = `<i class="fas fa-book" style="color: var(--primary-color);"></i>`;
            }

            editModal.style.display = 'flex';
        });
    });

    // Auto-update status when copies reach 0
    const editCopiesInput = document.getElementById('editCopies');
    const editStatusSelect = document.getElementById('editStatus');

    if (editCopiesInput && editStatusSelect) {
        editCopiesInput.addEventListener('input', function () {
            if (parseInt(this.value) <= 0) {
                editStatusSelect.value = 'Not Available';
            }
        });
    }

    // Live preview for image upload
    const editBookImage = document.getElementById('editBookImage');
    const editImagePreview = document.getElementById('editImagePreview');

    if (editBookImage && editImagePreview) {
        editBookImage.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    editImagePreview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Close Modals
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (viewModal) viewModal.style.display = 'none';
            if (editModal) editModal.style.display = 'none';
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === viewModal) viewModal.style.display = 'none';
        if (e.target === editModal) editModal.style.display = 'none';
    });
});
