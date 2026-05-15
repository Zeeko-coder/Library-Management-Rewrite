document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('tableSearchInput');
    const tabs = document.querySelectorAll('.tab-btn');
    const rows = document.querySelectorAll('.user-row');
    const emptyRow = document.getElementById('emptyStateRow');
    const emptyMsg = document.getElementById('emptyStateMessage');

    function filterUsers() {
        const activeTab = document.querySelector('.tab-btn.active');
        const filter = activeTab ? activeTab.getAttribute('data-filter') : 'all';
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        rows.forEach(row => {
            const role = row.getAttribute('data-role');
            const status = row.getAttribute('data-status');
            const name = row.querySelector('.user-name').textContent.toLowerCase();
            const email = row.querySelector('.user-email').textContent.toLowerCase();

            let tabVisible = false;
            if (filter === 'all') {
                tabVisible = true;
            } else if (filter === 'Pending') {
                tabVisible = (status === 'Pending');
            } else if (filter === 'Librarian' || filter === 'Student') {
                tabVisible = (role === filter && status === 'Approved');
            }

            const searchVisible = name.includes(searchTerm) || email.includes(searchTerm);

            if (tabVisible && searchVisible) {
                row.style.display = 'table-row';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Handle empty state
        if (visibleCount === 0) {
            emptyRow.style.display = 'table-row';
            if (searchTerm) {
                emptyMsg.textContent = `No users found matching "${searchTerm}"`;
            } else if (filter === 'Pending') {
                emptyMsg.textContent = "No pending request found";
            } else {
                emptyMsg.textContent = "No " + filter.toLowerCase() + "s found in the database.";
            }
        } else {
            emptyRow.style.display = 'none';
        }
    }

    // Tabs only filter the currently visible/loaded results
    tabs.forEach(btn => {
        btn.addEventListener('click', () => {
            tabs.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filterUsers();
        });
    });

    // Note: Search is now handled via server-side GET request (form submission)

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
