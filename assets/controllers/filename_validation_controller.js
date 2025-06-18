import { Controller } from '@hotwired/stimulus';

export default class FilenameValidationController extends Controller {
    static targets = ["filename", "newFilename", "message"];

    validateFilename(event) {
        const regex = /^[\p{L}0-9()][\p{L}0-9()_.'-]{2,253}[\p{L}0-9]$/gmu;

        let isValid = true;
        const fileInput = this.filenameTarget;

        // Check if files were selected
        if (fileInput.files && fileInput.files.length > 0) {
            const fileName = fileInput.files[0].name;
            console.log('FilenameValidationController::validateFilename - filename ', fileName);

            // Validate the filename
            isValid = regex.test(fileName);
            console.log('FilenameValidationController::validateFilename - isValid', isValid);

            if (isValid) {
                this.messageTarget.textContent = "";
            } else {
                this.messageTarget.textContent = "Format de nom de fichier invalide. Utilisez uniquement des lettres, chiffres, parenthèses, tirets, points et underscores. Le nom ne doit pas commencer ou finir par un point ou un tiret.";
                this.messageTarget.style.color = "DarkRed";
            }
        } else {
            // No file selected, clear any previous error message
            this.messageTarget.textContent = "";
        }
    }
    validateNewFilename() {
        const regex = /^[\p{L}0-9()][\p{L}0-9()_.'-]{2,253}[\p{L}0-9]$/gmu;

        let isValid = true;
        let name = this.newFilenameTarget.value;
        console.log('name', name);
        if (name != '') {
            isValid = regex.test(name);
        }
        console.log('isValid', isValid);
        if (isValid) {
            this.messageTarget.textContent = "";
        } else {
            this.messageTarget.textContent = "Format de nom de fichier invalide. Utilisez uniquement des lettres, chiffres, parenthèses, tirets, points et underscores. Le nom ne doit pas commencer ou finir par un point ou un tiret.";
            this.messageTarget.style.color = "DarkRed"; // Display the message in red color.
        }
    }

}