import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["teamName", "uapName", "message"];

    /**
     * Lifecycle method called when the controller is connected to the DOM.
     * Initializes tracking for the last modified input field.
     */
    connect() {
        // Initialize a property to track which field was last modified
        this.lastModifiedField = null;
    }

    /**
     * Event handler triggered when the team name input changes.
     * Sets the last modified field to 'team' and validates the input.
     */
    teamNameChanged() {
        this.lastModifiedField = 'team';
        this.validateTeamUapName();
    }

    /**
     * Event handler triggered when the UAP name input changes.
     * Sets the last modified field to 'uap' and validates the input.
     */
    uapNameChanged() {
        this.lastModifiedField = 'uap';
        this.validateTeamUapName();
    }

    /**
     * Validates the team or UAP name input against a specific format.
     * The validation ensures the name:
     * - Contains only uppercase letters and hyphens
     * - Is at least 3 characters long
     * - Doesn't start or end with a hyphen
     * - Doesn't contain consecutive hyphens
     * 
     * The function also:
     * - Converts input to uppercase
     * - Clears the non-active field
     * - Displays an error message if validation fails
     * 
     * No parameters as it uses controller targets and properties.
     * No return value as it updates the DOM directly.
     */
    validateTeamUapName() {
        const regex = /^([A-ZÉÈÊËÀÂÄÔÖÙÛÜÇ][A-ZÉÈÊËÀÂÄÔÖÙÛÜÇa-zéèêëàâäôöùûüç]+ [A-Z]+|[A-ZÉÈÊËÀÂÄÔÖÙÛÜÇ][A-ZÉÈÊËÀÂÄÔÖÙÛÜÇa-zéèêëàâäôöùûüç]+)$/;
        let isValid = true;
        let name = '';

        // Convert inputs to uppercase
        if (this.hasTeamNameTarget && this.teamNameTarget.value) {
            this.teamNameTarget.value = this.teamNameTarget.value;
        }

        if (this.hasUapNameTarget && this.uapNameTarget.value) {
            this.uapNameTarget.value = this.uapNameTarget.value.toUpperCase();
        }

        // Determine which field to use based on last modified
        if (this.lastModifiedField === 'team' && this.teamNameTarget.value.trim() !== '') {
            this.uapNameTarget.value = '';
            name = this.teamNameTarget.value;
        } else if (this.lastModifiedField === 'uap' && this.uapNameTarget.value.trim() !== '') {
            this.teamNameTarget.value = '';
            name = this.uapNameTarget.value;
        } else {
            // Fallback to non-empty field if lastModifiedField is not set
            name = this.teamNameTarget.value.trim() !== '' ?
                this.teamNameTarget.value :
                this.uapNameTarget.value;
        }

        console.log('name', name);

        if (name !== '') {
            isValid = regex.test(name);
        }

        if (isValid) {
            this.messageTarget.textContent = "";
        } else {
            this.messageTarget.textContent = "Format invalide. Veuillez saisir soit un mot simple, soit sous la forme 'Équipe X'.";
            this.messageTarget.style.color = "DarkRed"; // Display the message in red color.
        }
    }
}