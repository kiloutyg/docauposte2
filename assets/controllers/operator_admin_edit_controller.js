import OperatorAdminCreationController from './operator_admin_creation_controller';
import { operatorCodeService } from './services/operator_code_service';

export default class OperatorAdminEditController extends OperatorAdminCreationController {


    static targets = [
        "operatorFormCode"
    ];

    // Instance property to store the original code
    originalCode = '';

    // This method runs when the controller connects to the DOM
    connect() {
        // Call parent's connect method if it exists
        if (super.connect) {
            super.connect();
        }

        // Store the original code when the controller initializes
        if (this.hasOperatorFormCodeTarget) {
            this.originalCode = this.operatorFormCodeTarget.value;
        }
    }


    /**
     * Validates the operator code entered by the user after a short delay.
     * This method implements debouncing to prevent excessive validation calls during typing.
     * If the code format is invalid or the code already exists, it displays an error message
     * and automatically proposes a new compliant code.
     * 
     * @async
     * @returns {Promise<void>}
     */
    async editedCodeChecker() {
        // Clear any existing timeouts to debounce user input
        clearTimeout(this.codeTypingTimeout);
        this.codeTypingTimeout = setTimeout(async () => {
            const code = this.operatorFormCodeTarget.value.trim();
            const isValidFormat = await this.isCodeValidFormat(code);
            console.log('operatorAdminEditController::editedCodeChecker: isValidFormat:', isValidFormat);
            if (isValidFormat) {
                const isExistingCode = await operatorCodeService.checkIfCodeExists(code);
                if (isExistingCode) {
                    this.showCodeFormatExistenceError('Existe déjà.');
                    console.log('operatorAdminEditController::editedCodeChecker: Existe déjà.');
                    this.settingOperatorCodeBeforProposingCompliantCode();
                }
            } else {
                this.showCodeFormatExistenceError('Format invalide.');
                console.log('operatorAdminEditController::editedCodeChecker: Bad Format.');
                this.settingOperatorCodeBeforProposingCompliantCode();
            }
        }, 1000);
    }


    /**
     * Checks if the provided operator code has a valid format.
     * 
     * @param {string} code - The operator code to validate
     * @returns {Promise<boolean>} True if the code format is valid, false otherwise
     */
    async isCodeValidFormat(code) {
        return operatorCodeService.validateCode(code);
    }


    /**
     * Displays an error message when the code format is invalid.
     * Temporarily shows the error message by clearing the input field and setting a placeholder.
     * After a brief delay, restores the original code value.
     * 
     * @param {string} message - The error message to display
     * @returns {void}
     */
    showCodeFormatExistenceError(message) {
        // Clear the input to make the placeholder visible
        this.operatorFormCodeTarget.value = '';
        this.operatorFormCodeTarget.placeholder = message;

        // Highlight the input field to indicate an error
        this.operatorFormCodeTarget.classList.add('is-invalid');

        // After a short delay, restore the original code and remove the error styling
        clearTimeout(this.codeTypingMessageTimeout);
        this.codeTypingMessageTimeout = setTimeout(() => {
            this.operatorFormCodeTarget.value = this.originalCode;
            this.operatorFormCodeTarget.placeholder = '';
            this.operatorFormCodeTarget.classList.remove('is-invalid');
        }, 2000); // Shorter delay (2 seconds) for better user experience
    }



    /**
     * Retrieves operator code generation settings and proposes a new compliant code if enabled.
     * This method is called when an invalid or existing code is detected, to automatically
     * provide a valid alternative to the user.
     * 
     * @async
     * @returns {Promise<void>} A promise that resolves when the operation is complete
     */
    async settingOperatorCodeBeforProposingCompliantCode() {
        const settings = await operatorCodeService.getSettings();
        if (settings.methodEnabled) {
            const newCode = await this.proposeCompliantNewCode();
            this.operatorFormCodeTarget.value = newCode;
        }
    }


    /**
     * Generates a new unique operator code that complies with format requirements.
     * 
     * @async
     * @returns {Promise<string>} A new unique operator code
     */
    async proposeCompliantNewCode() {
        return await operatorCodeService.generateUniqueCode();
    }
}