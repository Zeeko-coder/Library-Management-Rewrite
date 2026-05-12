document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editProfileModal');
    const openBtn = document.getElementById('openEditModal');
    const closeBtns = document.querySelectorAll('.close-modal');

    if (openBtn) {
        openBtn.addEventListener('click', () => {
            editModal.style.display = 'block';
        });
    }

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            editModal.style.display = 'none';
        });
    });

    window.addEventListener('click', (e) => {
        if (e.target === editModal) {
            editModal.style.display = 'none';
        }
    });
});
