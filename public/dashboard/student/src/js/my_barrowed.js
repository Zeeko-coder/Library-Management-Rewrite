document.addEventListener('DOMContentLoaded', function() {
    // Student Borrowed History specific JS logic
    const searchInput = document.querySelector('.header-search input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const rows = document.querySelectorAll('.loans-table tbody tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // Return Book Modal Logic
    const returnModal = document.getElementById('returnModal');
    const returnTitle = document.getElementById('returnBookTitle');
    const returnIdInput = document.getElementById('returnBorrowId');
    const closeBtns = document.querySelectorAll('.close-modal');

    document.querySelectorAll('.open-return-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            
            returnTitle.textContent = title;
            returnIdInput.value = id;
            returnModal.style.display = 'block';
        });
    });

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            returnModal.style.display = 'none';
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === returnModal) {
            returnModal.style.display = 'none';
        }
    });
});
