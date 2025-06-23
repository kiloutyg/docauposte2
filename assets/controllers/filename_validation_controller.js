import { Controller } from '@hotwired/stimulus';

export default class FilenameValidationController extends Controller {
    static targets = ["filename", "newFilename", "message"];

    /**
     * Validates the filename of a selected file against a regular expression pattern.
     * The function checks if the filename follows the required format rules:
     * - Must start with a letter, number, or parenthesis
     * - Can contain letters, numbers, parentheses, underscores, periods, spaces, apostrophes, and hyphens
     * - Must be between 4-255 characters in length (including the first and last character)
     * - Must end with a letter or number
     * 
     * @param {Event} event - The event object triggered when a file is selected
     * @returns {void} - No return value, but updates the UI with validation results:
     *                   - Clears any error message if validation passes or no file is selected
     *                   - Displays an error message if validation fails
     */
    validateFilename(event) {
        const regex = /^[\p{L}0-9()][\p{L}0-9()_.\s'-]{2,253}[\p{L}0-9]$/mu;
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

    
    /**
     * Validates the value of the newFilename input field against a regular expression pattern.
     * The function checks if the filename follows the required format rules:
     * - Must start with a letter, number, or parenthesis
     * - Can contain letters, numbers, parentheses, underscores, periods, spaces, apostrophes, and hyphens
     * - Must be between 4-255 characters in length (including the first and last character)
     * - Must end with a letter or number
     * 
     * @returns {void} - No return value, but updates the UI with validation results:
     *                   - Clears any error message if validation passes or the field is empty
     *                   - Displays an error message if validation fails
     */
    validateNewFilename() {
        const regex = /^[\p{L}0-9()][\p{L}0-9()_.\s'-]{2,253}[\p{L}0-9]$/mu;

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