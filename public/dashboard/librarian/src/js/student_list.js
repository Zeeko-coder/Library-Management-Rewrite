function viewProfile(data) {
    document.getElementById('modalFullName').textContent = data.name;
    document.getElementById('modalAvatar').textContent = data.initials;
    document.getElementById('modalUsername').textContent = data.username;
    document.getElementById('modalEmail').textContent = data.email;
    document.getElementById('modalBookTitle').textContent = data.book_titles;
    document.getElementById('modalBookCopies').textContent = data.copies;

    document.getElementById('viewProfileModal').classList.add('active');
}

function closeProfileModal() {
    document.getElementById('viewProfileModal').classList.remove('active');
}

// Close modal when clicking outside
window.onclick = function(event) {
    let modal = document.getElementById('viewProfileModal');
    if (event.target == modal) {
        closeProfileModal();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    const pageTitle = document.getElementById('pageTitle');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.getAttribute('data-tab');

            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            // Hide badge count when visited
            const badge = tab.querySelector('.badge-count');
            if (badge) badge.style.display = 'none';

            contents.forEach(c => c.style.display = 'none');
            document.getElementById(target + 'Tab').style.display = 'block';

            if (target === 'all') pageTitle.innerText = 'Student Directory';
            else if (target === 'requests') pageTitle.innerText = 'Borrow Requests';
            else if (target === 'overdue') pageTitle.innerText = 'Overdue Monitoring';
            else if (target === 'active') pageTitle.innerText = 'Active Borrowing Students';
            else if (target === 'returned') pageTitle.innerText = 'Returned Books History';
        });
    });

    // Simple Search
    const searchInput = document.querySelector('.header-search input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('.tab-content:not([style*="display: none"]) .users-table tbody tr');
            rows.forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }
});
