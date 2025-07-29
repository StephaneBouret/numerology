import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["container"];

    showDetail(event) {
        event.preventDefault();
        const url = event.currentTarget.dataset.url;
        if (!url) return;

        fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
            .then(response => response.text())
            .then(html => {
                this.containerTarget.innerHTML = html;
            });
    }

    showForm(event) {
        event.preventDefault();
        const url = event.currentTarget.dataset.url;
        if (!url) return;

        fetch(url, { headers: { "X-Requested-With": "XMLHttpRequest" } })
            .then(response => response.text())
            .then(html => {
                this.containerTarget.innerHTML = html;
            });
    }

    reloadRecap(event) {
        event.preventDefault();
        fetch('/rendez-vous/types', { headers: { "X-Requested-With": "XMLHttpRequest" } })
            .then(response => response.text())
            .then(html => {
                this.containerTarget.innerHTML = html;
            });
    }
}