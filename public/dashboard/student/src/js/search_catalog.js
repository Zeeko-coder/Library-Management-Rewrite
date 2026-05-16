document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('borrowModal');
    const descModal = document.getElementById('descModal');
    const closeBtns = document.querySelectorAll('.close-modal');
    const borrowBtns = document.querySelectorAll('.btn-borrow[data-id]');
    const viewDescBtns = document.querySelectorAll('.btn-view-desc');

    const modalBookId = document.getElementById('modalBookId');
    const modalBookTitle = document.getElementById('modalBookTitle');
    const modalQuantity = document.getElementById('modalQuantity');
    const availableHint = document.getElementById('availableHint');

    if (borrowBtns) {
        // ... existing borrow logic ...
        borrowBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const available = this.getAttribute('data-available');

                if (modalBookId) modalBookId.value = id;
                if (modalBookTitle) modalBookTitle.value = title;
                if (modalQuantity) modalQuantity.max = available;
                if (availableHint) availableHint.textContent = 'Available copies: ' + available;

                if (modal) modal.style.display = 'flex';
            });
        });
    }

    if (viewDescBtns) {
        viewDescBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const data = this.dataset;
                document.getElementById('descTitle').textContent = data.title;
                document.getElementById('descAuthor').textContent = 'By ' + data.author;
                document.getElementById('descYear').textContent = 'Published: ' + data.year;
                document.getElementById('descText').textContent = data.desc;
                document.getElementById('descImage').src = data.image;
                
                if (descModal) descModal.style.display = 'flex';
            });
        });
    }

    if (closeBtns) {
        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                if (modal) modal.style.display = 'none';
                if (descModal) descModal.style.display = 'none';
            });
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
        if (e.target === descModal) {
            descModal.style.display = 'none';
        }
    });

    // Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== sidebarToggle) {
                sidebar.classList.remove('active');
            }
        });
    }
});
