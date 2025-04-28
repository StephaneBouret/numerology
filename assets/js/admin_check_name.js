document.addEventListener('DOMContentLoaded', () => {
    const inputWrapper = document.querySelector('.char-count input');
    if (inputWrapper) {
        const counter = document.createElement('div');
        counter.style.fontSize = '0.8rem';
        counter.style.marginTop = '0.25rem';
        counter.style.color = '#666'; // gris par défaut
        inputWrapper.parentNode.appendChild(counter);

        const updateCounter = () => {
            let length = inputWrapper.value.length;
            if (length > 30) {
                inputWrapper.value = inputWrapper.value.substring(0, 30);
                length = 30;
            }
            counter.textContent = `${length} / 30 caractères`;

            if (length === 30) {
                counter.style.color = '#e74c3c'; // rouge vif si limite atteinte
            } else {
                counter.style.color = '#666'; // sinon gris
            }
        };

        inputWrapper.addEventListener('input', updateCounter);
        updateCounter();
    }
});