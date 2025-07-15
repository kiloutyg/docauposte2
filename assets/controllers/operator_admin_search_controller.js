import { operatorCodeService } from './services/operator_code_service';

import { Controller } from '@hotwired/stimulus';

export default class OperatorAdminSearchController extends Controller {


    static targets = [
        'operatorAdminSearchNameInput',
        'operatorAdminSearchCodeInput',
        'operatorAdminSearchTeamInput',
        'operatorAdminSearchUapInput',
        'operatorAdminSearchIsTrainerInput',
        'operatorAdminSearchSubmit'


    ];


    /**
     * Initializes the controller when it connects to the DOM.
     * Stores the initial value of the trainer search input field to track changes
     * and determine if the form should be submitted based on value modifications.
     */
    connect() {
        this.initialIsTrainerValue = this.operatorAdminSearchIsTrainerInputTarget.value;
    }

    /**
     * Validates a search name input against a specific pattern.
     * The name must contain only lowercase letters and hyphens, where hyphens
     * can only appear between letter groups (not at the beginning or end).
     * 
     * @param {string} name - The name string to validate
     * @returns {boolean} Returns true if the name matches the validation pattern, false otherwise
     */
    validateSearchByName(name) {
        const regex = /^[a-z]+(-[a-z]+)*$/;
        name = name.toLowerCase();
        const isValid = regex.test(name.trim());
        return isValid;

    }


    /**
     * Validates a search code input against a dynamically retrieved regex pattern.
     * Fetches validation settings from the operator code service and tests the provided
     * code against the configured regular expression pattern.
     * 
     * @param {string} code - The operator code string to validate
     * @returns {Promise<boolean>} A promise that resolves to true if the code matches the validation pattern, false otherwise
     */
    async validateSearchByCode(code) {
        const settings = await operatorCodeService.getSettings();
        const regex = settings.regex;
        const isValid = regex.test(code.trim());
        return isValid;
    }


    /**
     * Validates a search team input against a specific pattern.
     * The team must start with a lowercase letter followed by one or more uppercase letters.
     * 
     * @param {string} team - The team string to validate
     * @returns {Promise<boolean>} A promise that resolves to true if the team matches the validation pattern, false otherwise
     */
    async validateSearchByTeam(team) {
        const regex = /^[a-z][A-Z]+$/;
        const isValid = regex.test(team.trim());
        return isValid;
    }

    /**
     * Validates a search UAP (Unit Administrative Postale) input against a specific pattern.
     * The UAP must start with a lowercase letter followed by one or more uppercase letters.
     * 
     * @param {string} uap - The UAP string to validate
     * @returns {Promise<boolean>} A promise that resolves to true if the UAP matches the validation pattern, false otherwise
     */
    async validateSearchByUap(uap) {
        const regex = /^[a-z][A-Z]+$/;
        const isValid = regex.test(uap.trim());
        return isValid;

    }


    /**
     * Initiates a delayed search form submission with error handling.
     * Clears any existing search timeout and sets a new one to prevent rapid successive submissions.
     * The actual search submission is wrapped in a try-catch block to handle potential errors gracefully.
     * 
     * @returns {void} This function does not return a value
     */
    submitSearchForm() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            try { this.submitSearch(); }
            catch (e) {
                console.error('submit Search Form error catch', e)
            }
        }, 1000);

    }


    /**
     * Executes the search form submission process by validating all search criteria and submitting if valid.
     * Collects values from all search input fields, validates non-empty fields using their respective
     * validation methods, and triggers form submission if at least one validation passes. After submission
     * or validation failure, initiates cleanup of form values.
     * 
     * @returns {void} This function does not return a value
     */
    submitSearch() {
        const name = this.operatorAdminSearchNameInputTarget.value;
        const code = this.operatorAdminSearchCodeInputTarget.value;
        const team = this.operatorAdminSearchTeamInputTarget.value;
        const uap = this.operatorAdminSearchUapInputTarget.value;
        const isTrainer = this.operatorAdminSearchIsTrainerInputTarget.value;

        let submitCount = 0;
        let validations = [];

        if (name !== '') validations.push(this.validateSearchByName(name));
        if (code !== '') validations.push(this.validateSearchByCode(code));
        if (team !== '') validations.push(this.validateSearchByTeam(team));
        if (uap !== '') validations.push(this.validateSearchByUap(uap));

        validations.push(isTrainer !== this.initialIsTrainerValue); // Assuming selection is mandatory unless "Tous"

        submitCount = validations.filter(Boolean).length;

        if (submitCount > 0) {
            this.operatorAdminSearchSubmitTarget.click();
            this.cleanSubmittedTargetValue();
        } else {
            console.error('Validation failed');
            this.cleanSubmittedTargetValue();
        }
    }



    /**
     * Clears all search form input values after a delayed timeout period.
     * Cancels any existing cleanup timeout and sets a new one to reset all search input fields
     * to empty strings after 15 seconds. This provides a cleanup mechanism to clear the form
     * after search operations have been completed.
     * 
     * @returns {void} This function does not return a value
     */
    cleanSubmittedTargetValue() {
        clearTimeout(this.cleanTimeout);
        this.cleanTimeout = setTimeout(() => {
            this.operatorAdminSearchUapInputTarget.value = '';
            this.operatorAdminSearchTeamInputTarget.value = '';
            this.operatorAdminSearchCodeInputTarget.value = '';
            this.operatorAdminSearchNameInputTarget.value = '';
            this.operatorAdminSearchIsTrainerInputTarget.value = '';
        }, 15000);
    }
}