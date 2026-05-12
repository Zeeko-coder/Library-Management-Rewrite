document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.getAttribute('data-filter');
            if (!filter) return; // Skip if it's the badge-only part or something

            // Update active tab
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Filter rows
            let visibleCount = 0;
            const rows = document.querySelectorAll('.user-row');
            const emptyRow = document.getElementById('emptyStateRow');
            const emptyMsg = document.getElementById('emptyStateMessage');

            rows.forEach(row => {
                const role = row.getAttribute('data-role');
                const status = row.getAttribute('data-status');
                let isVisible = false;

                if (filter === 'all') {
                    isVisible = true;
                } else if (filter === 'Pending') {
                    isVisible = (status === 'Pending');
                } else if (filter === 'Librarian' || filter === 'Student') {
                    isVisible = (role === filter && status === 'Approved');
                }

                if (isVisible) {
                    row.style.display = 'table-row';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Handle empty state
            if (visibleCount === 0) {
                emptyRow.style.display = 'table-row';
                if (filter === 'Pending') {
                    emptyMsg.textContent = "No pending request found";
                } else {
                    emptyMsg.textContent = "No " + filter.toLowerCase() + "s found in the database.";
                }
            } else {
                emptyRow.style.display = 'none';
            }
        });
    });

    // Delete User Modal Logic
    const deleteModal = document.getElementById('deleteUserModal');
    const deleteName = document.getElementById('deleteUserName');
    const deleteIdInput = document.getElementById('deleteUserId');
    const closeBtns = document.querySelectorAll('.close-modal');

    // Handle opening modal
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-delete-modal');
        if (btn) {
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            
            deleteName.textContent = name;
            deleteIdInput.value = id;
            deleteModal.style.display = 'block';
        }
    });

    // Handle closing modal
    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            deleteModal.style.display = 'none';
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === deleteModal) {
            deleteModal.style.display = 'none';
        }
    });
});
