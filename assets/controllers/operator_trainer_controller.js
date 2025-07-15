import { operatorCodeService } from './services/operator_code_service';
import { Controller } from '@hotwired/stimulus';
import axios from 'axios';


export default class OperatorTrainerController extends Controller {



    static targets = [
        "trainerOperatorName",
        "trainerOperatorCode",
        "trainerOperatorNameMessage",
        "trainerOperatorCodeMessage",
        "trainerOperatorLogon",
    ];

    currentRegexPattern = '[0-9]{5}';

    /**
     * Checks authentication status when the controller reconnects after page load/reload
     * This should be called when the controller connects to handle post-submission state
     */
    connect() {
        // Fetch the current regex pattern when the controller connects
        this.fetchRegexPattern();
    }

    /**
     * Fetches the current regex pattern for operator code validation from the server.
     * This method retrieves the validation pattern settings from the operator code service
     * and updates the controller's currentRegexPattern property accordingly.
     * If the fetch fails, the default pattern will be maintained.
     * 
     * @async
     * @returns {Promise<void>} No return value, but updates the currentRegexPattern property
     */
    async fetchRegexPattern() {
        try {
            const settings = await operatorCodeService.getSettings();
            console.log('Fetched global regex pattern:', settings.regex);
            if (settings) {
                // Remove any forward slashes that might be in the stored pattern
                this.currentRegexPattern = settings.regex.toString().replace(/^\/|\/$/g, '');
                console.log('Fetched regex pattern:', this.currentRegexPattern);
            }
        } catch (error) {
            console.error('Error fetching regex pattern:', error);
            // Keep the default pattern if there's an error
        }
    }

    /**
     * Handles the connection event for the trainer operator logon target element.
     * This method is automatically called by Stimulus when the trainerOperatorLogon target
     * element is connected to the DOM. It initiates the trainer login verification process
     * to check if a user is already authenticated as a trainer.
     * 
     * @returns {void} No return value
     */
    trainerOperatorLogonTargetConnected() {
        console.log('TrainerOperatorLogonTarget connected');
        this.trainerOperatorLoginCheck();
    }

    /**
     * Checks if the current user is authenticated as a trainer.
     * Makes an API call to verify the user's login status and trainer role.
     * If authenticated, it triggers the trainer authentication process.
     * 
     * @async
     * @returns {Promise<boolean|undefined>} Returns true if the user is authenticated as a trainer,
     *                                      undefined otherwise (in case of error or not found)
     */
    async trainerOperatorLoginCheck() {
        console.log('OperatorTrainerController: trainerOperatorLoginCheck');
        try {
            const response = await axios.post('/docauposte/operator/user-login-check');
            if (response.data.found) {
                this.trainerAuthenticated(response);
                return true;
            } else {
                console.error('No user connected or User not found as a trainer');
            }
        } catch (error) {
            console.error('error in axios request', error); // Log out the actual error

        }
    }


    /**
     * Validates the trainer operator name input field with debounced validation.
     * This method implements a delayed validation mechanism that waits for the user to stop typing
     * before performing validation. It checks if the entered name follows the required format
     * (firstname.lastname) using a regex pattern. If valid, it proceeds to check if the trainer
     * exists in the system. If invalid, it displays an appropriate error message.
     * 
     * The validation pattern enforces:
     * - Alphabetic characters only for first and last names
     * - A single dot separator between first and last name
     * - No hyphens at the beginning or end of the last name
     * - No consecutive hyphens in the last name
     * 
     * @returns {void} No return value, but triggers either trainer existence check or error message display
     */
    validateTrainerOperatorName() {
        clearTimeout(this.typingTimeout);  // clear any existing timeout to reset the timer
        this.typingTimeout = setTimeout(() => {
            const regex = /^[a-zA-Z]+\.(?!-)(?!.*--)[a-zA-Z-]+(?<!-)$/;
            const value = this.trainerOperatorNameTarget.value.trim();
            const isValid = regex.test(value);
            if (isValid) {
                this.checkTrainerExistence('name', value);
            } else {
                this.updateMessage(this.trainerOperatorNameMessageTarget, isValid, "Veuillez saisir sous la forme prenom.nom.");
            }
        }, 1800);
    }


    /**
     * Validates the trainer operator code input field with debounced validation.
     * This method enables the code input field, sets focus to it, and implements a delayed
     * validation mechanism that waits for the user to stop typing before performing validation.
     * It checks if the entered code is valid using the operator code service. If valid,
     * it proceeds to check if the trainer exists in the system. If invalid, it displays
     * an appropriate error message.
     * 
     * The validation process:
     * - Enables and focuses the code input field
     * - Uses a 1.8-second debounce delay to avoid excessive validation calls
     * - Only validates non-empty input values
     * - Calls the operator code service for validation
     * - Triggers trainer existence check on successful validation
     * - Displays error message for invalid codes
     * 
     * @returns {void} No return value, but triggers either trainer existence check or error message display
     */
    validateTrainerOperatorCode() {
        this.trainerOperatorCodeTarget.disabled = false;
        this.trainerOperatorCodeTarget.focus();

        clearTimeout(this.typingTimeout);  // clear any existing timeout to reset the timer
        this.typingTimeout = setTimeout(async () => {
            const value = this.trainerOperatorCodeTarget.value.trim();
            if (value.length > 0) // only check if the field is not empty
            {
                const isValid = await operatorCodeService.validateCode(value);
                if (isValid) {
                    this.checkTrainerExistence('code', value);
                } else {
                    this.updateMessage(this.trainerOperatorCodeMessageTarget, isValid, "Veuillez saisir un code valide: XXXXX.");
                }
            }
        }, 1800);
    }


    /**
     * Updates the display message for a target element based on validation status.
     * This method either clears the message content if validation is successful,
     * or displays an error message with bold red styling if validation fails.
     * 
     * @param {HTMLElement} targetElement - The DOM element where the message will be displayed
     * @param {boolean} isValid - Flag indicating whether the validation passed (true) or failed (false)
     * @param {string} errorMessage - The error message text to display when validation fails
     * @returns {void} No return value
     */
    updateMessage(targetElement, isValid, errorMessage) {
        if (isValid) {
            targetElement.textContent = "";
        } else {
            targetElement.textContent = errorMessage;
            targetElement.style.fontWeight = "bold";
            targetElement.style.color = "red";
        }
    }



    /**
     * Checks if a trainer exists in the system based on the provided field and value.
     * This method makes an API call to verify trainer existence and handles the response
     * appropriately. When checking by code, it also includes the trainer name in the payload
     * for additional validation. On success, it delegates response handling to another method.
     * On failure, it displays an error message to the user.
     * 
     * @async
     * @param {string} field - The field type being validated ('name' or 'code')
     * @param {string} value - The value to check for trainer existence (trainer name or operator code)
     * @returns {Promise<void>} No return value, but triggers response handling or error display
     */
    async checkTrainerExistence(field, value) {
        const payload = {};
        payload[field] = value;
        // If the field is 'code', also take the value of the name from the target.
        if (field === 'code') {
            payload['name'] = this.trainerOperatorNameTarget.value;
        }
        try {
            const response = await axios.post('/docauposte/operator/check-if-trainer-exist', payload);
            // Build the correct fieldName based on the field being checked.
            const fieldName = field === 'name' ? 'trainerOperatorName' : 'trainerOperatorCode';
            this.handleTrainerExistenceResponse(response, field, fieldName);
        } catch (error) {
            console.error('error in axios request', error); // Log out the actual error
            const messageTarget = this[`${fieldName}MessageTarget`];
            messageTarget.style.color = "red";
            messageTarget.textContent = "Erreur lors de la recherche du formateur.";
        }
    }



    /**
     * Handles the response from trainer existence check API calls.
     * This method processes the server response to determine if a trainer was found and updates
     * the UI accordingly. When a trainer is found, it either proceeds to code validation (for name checks)
     * or completes the authentication process (for code checks). It manages input field states,
     * displays appropriate success/error messages, and handles special cases like upload trainer status.
     * 
     * @param {Object} response - The axios response object from the trainer existence check API call
     * @param {Object} response.data - The response data containing trainer information
     * @param {boolean} response.data.found - Flag indicating whether the trainer was found in the system
     * @param {boolean} [response.data.uploadTrainer] - Optional flag indicating if this is an upload trainer scenario
     * @param {string} field - The field type that was validated ('name' or 'code')
     * @param {string} fieldName - The target name prefix used to access DOM elements ('trainerOperatorName' or 'trainerOperatorCode')
     * @returns {void} No return value, but updates UI elements and may trigger additional validation or authentication
     */
    handleTrainerExistenceResponse(response, field, fieldName) {
        if (response.data.found) {
            if (field === 'name') {
                this.validateTrainerOperatorCode();
            } else {
                this.trainerAuthenticated(response);
                if (response.data.uploadTrainer === false) {
                    this.trainerOperatorNameMessageTarget.style.fontWeight = "bold";
                    this.trainerOperatorNameMessageTarget.style.color = "green";
                    this.trainerOperatorNameMessageTarget.textContent = "Formateur trouvé.";
                } else {
                    this.trainerOperatorNameMessageTarget.textContent = "";
                }
            }
            this[`${fieldName}Target`].disabled = true;
            this[`${fieldName}MessageTarget`].textContent = "";
            this[`${fieldName}MessageTarget`].style.fontWeight = "bold";
            this[`${fieldName}MessageTarget`].style.color = "green";
            this[`${fieldName}MessageTarget`].textContent = "Formateur trouvé.";
        } else {
            this[`${fieldName}Target`].value = "";
            this[`${fieldName}MessageTarget`].textContent = "";
            this[`${fieldName}MessageTarget`].style.fontWeight = "bold";
            this[`${fieldName}MessageTarget`].style.color = "red";
            this[`${fieldName}MessageTarget`].textContent = field === 'name' ? "Formateur non trouvé." : "Code Opé Formateur erroné.";
            // Stop the repeating validation since we found the trainer
        };
    }


    /**
     * Handles the successful authentication of a trainer and initializes the training interface.
     * This method is called when a trainer has been successfully authenticated and verified.
     * It performs the necessary UI updates to enable the training functionality, including
     * loading the operator training form, enabling operator input fields, and displaying
     * the logout button for the authenticated trainer.
     * 
     * @param {Object} response - The axios response object from the successful trainer authentication
     * @param {Object} response.data - The response data containing trainer information
     * @param {number} response.data.trainerId - The unique identifier of the authenticated trainer
     * @param {boolean} [response.data.uploadTrainer] - Optional flag indicating special trainer status
     * @returns {void} No return value, but updates the UI to reflect authenticated trainer state
     */
    trainerAuthenticated(response) {
        // initialize the new operator form
        this.loadOperatorTrainingContent(response);
        // enable the training button
        const operatorInputs = document.querySelectorAll('.operator-input');
        operatorInputs.forEach(function (input) {
            input.disabled = false;
        });
        this.logOutInputSwitch(true);
    }

    /**
     * Toggles the display of the trainer logout button in the UI.
     * This method dynamically updates the trainer logout container to either show
     * or hide the logout button based on the trainer's authentication status.
     * When enabled, it displays a red "Déconnexion" button that triggers the logout action.
     * When disabled, it clears the container content, effectively hiding the logout option.
     * 
     * @param {boolean} logOut - Flag indicating whether to show (true) or hide (false) the logout button
     * @returns {void} No return value, but updates the DOM content of the trainer logout container
     */
    logOutInputSwitch(logOut) {
        const trainerLogOutContainer = document.getElementById('trainerLogOutContainer');
        let content = ``;
        if (logOut) {
            content = `
            <input
            type="button"
            class="btn btn-danger"
            data-action="click->operator-trainer#logOut"
            value="Déconnexion">
            `;
        }
        trainerLogOutContainer.innerHTML = content;

    }

    /**
     * Handles the logout process for the current trainer.
     * This method resets the trainer-related input fields, clears any displayed messages,
     * disables the operator code input, re-enables the trainer name input, unloads the operator
     * training content, and toggles the logout button off. It also disables all operator input fields.
     *
     * @returns {void} No return value.
     */
    logOut() {
        this.trainerOperatorNameTarget.value = "";
        this.trainerOperatorNameTarget.disabled = false;
        this.trainerOperatorCodeTarget.value = "";
        this.trainerOperatorCodeTarget.disabled = true;
        this.trainerOperatorNameMessageTarget.textContent = "";
        this.trainerOperatorCodeMessageTarget.textContent = "";
        this.validateTrainerOperatorName();
        this.unloadOperatorTrainingContent();
        this.logOutInputSwitch(false);
        const operatorInputs = document.querySelectorAll('.operator-input');
        operatorInputs.forEach(function (input) {
            input.disabled = true;
        });

    }


    /**
     * Loads the operator training content into the designated container.
     * This function dynamically generates HTML content for the operator training form,
     * injects it into the 'newOperatorContainer' element, and updates the
     * 'trainingValidationSubmitContainer' with trainer-specific information.
     * @param {Object} response - The axios response object containing trainer information.
     * @param {Object} response.data - The response data.
     * @param {number} response.data.trainerId - The unique identifier for the trainer.
     * @returns {void} This function does not return a value.
     */
    loadOperatorTrainingContent(response) {
        const container = document.getElementById('newOperatorContainer');
        // You would fetch this content via an API or similar.
        const content = `

                <div class="d-flex">
                    <div class="col-4 mx-1">
                        <input
                            type="text"
                            class="form-control capitalize-all-letters"
                            data-operator-training-target="newOperatorSurname"
                            data-action="keyup->operator-training#validateNewOperatorSurname input->operator-training#suggestSurname"
                            placeholder="NOM"
                            id="newOperatorSurname"
                            name="newOperatorSurname"
                            required>
                    <div
                        data-operator-training-target="nameSuggestions"
                        class="traininglist-suggestions-list rounded-bottom"></div>
                    </div>
                
                    <div
                        class="col-3 mx-0"
                        >
                        <input
                            type="text"
                            class="form-control capitalize-first-letter::first-letter"
                            data-operator-training-target="newOperatorFirstname"
                            data-action="keyup->operator-training#validateNewOperatorFirstname input->operator-training#suggestFirstname"
                            placeholder="Prenom"
                            id="newOperatorFirstname"
                            name="newOperatorFirstname"
                            required
                            disabled>
                    </div>
                    <div
                        class="col-2 mx-1"
                        >
                        <input
                            type="password"
                            pattern="${this.currentRegexPattern}"
                            class="form-control"
                            data-operator-training-target="newOperatorCode"
                            data-action="keyup->operator-training#validateNewOperatorCode"
                            placeholder="Code Opérateur"
                            id="newOperatorCode"
                            name="newOperatorCode"
                            required
                            disabled>
                    </div>
                        <input type="hidden" id="newOperatorName" name="newOperatorName">           
                    <div
                        class="col-1 mx-0"
                        >
                        <input
                            type="submit"
                            class="btn btn-primary"
                            data-operator-training-target="newOperatorSubmitButton"
                            data-action="click->operator-trainer#resetFollowingSubmit"
                            id="newOperatorSubmitButton"
                            name="newOperatorSubmitButton"
                            value="Ajouter"
                            disabled>
                    </div>
                </div>

                <div
                    data-operator-training-target="newOperatorNameMessage"
                    class="newOperatorName-message d-flex justify-content-evenly"></div>
                <div
                    data-operator-training-target="newOperatorCodeMessage"
                    class="newOperatorCode-message d-flex justify-content-evenly"></div>
                <div
                    data-operator-training-target="newOperatorTransferMessage"
                    class="newOperatorTransfer-message d-flex justify-content-evenly"></div>
        `;

        container.innerHTML = content;
        let trainerId = response.data.trainerId;
        const listUpdateSubmitContainer = document.getElementById('trainingValidationSubmitContainer');

        const listUpdateSubmitContent = `
        <input type="hidden" name="trainerId" value="${trainerId}">			
        <input
        type="submit"
        class="btn btn-primary"
        data-action="click->operator-trainer#resetFollowingSubmit"
        value="Enregistrer les modifications">
        `;
        listUpdateSubmitContainer.innerHTML = listUpdateSubmitContent;
    }



    /**
     * Unloads the operator training content from the designated containers.
     * This function clears the HTML content of both the 'newOperatorContainer' and
     * 'trainingValidationSubmitContainer' elements, effectively removing the operator
     * training form and related submission elements from the user interface.
     *
     * @returns {void} This function does not return a value.
     */
    unloadOperatorTrainingContent() {
        const container = document.getElementById('newOperatorContainer');
        container.innerHTML = ''; // Clears out the inner content of the div
        const listUpdateSubmitContainer = document.getElementById('trainingValidationSubmitContainer');
        listUpdateSubmitContainer.innerHTML = '';
    }

    /**
     * Resets the validation process following a submission attempt.
     * This function triggers the validation of the trainer operator name,
     * ensuring that the input is re-validated after a submission,
     * likely to handle cases where the input might have changed or to
     * provide immediate feedback on submission errors.
     *
     * @returns {void} This function does not return a value.
     */
    resetFollowingSubmit() {
        this.validateTrainerOperatorName();
    }
}
