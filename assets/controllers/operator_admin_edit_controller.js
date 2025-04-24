import OperatorAdminCreationController from './operator_admin_creation_controller';
import axios from 'axios';

export default class OperatorAdminEditController extends OperatorAdminCreationController {


    static targets = [
        "operatorFormCode"
    ];




    async editedCodeChecker() {
        // Clear any existing timeouts to debounce user input
        clearTimeout(this.codeTypingTimeout);

        this.codeTypingTimeout = setTimeout(async () => {
            const code = this.operatorFormCodeTarget.value.trim();
            const isValidFormat = this.isCodeValidFormat(code);
            if (isValidFormat) {
                const isValidSum = this.editedCodeFormatChecker(code);
                const isExistingCode = await this.existingCodeChecker(code);
                if (isValidSum && !isExistingCode) {
                    // Provide positive feedback to the user if necessary
                } else if (!isValidSum) {
                    this.showCodeFormatError('Bad Format.');
                    const newCode = await this.proposeCompliantNewCode();
                    this.operatorFormCodeTarget.value = newCode;
                } else if (isExistingCode) {
                    this.showCodeExistenceError('Existe déjà.');
                    const newCode = await this.proposeCompliantNewCode();
                    this.operatorFormCodeTarget.value = newCode;
                }
            } else {
                this.showCodeFormatError('Format invalide.');
                const newCode = await this.proposeCompliantNewCode();
                this.operatorFormCodeTarget.value = newCode;
            }
        }, 800);
    }



    // Helper method to check if the code format is valid
    isCodeValidFormat(code) {
        const regex = /^[0-9]{5}$/;
        return regex.test(code);
    }



    // Method to display format errors
    showCodeFormatError(message) {
        this.operatorFormCodeTarget.value = '';
        this.operatorFormCodeTarget.placeholder = message;
    }



    // Method to display existence errors
    showCodeExistenceError(message) {
        // You can implement visual feedback to the user here
        this.operatorFormCodeTarget.value = '';
        this.operatorFormCodeTarget.placeholder = message;
    }



    async existingCodeChecker(code) {
        // Since axios.post is asynchronous, we need to handle it with async/await or promises
        return axios.post('/docauposte/operator/check-if-code-exist', { code })
            .then(response => {
                const found = response.data.found;
                return found;
            })
            .catch(error => {
                console.error('Error checking for duplicate operator code.', error);
                // Handle error appropriately
                return false;
            });
    }



    editedCodeFormatChecker(code) {
        const sumOfFirstThreeDigits = code
            .substring(0, 3)
            .split('')
            .reduce((sum, digit) => sum + Number(digit), 0);
        const lastTwoDigitsValue = Number(code.substring(3));
        return sumOfFirstThreeDigits === lastTwoDigitsValue;
    }



    async proposeCompliantNewCode() {
        const newCode = this.codeGenerator();
        const response = await this.existingCodeChecker(newCode);
        if (response) {
            return this.proposeCompliantNewCode();
        } else {
            return newCode;
        }

    }
}


