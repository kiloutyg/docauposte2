import { Controller } from '@hotwired/stimulus';

export default class OperatorTrainingSelectController extends Controller {
    static targets = ["newOperatorSelectTeam", "newOperatorSelectUap", "newOperatorSelectMessage", "submit"];

    /**
     * Validates the selected options in the operator training selection form.
     * Displays an error message if either of the selects is not chosen.
     * Calls the submitButtonClicked method if both selects have valid options.
     */
    validateNewOperatorSelect() {
        const teamSelectValue = this.newOperatorSelectTeamTarget.value;
        const uapSelectValue = this.newOperatorSelectUapTarget.value;
        let message = "";
        let isValid = true;
    
        if (!teamSelectValue || !uapSelectValue) {
            // If either of the selects is not chosen, display an error message.
            message = "Selectionnez une option pour chaque champ.";
            isValid = false;
        }
        this.newOperatorSelectMessageTarget.textContent = message;
        this.newOperatorSelectMessageTarget.style.fontWeight = "bold";
        this.newOperatorSelectMessageTarget.style.color = isValid ? "black" : "red";
        if (isValid) {
            this.submitButtonClicked();
        }
    }

    /**
     * Triggers the click event on the submit button.
     *
     * @function submitButtonClicked
     * @memberof OperatorTrainingSelectController
     * @instance
     *
     * @description
     * This function is responsible for triggering the click event on the submit button.
     * It assumes that 'submitTarget' is the reference to your submit button added to the static targets array.
     *
     * @returns {void}
     */
    submitButtonClicked() {
        this.submitTarget.click();
    }

}