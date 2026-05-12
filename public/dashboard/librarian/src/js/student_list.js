function viewProfile(data) {
    document.getElementById('modalFullName').textContent = data.name;
    document.getElementById('modalAvatar').textContent = data.initials;
    document.getElementById('modalReturnedCount').textContent = data.returned;
    document.getElementById('modalActiveCount').textContent = data.active;
    document.getElementById('modalUsername').textContent = data.username;
    document.getElementById('modalEmail').textContent = data.email;

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

            contents.forEach(c => c.style.display = 'none');
            document.getElementById(target + 'Tab').style.display = 'block';

            if (target === 'all') pageTitle.innerText = 'Student Directory';
            else if (target === 'requests') pageTitle.innerText = 'Borrow Requests';
            else if (target === 'overdue') pageTitle.innerText = 'Overdue Monitoring';
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
