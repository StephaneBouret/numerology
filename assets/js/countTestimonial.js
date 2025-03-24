document.addEventListener('DOMContentLoaded', function () {
    function updateCount(textArea, countSpan, maxCount) {
        if (textArea && countSpan) {
            const count = textArea.value.length;
            countSpan.textContent = maxCount - count;

            if (count >= maxCount) {
                textArea.classList.add('textarea-name-error');
            } else {
                textArea.classList.remove('textarea-name-error');
            }
        }
    }

    function initTextAreaWatcher() {
        const textArea = document.querySelector('.description-textarea');
        const countSpan = document.querySelector('.description-count');
        const maxCount = 300;

        if (textArea && countSpan) {
            updateCount(textArea, countSpan, maxCount);

            textArea.addEventListener('input', function () {
                updateCount(textArea, countSpan, maxCount);
            });

            const form = textArea.closest('form');
            if (form) {
                form.addEventListener('submit', () => {
                    setTimeout(() => updateCount(textArea, countSpan, maxCount), 0);
                });
            }
        }
    }

    // Exécuter au chargement initial
    initTextAreaWatcher();

    // Réexécuter lors des mises à jour Turbo
    document.addEventListener('turbo:load', initTextAreaWatcher);
    document.addEventListener('turbo:frame-load', initTextAreaWatcher);
});
