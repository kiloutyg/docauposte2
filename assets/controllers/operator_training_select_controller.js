import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["newOperatorSelectTeam", "newOperatorSelectUap", "newOperatorSelectMessage"];

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
        this.newOperatorSelectMessageTarget.style.color = isValid ? "black" : "red";
    }
}