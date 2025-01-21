import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["filename", "message"];

    validateFilename() {
        const regex = /^(?!-)(?!.*--)[a-zA-Z-]{2,}(?<!-)\.(?!-)(?!.*--)[a-zA-Z-]{2,}(?<!-)$/;
        const isValid = regex.test(this.filenameTarget.value);

        if (isValid) {
            this.messageTarget.textContent = "";
        } else {
            this.messageTarget.textContent = "Format invalide. Veuillez saisir sous la forme prénom.nom.";
            this.messageTarget.style.color = "red"; // Display the message in red color.
        }
    }

}