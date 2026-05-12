function filterNotifs(type, btn) {
    // Update tabs
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    // Filter items
    document.querySelectorAll('.notif-item').forEach(item => {
        if (type === 'all' || item.dataset.type === type) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Real-time time ago updates
function updateTimeAgo() {
    document.querySelectorAll('.notif-time').forEach(el => {
        const timestamp = el.getAttribute('data-timestamp');
        if (!timestamp) return;

        const now = Math.floor(Date.now() / 1000);
        const diff = now - timestamp;

        let timeStr;
        if (diff < 60) timeStr = "Just now";
        else if (diff < 3600) timeStr = Math.floor(diff / 60) + " mins ago";
        else if (diff < 86400) timeStr = Math.floor(diff / 3600) + " hours ago";
        else {
            const date = new Date(timestamp * 1000);
            timeStr = date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
        }

        if (el.textContent !== timeStr) {
            el.textContent = timeStr;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Update every minute
    setInterval(updateTimeAgo, 60000);
    // Initial run
    updateTimeAgo();
});
