import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    submit(event) {
        event.preventDefault();
        const form = event.target;
        const url = form.action;
        const data = new FormData(form);

        fetch(url, { 
            method: "POST",
            body: data,
            headers: { "X-Requested-With": "XMLHttpRequest" } 
        })
        .then(response => response.text())
        .then(html => {
            // Remplace le container parent (celui du controller appointment_type)
            const container = form.closest('[data-controller="appointment-type"]');
            if (container) {
                container.innerHTML = html;
            }
         });
    }
}