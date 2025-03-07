document.addEventListener("DOMContentLoaded", function () {
    const checkbox = document.getElementById("purchase_form_agreeTerms");
    const button = document.getElementById("complete_button");

    if (checkbox && button) {
        checkbox.addEventListener("change", function () {
            button.disabled = !checkbox.checked;
        });
    }
});