document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('borrowModal');
    const closeBtns = document.querySelectorAll('.close-modal');
    const borrowBtns = document.querySelectorAll('.btn-borrow[data-id]');

    const modalBookId = document.getElementById('modalBookId');
    const modalBookTitle = document.getElementById('modalBookTitle');
    const modalQuantity = document.getElementById('modalQuantity');
    const availableHint = document.getElementById('availableHint');

    if (borrowBtns) {
        borrowBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');
                const available = this.getAttribute('data-available');

                if (modalBookId) modalBookId.value = id;
                if (modalBookTitle) modalBookTitle.value = title;
                if (modalQuantity) modalQuantity.max = available;
                if (availableHint) availableHint.textContent = 'Available copies: ' + available;

                if (modal) modal.style.display = 'block';
            });
        });
    }

    if (closeBtns) {
        closeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                if (modal) modal.style.display = 'none';
            });
        });
    }

    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            if (modal) modal.style.display = 'none';
        }
    });
});
