
import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class extends Controller {
    static targets = ["newOperatorName", "newOperatorMessageName", "newOperatorCode", "newOperatorMessageCode", "newOperatorMessageTransfer", "newOperatorSubmitButton"];

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
            : `Aucun doublon trouvé dans les ${fieldName}, prêt à créer.`;

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
}
