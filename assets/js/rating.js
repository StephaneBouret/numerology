document.addEventListener('DOMContentLoaded', function () {
    function initializeStarRating() {
        const ratingInput = document.querySelector('#testimonial_form_rating');

        if (!ratingInput) {
            console.log("RatingInput element not found");
            return;
        }

        const starRating = document.querySelector('.star-rating');
        if (!starRating) {
            return;
        }

        // Valeur par défaut si vide
        if (!ratingInput.value) {
            ratingInput.value = 5;
        }

        // Appliquer l'apparence des étoiles selon la valeur actuelle
        function updateStars(value) {
            starRating.querySelectorAll('i').forEach(function (star) {
                const starRatingValue = star.getAttribute('data-rating');
                if (parseInt(starRatingValue) <= parseInt(value)) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });
        }

        // Initialiser l'apparence
        updateStars(ratingInput.value);

        // Gérer le survol et le clic
        starRating.addEventListener('mouseover', function (event) {
            const target = event.target.closest('i');
            if (!target) return;

            const ratingValue = target.getAttribute('data-rating');
            updateStars(ratingValue);
        });

        starRating.addEventListener('click', function (event) {
            const target = event.target.closest('i');
            if (!target) return;

            const ratingValue = target.getAttribute('data-rating');
            ratingInput.value = ratingValue;
            updateStars(ratingValue);
        });
    }

    // Exécuter au chargement initial
    initializeStarRating();

    // Réexécuter lors des mises à jour Turbo
    document.addEventListener('turbo:load', initializeStarRating);
    document.addEventListener('turbo:frame-load', initializeStarRating);
});