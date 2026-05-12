document.addEventListener('DOMContentLoaded', function() {
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

            const statusBadge = document.getElementById('viewStatus');
            statusBadge.textContent = data.status;
            statusBadge.className = 'badge badge-' + data.status.toLowerCase().replace(' ', '-');

            viewModal.style.display = 'block';
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

            editModal.style.display = 'block';
        });
    });

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
