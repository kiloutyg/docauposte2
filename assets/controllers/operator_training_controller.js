import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class extends Controller {
    static targets = ["newOperatorName", "newOperatorMessageName", "newOperatorCode", "newOperatorMessageCode", "newOperatorSubmitButton"];
    /*
    Validate the new operator name and code fields.
    Update the message to the user based on the validation results.

    */
    validateNewOperatorName() {
        console.log('validating new operator name: this.newOperatorNameTarget.value: ', this.newOperatorNameTarget.value);
        const regex = /^[a-zA-Z]+\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/;
        const isValid = regex.test(this.newOperatorNameTarget.value);
        this.updateMessage(this.newOperatorMessageNameTarget, isValid, "Veuillez saisir sous la forme prénom.nom");

        if (isValid) {
            this.checkForExistingEntityByName();
        }
    }



    validateNewOperatorCode() {
        console.log('validating new operator code: this.newOperatorCodeTarget.value: ', this.newOperatorCodeTarget.value);
        const regex = /^[0-9]{5}$/;
        const isValid = regex.test(this.newOperatorCodeTarget.value);
        this.updateMessage(this.newOperatorMessageCodeTarget, isValid, "Veuillez saisir un code correct.");

        if (isValid) {
            this.checkForExistingEntityByCode();
        }
    }



    async checkForExistingEntityByName() {
        try {
            const response = await this.checkForDuplicate('/docauposte/operator/check-duplicate-by-name', this.newOperatorNameTarget.value);
            console.log('response for check existing entity by name: ', response);
            this.handleDuplicateResponse(response, this.newOperatorMessageNameTarget, "noms d'opérateurs");
        } catch (error) {
            console.error("Error checking for a duplicate operator name.", error);
            this.manageNewOperatorSubmitButton();
            this.newOperatorMessageNameTarget.textContent = "Erreur lors de la vérification du nom opérateur.";
        }
    }



    async checkForExistingEntityByCode() {
        try {
            const response = await this.checkForDuplicate('/docauposte/operator/check-duplicate-by-code', this.newOperatorCodeTarget.value);
            console.log('response for check existing entity by code: ', response);
            this.handleDuplicateResponse(response, this.newOperatorMessageCodeTarget, "codes opérateurs");
        } catch (error) {
            console.error("Error checking for a duplicate operator code.", error);
            this.manageNewOperatorSubmitButton();
            this.newOperatorMessageCodeTarget.textContent = "Erreur lors de la vérification du code opérateur.";
        }
    }



    updateMessage(targetElement, isValid, errorMessage) {
        if (isValid) {
            targetElement.textContent = "";
            this.manageSubmitButton();
        } else {
            targetElement.textContent = errorMessage;
            targetElement.style.color = "red";
            this.manageNewOperatorSubmitButton();
        }
    }




    duplicateCheckResults = [];

    handleDuplicateResponse(response, messageTarget, fieldName) {
        this.duplicateCheckResults.push(response);

        console.log(`Number of stuff: ${this.duplicateCheckResults.length}`);

        messageTarget.textContent = response.data.found
            ? response.data.message
            : `Aucun doublon trouvé dans les ${fieldName}, prêt à créer.`;

        messageTarget.style.color = response.data.found ? "red" : "green";

        if (this.duplicateCheckResults.length === 2) {
            const resultsMatch = this.duplicateCheckResults[0].data.found == true && this.duplicateCheckResults[1].data.found == true;
            if (resultsMatch) {
                const entitiesMatch = this.duplicateCheckResults[0].data.operator.id === this.duplicateCheckResults[1].data.operator.id;
                this.newOperatorMessageCodeTarget.textContent = "";
                this.newOperatorMessageNameTarget.textContent = "";
                if (entitiesMatch) {
                    this.manageNewOperatorSubmitButton(true, "Transferer");
                    this.newOperatorMessageNameTarget.textContent = "Nom et Code opérateurs correspondent à un même opérateur. Vous pouvez le transferer.";
                    this.newOperatorMessageNameTarget.style.color = "green";
                } else {

                    this.newOperatorMessageNameTarget.textContent = "Nom et Code opérateurs ne correspondent pas à un même opérateur. Veuillez saisir un autre nom ou code opérateur";
                    this.newOperatorMessageNameTarget.style.color = "red"; // Changed to red to indicate an error
                }

            }
            // Clear the results after handling them 
            this.duplicateCheckResults = [];
        }

    }


    manageSubmitButton() {
        const nameValue = this.newOperatorNameTarget.value.trim();
        const codeValue = this.newOperatorCodeTarget.value.trim();
        this.manageNewOperatorSubmitButton(!nameValue || !codeValue);
    }


    async manageNewOperatorSubmitButton(booleanValue = false, submitValue = "Ajouter") {

        this.newOperatorSubmitButtonTarget.disabled = !booleanValue;
        this.newOperatorSubmitButtonTarget.value = submitValue;


    }

    checkForDuplicate(url, value) {
        return axios.post(url, { value: value });
    }

    checkForCorrespondingEntity(results) {

        if (results[0].data.operator.id === results[1].data.operator.id) {
            this.manageNewOperatorSubmitButton(true, "Transferer");
            return true;
        } else {
            this.manageNewOperatorSubmitButton(false);
            return false;
        }
    }
}
