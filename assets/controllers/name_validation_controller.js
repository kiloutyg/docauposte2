import { Controller } from '@hotwired/stimulus';

export default class NameValidationController extends Controller {
    static targets = ["teamName",
        "uapName",
        "teamUapNameMessage",
        "productName",
        "productNameMessage",
        "workstationName",
        "workstationNameMessage",
        "saveButton",
        "organizationEntityName",
        "organizationEntityNameMessage",
        "submitButton"];

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
        const regex = /^(?!-)(?!.*--)[A-Za-zÉÈÊËÀÂÄÔÖÙÛÜÇéèêëàâäôöùûüç][A-Za-zÉÈÊËÀÂÄÔÖÙÛÜÇéèêëàâäôöùûüç -]{2,}(?<!-)(?<! )$/;
        let isValid = true;
        let name = '';

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
            this.teamUapNameMessageTarget.textContent = "";
        } else {
            this.teamUapNameMessageTarget.textContent = "Format invalide. Veuillez saisir soit un mot simple, soit sous la forme 'Équipe X'.";
            this.teamUapNameMessageTarget.style.color = "DarkRed"; // Display the message in red color.
        }
    }


    /**
     * Validates the product name input against a specific format.
     * The validation ensures the name follows the pattern:
     * - One or more uppercase letters followed by one or more digits
     * - Example: ABC123
     * 
     * The function also:
     * - Converts input to uppercase
     * - Enables or disables the save button based on validation
     * - Displays an error message if validation fails
     * 
     * No parameters as it uses controller targets.
     * No return value as it updates the DOM directly.
     */
    validateProductName() {
        const regex = /^[A-Z]+\d+$/;
        let isValid = true;
        let name = this.productNameTarget.value.toUpperCase();
        if (name != '') {
            isValid = regex.test(name);
        }
        if (isValid) {
            this.productNameMessageTarget.textContent = "";
            this.saveButtonTarget.disabled = false;
        } else {
            this.productNameMessageTarget.textContent = "Format invalide. Veuillez saisir sous la forme: ABC123";
            this.productNameMessageTarget.style.color = "DarkRed"; // Display the message in red color.
            this.saveButtonTarget.disabled = true;
        }
    }

    /**
     * Validates the workstation name input against a specific format.
     * The validation ensures the name contains only:
     * - Letters (A-Z, a-z)
     * - Numbers (0-9)
     * - Spaces
     * - Hyphens (-)
     * - Forward slashes (/)
     * - Parentheses ()
     * - Plus signs (+)
     * - Periods (.)
     * 
     * The function also:
     * - Converts input to uppercase
     * - Enables or disables the save button based on validation
     * - Displays an error message if validation fails
     * 
     * No parameters as it uses controller targets.
     * No return value as it updates the DOM directly.
     */
    validateWorkstationName() {
        const regex = /^[A-Za-z0-9\s\-/()+.]+$/;
        let isValid = true;
        let name = this.workstationNameTarget.value.toUpperCase();
        if (name != '') {
            isValid = regex.test(name);
        }
        if (isValid) {
            this.workstationNameMessageTarget.textContent = "";
            this.saveButtonTarget.disabled = false;
        } else {
            this.workstationNameMessageTarget.textContent = "Format invalide. Veuillez saisir sous la forme: Assy-P674-Poinçonneuse Peau";
            this.workstationNameMessageTarget.style.color = "DarkRed"; // Display the message in red color.
            this.saveButtonTarget.disabled = true;
        }
    }

    /**
     * Validates the organization entity name input against a specific format.
     * The validation ensures the name contains only:
     * - Letters (A-Z, a-z)
     * - Numbers (0-9)
     * - Spaces
     * - Hyphens (-)
     * - Parentheses ()
     * 
     * The function also:
     * - Converts input to uppercase
     * - Enables or disables the submit button based on validation
     * - Displays an error message if validation fails
     * 
     * No parameters as it uses controller targets.
     * No return value as it updates the DOM directly.
     */
    validateOrganizationEntityName() {
        const regex = /^[A-Za-z0-9\s\-()]+$/;
        let isValid = true;
        let name = this.organizationEntityNameTarget.value.toUpperCase();
        if (name != '') {
            isValid = regex.test(name);
        }
        if (isValid) {
            this.organizationEntityNameMessageTarget.textContent = "";
            this.submitButtonTarget.disabled = false;
        } else {
            this.organizationEntityNameMessageTarget.textContent = "Format invalide. Veuillez saisir uniquement des lettres, chiffres, espaces, tirets et parenthèses.";
            this.organizationEntityNameMessageTarget.style.color = "DarkRed"; // Display the message in red color.
            this.submitButtonTarget.disabled = true;
        }
    }
}