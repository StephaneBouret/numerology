document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('certificate-name-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const uuid = window.location.pathname.split('/').pop();
        const url = `/certificate/${uuid}/update-name`;

        const formData = new FormData(form);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Erreur lors de la mise à jour.');
            }
        } catch (error) {
            console.error('Erreur AJAX:', error);
            alert('Une erreur est survenue.');
        }
    });
});