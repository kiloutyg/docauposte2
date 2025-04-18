
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form.form');
    const originalValues = new Map();

    // Store original values
    form.querySelectorAll('input, select').forEach(element => {
        originalValues.set(element.name, element.value);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Create FormData with only modified values
        const formData = new FormData();
        form.querySelectorAll('input, select').forEach(element => {
            if (element.value !== originalValues.get(element.name)) {
                formData.append(element.name, element.value);
            }
        });

        // Submit the form using fetch
        fetch(form.action, {
            method: 'POST',
            body: formData
        }).then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            }
        });
    });
});
