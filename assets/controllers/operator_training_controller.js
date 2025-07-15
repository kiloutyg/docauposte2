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

    /**
     * Validates the new operator's surname.
     * It checks if the entered surname is valid based on a regex pattern,
     * updates the message accordingly, and enables the firstname field if the surname is valid.
     */
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

    /**
     * Validates the new operator's firstname.
     * It checks if the entered firstname is valid based on a regex pattern,
     * updates the message accordingly, and proceeds to validate the full name if the firstname is valid.
     */
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



    /**
     * Capitalizes the first letter of a string and converts the rest to lowercase.
     * @param {string} string - The input string to be capitalized.
     * @returns {string} The capitalized string.
     */
    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }



    /**
     * Validates the new operator's combined name (firstname.surname).
     * It checks if the entered name is valid based on a regex pattern,
     * updates the message accordingly, and checks for existing entities by name if the name is valid.
     */
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



    /**
     * Validates the new operator's code.
     * It checks if the entered code is valid using the operatorCodeService,
     * updates the message accordingly, and checks for existing entities by code if the code is valid.
     */
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



    

    /**
     * Checks for an existing entity by name, first using the standard name format and then the inverted format if necessary.
     * It calls an API endpoint to check for duplicates and handles the response to update the UI accordingly.
     */
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



    /**
     * Checks for an existing entity by code.
     * It calls an API endpoint to check for duplicates and handles the response to update the UI accordingly.
     * @async
     * @returns {Promise<void>} - A promise that resolves when the check is complete and the UI is updated.
     */
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



    /**
     * Updates the text content of a target element based on the validity of a condition.
     * If the condition is valid, the text content is cleared. Otherwise, an error message is displayed.
     * @param {HTMLElement} targetElement - The HTML element whose text content will be updated.
     * @param {boolean} isValid - A boolean indicating whether the condition is valid.
     * @param {string} errorMessage - The error message to display if the condition is not valid.
     * @returns {void}
     */
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

    /**
     * Handles the response from a duplicate check, updating the UI based on whether a duplicate was found.
     * @async
     * @param {Object} response - The response object from the duplicate check API call.
     * @param {HTMLElement} messageTarget - The HTML element where the response message will be displayed.
     * @param {string} fieldName - The name of the field being checked for duplicates (e.g., "noms d'opérateurs", "codes opérateurs").
     * @returns {Promise<void>} - A promise that resolves after the UI has been updated based on the response.
     */
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



    /**
     * Manages the state of the new operator submit button and resets the input fields.
     * This function enables or disables the submit button based on the `enableButton` parameter,
     * sets the submit button's value, and clears/resets various input fields and messages
     * after a delay of 15 seconds.
     * @param {boolean} [enableButton=false] - Determines whether to enable the submit button.
     *                                          If true, the button is enabled; otherwise, it is disabled.
     * @param {string} [submitValue="Ajouter"] - The text to display on the submit button.
     * @returns {void}
     */
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



    /**
     * Sends a POST request to check for a duplicate value.
     * @param {string} url - The URL endpoint to send the request to.
     * @param {string} value - The value to check for duplication.
     * @returns {Promise} A Promise that resolves with the response from the server.
     */
    checkForDuplicate(url, value) {
        return axios.post(url, { value: value });
    }



    /**
     * Checks if there are corresponding entities based on the duplicate check results for both name and code.
     * If both name and code have been found as duplicates, it executes the entity matching logic.
     * If neither name nor code has been found as duplicates, it executes the entity non-matching logic.
     * Otherwise, it disables the new operator submit button.
     *
     * @returns {void}
     */
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



    /**
     * Executes logic when both name and code duplicates are found, determining if the entities match and updating the UI accordingly.
     *
     * @param {boolean} matchesFound - Indicates whether both name and code duplicates were found.
     *                                 If true, it proceeds to check if the operator IDs from the name and code match.
     *                                 If false, it indicates that either name or code or both were not found as duplicates.
     * @returns {void}
     */
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



    /**
     * Executes logic when both name and code duplicates are not found, indicating that the entered name and code do not correspond to any existing operator.
     * It updates the UI to reflect this non-matching status and allows the user to add the new operator.
     *
     * @param {boolean} unMatchedFound - A boolean value that should be true when both the name and code do not match any existing operator.
     *                                   This parameter is used to enable the submit button, allowing the user to add the new operator.
     * @returns {void} This function does not return any value. It updates the UI to inform the user that the entered information does not match any existing operator and enables the submit button.
     */
    executeEntityNonMatchingLogic(unMatchedFound) {
        this.newOperatorTransferMessageTarget.textContent = "Nom et Code opérateurs ne correspondent à aucun opérateur. Vous pouvez les ajouter.";
        this.newOperatorTransferMessageTarget.style.color = "green";
        this.manageNewOperatorSubmitButton(unMatchedFound, "Ajouter");
    }



    /**
     * Resets the text content of specific message targets and enables all operator input fields.
     * This function clears the content of the name and code message targets if the transfer message target has content.
     * It also enables all input fields with the class 'operator-input'.
     *
     * @returns {void} This function does not return any value. It modifies the DOM by clearing text content and enabling input fields.
     */
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



    /**
     * Validates the entered training code after a delay, and checks the operator identity if the code is valid.
     * It uses the operatorCodeService to validate the code and updates the UI accordingly.
     * @async
     * @function validateCodeEntryForTraining
     * @returns {Promise<void>} - A promise that resolves when the validation and identity check are complete.
     */
    async validateCodeEntryForTraining() {
        clearTimeout(this.trainingCodeTypingTimeout);
        this.trainingCodeTypingTimeout = setTimeout(async () => {
            const isValid = await operatorCodeService.validateAndCheckCode(this.trainingOperatorCodeTarget.value);
            if (isValid) {
                this.checkOperatorIdentityByCode();
            } else {
                this.trainingOperatorCodeTarget.value = "";
                this.trainingOperatorCodeTarget.placeholder = "Invalide";
            }
        }, 1500);
    }



    /**
     * Checks the operator's identity by verifying the entered code against the operator's code stored in the database.
     * If the code matches, it calls the inputSwitch method to update the UI. If not, it clears the input field and sets the placeholder text to "Erroné".
     * @async
     * @function checkOperatorIdentityByCode
     * @returns {Promise<void>} - A promise that resolves when the identity check and UI update are complete.
     */
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



    /**
     * Sends a POST request to check the entered code against the operator's code, team, and UAP.
     * @param {string} url - The URL endpoint to send the request to. This should include the base path.
     * @param {string} code - The code entered by the operator to be checked.
     * @param {string} operatorId - The ID of the operator.
     * @param {string} teamId - The ID of the team the operator belongs to.
     * @param {string} uapId - The ID of the UAP (Unité Administrative Postale) the operator belongs to.
     * @returns {Promise} A Promise that resolves with the response from the server after checking the code against the operator's details.
     */
    checkCodeAgainstOperatorCode(url, code, operatorId, teamId, uapId) {
        return axios.post(`${url}/${teamId}/${uapId}`, { code: code, operatorId: operatorId, teamId: teamId, uapId: uapId });
    }



    /**
     * Updates the UI by replacing the training code input with a checkbox and label,
     * allowing the user to mark an operator as trained.
     * @param {Object} response - The response object containing data about the operator and training status.
     * @param {boolean} response.found - Indicates whether the operator was found and the code is valid.
     * @param {Object} response.operator - The operator object containing operator details.
     * @param {string} response.operator.id - The ID of the operator.
     * @returns {void}
     */
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



    /**
     * Proposes a compliant new code for the operator, sets it in the input field, and validates it.
     * It calls the codeGenerator to get a new code, sets the code in the newOperatorCodeTarget,
     * disables the input field, sets focus to it, and then validates the new code.
     *
     * @async
     * @returns {Promise<void>} - A promise that resolves after the code is generated, set, and validated.
     */
    async proposeCompliantNewCode() {
        const code = this.codeGenerator();
        this.newOperatorCodeTarget.value = code;
        this.newOperatorCodeTarget.disabled = true;
        this.newOperatorCodeTarget.focus();
        this.validateNewOperatorCode();

    }



    /**
     * Checks if a generated training code already exists in the database.
     * @async
     * @param {string} code - The training code to check for existence.
     * @returns {Promise<boolean>} - A promise that resolves with a boolean indicating whether the code was found (true) or not (false).
     */
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



    /**
     * Suggests surnames based on the input value, triggering a delayed API call to fetch suggestions.
     * It validates the input against a regex pattern before fetching suggestions.
     * @param {Event} event - The input event that triggered the suggestion.
     *                      The event target's value is used as the basis for the suggestions.
     * @returns {void}
     */
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


    /**
     * Suggests firstnames based on the input value, triggering a delayed API call to fetch suggestions.
     * It validates the input against a regex pattern before fetching suggestions.
     * @param {Event} event - The input event that triggered the suggestion.
     *                      The event target's value is used as the basis for the suggestions.
     * @returns {void}
     */
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

    /**
     * Fetches name suggestions from the server based on the provided name and input field type.
     * It also checks if there are existing suggestions for the first name or surname and stores them.
     * @async
     * @param {string} name - The name to fetch suggestions for.
     * @param {string} inputField - The type of input field ('surname' or 'firstname') to determine the context of the name.
     * @returns {Promise<Array>} - A promise that resolves with an array of name suggestions.
     */
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




    /**
     * Checks if the suggestionsResults array is empty. If it's not empty, it filters the provided response
     * to remove any suggestions that are already present in the suggestionsResults array. If it is empty,
     * it assigns the response to the suggestionsResults array.
     * @async
     * @param {Array} response - An array of suggestion objects to check against existing results.
     * @returns {Promise<Array>} - Returns a promise that resolves with an array of suggestion objects.
     *   If suggestionsResults is not empty, it returns the filtered response containing only unique suggestions.
     *   If suggestionsResults is empty, it returns the original response.
     */
    async checkIfSuggestionsResultsEmpty(response) {
        if (this.suggestionsResults.length > 0) {
            const checkedResponses = await this.checkForDuplicatesuggestionsResults(response);
            return checkedResponses;
        } else {
            this.suggestionsResults = response;
            return response;
        }
    }



    /**
     * Checks for duplicate suggestions in the provided responses against existing suggestionsResults.
     * It filters the responses to identify suggestions that already exist in the suggestionsResults array.
     * If no duplicates are found, it clears the suggestionsResults array.
     * @async
     * @param {Array} responses - An array of suggestion objects to check for duplicates. Each object should have a unique 'id' property.
     * @returns {Promise<Array>} - Returns a promise that resolves with an array containing only the duplicate suggestion objects found in the responses.
     *   If no duplicates are found, the function may modify the suggestionsResults array by clearing it.
     */
    async checkForDuplicatesuggestionsResults(responses) {

        const duplicateSuggestions = responses.filter(response => {
            return this.suggestionsResults.some(suggestion => suggestion.id === response.id);
        });

        if (duplicateSuggestions.length === 0) {
            this.suggestionsResults = [];
        }
        return duplicateSuggestions;

    }



    /**
     * Displays name suggestions in the UI based on the provided responses.
     * Each suggestion is a clickable item that updates the firstname and lastname input fields.
     * The suggestions are cleared after a selection is made.
     *
     * @param {Array} responses - An array of objects representing name suggestions.
     *                           Each object should have 'name', 'code', 'team', and 'uap' properties.
     *
     * @returns {void} This function does not return any value. It modifies the DOM to display name suggestions.
     */
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



    /**
     * Generates a unique training code for a new operator.
     * It calls the generateUniqueCode method from the operatorCodeService to generate a unique code.
     * The generated code is then logged to the console for debugging purposes.
     *
     * @async
     * @function codeGenerator
     * @returns {Promise<string>} - A promise that resolves with the generated unique training code.
     */
    async codeGenerator() {
        console.log('OperatorAdminCreationController: Calling codeGenerator');
        const code = await operatorCodeService.generateUniqueCode();
        console.log('OperatorAdminCreationController: Generated code:', code);
        return code;
    }
}
