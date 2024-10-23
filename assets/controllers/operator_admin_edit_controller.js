import OperatorAdminCreationController from './operator_admin_creation_controller';
import axios from 'axios';

export default class operatorAdminEdit extends OperatorAdminCreationController {


    static targets = [
        "operatorFormFirstname",
        "operatorFormLastname",
        "operatorFormCode",
        "operatorFormTeam",
        "operatorFormUap",
        "operatorFormIsTrainer",
    ];


    connect() {
        // Store initial values if needed
        this.initialValues = {
            initialFirstname: this.operatorFormFirstnameTarget.value,
            initialLastname: this.operatorFormLastnameTarget.value,
            initialCode: this.operatorFormCodeTarget.value,
            initialTeam: this.operatorFormTeamTarget.value,
            initialUap: this.operatorFormUapTarget.value,
            initialIsTrainer: this.operatorFormIsTrainerTarget.checked,
        };
    }



    async editedCodeChecker() {
        // Clear any existing timeouts to debounce user input
        clearTimeout(this.codeTypingTimeout);

        this.codeTypingTimeout = setTimeout(async () => {
            const code = this.operatorFormCodeTarget.value.trim();
            const isValidFormat = this.isCodeValidFormat(code);

            if (isValidFormat) {
                const isValidSum = this.editedCodeFormatChecker(code);
                console.log('isValidSum', isValidSum)
                const isExistingCode = await this.existingCodeChecker(code);
                console.log('isExistingCode', isExistingCode)

                if (isValidSum && !isExistingCode) {
                    // Code is valid and doesn't exist
                    console.log('Code is valid and unique.');
                    // Provide positive feedback to the user if necessary
                } else if (!isValidSum) {
                    console.log('Sum of first three digits does not equal last two digits.');
                    this.showCodeFormatError('Bad Format.');

                        const newCode = await this.proposeCompliantNewCode();
                        this.operatorFormCodeTarget.value = newCode;
                } else if (isExistingCode) {
                    console.log('Code already exists.');
                    this.showCodeExistenceError('Existe déjà.');

                        const newCode = await this.proposeCompliantNewCode();
                        this.operatorFormCodeTarget.value = newCode;
                }
            } else {
                console.log('Code format is not compliant.');
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
        console.log('checking if code already exists, code:', code);
        return axios.post('/docauposte/operator/check-if-code-exist', { code })
            .then(response => {
                console.log('response', response)
                console.log('response.data.found', response.data.found)
                const found = response.data.found;
                console.log('found', found)
                return found;
            })
            .catch(error => {
                console.error('Error checking for duplicate operator code.', error);
                // Handle error appropriately
                return false;
            });
    }



    editedCodeFormatChecker(code) {
        console.log('editing code format checker, code:', code);
        const sumOfFirstThreeDigits = code
            .substring(0, 3)
            .split('')
            .reduce((sum, digit) => sum + Number(digit), 0);

        const lastTwoDigitsValue = Number(code.substring(3));

        return sumOfFirstThreeDigits === lastTwoDigitsValue;
    }



    async proposeCompliantNewCode() {
        console.log('proposing a compliant new code');

        const newCode = this.codeGenerator();
        console.log('newCode', newCode);
        const response = await this.existingCodeChecker(newCode);
        if (response) {
            console.log('newCode already exists, proposing a new one');
            return this.proposeCompliantNewCode();
        } else {
            return newCode;
        }

    }
}


