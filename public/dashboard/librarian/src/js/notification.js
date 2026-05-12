document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab-btn');
    const items = document.querySelectorAll('.notif-item');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const filter = tab.getAttribute('data-filter');
            
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            items.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-type') === filter) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Dismiss functionality (local only for now)
    const dismissBtns = document.querySelectorAll('.btn-dismiss');
    dismissBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const item = btn.closest('.notif-item');
            item.style.opacity = '0';
            setTimeout(() => item.remove(), 300);
        });
    });
});
