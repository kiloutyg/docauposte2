
import { Controller } from '@hotwired/stimulus';
import axios from 'axios';


export default class OperatorTrainerController extends Controller {



    static targets = [
        "trainerOperatorName",
        "trainerOperatorCode",
        "trainerOperatorNameMessage",
        "trainerOperatorCodeMessage",

    ];



    validateTrainerOperatorName() {

        clearTimeout(this.typingTimeout);  // clear any existing timeout to reset the timer

        this.typingTimeout = setTimeout(() => {
            console.log('validating trainer name:', this.trainerOperatorNameTarget.value);

            const regex = /^[a-zA-Z]+\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/;

            const value = this.trainerOperatorNameTarget.value.trim();
            const isValid = regex.test(value);

            if (isValid) {
                this.checkTrainerExistence('name', value);
            } else {
                this.updateMessage(this.trainerOperatorNameMessageTarget, isValid, "Veuillez saisir sous la forme prenom.nom.");
            }
        }, 800);
    }


    validateTrainerOperatorCode() {
        this.trainerOperatorCodeTarget.disabled = false;
        this.trainerOperatorCodeTarget.focus();

        clearTimeout(this.typingTimeout);  // clear any existing timeout to reset the timer
        this.typingTimeout = setTimeout(() => {
            console.log('validating trainer code:', this.trainerOperatorCodeTarget.value);

            const regex = /^[0-9]{5}$/;
            const value = this.trainerOperatorCodeTarget.value.trim();
            const isValid = regex.test(value);

            if (value.length > 0) { // only check if the field is not empty
                if (isValid) {
                    this.checkTrainerExistence('code', value);
                } else {
                    this.updateMessage(this.trainerOperatorCodeMessageTarget, isValid, "Veuillez saisir un code valide: XXXXX.");
                }
            }
        }, 800);
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
        console.log('payload1:', payload);
        console.log('uploadId:', this.trainerOperatorNameTarget.dataset.uploadId);
        payload['uploadId'] = this.trainerOperatorNameTarget.dataset.uploadId;
        console.log('payload2:', payload);
        // If the field is 'code', also take the value of the name from the target.
        if (field === 'code') {
            payload['name'] = this.trainerOperatorNameTarget.value;
        }
        console.log('payload3:', payload);

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
                this.trainerAuthenticated(response);
                console.log('response.data.uploadTrainer:', response.data.uploadTrainer)
                if (response.data.uploadTrainer === false) {
                    this.trainerOperatorNameMessageTarget.style.fontWeight = "bold";
                    this.trainerOperatorNameMessageTarget.style.color = "red";
                    // this.trainerOperatorNameMessageTarget.textContent = "Formateur trouvé. Non habilité sur ce process.";
                    this.trainerOperatorNameMessageTarget.textContent = "Formateur trouvé.";
                } else {
                    this.trainerOperatorNameMessageTarget.textContent = "";
                }
            }
            this[`${fieldName}Target`].disabled = true;
            this[`${fieldName}MessageTarget`].textContent = "";
            this[`${fieldName}MessageTarget`].style.fontWeight = "bold";
            this[`${fieldName}MessageTarget`].style.color = "green";
            this[`${fieldName}MessageTarget`].textContent = "Formateur trouvé.";
        } else {
            this[`${fieldName}Target`].value = "";
            this[`${fieldName}MessageTarget`].textContent = "";
            this[`${fieldName}MessageTarget`].style.fontWeight = "bold";
            this[`${fieldName}MessageTarget`].style.color = "red";
            this[`${fieldName}MessageTarget`].textContent = "Formateur non trouvé. "[field];
            // Stop the repeating validation since we found the trainer
        };
    }


    trainerAuthenticated(response) {
        // initialize the new operator form
        this.loadOperatorTrainingContent(response);
        // enable the training button
        console.log('trainer authenticated');
        const operatorInputs = document.querySelectorAll('.operator-input');
        operatorInputs.forEach(function (input) {
            input.disabled = false;
        });
        this.logOutInputSwitch(true);
    }

    logOutInputSwitch(logOut) {
        console.log('logOutInputSwitch');
        const trainerLogOutContainer = document.getElementById('trainerLogOutContainer');
        let content = ``;
        if (logOut) {
            content = `
            <input
            type="button"
            class="btn btn-danger"
            data-action="click->operator-trainer#logOut"
            value="Déconnexion">
            `;
        }
        trainerLogOutContainer.innerHTML = content;

    }

    logOut() {
        console.log('logOut');
        this.trainerOperatorNameTarget.value = "";
        this.trainerOperatorNameTarget.disabled = false;
        this.trainerOperatorCodeTarget.value = "";
        this.trainerOperatorCodeTarget.disabled = true;
        this.trainerOperatorNameMessageTarget.textContent = "";
        this.trainerOperatorCodeMessageTarget.textContent = "";
        this.validateTrainerOperatorName();
        this.unloadOperatorTrainingContent();
        this.logOutInputSwitch(false);
        const operatorInputs = document.querySelectorAll('.operator-input');
        operatorInputs.forEach(function (input) {
            input.disabled = true;
        });

    }


    loadOperatorTrainingContent(response) {
        const container = document.getElementById('newOperatorContainer');

        // You would fetch this content via an API or similar.
        const content = `

                <div class="d-flex">
                    <div class="col-4 mx-1">
                        <input
                            type="text"
                            class="form-control capitalize-all-letters"
                            data-operator-training-target="newOperatorSurname"
                            data-action="keyup->operator-training#validateNewOperatorSurname input->operator-training#suggestSurname"
                            placeholder="NOM"
                            id="newOperatorSurname"
                            name="newOperatorSurname"
                            required>
                    <div
                        data-operator-training-target="nameSuggestions"
                        class="traininglist-suggestions-list rounded-bottom"></div>
                    </div>
                
                    <div
                        class="col-3 mx-0"
                        >
                        <input
                            type="text"
                            class="form-control capitalize-first-letter::first-letter"
                            data-operator-training-target="newOperatorFirstname"
                            data-action="keyup->operator-training#validateNewOperatorFirstname input->operator-training#suggestFirstname"
                            placeholder="Prenom"
                            id="newOperatorFirstname"
                            name="newOperatorFirstname"
                            required
                            disabled>
                    </div>
                    <div
                        class="col-2 mx-1"
                        >
                        <input
                            type="text"
                            pattern="[0-9]{5}"
                            maxlength="5"
                            class="form-control"
                            data-operator-training-target="newOperatorCode"
                            data-action="keyup->operator-training#validateNewOperatorCode"
                            placeholder="Code Opérateur"
                            id="newOperatorCode"
                            name="newOperatorCode"
                            required
                            disabled>
                    </div>
                        <input type="hidden" id="newOperatorName" name="newOperatorName">           
                    <div
                        class="col-1 mx-0"
                        >
                        <input
                            type="submit"
                            class="btn btn-primary"
                            data-operator-training-target="newOperatorSubmitButton"
                            data-action="click->operator-trainer#resetFollowingSubmit"
                            id="newOperatorSubmitButton"
                            name="newOperatorSubmitButton"
                            value="Ajouter"
                            disabled>
                    </div>
                </div>

                <div
                    data-operator-training-target="newOperatorNameMessage"
                    class="newOperatorName-message d-flex justify-content-evenly"></div>
                <div
                    data-operator-training-target="newOperatorCodeMessage"
                    class="newOperatorCode-message d-flex justify-content-evenly"></div>
                <div
                    data-operator-training-target="newOperatorTransferMessage"
                    class="newOperatorTransfer-message d-flex justify-content-evenly"></div>
        `;

        container.innerHTML = content;
        console.log('response inside loadOperatorTrainingContent the famous form :', response)
        let trainerId = response.data.trainerId;
        const listUpdateSubmitContainer = document.getElementById('trainingValidationSubmitContainer');

        const listUpdateSubmitContent = `
        <input type="hidden" name="trainerId" value="${trainerId}">			
        <input
        type="submit"
        class="btn btn-primary"
        data-action="click->operator-trainer#resetFollowingSubmit"
        value="Enregistrer les modifications">
        `;
        listUpdateSubmitContainer.innerHTML = listUpdateSubmitContent;
    }



    unloadOperatorTrainingContent() {
        const container = document.getElementById('newOperatorContainer');
        container.innerHTML = ''; // Clears out the inner content of the div
        const listUpdateSubmitContainer = document.getElementById('trainingValidationSubmitContainer');
        listUpdateSubmitContainer.innerHTML = '';
    }



    resetFollowingSubmit() {
        console.log('new operator submit button clicked');
        this.validateTrainerOperatorName();
    }

}
