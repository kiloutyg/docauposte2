import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["username", "message"];

    validateUsername() {
        const regex = /^[a-zA-Z]+\.[a-zA-Z]+$/;
        const isValid = regex.test(this.usernameTarget.value);

        if (isValid) {
            this.messageTarget.textContent = "";
        } else {
            this.messageTarget.textContent = "Format invalide. Veuillez saisir sous la forme prénom.nom.";
            this.messageTarget.style.color = "red"; // Display the message in red color.
        }
    }

}