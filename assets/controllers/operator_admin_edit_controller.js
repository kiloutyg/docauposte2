import { Controller } from '@hotwired/stimulus';
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



    modifiedFieldChecker() {
        console.log('modified field checker');
        switch (this.operatorFormCodeTarget.value.changed) {
            case true:
                this.editedCodeChecker();
                break;
        }
    }


    editedCodeChecker() {

        console.log('editing code checker', this.operatorFormCodeTarget.value);

        clearTimeout(this.codeTypingTimeout);
        this.codeTypingTimeout = setTimeout(() => {

            const code = this.operatorFormCodeTarget.value.trim();
            console.log('code', code);
            const regex = /^[0-9]{5}$/;
            let isValid = regex.test(code);
            console.log('isValid', isValid);

            if (isValid) {
                const response = this.editedCodeFormatChecker(code);

            } else {
                console.log('code format is not regex compliant');
                this.operatorFormCodeTarget.value = "";
                this.operatorFormCodeTarget.placeholder = 'Format invalide';
            }
        }, 800);
    }


    existingCodeChecker(code) {
        console.log('checking generated code');
        return axios.post(`/docauposte/operator/check-if-code-exist`, { code: code });
    }



    editedCodeFormatChecker() {

        clearTimeout(this.codeFormatCheckerTimeout);
        this.codeFormatCheckerTimeout = setTimeout(() => {

            console.log('editing code format checker');
            const code = this.operatorFormCodeTarget.value.trim();
            const sumOfFirstThreeDigit = code
                .toString()
                .split('')
                .slice(0, 3)
                .reduce((sum, digit) => sum + Number(digit), 0);
            console.log('sum of first three digits:', sumOfFirstThreeDigit);
            const valueOfLastTwoDigit = code.toString()
                .split('')
                .slice(3)
                .join('');
            console.log('value of last two digits:', valueOfLastTwoDigit);

            if (sumOfFirstThreeDigit === valueOfLastTwoDigit) {
                try {
                    const reponse = this.existingCodeChecker(code);
                    if (reponse.data.found) {
                        console.log('code already exists');
                        this.operatorFormCodeTarget.value = "";
                        this.operatorFormCodeTarget.placeholder = 'Code déjà utilisé';
                    } else {
                        console.log('code does not exist and is valid ');
                        return;
                    }
                } catch (error) {
                    console.error('Error checking for duplicate operator code.', error);
                }
            } else {
                console.log('sum of first three digits is not equal to value of last two digits');
                clearTimeout(this.codeFormatCheckerNewCodeMessageTimeout);
                this.codeFormatCheckerNewCodeMessageTimeout = setTimeout(() => {
                    this.operatorFormCodeTarget.value = "";
                    this.operatorFormCodeTarget.placeholder = 'Code Auto 8sec';
                    clearTimeout(this.codeFormatCheckerNewCodeTimeout);
                    this.codeFormatCheckerNewCodeTimeout = setTimeout(() => {
                        const newCode = this.codeGenerator();
                        console.log('newCode', newCode);
                        this.operatorFormCodeTarget.value = newCode;
                    }, 800);
                }, 800);
            }
        }, 800);
    }
}
