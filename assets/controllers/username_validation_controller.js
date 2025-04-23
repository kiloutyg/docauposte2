import { Controller } from '@hotwired/stimulus';

export default class UsernameValidationController extends Controller {
    static targets = ["username", "message"];

    validateUsername() {
        const regex = /^(?!-)(?!.*--)[a-z-]{2,}(?<!-)\.(?!-)(?!.*--)[a-z-]{2,}(?<!-)$/;
        const isValid = regex.test(this.usernameTarget.value);

        if (isValid) {
            this.messageTarget.textContent = "";
        } else {
            this.messageTarget.textContent = "Format invalide. Veuillez saisir sous la forme prénom.nom.";
            this.messageTarget.style.color = "red"; // Display the message in red color.
        }
    }

}