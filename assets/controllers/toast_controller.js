import { Controller } from '@hotwired/stimulus';
import { Toast } from 'bootstrap';

export default class extends Controller {
    static targets = ["toast", "body"];
    static values = {
        delay: { type: Number, default: 3500 },
        autohide: { type: Boolean, default: true }
    };

    connect() {
        this.toastInstance = new Toast(this.toastTarget, {
        delay: this.delayValue,
        autohide: this.autohideValue
        });
    }

    // Appelée via data-action="toast:show@document->toast#show"
    show(event) {
        const { message = '', variant = 'success' } = event?.detail || {};

        // Change la couleur (success/danger/warning/info/primary...)
        this.toastTarget.classList.remove(
        'text-bg-success','text-bg-danger','text-bg-warning','text-bg-info','text-bg-primary'
        );
        this.toastTarget.classList.add(`text-bg-${variant}`);

        if (this.hasBodyTarget) this.bodyTarget.textContent = message;
        this.toastInstance.show();
    }
}
