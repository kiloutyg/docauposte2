import { Controller } from '@hotwired/stimulus';

export default class OperatorTrainingSelectController extends Controller {
    static targets = ["newOperatorSelectTeam", "newOperatorSelectUap", "newOperatorSelectMessage", "submit"];

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

    submitButtonClicked() {
        // Trigger the click event on the submit button.
        // Assuming 'submitTarget' is the reference to your submit button added to the static targets array.
        this.submitTarget.click();
    }

}