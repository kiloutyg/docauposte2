import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class OperatorAdminController extends Controller {


    static targets = [
        "newOperatorSurname",
        "newOperatorFirstname",
        "newOperatorNameMessage",  // No need for data-action if only updated programmatically
        "newOperatorCode",
        "newOperatorCodeMessage",  // No need for data-action if only updated programmatically
        "newOperatorTransferMessage",  // No need for data-action if only updated programmatically
        "newOperatorSubmitButton",
        "trainingOperatorCode",
    ];


    connect() {
        this.initializeDynamicFields();
    }

    initializeDynamicFields() {
        this.element.querySelectorAll("[data-operator-admin-target]").forEach(element => {
            const actionMapping = {
                keyup: 'validateField',
                input: 'capitalizeFirstLetter'
            };

            // Ensure that element has a 'data-action' attribute before accessing it
            if (element.dataset.action) {
                Object.keys(actionMapping).forEach(event => {
                    if (element.dataset.action.includes(event)) {
                        element.addEventListener(event, this[actionMapping[event]].bind(this));
                    }
                });
            } else {
                console.warn('data-action attribute missing on:', element);
            }
        });
    }


    validateField(event) {
        const target = event.target;
        const value = target.value;
        const fieldType = target.dataset.type;

        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
            let regex;
            let isValid = false;

            switch (fieldType) {
                case 'surname':
                    regex = /^[A-Z]+$/;
                    target.value = value.toUpperCase();
                    isValid = regex.test(target.value.trim());
                    if (isValid) this.prepareCombinedName();
                    break;
                case 'firstname':
                    regex = /^[A-Z][a-z]+(-[A-Z][a-z]+)*$/;
                    target.value = this.capitalizeFirstLetter(value);
                    isValid = regex.test(target.value.trim());
                    if (isValid) this.prepareCombinedName();
                    break;
                case 'code':
                    regex = /^[0-9]{5}$/;
                    isValid = regex.test(value.trim());
                    break;
            }

            const messageTarget = this.element.querySelector(`[data-message-for="${fieldType}"]`);
            this.updateMessage(messageTarget, isValid, "Invalid input");

            if (isValid && fieldType !== 'code') {
                this.enableNextField(target);
            }
        }, 800);
    }

    prepareCombinedName() {
        if (this.hasValidSurname() && this.hasValidFirstname()) {
            const combinedName = `${this.newOperatorFirstnameTarget.value.trim()}.${this.newOperatorSurnameTarget.value.trim()}`;
            this.newOperatorNameTarget.value = combinedName.toLowerCase(); // Assuming you have a target for the full name
            this.validateNewOperatorName();
        }
    }

    hasValidSurname() {
        return /^[A-Z]+$/.test(this.newOperatorSurnameTarget.value.trim());
    }

    hasValidFirstname() {
        return /^[A-Z][a-z]+(-[A-Z][a-z]+)*$/.test(this.newOperatorFirstnameTarget.value.trim());
    }

    validateNewOperatorName() {
        const fullName = this.newOperatorNameTarget.value;
        const regex = /^[a-zA-Z]+\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/;
        const isValid = regex.test(fullName);
        const messageTarget = this.newOperatorNameMessageTarget; // Assuming you have this target

        this.updateMessage(messageTarget, isValid, "Please enter valid firstname.lastname format.");

        if (isValid) {
            this.checkForExistingEntityByName(fullName);
        } else {
            this.newOperatorCodeTarget.disabled = true; // Disable further actions until name is corrected
        }
    }

    checkForExistingEntityByName(name) {
        console.log('Checking for existing entity by name:', name);
        this.checkForExistingEntity('name');
    }

    checkForExistingEntityByCode() {
        const code = this.newOperatorCodeTarget.value;
        console.log('Checking for existing entity by code:', code);
        this.checkForExistingEntity('code');
    }



    enableNextField(currentField) {
        const nextFieldId = currentField.dataset.nextFieldId;
        if (nextFieldId) {
            const nextField = this.element.querySelector(`#${nextFieldId}`);
            nextField.disabled = false;
            nextField.focus();
        }
    }

    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }

    capitalizeFirstLetter(event) {
        const input = event.target;
        if (input.selectionStart <= 1) {
            input.value = this.capitalizeFirstLetter(input.value);
        }
    }

    async checkForExistingEntity(field) {
        const fieldValue = this.element.querySelector(`[data-operator-admin-target='${field}']`).value;
        const url = field === 'name' ? '/docauposte/operator/check-duplicate-by-name' : '/docauposte/operator/check-duplicate-by-code';
        try {
            const response = await axios.post(url, { value: fieldValue });
            const messageTarget = this.element.querySelector(`[data-message-for="${field}"]`);
            this.handleDuplicateResponse(response.data, messageTarget, field);
        } catch (error) {
            console.error(`Error checking for a duplicate operator ${field}.`, error);
            const messageTarget = this.element.querySelector(`[data-message-for="${field}"]`);
            messageTarget.textContent = `Erreur lors de la vérification du ${field} de l'opérateur.`;
            messageTarget.style.color = "red";
        }
    }
    handleDuplicateResponse(data, messageTarget, fieldName) {
        console.log(`Handling duplicate response for ${fieldName}:`, data.found, data.message);
        messageTarget.textContent = data.found ? data.message : `Aucun doublon trouvé pour les ${fieldName}.`;
        messageTarget.style.color = data.found ? "red" : "green";
        this.manageNewOperatorSubmitButton(!data.found, fieldName);
    }

    manageNewOperatorSubmitButton(enableButton = false) {
        console.log(`Setting new operator submit button - Enabled: ${enableButton}`);
        this.newOperatorSubmitButtonTarget.disabled = !enableButton;
        if (enableButton) {
            this.resetFieldsAfterSubmit();
            if (fieldName === 'name') {
                this.newOperatorCodeTarget.disabled = false;
                this.newOperatorCodeTarget.focus();
            }
        }
    }

    resetFieldsAfterSubmit() {
        console.log('Resetting new operator form');
        this.targets.forEach(target => {
            if (target.type === 'text' || target.type === 'number') {
                target.value = '';
                target.disabled = true; // Assuming the first field is enabled via another method when it’s time to add a new operator
            }
        });
        this.newOperatorSurnameTarget.disabled = false; // Enable the first field
        this.newOperatorSurnameTarget.focus();
    }

    updateMessage(targetElement, isValid, errorMessage) {
        if (isValid) {
            targetElement.textContent = "";
            this.manageNewOperatorSubmitButton(true);
        } else {
            targetElement.textContent = errorMessage;
            targetElement.style.color = "red";
            this.manageNewOperatorSubmitButton(false);
        }
    }

}
