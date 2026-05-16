document.addEventListener('DOMContentLoaded', function () {
    const remindButtons = document.querySelectorAll('.remind-btn');

    remindButtons.forEach(button => {
        button.addEventListener('click', function () {
            const recordId = this.getAttribute('data-record-id');
            const studentName = this.getAttribute('data-student-name');
            const bookTitle = this.getAttribute('data-book-title');

            Swal.fire({
                title: 'Send Reminder?',
                text: `Send an email reminder to ${studentName} for the book "${bookTitle}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, send it!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('process/send_overdue_reminder.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `record_id=${recordId}`
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(response.statusText)
                            }
                            return response.json()
                        })
                        .catch(error => {
                            Swal.showValidationMessage(
                                `Request failed: ${error}`
                            )
                        })
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.success) {
                        Swal.fire({
                            title: 'Sent!',
                            text: result.value.message,
                            icon: 'success'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: result.value.message,
                            icon: 'error'
                        });
                    }
                }
            });
        });
    });

    // Notify All Overdue
    const notifyAllBtn = document.querySelector('.notify-all-btn');
    if (notifyAllBtn) {
        notifyAllBtn.addEventListener('click', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Notify All Overdue?',
                text: 'This will send email reminders and dashboard notifications to ALL students with overdue books.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, notify everyone!',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch('process/notify_all_overdue.php', {
                        method: 'POST'
                    })
                        .then(response => {
                            if (!response.ok) throw new Error(response.statusText);
                            return response.json();
                        })
                        .catch(error => {
                            Swal.showValidationMessage(`Request failed: ${error}`);
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    if (result.value.success) {
                        Swal.fire('Notifications Sent!', result.value.message, 'success');
                    } else {
                        Swal.fire('Error!', result.value.message, 'error');
                    }
                }
            });
        });
    }

    // Auto-hide alerts if they exist in the DOM
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
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
            if (sidebar && sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== sidebarToggle) {
                sidebar.classList.remove('active');
            }
        });
    }
});
