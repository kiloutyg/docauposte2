import { Controller } from '@hotwired/stimulus';
import { operatorCodeService } from './services/operator_code_service';
import axios from 'axios';

export default class OperatorAdminCreationController extends Controller {

    static targets = [
        "newOperatorLastname",
        "newOperatorFirstname",
        "newOperatorCode",
        "newOperatorTeam",
        "newOperatorUaps",
        "newOperatorIsTrainer",
        "newOperatorNameMessage",
        "newOperatorCodeMessage",
        "newOperatorTransferMessage",
        "newOperatorSubmitButton",
        "nameSuggestions",
    ];


    suggestionsResults = [];


    /**
     * Validates the new operator's lastname input field with debounced validation.
     * Checks if the lastname contains only uppercase letters (minimum 2 characters) without accents or special characters.
     * If valid, enables the firstname field and triggers firstname validation.
     * 
     * @returns {void} This function does not return a value.
     */
    validateNewOperatorLastname() {
        clearTimeout(this.lastnameTypingTimeout);
        this.lastnameTypingTimeout = setTimeout(() => {
            const regex = /^[A-Z][A-Z]+$/;
            const lastname = this.newOperatorLastnameTarget.value.toUpperCase();
            const isValid = regex.test(lastname.trim());
            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir un nom valide(sans accent, ni caractères spéciaux).");
            if (isValid) {
                if (this.newOperatorFirstnameTarget.value.trim() === "") {
                    this.newOperatorFirstnameTarget.disabled = false;
                    this.newOperatorFirstnameTarget.focus();
                }
                this.validateNewOperatorFirstname();
            }
        }, 1000);
    }



    /**
     * Validates the new operator's firstname input field with debounced validation.
     * Checks if the firstname follows the proper format: starts with uppercase letter followed by lowercase letters,
     * with optional hyphenated parts (e.g., "Jean-Pierre"). If valid, creates combined name formats and triggers
     * name validation.
     * 
     * @returns {void} This function does not return a value.
     */
    validateNewOperatorFirstname() {
        clearTimeout(this.firstnameTypingTimeout);
        this.firstnameTypingTimeout = setTimeout(() => {
            const firstnameValue = this.newOperatorFirstnameTarget.value;
            this.firstnameValue = this.capitalizeFirstLetter(firstnameValue);
            const regex = /^[A-Z][a-z]+(-[A-Z][a-z]+)*$/;
            const isValid = regex.test(this.firstnameValue.trim());
            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir un prenom valide(sans accent, ni caractères spéciaux).");
            if (isValid) {
                let combinedName = `${this.newOperatorFirstnameTarget.value.trim()}.${this.newOperatorLastnameTarget.value.trim()}`;
                this.newOperatorNameTarget = combinedName.toLowerCase();
                let invertedCombined = `${this.newOperatorLastnameTarget.value.trim()}.${this.newOperatorFirstnameTarget.value.trim()}`;
                this.newOperatorInvertedNameTarget = invertedCombined.toLowerCase();
                this.validateNewOperatorName();
            }
        }, 1000);
    }



    /**
     * Capitalizes the first letter of a string and converts the rest to lowercase.
     * This utility function is commonly used for formatting names and text inputs
     * to ensure proper capitalization format.
     * 
     * @param {string} string - The input string to be capitalized
     * @returns {string} The formatted string with the first character in uppercase and remaining characters in lowercase
     */
    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }



    /**
     * Event handler that capitalizes the first letter of an input field value while converting the rest to lowercase.
     * This method is typically used as an event listener for input fields to ensure proper text formatting.
     * Only applies the capitalization if the cursor is positioned at or before the second character.
     * 
     * @param {Event} event - The DOM event object containing information about the triggered event
     * @returns {void} This function does not return a value.
     */
    capitalizeFirstLetterMethod(event) {
        const input = event.target;
        if (input.selectionStart <= 1) {
            input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1).toLowerCase();
        }
    }



    /**
     * Validates the combined operator name format with debounced validation.
     * Checks if the combined name follows the pattern "firstname.lastname" where firstname and lastname
     * contain only letters, and hyphens are allowed in lastname but not at the beginning or end.
     * Handles duplicate checking by comparing against previously validated results to avoid redundant validation.
     * If valid, enables code field and triggers duplicate checking and code validation.
     * 
     * @returns {void} This function does not return a value.
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
            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir sous la forme prenom.nom.");
            this.newOperatorCodeTarget.disabled = true;

            if (isValid) {
                this.checkForExistingEntityByName();
                if (this.newOperatorCodeTarget.value !== "") {
                    this.validateNewOperatorCode();
                }
            }
        }, 1000); // delay in milliseconds
    }




    /**
     * Validates the new operator's code input field with debounced validation.
     * Performs asynchronous validation using the operator code service to check if the entered code
     * follows the correct format and business rules. Implements duplicate checking optimization by
     * comparing against previously validated results to avoid redundant validation calls.
     * If the code is valid, triggers duplicate checking against existing operator codes in the system.
     * Updates the UI message target with validation results and manages error states appropriately.
     * 
     * @returns {void} This function does not return a value.
     */
    validateNewOperatorCode() {
        clearTimeout(this.codeTypingTimeout);
        this.codeTypingTimeout = setTimeout(async () => {
            console.log('OperatorAdminCreationController: Validating operator code:', this.newOperatorCodeTarget.value);
            let isValidPromise;
            
            if (this.duplicateCheckResults.code) {
                if (this.newOperatorCodeTarget.value.trim() === this.duplicateCheckResults.code.data.value) {
                    console.log('OperatorAdminCreationController: Code already validated');
                    return;
                } else {
                    console.log('OperatorAdminCreationController: Resetting duplicate check results for code');
                    this.duplicateCheckResults.code = null;
                    isValidPromise = operatorCodeService.validateCode(this.newOperatorCodeTarget.value.trim());
                    console.log('OperatorAdminCreationController: Code validation promise created if no duplicate');
                }
            } else {
                isValidPromise = operatorCodeService.validateCode(this.newOperatorCodeTarget.value.trim());
                console.log('OperatorAdminCreationController: Code validation promise created if duplicate');
            }
            
            try {
                const isValid = await isValidPromise;
                console.log('OperatorAdminCreationController: Code validation general result:', isValid);
                this.updateMessage(this.newOperatorCodeMessageTarget, isValid, "Veuillez saisir un code correct.");
                
                if (isValid) {
                    console.log('OperatorAdminCreationController: Code is valid, checking for duplicates');
                    this.duplicateCheckResults.code = null;
                    this.checkForExistingEntityByCode();
                }
            } catch (error) {
                console.error('OperatorAdminCreationController: Error validating code:', error);
                this.updateMessage(this.newOperatorCodeMessageTarget, false, "Erreur lors de la validation du code.");
            }
        }, 1000);
    }




    /**
     * Asynchronously checks for existing operators with duplicate names in the system.
     * Performs a two-step validation process: first checks the default name format (firstname.lastname),
     * and if no duplicate is found, checks the inverted name format (lastname.firstname).
     * Updates the UI with appropriate messages and manages the submit button state based on the results.
     * 
     * @returns {Promise<void>} A promise that resolves when the duplicate checking process is complete.
     *                          Does not return a value but updates UI elements and internal state.
     */
    async checkForExistingEntityByName() {
        try {
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
     * Asynchronously checks for existing operators with duplicate codes in the system.
     * Makes an API call to verify if the current operator code already exists in the database.
     * Updates the UI with appropriate messages and manages the submit button state based on the results.
     * Handles errors gracefully by displaying error messages and disabling the submit button.
     * 
     * @returns {Promise<void>} A promise that resolves when the duplicate checking process is complete.
     *                          Does not return a value but updates UI elements and internal state.
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
     * Updates the display message for a target element based on validation results.
     * If validation is successful, clears the message content. If validation fails,
     * displays the error message with bold red styling and disables the submit button.
     * 
     * @param {HTMLElement} targetElement - The DOM element where the message will be displayed
     * @param {boolean} isValid - Flag indicating whether the validation passed (true) or failed (false)
     * @param {string} errorMessage - The error message to display when validation fails
     * @returns {void} This function does not return a value.
     */
    updateMessage(targetElement, isValid, errorMessage) {
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




    /**
     * Handles the response from duplicate checking operations and updates the UI accordingly.
     * Processes duplicate check results for operator names or codes, displays appropriate messages,
     * and manages the form state based on whether duplicates were found. If no duplicates are found,
     * enables the code field and may trigger automatic code generation. If duplicates are found,
     * stores the results and checks for corresponding entities.
     * 
     * @param {Object} response - The response object from the duplicate check API call
     * @param {Object} response.data - The data portion of the response
     * @param {boolean} response.data.found - Whether a duplicate was found
     * @param {string} response.data.message - The message to display when duplicate is found
     * @param {string} response.data.field - The field type being checked ("name" or "code")
     * @param {Object} [response.data.operator] - The operator object if duplicate is found
     * @param {HTMLElement} messageTarget - The DOM element where the result message will be displayed
     * @param {string} fieldName - The human-readable name of the field being checked (e.g., "noms d'opérateurs", "codes opérateurs")
     * @returns {Promise<void>} A promise that resolves when all UI updates and potential code generation are complete
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
        } else {
            const settings = await operatorCodeService.getSettings();
            if (response.data.field === "name" && settings.methodEnabled) {
                this.codeGeneratorInitiator();
            }
            this.newOperatorCodeTarget.disabled = false;
            this.newOperatorCodeTarget.focus();
            this.manageNewOperatorSubmitButton(true);
        }
    }




    /**
     * Manages the state of the new operator submit button and handles form reset functionality.
     * Controls the submit button's enabled/disabled state, updates the hidden name field with the current
     * operator name target value, and sets up a delayed form reset that clears all form fields and
     * resets the component state after a specified timeout period.
     * 
     * @param {boolean} [enableButton=false] - Flag indicating whether to enable (true) or disable (false) the submit button
     * @returns {void} This function does not return a value.
     */
    manageNewOperatorSubmitButton(enableButton = false) {
        this.newOperatorSubmitButtonTarget.disabled = !enableButton;
        document.getElementById('newOperatorName').value = this.newOperatorNameTarget;
        clearTimeout(this.validatedTimeout);
        this.validatedTimeout = setTimeout(() => {
            this.newOperatorCodeTarget.value = "";
            this.newOperatorLastnameTarget.value = "";
            this.newOperatorFirstnameTarget.value = "";
            this.newOperatorNameTarget = "";
            this.duplicateCheckResults = { name: null, code: null };
            this.newOperatorLastnameTarget.disabled = false;
            this.newOperatorCodeTarget.disabled = true;
            this.newOperatorLastnameTarget.focus();
            this.newOperatorSubmitButtonTarget.disabled = true;
            this.newOperatorCodeMessageTarget.textContent = "";
            this.newOperatorNameMessageTarget.textContent = "";
            this.newOperatorTransferMessageTarget.textContent = "";
            this.newOperatorTeamTarget.value = "";
            this.newOperatorUapsTarget.value = "";
            this.newOperatorIsTrainerTarget.checked = null;
            this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions 
            this.suggestionsResults = [];
        }, 110000);

    }



    /**
     * Makes an HTTP POST request to check for duplicate values in the system.
     * This utility function sends a value to a specified endpoint to verify if it already exists
     * in the database, typically used for validating operator names or codes for uniqueness.
     * 
     * @param {string} url - The API endpoint URL to send the duplicate check request to
     * @param {string} value - The value to check for duplicates (e.g., operator name or code)
     * @returns {Promise<Object>} A promise that resolves to an axios response object containing
     *                           the duplicate check results from the server
     */
    checkForDuplicate(url, value) {
        return axios.post(url, { value: value });
    }



    /**
     * Checks for corresponding entities between name and code duplicate validation results.
     * This function evaluates the duplicate check results for both operator name and code fields
     * to determine if they correspond to the same entity or different entities. Based on the
     * evaluation, it triggers appropriate logic for handling matching or non-matching scenarios.
     * If both name and code checks have been completed, it analyzes whether both found duplicates,
     * both found no duplicates, or have mixed results, then delegates to specific handling methods.
     * 
     * @returns {void} This function does not return a value but triggers other methods based on the duplicate check results.
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
     * Executes entity matching logic to determine if duplicate name and code belong to the same operator.
     * Compares the operator IDs from both name and code duplicate check results to verify if they
     * reference the same entity. If the entities match (same operator found for both name and code),
     * the submit button is disabled to prevent duplicate creation. If they don't match (different
     * operators found), the submit button is enabled allowing the creation to proceed.
     * 
     * @param {boolean} matchesFound - Flag indicating whether duplicates were found for both name and code fields
     * @returns {void} This function does not return a value but manages the submit button state based on entity matching results.
     */
    executeEntityMatchingLogic(matchesFound) {
        const nameOperatorId = this.duplicateCheckResults.name?.data?.operator?.id;
        const codeOperatorId = this.duplicateCheckResults.code?.data?.operator?.id;
        const entitiesMatch = matchesFound && nameOperatorId === codeOperatorId;
        this.manageNewOperatorSubmitButton(!entitiesMatch);
    }




    /**
     * Executes entity non-matching logic when duplicate check results indicate no duplicates were found.
     * This function handles the scenario where both name and code duplicate checks return negative results,
     * indicating that neither the operator name nor code already exist in the system. In this case,
     * the function enables the submit button to allow the new operator creation to proceed.
     * 
     * @param {boolean} unMatchedFound - Flag indicating whether no duplicates were found for both name and code fields.
     *                                   When true, it means both checks returned no duplicates, allowing operator creation.
     * @returns {void} This function does not return a value but manages the submit button state based on the non-matching results.
     */
    executeEntityNonMatchingLogic(unMatchedFound) {
        this.manageNewOperatorSubmitButton(unMatchedFound);
    }



    /**
     * Generates a unique operator code using the operator code service.
     * This asynchronous function serves as a wrapper around the operator code service's
     * generateUniqueCode method, providing logging for debugging purposes and returning
     * the generated code for use in operator creation workflows.
     * 
     * @returns {Promise<string>} A promise that resolves to a unique operator code string
     *                           generated by the operator code service
     */
    async codeGenerator() {
        console.log('OperatorAdminCreationController: Calling codeGenerator');
        const code = await operatorCodeService.generateUniqueCode();
        console.log('OperatorAdminCreationController: Generated code:', code);
        return code;
    }






    /**
     * Initiates the automatic operator code generation process with uniqueness validation.
     * This asynchronous function generates a unique operator code and recursively ensures its uniqueness
     * by checking against existing codes in the system. If a duplicate is found, it recursively calls
     * itself to generate a new code until a unique one is found. Once a unique code is generated,
     * it populates the code input field, disables it, and triggers validation.
     * 
     * @returns {Promise<void>} A promise that resolves when a unique code has been generated,
     *                          validated, and set in the input field. Does not return a value
     *                          but updates UI elements and triggers validation processes.
     */
    async codeGeneratorInitiator() {
        console.log('OperatorAdminCreationController: Initiating code generation');
        const newCode = await this.codeGenerator();
        console.log('OperatorAdminCreationController: Generated code:', newCode);
        // Check if the generated code already exists
        try {
            const response = await this.generatedCodeChecker(newCode);
            console.log('OperatorAdminCreationController: Code existence check response:', response);

            if (response.data.found) {
                console.log('OperatorAdminCreationController: Code already exists, generating a new one');
                await this.codeGeneratorInitiator(); // Recursively generate a new code
            } else {
                console.log('OperatorAdminCreationController: Code is unique, setting it to the input field');
                // Set the new code to the input field and proceed
                this.newOperatorCodeTarget.value = newCode;
                this.newOperatorCodeTarget.disabled = true;
                this.newOperatorCodeTarget.focus();
                this.validateNewOperatorCode();
            }
        } catch (error) {
            console.error('OperatorAdminCreationController: Error checking for duplicate operator code:', error);
            // Handle error accordingly
        }
    }






    /**
     * Checks if a generated operator code already exists in the system.
     * This asynchronous function verifies the uniqueness of a generated operator code by
     * querying the operator code service. It formats the response to match the expected
     * structure used by other duplicate checking methods in the controller.
     * 
     * @param {string} code - The operator code to check for existence in the system
     * @returns {Promise<Object>} A promise that resolves to an object with the structure
     *                           { data: { found: boolean } } where 'found' indicates
     *                           whether the code already exists (true) or is unique (false)
     * @throws {Error} Throws an error if the code existence check fails or if there are
     *                 network/service communication issues
     */
    async generatedCodeChecker(code) {
        console.log('OperatorAdminCreationController: Checking if code exists:', code);
        try {
            const exists = await operatorCodeService.checkIfCodeExists(code);
            console.log('OperatorAdminCreationController: Code exists check result:', exists);
            // Format the response to match what the controller expects
            return { data: { found: exists } };
        } catch (error) {
            console.error('OperatorAdminCreationController: Error checking if code exists:', error);
            throw error;
        }
    }





    /**
     * Handles lastname input events to provide autocomplete suggestions with debounced validation.
     * Validates that the input contains only uppercase letters and fetches matching lastname suggestions
     * from the server. Clears suggestions if input is empty or invalid, and manages the submit button state.
     * Uses a 500ms debounce delay to prevent excessive API calls during rapid typing.
     * 
     * @param {Event} event - The DOM input event object containing the target input element
     * @returns {void} This function does not return a value but updates UI elements and triggers suggestion display
     */
    suggestLastname(event) {
        const input = event.target.value;
        if (input.length > 0) { // Only start suggesting after at least 2 characters have been entered
            clearTimeout(this.suggestTimeout);
            this.suggestTimeout = setTimeout(async () => {
                const regex = /^[A-Z]+$/;
                const isValid = regex.test(input.toUpperCase().trim());
                if (isValid) {
                    const response = await this.fetchNameSuggestions(input, 'lastname');
                    this.displaySuggestions(response)
                } else {
                    this.manageNewOperatorSubmitButton();
                }
            }, 500); // Delay to avoid too frequent calls
        } else {
            this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions if the input is too short
        }
    }






    /**
     * Handles firstname input events to provide autocomplete suggestions with debounced validation.
     * Validates that the input follows proper firstname format (starts with uppercase letter followed by lowercase letters,
     * with optional hyphenated parts) and fetches matching firstname suggestions from the server. Clears suggestions 
     * if input is empty or invalid, and manages the submit button state. Uses a 500ms debounce delay to prevent 
     * excessive API calls during rapid typing.
     * 
     * @param {Event} event - The DOM input event object containing the target input element
     * @returns {void} This function does not return a value but updates UI elements and triggers suggestion display
     */
    suggestFirstname(event) {
        const input = event.target.value;
        if (input.length > 0) { // Only start suggesting after at least 2 characters have been entered
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
            }, 500); // Delay to avoid too frequent calls
        } else {
            this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions if the input is too short
        }
    }







    /**
     * Fetches name suggestions from the server based on the input field type and current form state.
     * This asynchronous function handles autocomplete functionality by making API calls to retrieve
     * matching operator names. It implements intelligent suggestion logic by first checking if the
     * opposite field (firstname/lastname) has a value and fetching suggestions for that field to
     * populate the suggestions results cache. Then it fetches suggestions for the current input
     * and processes them through duplicate checking logic.
     * 
     * @param {string} name - The name value to search for suggestions (either firstname or lastname)
     * @param {string} inputField - The type of input field being processed, either 'firstname' or 'lastname'
     * @returns {Promise<Array>} A promise that resolves to an array of suggestion objects after
     *                          processing through duplicate checking logic. Each suggestion object
     *                          typically contains operator information like name, code, team, and UAP details.
     */
    async fetchNameSuggestions(name, inputField) {
        let response;
        if (inputField === 'lastname' && this.newOperatorFirstnameTarget.value.trim() != "") {
            const firstNameResponse = await axios.post(`suggest-names`, { name: this.newOperatorFirstnameTarget.value.trim() });
            this.suggestionsResults = firstNameResponse.data;
        } else if (inputField === 'firstname' && this.newOperatorLastnameTarget.value.trim() != "") {
            const lastNameResponse = await axios.post(`suggest-names`, { name: this.newOperatorLastnameTarget.value.trim() });
            this.suggestionsResults = lastNameResponse.data;
        }
        response = await axios.post(`suggest-names`, { name: name });
        return this.checkIfSuggestionsResultsEmpty(response.data);
    }







    /**
     * Checks if the suggestions results cache is empty and processes responses accordingly.
     * If the suggestions results cache contains data, it filters the new response through
     * duplicate checking logic to find matching suggestions. If the cache is empty, it
     * populates the cache with the new response data and returns it directly.
     * 
     * @param {Array} response - An array of suggestion objects from the server containing
     *                          operator information such as name, code, team, and UAP details
     * @returns {Promise<Array>} A promise that resolves to an array of suggestion objects.
     *                          If suggestions cache has data, returns filtered duplicates.
     *                          If cache is empty, returns the original response array.
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
     * Filters suggestion responses to find duplicates that match existing suggestions in the cache.
     * This function compares incoming suggestion responses against the cached suggestions results
     * to identify which suggestions already exist in the cache based on their unique ID values.
     * Used to prevent duplicate suggestions from being displayed in the autocomplete functionality.
     * 
     * @param {Array<Object>} responses - An array of suggestion objects from the server response.
     *                                   Each object should contain at least an 'id' property for comparison,
     *                                   along with other operator information like name, code, team, and UAP details.
     * @returns {Promise<Array<Object>>} A promise that resolves to an array of suggestion objects that
     *                                  have matching IDs with existing suggestions in the cache.
     *                                  Returns an empty array if no duplicates are found.
     */
    async checkForDuplicatesuggestionsResults(responses) {
        const duplicateSuggestions = responses.filter(response => {
            return this.suggestionsResults.some(suggestion => suggestion.id === response.id);
        });
        return duplicateSuggestions;
    }







    /**
     * Displays autocomplete suggestions for operator names in the UI and handles user selection.
     * This function renders a list of clickable suggestion items based on the provided responses,
     * formats the display names properly, and sets up click event handlers for each suggestion.
     * When a suggestion is clicked, it automatically populates all related form fields with the
     * selected operator's information and triggers validation processes.
     * 
     * @param {Array<Object>} responses - An array of suggestion objects containing operator information.
     *                                   Each object should have properties: name (firstname.lastname format),
     *                                   code, team_name, team_id, uap_name, uap_id, and is_trainer.
     * @returns {void} This function does not return a value but updates the DOM by rendering
     *                 suggestion items and managing their visibility and click event handlers.
     */
    displaySuggestions(responses) {
        // Assuming 'responses' is an array of objects each with 'name', 'code', 'team', and 'uap'
        this.nameSuggestionsTarget.innerHTML = responses.map(response => {
            const parts = response.name.split('.'); // Split the 'name' to get firstName and lastName
            const firstName = this.capitalizeFirstLetter(parts[0]); // Capitalize the first name
            const lastName = parts.length > 1 ? this.capitalizeFirstLetter(parts[1]) : ''; // Handle last name if present
            const teamName = response.team_name; // Get the team name
            const teamId = response.team_id; // Get the team id
            const uapName = response.uap_name; // Get the uap name
            const uapId = response.uap_id; // Get the uap id
            const code = response.code; // Get the code
            const isTrainerBool = response.is_trainer; // Get the isTrainer value
            return `<div class="suggestion-item" data-firstname="${firstName}" data-lastname="${lastName}" data-code="${code}" data-team="${teamId}" data-uap="${uapId}" data-istrainer="${isTrainerBool}">
            ${lastName} ${firstName} - ${teamName} - ${uapName} (${code})
        </div>`;
        }).join('');
        this.nameSuggestionsTarget.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', (event) => {
                const firstname = event.currentTarget.getAttribute('data-firstname');
                const lastname = event.currentTarget.getAttribute('data-lastname');
                const code = event.currentTarget.getAttribute('data-code');
                const team = event.currentTarget.getAttribute('data-team');
                const uap = event.currentTarget.getAttribute('data-uap');
                const isTrainer = event.currentTarget.getAttribute('data-istrainer');

                this.newOperatorFirstnameTarget.value = firstname;
                this.newOperatorLastnameTarget.value = lastname;
                this.newOperatorCodeTarget.value = code;
                this.newOperatorTeamTarget.value = team;
                this.newOperatorUapsTarget.value = uap;

                if (isTrainer === '1') {
                    this.newOperatorIsTrainerTarget.checked = true;
                } else {
                    this.newOperatorIsTrainerTarget.checked = false;
                }
                this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions after selection
                this.validateNewOperatorLastname()
                this.suggestionsResults = [];
            });
        });
        this.nameSuggestionsTarget.style.display = responses.length ? 'block' : 'none';
    }

}