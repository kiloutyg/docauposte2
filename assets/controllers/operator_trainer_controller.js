
import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class extends Controller {
    static targets = [
        "trainerOperatorName",
        "trainerOperatorCode",
        "trainerOperatorNameMessage",
        "trainerOperatorCodeMessage"
    ];



    validateTrainerOperatorName() {

        clearTimeout(this.typingTimeout);  // clear any existing timeout to reset the timer

        this.typingTimeout = setTimeout(() => {
            console.log('validating new operator name:', this.trainerOperatorNameTarget.value);

            const regex = /^[a-zA-Z]+\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/;

            const value = this.trainerOperatorNameTarget.value.trim();
            const isValid = regex.test(value);

            if (isValid) {
                this.checkTrainerExistance('name', value);
            } else {
                this.updateMessage(this.trainerOperatorNameMessageTarget, isValid, "Veuillez saisir sous la forme prénom.nom.")
            }
        }, 1000);
    }

    validateTrainerOperatorCode() {
        clearTimeout(this.typingTimeout);  // clear any existing timeout to reset the timer
        this.typingTimeout = setTimeout(() => {
            console.log('validating new operator code:', this.trainerOperatorCodeTarget.value);

            const regex = /^[0-9]{5}$/;
            const value = this.trainerOperatorCodeTarget.value.trim();
            const isValid = regex.test(value);

            if (isValid) {
                this.checkTrainerExistance('code', value);
            } else {
                this.updateMessage(this.trainerOperatorCodeMessageTarget, isValid, "Veuillez saisir un code valide: XXXXX.")
            }
        }, 1000);
    }

    updateMessage(targetElement, isValid, errorMessage) {
        console.log(`Updating message: isValid: ${isValid}`);
        if (isValid) {
            targetElement.textContent = "";
        } else {
            targetElement.textContent = errorMessage;
            targetElement.style.fontWeight = "bold";
            targetElement.style.color = "red";
        }
    }



    async checkTrainerExistance(field, value) {
        if (field === 'name') {
            try {

                const response = await this.checkForDuplicate('/docauposte/operator/check-if-trainer-exist', value)
                console.log('entire axios response for trainer existence:', response.data);
                this.handleTrainerExistenceResponse(response, field, 'trainerOperatorName');
            } catch (error) {
                console.log('error in axios request');
                this.trainerOperatorNameMessageTarget.style.color = "red";
                this.trainerOperatorNameMessageTarget.textContent = "Erreur lors de la recherche du formateur.";
            }

        } else if (field === 'code') {
            try {
                const response = await this.checkForDuplicate('/docauposte/operator/check-if-trainer-exist', code, value)
                console.log('entire axios response for trainer existence:', response.data);
                this.handleTrainerExistenceResponse(response, field, 'trainerOperatorCode');
            } catch (error) {
                console.log('error in axios request');
                this.trainerOperatorMessageCodeTarget.style.color = "red";
                this.trainerOperatorMessageCodeTarget.textContent = "Erreur lors de la recherche du formateur.";
            }
        }
    }

    checkForDuplicate(url, field, value) {
        console.log(`Checking for duplicate at ${url} with value:`, value);
        return axios.post(url, { [field]: value });
    }




    handleTrainerExistenceResponse(response, field, fieldName) {
        if (response.data.found) {
            // this[`${fieldName}Target`].value = code;
            this[`${fieldName}Target`].disabled = true;
            this[`${fieldName}Target`].focus();
            this.validateTrainerOperatorCode();
        } else {
            this[`${fieldName}Target`].value = "";
            this[`${fieldName}MessageTarget`].style.color = "red";
            this[`${fieldName}MessageTarget`].textContent = "Formateur non trouvé. "[field];

        };

    }


}