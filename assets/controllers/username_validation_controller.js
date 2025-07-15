import { Controller } from '@hotwired/stimulus';

export default class UsernameValidationController extends Controller {
    static targets = ["username", "message"];

    /**
     * Validates the username input based on a specific format.
     *
     * @function validateUsername
     * @memberof UsernameValidationController
     * @instance
     *
     * @description
     * This function checks if the username input matches the required format.
     * The format is a combination of two parts separated by a dot, where each part
     * must be at least 2 characters long and can only contain lowercase letters and hyphens.
     * The function uses a regular expression to perform the validation.
     *
     * @param {Event} [event] - The event object that triggered the validation (optional).
     *
     * @returns {void}
     *
     * @example
     * // Example usage:
     * // Assuming the username input is in an HTML element with the id "username"
     * // and the message display element is in an HTML element with the id "message"
     * const usernameInput = document.getElementById('username');
     * const messageDisplay = document.getElementById('message');
     *
     * // Create a new instance of the UsernameValidationController
     * const usernameValidationController = new UsernameValidationController();
     *
     * // Set the username and message targets
     * usernameValidationController.usernameTarget = usernameInput;
     * usernameValidationController.messageTarget = messageDisplay;
     *
     * // Trigger the validation
     * usernameValidationController.validateUsername();
     */
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