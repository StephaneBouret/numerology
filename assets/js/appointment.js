document.addEventListener("DOMContentLoaded", function () {
    const checkbox = document.getElementById("appointment_checkout_form_agreeTerms");
    const button = document.getElementById("complete_button");

    if (checkbox && button) {
        checkbox.addEventListener("change", function () {
            button.disabled = !checkbox.checked;
        });
    }
});
