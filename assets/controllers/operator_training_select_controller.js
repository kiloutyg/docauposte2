import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["newOperatorSelect", "newOperatorSelectMessage"];

    validateNewOperatorSelect() {
        const operatorSelectValue = this.newOperatorSelectTarget.value;
        let message = "";
        let isValid = true;

        // Check if the select element has a selected option with a non-empty value.
        if (operatorSelectValue === "") {
            message = "Veuillez sélectionner une option.";
            isValid = false;
        }
        this.newOperatorSelectMessageTarget.textContent = message;
        this.newOperatorSelectMessageTarget.style.color = isValid ? "black" : "red"; l
    }
}