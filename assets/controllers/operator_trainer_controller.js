import { operatorCodeService } from './services/operator_code_service';
import { Controller } from '@hotwired/stimulus';
import axios from 'axios';


export default class OperatorTrainerController extends Controller {



    static targets = [
        "trainerOperatorName",
        "trainerOperatorCode",
        "trainerOperatorNameMessage",
        "trainerOperatorCodeMessage",
        "trainerOperatorLogon",
    ];

    currentRegexPattern = '[0-9]{5}';

    connect() {
        // Fetch the current regex pattern when the controller connects
        this.fetchRegexPattern();
    }

    async fetchRegexPattern() {
        try {
            const settings = await operatorCodeService.getSettings();
            console.log('Fetched global regex pattern:', settings.regex);
            if (settings) {
                // Remove any forward slashes that might be in the stored pattern
                this.currentRegexPattern = settings.regex.toString().replace(/^\/|\/$/g, '');
                console.log('Fetched regex pattern:', this.currentRegexPattern);
            }
        } catch (error) {
            console.error('Error fetching regex pattern:', error);
            // Keep the default pattern if there's an error
        }
    }




    trainerOperatorLogonTargetConnected() {
        this.trainerOperatorLoginCheck();
    }


    async trainerOperatorLoginCheck() {
        try {
            const response = await axios.post('/docauposte/operator/user-login-check');
            if (response.data.found) {
                this.trainerAuthenticated(response);
            } else {
                console.error('No user connected or User not found as a trainer');
            }
        } catch (error) {
            console.error('error in axios request', error); // Log out the actual error

        }
    }


    validateTrainerOperatorName() {
        clearTimeout(this.typingTimeout);  // clear any existing timeout to reset the timer
        this.typingTimeout = setTimeout(() => {
            const regex = /^[a-zA-Z]+\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/;
            const value = this.trainerOperatorNameTarget.value.trim();
            const isValid = regex.test(value);
            if (isValid) {
                this.checkTrainerExistence('name', value);
            } else {
                this.updateMessage(this.trainerOperatorNameMessageTarget, isValid, "Veuillez saisir sous la forme prenom.nom.");
            }
        }, 1800);
    }


    validateTrainerOperatorCode() {
        this.trainerOperatorCodeTarget.disabled = false;
        this.trainerOperatorCodeTarget.focus();

        clearTimeout(this.typingTimeout);  // clear any existing timeout to reset the timer
        this.typingTimeout = setTimeout(() => {
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
        }, 1800);
    }


    updateMessage(targetElement, isValid, errorMessage) {
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
                if (response.data.uploadTrainer === false) {
                    this.trainerOperatorNameMessageTarget.style.fontWeight = "bold";
                    this.trainerOperatorNameMessageTarget.style.color = "green";
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
            this[`${fieldName}MessageTarget`].textContent = field === 'name' ? "Formateur non trouvé." : "Code Opé Formateur erroné.";
            // Stop the repeating validation since we found the trainer
        };
    }


    trainerAuthenticated(response) {
        // initialize the new operator form
        this.loadOperatorTrainingContent(response);
        // enable the training button
        const operatorInputs = document.querySelectorAll('.operator-input');
        operatorInputs.forEach(function (input) {
            input.disabled = false;
        });
        this.logOutInputSwitch(true);
    }

    logOutInputSwitch(logOut) {
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
                            pattern="${this.currentRegexPattern}"
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
        this.validateTrainerOperatorName();
    }

}
