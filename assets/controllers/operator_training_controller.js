
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
            console.log('validating new operator surname:', this.newOperatorSurnameTarget.value);
            const regex = /^[A-Z][A-Z]+$/;
            const surname = this.newOperatorSurnameTarget.value.toUpperCase();
            const isValid = regex.test(surname.trim());
            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir un nom valide(sans accent, ni caractères spéciaux).");
            if (isValid) {
                // this.newOperatorLastnameTarget.disabled = true;
                if (this.newOperatorFirstnameTarget.value.trim() === "") {
                    this.newOperatorFirstnameTarget.disabled = false;
                    this.newOperatorFirstnameTarget.focus();
                }
                this.validateNewOperatorFirstname();
            }

        }, 1200);
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
            console.log('validating new operator firstname:', this.newOperatorFirstnameTarget.value);
            const regex = /^[A-Z][a-z]+(-[A-Z][a-z]+)*$/;
            const isValid = regex.test(this.firstnameValue.trim());
            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir un prenom valide(sans accent, ni caractères spéciaux).");
            if (isValid) {
                let combinedName = `${this.newOperatorFirstnameTarget.value.trim()}.${this.newOperatorSurnameTarget.value.trim()}`;
                this.newOperatorNameTarget = combinedName.toLowerCase();

                let invertedCombined = `${this.newOperatorSurnameTarget.value.trim()}.${this.newOperatorFirstnameTarget.value.trim()}`;
                this.newOperatorInvertedNameTarget = invertedCombined.toLowerCase();
                // this.newOperatorFirstnameTarget.disabled = true;
                this.validateNewOperatorName();
            }
        }, 1200);
    }

    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }


    validateNewOperatorName() {
        clearTimeout(this.nameTypingTimeout);  // clear any existing timeout to reset the timer

        this.nameTypingTimeout = setTimeout(() => {
            console.log('validating new operator name:', this.newOperatorNameTarget);

            const regex = /^[a-zA-Z]+\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/;
            let isValid;

            if (this.duplicateCheckResults.name) {
                console.log('duplicate check results data value for name:', this.duplicateCheckResults.name.data.value);
                if (this.newOperatorNameTarget.trim() === this.duplicateCheckResults.name.data.value) {
                    console.log('Name is the same as the previous duplicate check, no need to do anything.');
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
        }, 1200); // delay in milliseconds
    }



    validateNewOperatorCode() {
        clearTimeout(this.codeTypingTimeout);
        this.codeTypingTimeout = setTimeout(() => {

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
            this.newOperatorTransferMessageTarget.textContent = "";
            this.updateMessage(this.newOperatorCodeMessageTarget, isValid, "Veuillez saisir un code correct.");

            if (isValid) {
                console.log('Code is valid, clearing duplicate check results and checking for existing entity by code')
                this.duplicateCheckResults.code = null;
                this.checkForExistingEntityByCode();
            }
        }, 1200);
    }



    async checkForExistingEntityByName() {
        try {
            // Initial log indicating the start of a duplicate check
            console.log('Checking for existing entity by name:', this.newOperatorNameTarget);

            // First check for the default name
            let response = await this.checkForDuplicate('/docauposte/operator/check-duplicate-by-name', this.newOperatorNameTarget);
            console.log('Response for existing entity by name:', response.data.found);

            // Only proceed to check the inverted name if no duplicate was found for the first name
            if (!response.data.found) {
                console.log('Checking for existing entity by name:', this.newOperatorInvertedNameTarget);
                response = await this.checkForDuplicate('/docauposte/operator/check-duplicate-by-name', this.newOperatorInvertedNameTarget);
                console.log('Response for existing entity by name inverted:', response.data.found);
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
            console.log('checking for existing entity by code:', this.newOperatorCodeTarget.value);

            const response = await this.checkForDuplicate('/docauposte/operator/check-duplicate-by-code', this.newOperatorCodeTarget.value);
            console.log('response for existing entity by code:', response.data.found);
            this.handleDuplicateResponse(response, this.newOperatorCodeMessageTarget, "codes opérateurs");
        } catch (error) {
            console.error("Error checking for a duplicate operator code.", error);
            this.manageNewOperatorSubmitButton();
            this.newOperatorCodeMessageTarget.textContent = "Erreur lors de la vérification du code opérateur.";
        }
    }



    updateMessage(targetElement, isValid, errorMessage) {
        console.log(`Updating message: isValid: ${isValid}`);
        if (isValid) {
            targetElement.textContent = "";
        } else {
            targetElement.textContent = errorMessage;
            targetElement.style.fontWeight = "bold";
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

        messageTarget.style.fontWeight = "bold";
        messageTarget.style.color = response.data.found ? "red" : "green";

        if (response.data.found) {
            console.log('what\'s in the duplicate check results variable:', this.duplicateCheckResults);
            if (response.data.field === "name") {
                this.duplicateCheckResults.name = response;
                console.log('Duplicate check results for name:', this.duplicateCheckResults.name);
            } else if (response.data.field === "code") {
                this.duplicateCheckResults.code = response;
                console.log('Duplicate check results for code:', this.duplicateCheckResults.code);
            }
            this.checkForCorrespondingEntity();
            this.newOperatorCodeTarget.disabled = false;
            this.newOperatorCodeTarget.focus();
        } else {
            console.log('No duplicate found, generating a code and allowing to write it.' + response.data.field);
            if (response.data.field === "name") {
                console.log('No duplicate found, generating a code and allowing to write it.');
                this.codeGenerator();
            }
            this.newOperatorCodeTarget.disabled = false;
            this.newOperatorCodeTarget.focus();
            this.manageNewOperatorSubmitButton(true, "Ajouter");
        }
    }



    manageNewOperatorSubmitButton(enableButton = false, submitValue = "Ajouter") {
        console.log(`Setting new operator submit button - Enabled: ${enableButton}, Value: ${submitValue}`);
        console.log('suggestionsResults length:', this.suggestionsResults.length)
        if (this.suggestionsResults.length === 0) {
            this.newOperatorSubmitButtonTarget.disabled = !enableButton;
        }
        document.getElementById('newOperatorName').value = this.newOperatorNameTarget;
        this.newOperatorSubmitButtonTarget.value = submitValue;
        clearTimeout(this.validatedTimeout);
        this.validatedTimeout = setTimeout(() => {
            console.log('Resetting new operator form after 10 seconds')
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
        }, 10000);

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

        this.newOperatorTransferMessageTarget.textContent = message;
        this.newOperatorTransferMessageTarget.style.color = entitiesMatch ? "green" : "red";
        this.resetUselessMessages();

        console.log(`Manage submit button to be ${entitiesMatch ? "enabled" : "disabled"} with value ${submitValue}`);
        this.manageNewOperatorSubmitButton(entitiesMatch, submitValue);
    }



    executeEntityNonMatchingLogic(unMatchedFound) {
        this.newOperatorTransferMessageTarget.textContent = "Nom et Code opérateurs ne correspondent à aucun opérateur. Vous pouvez les ajouter.";
        this.newOperatorTransferMessageTarget.style.color = "green";
        this.manageNewOperatorSubmitButton(unMatchedFound, "Ajouter");
    }



    resetUselessMessages() {
        console.log('Resetting useless messages if there is a transfer message.');
        if (this.newOperatorTransferMessageTarget.textContent !== "") {
            console.log('Clearing name and code validation messages');
            this.newOperatorNameMessageTarget.textContent = "";
            this.newOperatorCodeMessageTarget.textContent = "";
        }
        const operatorInputs = document.querySelectorAll('.operator-input');
        operatorInputs.forEach(function (input) {
            input.disabled = false;
        });
    }



    validateCodeEntryForTraining() {
        clearTimeout(this.trainingCodeTypingTimeout);
        this.trainingCodeTypingTimeout = setTimeout(() => {
            console.log('validating training operator code:', this.trainingOperatorCodeTarget.value);
            const regex = /^[0-9]{5}$/;
            const isValid = regex.test(this.trainingOperatorCodeTarget.value.trim());

            if (isValid) {
                this.checkOperatorIdentityByCode();
            } else {
                this.trainingOperatorCodeTarget.value = "";
                this.trainingOperatorCodeTarget.placeholder = "Invalide";
            }
        }, 1200);
    }



    async checkOperatorIdentityByCode() {
        const code = this.trainingOperatorCodeTarget.value;
        const operatorId = this.trainingOperatorCodeTarget.dataset.operatorId;
        const teamId = this.trainingOperatorCodeTarget.dataset.teamId;
        const uapId = this.trainingOperatorCodeTarget.dataset.uapId;

        try {
            console.log('Checking operator identity by code:', this.trainingOperatorCodeTarget.value);
            const response = await this.checkCodeAgainstOperatorCode('/docauposte/operator/check-entered-code-against-operator-code', code, operatorId, teamId, uapId);
            if (response.data.found) {
                console.log('response for operator identity by code:', response.data);
                this.inputSwitch(response.data);
            } else {
                console.log('No operator found with the entered code.');
                this.trainingOperatorCodeTarget.value = "";
                this.trainingOperatorCodeTarget.placeholder = "Invalide";
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



    codeGenerator() {
        console.log('generating a code');
        const code = Math.floor(10000 + Math.random() * 90000);
        const response = this.generatedCodeChecker(code);
        console.log('response for generated code:', response.data);
        if (response.data) {
            console.log('Code already exists, generating another code');
            this.codeGenerator();
        } else {
            this.newOperatorCodeTarget.value = code;
            this.newOperatorCodeTarget.disabled = true;
            this.newOperatorCodeTarget.focus();
            this.validateNewOperatorCode();
        }
    }



    generatedCodeChecker(code) {
        console.log('checking generated code');
        return axios.post(`/docauposte/operator/check-if-code-exist`, { code: code });
    }



    newOperatorHandleSubmit() {
        console.log('submitting new operator form');

    }

    suggestSurname(event) {
        const input = event.target.value;
        console.log('suggesting Surname:', input);
        if (input.length > 0) { // Only start suggesting after at least 3 characters have been entered
            clearTimeout(this.suggestTimeout);
            this.suggestTimeout = setTimeout(async () => {
                const regex = /^[A-Z][A-Z]+$/;
                const isValid = regex.test(input.toUpperCase().trim());
                console.log('is input valid for fetching suggestions for Surname:', isValid);

                if (isValid) {
                    console.log('fetching suggestions for Surname:', input);
                    const response = await this.fetchNameSuggestions(input, 'surname');
                    console.log('suggestions response:', response);
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
        console.log('suggesting firstname:', input);
        if (input.length > 0) { // Only start suggesting after at least 3 characters have been entered
            clearTimeout(this.suggestTimeout);
            this.suggestTimeout = setTimeout(async () => {
                const regex = /^[A-Z][a-z]*(-[A-Z][a-z]*)*$/;
                const isValid = regex.test(input.trim());
                console.log('is input valid for fetching suggestions for firstname:', isValid);

                if (isValid) {
                    console.log('fetching suggestions for firstname:', input);
                    const response = await this.fetchNameSuggestions(input, 'firstname');
                    console.log('suggestions response:', response);
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
        console.log('fetching name suggestions:', name, inputField);
        let response;

        if (inputField === 'surname' && this.newOperatorFirstnameTarget.value.trim() !== "") {
            console.log('first name is not empty');
            const firstNameResponse = await axios.post(`/docauposte/operator/suggest-names`, { name: this.newOperatorFirstnameTarget.value.trim() });
            this.suggestionsResults = firstNameResponse.data;

        } else if (inputField === 'firstname' && this.newOperatorSurnameTarget.value.trim() !== "") {
            console.log('last name is not empty');
            const surNameResponse = await axios.post(`/docauposte/operator/suggest-names`, { name: this.newOperatorSurnameTarget.value.trim() });
            this.suggestionsResults = surNameResponse.data;
        }

        response = await axios.post(`/docauposte/operator/suggest-names`, { name: name });

        console.log('response for name suggestions:', response.data);
        return this.checkIfSuggestionsResultsEmpty(response.data);
    }




    async checkIfSuggestionsResultsEmpty(response) {
        console.log('checking if suggestions results are empty:', this.suggestionsResults);
        if (this.suggestionsResults.length > 0) {
            console.log('this.suggestionsResults return TRUE in fetchNameSuggestions')
            const checkedResponses = await this.checkForDuplicatesuggestionsResults(response);
            return checkedResponses;
        } else {
            console.log('this.suggestionsResults return FALSE in fetchNameSuggestions')
            this.suggestionsResults = response;
            return response;
        }
    }



    async checkForDuplicatesuggestionsResults(responses) {
        console.log('checking for duplicate suggestions results responses:', responses);
        console.log('checking for duplicate suggestions results suggestionsResults:', this.suggestionsResults);

        const duplicateSuggestions = responses.filter(response => {
            return this.suggestionsResults.some(suggestion => suggestion.id === response.id);
        });

        console.log('filtered suggestions:', duplicateSuggestions);
        if (duplicateSuggestions.length === 0) {
            this.suggestionsResults = [];
        }
        return duplicateSuggestions;

    }




    displaySuggestions(responses) {
        console.log('displaying suggestions:', responses);
        // Assuming 'responses' is an array of objects each with 'name', 'code', 'team', and 'uap'
        this.nameSuggestionsTarget.innerHTML = responses.map(response => {
            const parts = response.name.split('.'); // Split the 'name' to get firstName and lastName
            const firstName = this.capitalizeFirstLetter(parts[0]); // Capitalize the first name
            const lastName = parts.length > 1 ? parts[1].toUpperCase() : ''; // Handle last name if present
            const teamName = response.team_name; // Get the team name
            const teamId = response.team_id; // Get the team id
            const uapName = response.uap_name; // Get the uap name
            const uapId = response.uap_id; // Get the uap id
            const code = response.code; // Get the code
            const isTrainerBool = response.is_trainer; // Get the isTrainer value
            return `<div class="traininglist-suggestion-item" data-firstname="${firstName}" data-lastname="${lastName}">
            ${lastName} ${firstName}
        </div>`;
        }).join('');

        this.nameSuggestionsTarget.querySelectorAll('.traininglist-suggestion-item').forEach(item => {
            item.addEventListener('click', (event) => {
                const firstname = event.currentTarget.getAttribute('data-firstname');
                const lastname = event.currentTarget.getAttribute('data-lastname');
                const code = event.currentTarget.getAttribute('data-code');
                const team = event.currentTarget.getAttribute('data-team');
                const uap = event.currentTarget.getAttribute('data-uap');
                const isTrainer = event.currentTarget.getAttribute('data-istrainer');

                console.log('selected suggestion firstname, lastname, code, team, uap:', firstname, lastname, code, team, uap, isTrainer);

                this.newOperatorFirstnameTarget.value = firstname;
                this.newOperatorSurnameTarget.value = lastname;

                this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions after selection
                this.validateNewOperatorSurname()
                this.suggestionsResults = [];

            });
        });

        this.nameSuggestionsTarget.style.display = responses.length ? 'block' : 'none';

    }
}

