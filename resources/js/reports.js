document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');
    form.addEventListener('submit', e => {
        // Example: warn if description < 20 chars
        const desc = form.querySelector('textarea[name="description"]').value;
        if (desc.length < 20) {
            e.preventDefault();
            alert('Please provide a more detailed description (at least 20 characters).');
        }
    });
});
