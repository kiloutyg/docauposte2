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
        }, 1000); // delay in milliseconds
    }




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



    async codeGenerator() {
        console.log('OperatorAdminCreationController: Calling codeGenerator');
        const code = await operatorCodeService.generateUniqueCode();
        console.log('OperatorAdminCreationController: Generated code:', code);
        return code;
    }






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