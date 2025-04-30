import OperatorAdminCreationController from './operator_admin_creation_controller';
import { operatorCodeService } from './services/operator_code_service';

export default class operatorAdminEdit extends OperatorAdminCreationController {


    static targets = [
        "operatorFormCode"
    ];


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
            const isValidFormat = this.isCodeValidFormat(code);
            if (isValidFormat) {
                const isExistingCode = await operatorCodeService.checkIfCodeExists(code);
                if (isExistingCode) {
                    this.showCodeExistenceError('Existe déjà.');
                    console.log('operatorAdminEditController::editedCodeChecker: Existe déjà.');
                    const newCode = await this.proposeCompliantNewCode();
                    this.operatorFormCodeTarget.value = newCode;
                }
            } else {
                this.showCodeFormatError('Format invalide.');
                console.log('operatorAdminEditController::editedCodeChecker: Bad Format.');
                const newCode = await this.proposeCompliantNewCode();
                this.operatorFormCodeTarget.value = newCode;
            }
        }, 800);
    }


    /**
     * Checks if the provided operator code has a valid format.
     * 
     * @param {string} code - The operator code to validate
     * @returns {boolean} True if the code format is valid, false otherwise
     */
    isCodeValidFormat(code) {
        return operatorCodeService.validateCode(code);
    }


    /**
     * Displays an error message when the code format is invalid.
     * Clears the input field and sets the placeholder to the error message.
     * 
     * @param {string} message - The error message to display
     * @returns {void}
     */
    showCodeFormatError(message) {
        this.operatorFormCodeTarget.value = '';
        this.operatorFormCodeTarget.placeholder = message;
    }


    /**
     * Displays an error message when the code already exists.
     * Clears the input field and sets the placeholder to the error message.
     * 
     * @param {string} message - The error message to display
     * @returns {void}
     */
    showCodeExistenceError(message) {
        // You can implement visual feedback to the user here
        this.operatorFormCodeTarget.value = '';
        this.operatorFormCodeTarget.placeholder = message;
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