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
window.onclick = function (event) {
    let modal = document.getElementById('viewProfileModal');
    if (event.target == modal) {
        closeProfileModal();
    }
}

document.addEventListener('DOMContentLoaded', function () {
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

    // Tab-specific Search
    const tabSearchInputs = document.querySelectorAll('.tab-search-input');
    tabSearchInputs.forEach(input => {
        input.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            const target = this.getAttribute('data-target');
            const table = document.getElementById(target + 'Table');
            if (!table) return;

            const rows = table.querySelectorAll('tbody tr:not(.empty-row)');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const isMatch = text.includes(term);
                row.style.display = isMatch ? '' : 'none';
                if (isMatch) visibleCount++;
            });
        });
    });

    // Sync header search with active tab search
    const headerSearchInput = document.querySelector('.header-search input');
    if (headerSearchInput) {
        headerSearchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            const activeTab = document.querySelector('.tab-content:not([style*="display: none"])');
            if (!activeTab) return;

            const tabSearch = activeTab.querySelector('.tab-search-input');
            if (tabSearch) {
                tabSearch.value = this.value;
                tabSearch.dispatchEvent(new Event('input'));
            }
        });
    }

    // Handle Tab via URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    if (tabParam) {
        const targetTab = document.querySelector(`.tab-btn[data-tab="${tabParam}"]`);
        if (targetTab) {
            targetTab.click();
        }
    }
});
