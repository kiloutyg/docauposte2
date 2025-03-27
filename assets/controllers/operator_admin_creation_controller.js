
import { Controller } from '@hotwired/stimulus';

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
                    // this.newOperatorFirstnameTarget.focus();
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
        }, 800);
    }



    capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
    }



    capitalizeFirstLetterMethod(event) {
        const input = event.target;
        if (input.selectionStart <= 1) {
            input.value = input.value.charAt(0).toUpperCase() + input.value.slice(1).toLowerCase();
        }
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
            const regex = /^[0-9]{5}$/;
            let isValid;

            if (this.duplicateCheckResults.code) {
                if (this.newOperatorCodeTarget.value.trim() === this.duplicateCheckResults.code.data.value) {
                    return;
                } else {
                    this.duplicateCheckResults.code = null;
                    const code = this.newOperatorCodeTarget.value
                    const sumOfFirstThreeDigit = code.toString.split('').slice(0, 3).reduce((sum, digit) => sum + Number(digit), 0);
                    const valueOfLastTwoDigit = code.toString.split('').slice(3).join('');
                    if (sumOfFirstThreeDigit === valueOfLastTwoDigit) {
                        isValid = regex.test(this.newOperatorCodeTarget.value.trim());
                    }
                }
            } else {
                isValid = regex.test(this.newOperatorCodeTarget.value.trim());
            }
            this.updateMessage(this.newOperatorCodeMessageTarget, isValid, "Veuillez saisir un code correct.");

            if (isValid) {
                this.duplicateCheckResults.code = null;
                this.checkForExistingEntityByCode();
            }
        }, 800);
    }



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
            if (response.data.field === "name") {
                this.codeGeneratorInitiator();
            }
            this.newOperatorCodeTarget.disabled = false;
            this.newOperatorCodeTarget.focus();
            this.manageNewOperatorSubmitButton(true);
        }
    }


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
        this.manageNewOperatorSubmitButton(!entitiesMatch);
    }



    executeEntityNonMatchingLogic(unMatchedFound) {
        this.manageNewOperatorSubmitButton(unMatchedFound);
    }


    codeGenerator() {
        // Generate a random integer between 1 and 999
        const code = Math.floor(1 + Math.random() * 999);

        // Sum the digits of the 'code' integer
        let sumOfDigits = code
            .toString()
            .split('')
            .reduce((sum, digit) => sum + Number(digit), 0);
        const sumOfDigitsString = sumOfDigits.toString();
        if (sumOfDigitsString.length < 2) {
            sumOfDigits = '0' + sumOfDigits;
        }
        // Combine the original code and the sum of its digits
        let newCode = code.toString() + sumOfDigits.toString();
        // Ensure 'newCode' has exactly 5 digits
        if (newCode.length < 5) {
            // Pad with leading zeros if less than 5 digits
            newCode = newCode.padStart(5, '0');
        } else if (newCode.length > 5) {
            // If more than 5 digits, use the last 5 digits
            newCode = newCode.slice(-5);
        }
        return newCode;
    }

    async codeGeneratorInitiator() {
        const newCode = this.codeGenerator();
        // Check if the generated code already exists
        try {
            const response = await this.generatedCodeChecker(newCode);
            if (response.data.found) {
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
        return axios.post(`/docauposte/operator/check-if-code-exist`, { code: code });
    }



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
        return duplicateSuggestions;
    }



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