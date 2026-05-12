document.addEventListener('DOMContentLoaded', function() {
    // Modals
    const addModal = document.getElementById('addBookModal');
    const viewModal = document.getElementById('viewBookModal');
    const editModal = document.getElementById('editBookModal');

    // Buttons
    const openAddBtn = document.getElementById('openAddModal');
    const viewBtns = document.querySelectorAll('.btn-view');
    const editBtns = document.querySelectorAll('.btn-edit');
    const deleteBtns = document.querySelectorAll('.btn-delete');
    const closeBtns = document.querySelectorAll('.close-modal');

    // Open Add Modal
    openAddBtn.addEventListener('click', () => {
        addModal.style.display = 'block';
    });

    // Delete Confirmation Logic
    const deleteModal = document.getElementById('deleteBookModal');
    const deleteTitle = document.getElementById('deleteBookTitle');
    const deleteIdInput = document.getElementById('deleteBookId');

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const bookId = btn.dataset.id;
            const bookTitle = btn.dataset.title;
            
            deleteTitle.textContent = bookTitle;
            deleteIdInput.value = bookId;
            deleteModal.style.display = 'block';
        });
    });

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
            addModal.style.display = 'none';
            viewModal.style.display = 'none';
            editModal.style.display = 'none';
            deleteModal.style.display = 'none';
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === addModal) addModal.style.display = 'none';
        if (e.target === viewModal) viewModal.style.display = 'none';
        if (e.target === editModal) editModal.style.display = 'none';
        if (e.target === deleteModal) deleteModal.style.display = 'none';
    });
});
