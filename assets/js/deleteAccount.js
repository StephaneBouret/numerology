document.addEventListener('DOMContentLoaded', function () {
    const confirmCheckbox = document.getElementById('confirm');
    const deleteButton = document.getElementById('deleteButton');

    if (confirmCheckbox && deleteButton) {
        confirmCheckbox.addEventListener('change', function () {
            deleteButton.disabled = !confirmCheckbox.checked;
        });
    }
});