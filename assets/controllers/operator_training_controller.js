import { operatorCodeService } from './services/operator_code_service';
import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class OperatorTrainingController extends Controller {


    static targets = [
        "newOperatorSurname",
        "newOperatorFirstname",
        "newOperatorNameMessage",
        "newOperatorCode",
        "newOperatorCodeMessage",
        "newOperatorTransferMessage",
        "newOperatorSubmitButton",
        "trainingOperatorCode",
        "nameSuggestions",
    ];


    suggestionsResults = [];

    validateNewOperatorSurname() {
        clearTimeout(this.surnameTypingTimeout);
        this.surnameTypingTimeout = setTimeout(() => {
            const regex = /^[A-Z][A-Z]+$/;
            const surname = this.newOperatorSurnameTarget.value.toUpperCase();
            const isValid = regex.test(surname.trim());
            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir un nom valide(sans accent, ni caractères spéciaux).");
            if (isValid) {
                if (this.newOperatorFirstnameTarget.value.trim() === "") {
                    this.newOperatorFirstnameTarget.disabled = false;
                    this.newOperatorFirstnameTarget.focus();
                }
                this.validateNewOperatorFirstname();
            }

        }, 1500);
    }

    validateNewOperatorFirstname() {
        document.getElementById('newOperatorFirstname').addEventListener('input', function (e) {
            var value = e.target.value;
            e.target.value = value.charAt(0).toUpperCase() + value.slice(1);
        });

        clearTimeout(this.firstnameTypingTimeout);
        this.firstnameTypingTimeout = setTimeout(() => {
            const firstnameValue = this.newOperatorFirstnameTarget.value;
            this.firstnameValue = this.capitalizeFirstLetter(firstnameValue);
            const regex = /^[A-Z][a-z]+(-[A-Z][a-z]+)*$/;
            const isValid = regex.test(this.firstnameValue.trim());
            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir un prenom valide(sans accent, ni caractères spéciaux).");
            if (isValid) {
                let combinedName = `${this.newOperatorFirstnameTarget.value.trim()}.${this.newOperatorSurnameTarget.value.trim()}`;
                this.newOperatorNameTarget = combinedName.toLowerCase();

                let invertedCombined = `${this.newOperatorSurnameTarget.value.trim()}.${this.newOperatorFirstnameTarget.value.trim()}`;
                this.newOperatorInvertedNameTarget = invertedCombined.toLowerCase();
                this.validateNewOperatorName();
            }
        }, 1500);
    }



    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }



    validateNewOperatorName() {
        clearTimeout(this.nameTypingTimeout);  // clear any existing timeout to reset the timer

        this.nameTypingTimeout = setTimeout(() => {

            const regex = /^[a-zA-Z]+\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/;
            let isValid;

            if (this.duplicateCheckResults.name) {
                if (this.newOperatorNameTarget.trim() === this.duplicateCheckResults.name.data.value) {
                    return;
                } else {
                    this.duplicateCheckResults.name = null;
                    isValid = regex.test(this.newOperatorNameTarget.trim());
                }
            } else {
                isValid = regex.test(this.newOperatorNameTarget.trim());
            }

            this.newOperatorTransferMessageTarget.textContent = "";
            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir sous la forme NOM Prenom.");
            this.newOperatorCodeTarget.disabled = true;

            if (isValid) {
                this.checkForExistingEntityByName();
            }
        }, 1500); // delay in milliseconds
    }



    validateNewOperatorCode() {
        clearTimeout(this.codeTypingTimeout);
        this.codeTypingTimeout = setTimeout(async () => {

            let isValid;

            if (this.duplicateCheckResults.code) {
                if (this.newOperatorCodeTarget.value.trim() === this.duplicateCheckResults.code.data.value) {
                    return;
                } else {
                    this.duplicateCheckResults.code = null;
                    isValid = await operatorCodeService.validateCode(this.newOperatorCodeTarget.value.trim());
                }
            } else {
                isValid = await operatorCodeService.validateCode(this.newOperatorCodeTarget.value.trim());
            }
            this.newOperatorTransferMessageTarget.textContent = "";
            this.updateMessage(this.newOperatorCodeMessageTarget, isValid, "Veuillez saisir un code correct.");

            if (isValid) {
                this.duplicateCheckResults.code = null;
                this.checkForExistingEntityByCode();
            }
        }, 1500);
    }



    async checkForExistingEntityByName() {
        try {
            // Initial log indicating the start of a duplicate check

            // First check for the default name
            let response = await this.checkForDuplicate('/docauposte/operator/check-duplicate-by-name', this.newOperatorNameTarget);

            // Only proceed to check the inverted name if no duplicate was found for the first name
            if (!response.data.found) {
                response = await this.checkForDuplicate('/docauposte/operator/check-duplicate-by-name', this.newOperatorInvertedNameTarget);
            }

            // Handle the response based on the last API call made
            this.handleDuplicateResponse(response, this.newOperatorNameMessageTarget, "noms d'opérateurs");

        } catch (error) {
            // Error handling for any issue during the API call or processing
            console.error("Error checking for a duplicate operator name.", error);
            this.manageNewOperatorSubmitButton();
            this.newOperatorNameMessageTarget.textContent = "Erreur lors de la vérification du nom opérateur.";
        }
    }



    async checkForExistingEntityByCode() {
        try {
            const response = await this.checkForDuplicate('/docauposte/operator/check-duplicate-by-code', this.newOperatorCodeTarget.value);
            this.handleDuplicateResponse(response, this.newOperatorCodeMessageTarget, "codes opérateurs");
        } catch (error) {
            console.error("Error checking for a duplicate operator code.", error);
            this.manageNewOperatorSubmitButton();
            this.newOperatorCodeMessageTarget.textContent = "Erreur lors de la vérification du code opérateur.";
        }
    }



    updateMessage(targetElement, isValid, errorMessage) {
        clearTimeout(this.messageTimeout);
        this.messageTimeout = setTimeout(() => {
            if (isValid) {
                targetElement.textContent = "";
            } else {
                targetElement.textContent = errorMessage;
                targetElement.style.fontWeight = "bold";
                targetElement.style.color = "red";
                this.manageNewOperatorSubmitButton();
            }
        }, 1000);
    }



    duplicateCheckResults = { name: null, code: null };

    async handleDuplicateResponse(response, messageTarget, fieldName) {

        messageTarget.textContent = response.data.found
            ? response.data.message
            : `Aucun doublon trouvé dans les ${fieldName}.`;

        messageTarget.style.fontWeight = "bold";
        messageTarget.style.color = response.data.found ? "red" : "green";

        if (response.data.found) {
            if (response.data.field === "name") {
                this.duplicateCheckResults.name = response;
            } else if (response.data.field === "code") {
                this.duplicateCheckResults.code = response;
            }
            this.checkForCorrespondingEntity();
            this.newOperatorCodeTarget.disabled = false;
            this.newOperatorCodeTarget.focus();
        } else {
            const settings = await operatorCodeService.getSettings()
            if (response.data.field === "name" && settings.methodEnabled) {
                this.proposeCompliantNewCode();
            }
            this.newOperatorCodeTarget.disabled = false;
            this.newOperatorCodeTarget.focus();
            this.manageNewOperatorSubmitButton(true, "Ajouter");
        }
    }



    manageNewOperatorSubmitButton(enableButton = false, submitValue = "Ajouter") {
        if (this.suggestionsResults.length === 0) {
            this.newOperatorSubmitButtonTarget.disabled = !enableButton;
        }
        document.getElementById('newOperatorName').value = this.newOperatorNameTarget;
        this.newOperatorSubmitButtonTarget.value = submitValue;
        clearTimeout(this.validatedTimeout);
        this.validatedTimeout = setTimeout(() => {
            this.newOperatorCodeTarget.value = "";
            this.newOperatorSurnameTarget.value = "";
            this.newOperatorFirstnameTarget.value = "";
            this.newOperatorNameTarget = "";
            this.duplicateCheckResults = { name: null, code: null };
            this.newOperatorSurnameTarget.disabled = false;
            this.newOperatorCodeTarget.disabled = true;
            this.newOperatorSurnameTarget.focus();
            this.newOperatorSubmitButtonTarget.disabled = true;
            this.newOperatorFirstnameTarget.disabled = true;
            this.resetUselessMessages();
            this.newOperatorCodeMessageTarget.textContent = "";
            this.newOperatorNameMessageTarget.textContent = "";
            this.newOperatorTransferMessageTarget.textContent = "";
            this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions
            this.suggestionsResults = [];
        }, 15000);

    }



    checkForDuplicate(url, value) {
        return axios.post(url, { value: value });
    }



    checkForCorrespondingEntity() {
        if (this.duplicateCheckResults.name && this.duplicateCheckResults.code) {

            const bothFound = Object.values(this.duplicateCheckResults).every(result => result.data.found);
            const bothNotFound = Object.values(this.duplicateCheckResults).every(result => !result.data.found);

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

        const nameOperatorId = this.duplicateCheckResults.name?.data?.operator?.id;
        const codeOperatorId = this.duplicateCheckResults.code?.data?.operator?.id;
        const entitiesMatch = matchesFound && nameOperatorId === codeOperatorId;

        const submitValue = entitiesMatch ? "Transferer" : "Ajouter";
        const message = entitiesMatch
            ? "Nom et Code opérateurs correspondent à un même opérateur. Vous pouvez le transferer."
            : "Nom et Code opérateurs ne correspondent pas à un même opérateur. Veuillez saisir un autre nom ou code opérateur";

        this.newOperatorTransferMessageTarget.textContent = message;
        this.newOperatorTransferMessageTarget.style.color = entitiesMatch ? "green" : "red";
        this.resetUselessMessages();

        this.manageNewOperatorSubmitButton(entitiesMatch, submitValue);
    }



    executeEntityNonMatchingLogic(unMatchedFound) {
        this.newOperatorTransferMessageTarget.textContent = "Nom et Code opérateurs ne correspondent à aucun opérateur. Vous pouvez les ajouter.";
        this.newOperatorTransferMessageTarget.style.color = "green";
        this.manageNewOperatorSubmitButton(unMatchedFound, "Ajouter");
    }



    resetUselessMessages() {
        if (this.newOperatorTransferMessageTarget.textContent !== "") {
            this.newOperatorNameMessageTarget.textContent = "";
            this.newOperatorCodeMessageTarget.textContent = "";
        }
        const operatorInputs = document.querySelectorAll('.operator-input');
        operatorInputs.forEach(function (input) {
            input.disabled = false;
        });
    }



    async validateCodeEntryForTraining() {
        clearTimeout(this.trainingCodeTypingTimeout);
        this.trainingCodeTypingTimeout = setTimeout(async () => {
            const isValid = await operatorCodeService.validateTrainingCode(this.trainingOperatorCodeTarget.value);
            if (isValid) {
                this.checkOperatorIdentityByCode();
            } else {
                this.trainingOperatorCodeTarget.value = "";
                this.trainingOperatorCodeTarget.placeholder = "Invalide";
            }
        }, 1500);
    }



    async checkOperatorIdentityByCode() {
        const code = this.trainingOperatorCodeTarget.value;
        const operatorId = this.trainingOperatorCodeTarget.dataset.operatorId;
        const teamId = this.trainingOperatorCodeTarget.dataset.teamId;
        const uapId = this.trainingOperatorCodeTarget.dataset.uapId;

        try {
            const response = await this.checkCodeAgainstOperatorCode('/docauposte/operator/check-entered-code-against-operator-code', code, operatorId, teamId, uapId);
            if (response.data.found) {
                this.inputSwitch(response.data);
            } else {
                this.trainingOperatorCodeTarget.value = "";
                this.trainingOperatorCodeTarget.placeholder = "Erroné";
            }
        } catch (error) {
            console.error("Error checking for operator identity by code.", error);
        }
    }



    checkCodeAgainstOperatorCode(url, code, operatorId, teamId, uapId) {
        return axios.post(`${url}/${teamId}/${uapId}`, { code: code, operatorId: operatorId, teamId: teamId, uapId: uapId });
    }



    inputSwitch(response) {

        if (response.found) {
            // Create checkbox element
            const checkbox = document.createElement('input');
            checkbox.setAttribute('type', 'checkbox');
            checkbox.setAttribute('class', 'btn-check');
            checkbox.setAttribute('name', `operators[${response.operator.id}][trained]`);
            checkbox.setAttribute('id', `success-outlined[${response.operator.id}]`);
            checkbox.setAttribute('autocomplete', 'off');
            checkbox.setAttribute('value', 'true');


            // Create label element
            const label = document.createElement('label');
            label.setAttribute('class', 'btn btn-outline-success p-1 m-1');
            label.setAttribute('for', `success-outlined[${response.operator.id}]`);
            label.textContent = 'À Former';

            // Set the background color of the label to white
            label.style.backgroundColor = 'white';

            // Event listener to toggle class based on checkbox checked state
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                    label.style.backgroundColor = 'green';
                    label.textContent = 'Formé';

                } else {
                    label.style.backgroundColor = 'white';
                    label.textContent = 'À Former';

                }
            });

            // Remove the original text input element
            this.trainingOperatorCodeTarget.remove();

            // Append the new checkbox and label to the parent container
            const parentContainer = document.querySelector(`#trainingCheckbox${response.operator.id}`); // Replace '#parent_container' with the actual ID or class of the parent container where you want to insert the checkbox and label.


            parentContainer.appendChild(checkbox);
            parentContainer.appendChild(label);
        } else {
            this.trainingOperatorCodeTarget.value = "";
            this.trainingOperatorCodeTarget.placeholder = "Invalide";

        }
    }



    async proposeCompliantNewCode() {
        const code = this.codeGenerator();
        this.newOperatorCodeTarget.value = code;
        this.newOperatorCodeTarget.disabled = true;
        this.newOperatorCodeTarget.focus();
        this.validateNewOperatorCode();

    }



    async generatedTrainingCodeChecker(code) {
        // Since axios.post is asynchronous, we need to handle it with async/await or promises
        return axios.post('/docauposte/operator/check-if-code-exist', { code })
            .then(response => {
                const found = response.data.found;
                return found;
            })
            .catch(error => {
                console.error('Error checking for duplicate operator code.', error);
                // Handle error appropriately
                return false;
            });
    }



    suggestSurname(event) {
        const input = event.target.value;
        if (input.length > 0) { // Only start suggesting after at least 3 characters have been entered
            clearTimeout(this.suggestTimeout);
            this.suggestTimeout = setTimeout(async () => {
                const regex = /^[A-Z][A-Z]+$/;
                const isValid = regex.test(input.toUpperCase().trim());

                if (isValid) {
                    const response = await this.fetchNameSuggestions(input, 'surname');
                    this.displaySuggestions(response)
                } else {
                    this.manageNewOperatorSubmitButton();
                }
            }, 1200); // Delay to avoid too frequent calls
        } else {
            this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions if the input is too short
        }
    }


    suggestFirstname(event) {
        const input = event.target.value;
        if (input.length > 0) { // Only start suggesting after at least 3 characters have been entered
            clearTimeout(this.suggestTimeout);
            this.suggestTimeout = setTimeout(async () => {
                const regex = /^[A-Z][a-z]*(-[A-Z][a-z]*)*$/;
                const isValid = regex.test(input.trim());

                if (isValid) {
                    const response = await this.fetchNameSuggestions(input, 'firstname');
                    this.displaySuggestions(response)
                } else {
                    this.manageNewOperatorSubmitButton();
                }

            }, 1200); // Delay to avoid too frequent calls
        } else {
            this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions if the input is too short
        }
    }

    async fetchNameSuggestions(name, inputField) {
        let response;

        if (inputField === 'surname' && this.newOperatorFirstnameTarget.value.trim() !== "") {
            const firstNameResponse = await axios.post(`/docauposte/operator/suggest-names`, { name: this.newOperatorFirstnameTarget.value.trim() });
            this.suggestionsResults = firstNameResponse.data;

        } else if (inputField === 'firstname' && this.newOperatorSurnameTarget.value.trim() !== "") {
            const surNameResponse = await axios.post(`/docauposte/operator/suggest-names`, { name: this.newOperatorSurnameTarget.value.trim() });
            this.suggestionsResults = surNameResponse.data;
        }

        response = await axios.post(`/docauposte/operator/suggest-names`, { name: name });

        return this.checkIfSuggestionsResultsEmpty(response.data);
    }




    async checkIfSuggestionsResultsEmpty(response) {
        if (this.suggestionsResults.length > 0) {
            const checkedResponses = await this.checkForDuplicatesuggestionsResults(response);
            return checkedResponses;
        } else {
            this.suggestionsResults = response;
            return response;
        }
    }



    async checkForDuplicatesuggestionsResults(responses) {

        const duplicateSuggestions = responses.filter(response => {
            return this.suggestionsResults.some(suggestion => suggestion.id === response.id);
        });

        if (duplicateSuggestions.length === 0) {
            this.suggestionsResults = [];
        }
        return duplicateSuggestions;

    }



    displaySuggestions(responses) {
        // Assuming 'responses' is an array of objects each with 'name', 'code', 'team', and 'uap'
        this.nameSuggestionsTarget.innerHTML = responses.map(response => {
            const parts = response.name.split('.'); // Split the 'name' to get firstName and lastName
            const firstName = this.capitalizeFirstLetter(parts[0]); // Capitalize the first name
            const lastName = parts.length > 1 ? parts[1].toUpperCase() : ''; // Handle last name if present
            return `<div class="traininglist-suggestion-item" data-firstname="${firstName}" data-lastname="${lastName}">
            ${lastName} ${firstName}
        </div>`;
        }).join('');

        this.nameSuggestionsTarget.querySelectorAll('.traininglist-suggestion-item').forEach(item => {
            item.addEventListener('click', (event) => {
                const firstname = event.currentTarget.getAttribute('data-firstname');
                const lastname = event.currentTarget.getAttribute('data-lastname');

                this.newOperatorFirstnameTarget.value = firstname;
                this.newOperatorSurnameTarget.value = lastname;

                this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions after selection
                this.validateNewOperatorSurname()
                this.suggestionsResults = [];

            });
        });

        this.nameSuggestionsTarget.style.display = responses.length ? 'block' : 'none';

    }



    async codeGenerator() {
        console.log('OperatorAdminCreationController: Calling codeGenerator');
        const code = await operatorCodeService.generateUniqueCode();
        console.log('OperatorAdminCreationController: Generated code:', code);
        return code;
    }
}
