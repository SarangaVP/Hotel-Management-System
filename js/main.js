document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', (e) => {
            let inputs = form.querySelectorAll('input[required], select[required]');
            inputs.forEach(input => {
                if (!input.value) {
                    e.preventDefault();
                    alert('Please fill all required fields.');
                }
            });
        });
    });
});