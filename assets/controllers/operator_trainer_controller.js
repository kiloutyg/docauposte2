
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
            if (field === 'name') {
                this.validateTrainerOperatorCode();
            } else {
                this.trainerAuthenticated();
                this.trainerOperatorNameMessageTarget.textContent = "";
            }
            this[`${fieldName}Target`].disabled = true;
            this[`${fieldName}MessageTarget`].style.fontWeight = "bold";
            this[`${fieldName}MessageTarget`].style.color = "green";
            this[`${fieldName}MessageTarget`].textContent = "Formateur trouvé.";
        } else {
            this[`${fieldName}Target`].value = "";
            this[`${fieldName}MessageTarget`].style.fontWeight = "bold";
            this[`${fieldName}MessageTarget`].style.color = "red";
            this[`${fieldName}MessageTarget`].textContent = "Formateur non trouvé. "[field];

        };

    }
    trainerAuthenticated() {
        // initialize the new operator form
        this.loadOperatorTrainingContent();
        // enable the training button

    }



    loadOperatorTrainingContent() {
        const container = document.getElementById('newOperatorContainer');

        // You would fetch this content via an API or similar.
        const content = `

            <div>
                <div
                    class="d-flex flex-fill">
                    <input
                        type="text"
                        class="form-control "
                        data-operator-training-target="newOperatorName"
                        data-action="keyup->operator-training#validateNewOperatorName"
                        placeholder="nom.prénom"
                        id="newOperatorName"
                        name="newOperatorName"
                        style="flex:0.5;"
                        required>
                    <input
                        type="text"
                        class="form-control"
                        data-operator-training-target="newOperatorCode"
                        data-action="keyup->operator-training#validateNewOperatorCode"
                        placeholder="Code Opérateur"
                        id="newOperatorCode"
                        name="newOperatorCode"
                        style="flex:0.3;"
                        required
                        disabled>
                    <input
                        type="submit"
                        class="btn btn-primary "
                        data-operator-training-target="newOperatorSubmitButton"
                        id="newOperatorSubmitButton"
                        name="newOperatorSubmitButton"
                        value="Ajouter"
                        style="flex:0.2;"
                        disabled>

                </div>
                <div
                    data-operator-training-target="newOperatorMessageName"
                    class="newOperatorName-message d-flex justify-content-evenly"></div>
                <div
                    data-operator-training-target="newOperatorMessageCode"
                    class="newOperatorCode-message d-flex justify-content-evenly"></div>
                <div
                    data-operator-training-target="newOperatorMessageTransfer"
                    class="newOperatorTransfer-message d-flex justify-content-evenly"></div>
            </div>

        `;

        container.innerHTML = content;
    }

    unloadOperatorTrainingContent() {
        const container = document.getElementById('newOperatorContainer');
        container.innerHTML = ''; // Clears out the inner content of the div
    }
}