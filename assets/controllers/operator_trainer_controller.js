
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
                this.checkTrainerExistence('name', value);
            } else {
                this.updateMessage(this.trainerOperatorNameMessageTarget, isValid, "Veuillez saisir sous la forme prénom.nom.")
            }
        }, 1000);
    }

    validateTrainerOperatorCode() {
        this.trainerOperatorCodeTarget.disabled = false;
        this.trainerOperatorCodeTarget.focus();

        clearTimeout(this.typingTimeout);  // clear any existing timeout to reset the timer
        this.typingTimeout = setTimeout(() => {
            console.log('validating new operator code:', this.trainerOperatorCodeTarget.value);

            const regex = /^[0-9]{5}$/;
            const value = this.trainerOperatorCodeTarget.value.trim();
            const isValid = regex.test(value);

            if (value.length > 0) { // only check if the field is not empty
                if (isValid) {
                    this.checkTrainerExistence('code', value);
                } else {
                    this.updateMessage(this.trainerOperatorCodeMessageTarget, isValid, "Veuillez saisir un code valide: XXXXX.")
                }
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



    // async checkTrainerExistence(field, value) {
    //     if (field === 'name') {
    //         try {

    //             const response = await this.checkForDuplicate('/docauposte/operator/check-if-trainer-exist', field, value)
    //             console.log('entire axios response for trainer existence:', response.data);
    //             this.handleTrainerExistenceResponse(response, field, 'trainerOperatorName');
    //         } catch (error) {
    //             console.log('error in axios request');
    //             this.trainerOperatorNameMessageTarget.style.color = "red";
    //             this.trainerOperatorNameMessageTarget.textContent = "Erreur lors de la recherche du formateur.";
    //         }

    //     } else if (field === 'code') {
    //         try {
    //             const fields = ['name', field];
    //             const values = [this.trainerOperatorNameTarget.value, value];
    //             const response = await this.checkForDuplicate('/docauposte/operator/check-if-trainer-exist', field, fields, value, values)
    //             console.log('entire axios response for trainer existence:', response.data);
    //             this.handleTrainerExistenceResponse(response, field, 'trainerOperatorCode');
    //         } catch (error) {
    //             console.log('error in axios request');
    //             this.trainerOperatorCodeMessageTarget.style.color = "red";
    //             this.trainerOperatorCodeMessageTarget.textContent = "Erreur lors de la recherche du formateur.";
    //         }
    //     }
    // }

    // checkForDuplicate(url, field, fields, value, values) {
    //     if (fields && values) {
    //         console.log(`Checking for duplicates at ${url} with values:`, values);
    //         const payload = fields.reduce((obj, field, index) => {
    //             obj[field] = values[index];
    //             return obj;
    //         }, {});
    //         return axios.post(url, payload);
    //     } else {
    //         console.log(`Checking for duplicate at ${url} with value:`, value);

    //         return axios.post(url, { [field]: value });
    //     }




    async checkTrainerExistence(field, value) {
        const payload = {};
        payload[field] = value;

        // If the field is 'code', also take the value of the name from the target.
        if (field === 'code') {
            payload['name'] = this.trainerOperatorNameTarget.value;
        }

        try {
            const response = await axios.post('/docauposte/operator/check-if-trainer-exist', payload);
            console.log('entire axios response for trainer existence:', response.data);

            // Build the correct fieldName based on the field being checked.
            const fieldName = field === 'name' ? 'trainerOperatorName' : 'trainerOperatorCode';
            this.handleTrainerExistenceResponse(response, field, fieldName);

        } catch (error) {
            console.error('error in axios request', error); // Log out the actual error
            const messageTarget = this[`${fieldName}MessageTarget`];
            messageTarget.style.color = "red";
            messageTarget.textContent = "Erreur lors de la recherche du formateur.";
        }
    }




    handleTrainerExistenceResponse(response, field, fieldName) {
        if (response.data.found) {
            this[`${fieldName}Target`].disabled = true;
            this.validateTrainerOperatorCode();
        } else {
            this[`${fieldName}Target`].value = "";
            this[`${fieldName}MessageTarget`].style.color = "red";
            this[`${fieldName}MessageTarget`].textContent = "Formateur non trouvé. "[field];

        };

    }


    // trainerExistenceValidated(response, field, fieldName) {
    //     if (field === "name") {

    //     }


}