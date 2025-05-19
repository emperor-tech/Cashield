document.addEventListener('DOMContentLoaded', () => {
    window.Echo.private('App.Models.User.' + window.Laravel.userId)
        .notification((data) => {
            alert(`New report on ${data.campus} (Severity: ${data.severity})`);
            // optionally refresh our report list via AJAX
        });
});
