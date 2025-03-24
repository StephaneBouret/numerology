document.addEventListener('DOMContentLoaded', function () {
    function initializeStarRating() {
        const ratingInput = document.querySelector('#testimonial_form_rating');

        if (ratingInput) {
            const starRating = document.querySelector('.star-rating');

            if (!starRating) {
                return;
            }

            starRating.addEventListener('mouseover', function (event) {
                if (event.target.matches('i') || event.target.closest('i')) {
                    const target = event.target.matches('i') ? event.target : event.target.closest('i');
                    const ratingValue = target.getAttribute('data-rating');
                    ratingInput.value = ratingValue;

                    // Met à jour l'apparence des étoiles
                    starRating.querySelectorAll('i').forEach(function (star) {
                        const starRatingValue = star.getAttribute('data-rating');
                        if (starRatingValue <= ratingValue) {
                            star.classList.remove('far');
                            star.classList.add('fas');
                        } else {
                            star.classList.remove('fas');
                            star.classList.add('far');
                        }
                    });
                }
            });
        } else {
            console.log("RatingInput element not found");
        }
    }

    // Exécuter au chargement initial
    initializeStarRating();

    // Réexécuter lors des mises à jour Turbo
    document.addEventListener('turbo:load', initializeStarRating);
    document.addEventListener('turbo:frame-load', initializeStarRating);
});