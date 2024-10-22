
import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class OperatorAdminCreationController extends Controller {



    static targets = [
        "newOperatorLastname",
        "newOperatorFirstname",
        "newOperatorCode",
        "newOperatorTeam",
        "newOperatorUap",
        "newOperatorIsTrainer",
        "newOperatorNameMessage",
        "newOperatorCodeMessage",
        "newOperatorTransferMessage",
        "newOperatorSubmitButton",
        "nameSuggestions",
    ];



    suggestionsResults = [];



    validateNewOperatorLastname() {
        clearTimeout(this.lastnameTypingTimeout);
        this.lastnameTypingTimeout = setTimeout(() => {
            console.log('checking what is carried by the target:', this.newOperatorLastnameTarget);
            console.log('validating new operator lastname:', this.newOperatorLastnameTarget.value);
            const regex = /^[A-Z][A-Z]+$/;
            const lastname = this.newOperatorLastnameTarget.value.toUpperCase();
            const isValid = regex.test(lastname.trim());
            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir un nom valide(sans accent, ni caractères spéciaux).");
            if (isValid) {
                // this.newOperatorLastnameTarget.disabled = true;
                if (this.newOperatorFirstnameTarget.value.trim() === "") {
                    this.newOperatorFirstnameTarget.disabled = false;
                    this.newOperatorFirstnameTarget.focus();
                }
                this.validateNewOperatorFirstname();
            }
        }, 800);
    }



    validateNewOperatorFirstname() {
        clearTimeout(this.firstnameTypingTimeout);
        this.firstnameTypingTimeout = setTimeout(() => {
            const firstnameValue = this.newOperatorFirstnameTarget.value;
            this.firstnameValue = this.capitalizeFirstLetter(firstnameValue);
            console.log('validating new operator firstname:', this.newOperatorFirstnameTarget.value);
            console.log('validating new operator firstname:', this.firstnameValue)
            const regex = /^[A-Z][a-z]+(-[A-Z][a-z]+)*$/;
            const isValid = regex.test(this.firstnameValue.trim());
            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir un prenom valide(sans accent, ni caractères spéciaux).");
            if (isValid) {
                let combinedName = `${this.newOperatorFirstnameTarget.value.trim()}.${this.newOperatorLastnameTarget.value.trim()}`;
                this.newOperatorNameTarget = combinedName.toLowerCase();

                let invertedCombined = `${this.newOperatorLastnameTarget.value.trim()}.${this.newOperatorFirstnameTarget.value.trim()}`;
                this.newOperatorInvertedNameTarget = invertedCombined.toLowerCase();
                // this.newOperatorFirstnameTarget.disabled = true;
                this.validateNewOperatorName();
            }
        }, 800);
    }



    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }



    capitalizeFirstLetterMethod(event) {
        console.log('capitalizing first letter:', event.target);
        const input = event.target;
        if (input.selectionStart <= 1) {
            input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1).toLowerCase();
        }
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

            this.updateMessage(this.newOperatorNameMessageTarget, isValid, "Veuillez saisir sous la forme prenom.nom.");
            this.newOperatorCodeTarget.disabled = true;

            if (isValid) {
                this.checkForExistingEntityByName();
                if (this.newOperatorCodeTarget.value !== "") {
                    this.validateNewOperatorCode();
                }
            }
        }, 800); // delay in milliseconds
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
                    const code = this.newOperatorCodeTarget.value
                    const sumOfFirstThreeDigit = code.toString.split('').slice(0, 3).reduce((sum, digit) => sum + Number(digit), 0);
                    console.log('sum of first three digits:', sumOfFirstThreeDigit);
                    const valueOfLastTwoDigit = code.toString.split('').slice(3).join('');
                    console.log('value of last two digits:', valueOfLastTwoDigit);
                    if (sumOfFirstThreeDigit === valueOfLastTwoDigit) {
                        isValid = regex.test(this.newOperatorCodeTarget.value.trim());
                    }
                }
            } else {
                isValid = regex.test(this.newOperatorCodeTarget.value.trim());
            }
            this.updateMessage(this.newOperatorCodeMessageTarget, isValid, "Veuillez saisir un code correct.");

            if (isValid) {
                console.log('Code is valid, clearing duplicate check results and checking for existing entity by code')
                this.duplicateCheckResults.code = null;
                this.checkForExistingEntityByCode();
            }
        }, 800);
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

        } else {
            console.log('No duplicate found, generating a code and allowing to write it.' + response.data.field);
            if (response.data.field === "name") {
                console.log('No duplicate found, generating a code and allowing to write it.');
                this.codeGeneratorInitiator();
            }
            this.newOperatorCodeTarget.disabled = false;
            this.newOperatorCodeTarget.focus();
            this.manageNewOperatorSubmitButton(true);
        }
    }


    manageNewOperatorSubmitButton(enableButton = false) {
        console.log(`Setting new operator submit button - Enabled: ${enableButton}`);
        this.newOperatorSubmitButtonTarget.disabled = !enableButton;
        document.getElementById('newOperatorName').value = this.newOperatorNameTarget;
        clearTimeout(this.validatedTimeout);
        this.validatedTimeout = setTimeout(() => {
            console.log('Resetting new operator form after 15 seconds')
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
            this.newOperatorUapTarget.value = "";
            this.newOperatorIsTrainerTarget.checked = null;
            this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions 
            this.suggestionsResults = [];
        }, 15000);

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

        const message = entitiesMatch
            ? "Nom et Code opérateurs correspondent à un même opérateur. Vous pouvez le transferer."
            : "Nom et Code opérateurs ne correspondent pas à un même opérateur. Veuillez saisir un autre nom ou code opérateur";

        console.log(`Manage submit button to be ${entitiesMatch ? "enabled" : "disabled"} `);
        this.manageNewOperatorSubmitButton(!entitiesMatch);
    }



    executeEntityNonMatchingLogic(unMatchedFound) {
        this.manageNewOperatorSubmitButton(unMatchedFound);
    }


    codeGenerator() {
        console.log('Generating a code');

        // Generate a random integer between 1 and 999
        const code = Math.floor(1 + Math.random() * 999);

        // Sum the digits of the 'code' integer
        const sumOfDigits = code
            .toString()
            .split('')
            .reduce((sum, digit) => sum + Number(digit), 0);

        if (sumOfDigits.length < 2) {
            console.log('sumOfDigits is less than 2, adding a leading zero:', sumOfDigits);
            sumOfDigits = '0' + sumOfDigits;
        }

        // Combine the original code and the sum of its digits
        let newCode = code.toString() + sumOfDigits.toString();
        console.log('newCode combined:', newCode);

        // Ensure 'newCode' has exactly 5 digits
        if (newCode.length < 5) {
            // Pad with leading zeros if less than 5 digits
            newCode = newCode.padStart(5, '0');
            console.log('newCode padded with leading zeros:', newCode);
        } else if (newCode.length > 5) {
            // If more than 5 digits, use the last 5 digits
            newCode = newCode.slice(-5);
            console.log('newCode truncated to 5 digits:', newCode);
        }

        console.log('generated code:', newCode);
        return newCode;
    }

    async codeGeneratorInitiator() {
        console.log('initiating code generator');
        const newCode = this.codeGenerator();
        // Check if the generated code already exists
        try {
            const response = await this.generatedCodeChecker(newCode);
            console.log('Response for generated code:', response.data);

            if (response.data.found) {
                console.log('Code already exists, generating another code');
                await this.codeGeneratorInitiator(); // Recursively generate a new code
            } else {
                // Set the new code to the input field and proceed
                this.newOperatorCodeTarget.value = newCode;
                this.newOperatorCodeTarget.disabled = true;
                this.newOperatorCodeTarget.focus();
                this.validateNewOperatorCode();
            }
        } catch (error) {
            console.error('Error checking for duplicate operator code.', error);
            // Handle error accordingly
        }
    }



    generatedCodeChecker(code) {
        console.log('checking generated code');
        return axios.post(`/docauposte/operator/check-if-code-exist`, { code: code });
    }



    suggestLastname(event) {
        const input = event.target.value;
        console.log('suggesting lastname:', input);
        console.log('suggesting lastname:', input.length > 0);

        if (input.length > 0) { // Only start suggesting after at least 2 characters have been entered
            clearTimeout(this.suggestTimeout);
            this.suggestTimeout = setTimeout(async () => {
                const regex = /^[A-Z]+$/;
                // const upperCasedInput = input.toUpperCase();
                // const isValid = regex.test(upperCasedInput);
                const isValid = regex.test(input.toUpperCase().trim());

                console.log('is input valid for fetching suggestions for lastname:', isValid);

                if (isValid) {
                    console.log('fetching suggestions for lastname:', input);
                    const response = await this.fetchNameSuggestions(input, 'lastname');
                    console.log('suggestions response:', response);
                    this.displaySuggestions(response)
                } else {
                    this.manageNewOperatorSubmitButton();
                }

            }, 500); // Delay to avoid too frequent calls
        } else {
            this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions if the input is too short
        }
    }



    suggestFirstname(event) {
        const input = event.target.value;
        console.log('suggesting firstname:', input);
        if (input.length > 0) { // Only start suggesting after at least 2 characters have been entered
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
            }, 500); // Delay to avoid too frequent calls
        } else {
            this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions if the input is too short
        }
    }



    async fetchNameSuggestions(name, inputField) {
        console.log('fetching name suggestions:', name, inputField);
        let response;

        if (inputField === 'lastname' && this.newOperatorFirstnameTarget.value.trim() != "") {
            console.log('first name is not empty');
            const firstNameResponse = await axios.post(`suggest-names`, { name: this.newOperatorFirstnameTarget.value.trim() });
            this.suggestionsResults = firstNameResponse.data;

        } else if (inputField === 'firstname' && this.newOperatorLastnameTarget.value.trim() != "") {
            console.log('last name is not empty');
            const lastNameResponse = await axios.post(`suggest-names`, { name: this.newOperatorLastnameTarget.value.trim() });
            this.suggestionsResults = lastNameResponse.data;
        }

        response = await axios.post(`suggest-names`, { name: name });

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
        return duplicateSuggestions;
    }



    displaySuggestions(responses) {
        console.log('displaying suggestions:', responses);
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

                console.log('selected suggestion firstname, lastname, code, team, uap:', firstname, lastname, code, team, uap, isTrainer);

                this.newOperatorFirstnameTarget.value = firstname;
                this.newOperatorLastnameTarget.value = lastname;
                this.newOperatorCodeTarget.value = code;
                this.newOperatorTeamTarget.value = team;
                this.newOperatorUapTarget.value = uap;
                if (isTrainer === '1') {
                    this.newOperatorIsTrainerTarget.checked = true;
                } else {
                    this.newOperatorIsTrainerTarget.checked = false;
                }
                this.nameSuggestionsTarget.innerHTML = ''; // Clear suggestions after selection
                this.validateNewOperatorLastname()
                console.log('newOperatorTeamTarget:', this.newOperatorTeamTarget.value);
                console.log('newOperatorUapTarget:', this.newOperatorUapTarget.value);
                console.log('newOperatorIsTrainerTarget:', this.newOperatorIsTrainerTarget);
                this.suggestionsResults = [];
            });
        });
        this.nameSuggestionsTarget.style.display = responses.length ? 'block' : 'none';
    }

}