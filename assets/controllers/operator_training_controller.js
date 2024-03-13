
import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class extends Controller {
    static targets = ["newOperatorName", "newOperatorMessageName", "newOperatorCode", "newOperatorMessageCode", "newOperatorMessageTransfer", "newOperatorSubmitButton", "trainingOperatorCode"];

    validateNewOperatorName() {
        console.log('validating new operator name:', this.newOperatorNameTarget.value);

        const regex = /^[a-zA-Z]+\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/;
        let isValid;

        if (this.duplicateCheckResults.name) {
            console.log('duplicate check results data value for name:', this.duplicateCheckResults.name.data.value);
            if (this.newOperatorNameTarget.value.trim() === this.duplicateCheckResults.name.data.value) {
                console.log('Name is the same as the previous duplicate check, no need to do anything.');
                return;
            } else {
                this.duplicateCheckResults.name = null;
                isValid = regex.test(this.newOperatorNameTarget.value.trim());
            }
        } else {
            isValid = regex.test(this.newOperatorNameTarget.value.trim());
        }

        this.newOperatorMessageTransferTarget.textContent = "";
        this.updateMessage(this.newOperatorMessageNameTarget, isValid, "Veuillez saisir sous la forme prénom.nom");

        if (isValid) {
            this.checkForExistingEntityByName();
        }
    }


    validateNewOperatorCode() {
        console.log('validating new operator code:', this.newOperatorCodeTarget.value);

        const regex = /^[0-9]{5}$/;
        let isValid;

        if (this.duplicateCheckResults.code) {
            console.log('duplicate check results data value for code:', this.duplicateCheckResults.code.data.value);
            if (this.newOperatorCodeTarget.value.trim() === this.duplicateCheckResults.code.data.value) {
                console.log('Code is the same as the previous duplicate check, no need to do anything.');
                return;
            } else {
                this.duplicateCheckResults.code = null;
                isValid = regex.test(this.newOperatorCodeTarget.value.trim());
            }
        } else {
            isValid = regex.test(this.newOperatorCodeTarget.value.trim());
        }
        this.newOperatorMessageTransferTarget.textContent = "";
        this.updateMessage(this.newOperatorMessageCodeTarget, isValid, "Veuillez saisir un code correct.");

        if (isValid) {
            console.log('Code is valid, clearing duplicate check results and checking for existing entity by code')
            this.duplicateCheckResults.code = null;
            this.checkForExistingEntityByCode();
        }
    }


    async checkForExistingEntityByName() {
        try {
            console.log('checking for existing entity by name:', this.newOperatorNameTarget.value);

            const response = await this.checkForDuplicate('/docauposte/operator/check-duplicate-by-name', this.newOperatorNameTarget.value);
            console.log('response for existing entity by name:', response.data.found);
            this.handleDuplicateResponse(response, this.newOperatorMessageNameTarget, "noms d'opérateurs");
        } catch (error) {
            console.error("Error checking for a duplicate operator name.", error);
            this.manageNewOperatorSubmitButton();
            this.newOperatorMessageNameTarget.textContent = "Erreur lors de la vérification du nom opérateur.";
        }
    }



    async checkForExistingEntityByCode() {
        try {
            console.log('checking for existing entity by code:', this.newOperatorCodeTarget.value);

            const response = await this.checkForDuplicate('/docauposte/operator/check-duplicate-by-code', this.newOperatorCodeTarget.value);
            console.log('response for existing entity by code:', response.data.found);
            this.handleDuplicateResponse(response, this.newOperatorMessageCodeTarget, "codes opérateurs");
        } catch (error) {
            console.error("Error checking for a duplicate operator code.", error);
            this.manageNewOperatorSubmitButton();
            this.newOperatorMessageCodeTarget.textContent = "Erreur lors de la vérification du code opérateur.";
        }
    }



    updateMessage(targetElement, isValid, errorMessage) {
        console.log(`Updating message: isValid: ${isValid}`);
        if (isValid) {
            targetElement.textContent = "";
        } else {
            targetElement.textContent = errorMessage;
            targetElement.style.color = "red";
            this.manageNewOperatorSubmitButton();
        }
    }



    duplicateCheckResults = { name: null, code: null };

    handleDuplicateResponse(response, messageTarget, fieldName) {
        console.log(`Handling duplicate response for ${fieldName}:`, response.data.found, response.data.field, response.data.message);

        messageTarget.textContent = response.data.found
            ? response.data.message
            : `Aucun doublon trouvé dans les ${fieldName}.`;

        messageTarget.style.color = response.data.found ? "red" : "green";

        console.log('what\'s in the duplicate check results variable:', this.duplicateCheckResults);

        if (response.data.field === "name") {
            // if (fieldName === "noms d'opérateurs" && response.data.field == "name") {
            this.duplicateCheckResults.name = response;
            console.log('Duplicate check results for name:', this.duplicateCheckResults.name);
        } else if (response.data.field === "code") {
            // } else if (fieldName === "codes opérateurs" && response.data.field == "code") {
            this.duplicateCheckResults.code = response;
            console.log('Duplicate check results for code:', this.duplicateCheckResults.code);
        }
        this.checkForCorrespondingEntity();
    }



    manageNewOperatorSubmitButton(enableButton = false, submitValue = "Ajouter") {
        console.log(`Setting new operator submit button - Enabled: ${enableButton}, Value: ${submitValue}`);
        this.newOperatorSubmitButtonTarget.disabled = !enableButton;
        this.newOperatorSubmitButtonTarget.value = submitValue;
    }



    checkForDuplicate(url, value) {
        console.log(`Checking for duplicate at ${url} with value:`, value);
        return axios.post(url, { value: value });
    }



    checkForCorrespondingEntity() {
        console.log('Checking for corresponding entity with duplicate check results:', this.duplicateCheckResults);
        if (this.duplicateCheckResults.name && this.duplicateCheckResults.code) {

            console.log('Evaluating duplicate check results for name and code match');
            const bothFound = Object.values(this.duplicateCheckResults).every(result => result.data.found);
            const bothNotFound = Object.values(this.duplicateCheckResults).every(result => !result.data.found);

            console.log('Both found:', bothFound, 'Both not found:', bothNotFound);

            if (bothFound) {
                this.executeEntityMatchingLogic(bothFound);
            } else if (bothNotFound) {
                this.executeEntityNonMatchingLogic(bothNotFound);
            } else {
                this.manageNewOperatorSubmitButton(false);
            }
        }
    }


    executeEntityMatchingLogic(matchesFound) {
        console.log('Executing entity matching logic:', matchesFound);

        const nameOperatorId = this.duplicateCheckResults.name?.data?.operator?.id;
        const codeOperatorId = this.duplicateCheckResults.code?.data?.operator?.id;
        const entitiesMatch = matchesFound && nameOperatorId === codeOperatorId;

        const submitValue = entitiesMatch ? "Transferer" : "Ajouter";
        const message = entitiesMatch
            ? "Nom et Code opérateurs correspondent à un même opérateur. Vous pouvez le transferer."
            : "Nom et Code opérateurs ne correspondent pas à un même opérateur. Veuillez saisir un autre nom ou code opérateur";

        this.newOperatorMessageTransferTarget.textContent = message;
        this.newOperatorMessageTransferTarget.style.color = entitiesMatch ? "green" : "red";
        this.resetUselessMessages();

        console.log(`Manage submit button to be ${entitiesMatch ? "enabled" : "disabled"} with value ${submitValue}`);
        this.manageNewOperatorSubmitButton(entitiesMatch, submitValue);
    }


    executeEntityNonMatchingLogic(unMatchedFound) {
        this.newOperatorMessageTransferTarget.textContent = "Nom et Code opérateurs ne correspondent à aucun opérateur. Vous pouvez les ajouter.";
        this.newOperatorMessageTransferTarget.style.color = "green";
        this.manageNewOperatorSubmitButton(unMatchedFound, "Ajouter");
    }


    resetUselessMessages() {
        console.log('Resetting useless messages if there is a transfer message.');
        if (this.newOperatorMessageTransferTarget.textContent !== "") {
            console.log('Clearing name and code validation messages');
            this.newOperatorMessageNameTarget.textContent = "";
            this.newOperatorMessageCodeTarget.textContent = "";
        }
    }



    validateCodeEntryForTraining() {
        console.log('validating training operator code:', this.trainingOperatorCodeTarget.value);
        const regex = /^[0-9]{5}$/;
        const isValid = regex.test(this.trainingOperatorCodeTarget.value.trim());

        if (isValid) {
            this.checkOperatorIdentityByCode();
        }
    }


    async checkOperatorIdentityByCode() {
        const code = this.trainingOperatorCodeTarget.value;
        const operatorId = this.trainingOperatorCodeTarget.dataset.operatorId;
        const teamId = this.trainingOperatorCodeTarget.dataset.teamId;
        const uapId = this.trainingOperatorCodeTarget.dataset.uapId;

        try {
            console.log('Checking operator identity by code:', this.trainingOperatorCodeTarget.value);
            const response = await this.checkCodeAgainstOperatorCode('/docauposte/operator/check-entered-code-against-operator-code', code, operatorId, teamId, uapId);
            if (response.data) {
                console.log('response for operator identity by code:', response.data);
                this.inputSwitch(response.data);
            }
        } catch (error) {
            console.error("Error checking for operator identity by code.", error);
        }

    }


    checkCodeAgainstOperatorCode(url, code, operatorId, teamId, uapId) {
        console.log(`Checking code against operator code: ${code}, operatorId: ${operatorId}, teamId: ${teamId}, uapId: ${uapId}`);
        return axios.post(`${url}/${teamId}/${uapId}`, { code: code, operatorId: operatorId, teamId: teamId, uapId: uapId });
    }

    inputSwitch(response) {
        console.log('input switch response:', response);

        if (response.found) {
            // Create checkbox element
            const checkbox = document.createElement('input');
            checkbox.setAttribute('type', 'checkbox');
            checkbox.setAttribute('class', 'btn-check');
            checkbox.setAttribute('name', `operators[${response.operator.id}][trained]`);
            checkbox.setAttribute('id', `success-outlined[${response.operator.id}]`);
            checkbox.setAttribute('autocomplete', 'off');
            checkbox.setAttribute('value', 'true')

            // Create label element
            const label = document.createElement('label');
            label.setAttribute('class', 'btn btn-outline-success p-1 m-1');
            label.setAttribute('for', `success-outlined[${response.operator.id}]`);
            label.textContent = 'Formé';

            // Remove the original text input element
            this.trainingOperatorCodeTarget.remove();

            // Append the new checkbox and label to the parent container
            const parentContainer = document.querySelector(`#trainingCheckbox${response.operator.id}`); // Replace '#parent_container' with the actual ID or class of the parent container where you want to insert the checkbox and label.
            parentContainer.appendChild(checkbox);
            parentContainer.appendChild(label);
        } else {
            this.trainingOperatorCodeTarget.value = "";
            this.trainingOperatorCodeTarget.placeholder = "Code invalide";
        }
    }

}
