document.addEventListener('DOMContentLoaded', function () {
    function launchNotification() {
        const notification = document.getElementById('launch-form');

        if (!notification || notification.dataset.listenerAttached) {
            return;
        }

        notification.addEventListener('submit', async function (event) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);
            const submitButton = form.querySelector('#submit-button');
            const spinner = submitButton.querySelector('.spinner-border');
            const buttonText = submitButton.querySelector('.button-text');
            const output = document.getElementById('launch-response');

            // Désactiver le bouton pendant le traitement
            if (submitButton) {
                submitButton.disabled = true;
                spinner.classList.remove('d-none');
                buttonText.textContent = "Envoi en cours...";
            }

            try {
                const response = await fetch(form.dataset.api, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                output.innerHTML = '';

                if (result.success) {
                    output.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
                    form.reset();
                } else {
                    output.innerHTML =
                        `<div class="alert alert-danger">${result.errors.join('<br>')}</div>`;
                }

                // Disparition automatique du message au bout de 3 secondes
                setTimeout(() => {
                    output.innerHTML = '';
                }, 3000);
            } catch (error) {
                output.innerHTML =
                    `<div class="alert alert-danger">Une erreur est survenue. Veuillez réessayer.</div>`;

            } finally {
                // Réactiver le bouton
                if (submitButton) {
                    submitButton.disabled = false;
                    spinner.classList.add('d-none');
                    buttonText.textContent = "Être notifié";
                }
            }
        });

        notification.dataset.listenerAttached = 'true';
    }

    launchNotification();

    document.addEventListener('turbo:load', launchNotification);
    document.addEventListener('turbo:frame-load', launchNotification);
});