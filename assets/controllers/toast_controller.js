import { Controller } from '@hotwired/stimulus';
import { Toast } from 'bootstrap';

export default class extends Controller {
    static targets = ["toast"];

    connect() {
        this.toastInstance = new Toast(this.toastTarget);
    }

    showToast() {
        this.toastInstance.show();
    }
}