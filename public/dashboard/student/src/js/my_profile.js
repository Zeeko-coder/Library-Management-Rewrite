function openModal() {
    const modal = document.getElementById('editProfileModal');
    if (modal) modal.classList.add('active');
}

function closeModal() {
    const modal = document.getElementById('editProfileModal');
    if (modal) modal.classList.remove('active');
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('editProfileModal');
    if (event.target === modal) {
        closeModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Student Profile specific JS logic
    console.log("Profile JS loaded");
});
