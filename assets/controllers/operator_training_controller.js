import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["newOperator", "newOperatorMessage", "newOperatorCode", "newOperatorMessageCode"];

    validateNewOperator() {
        const regex = /^[a-zA-Z]+\.[a-zA-Z]+$/;
        const isValid = regex.test(this.newOperatorTarget.value);

        if (isValid) {
            this.newOperatorMessageTarget.textContent = "";
        } else {
            this.newOperatorMessageTarget.textContent = "Veuillez saisir sous la forme prénom.nom";
            this.newOperatorMessageTarget.style.color = "red"; // Display the message in red color.
        }
    }


    validateNewOperatorCode() {
        const regex = /^[0-9]+$/;
        const isValid = regex.test(this.newOperatorCodeTarget.value);

        if (isValid) {
            this.newOperatorMessageCodeTarget.textContent = "";
        } else {
            this.newOperatorMessageCodeTarget.textContent = "Veuillez saisir un code correcte.";
            this.newOperatorMessageCodeTarget.style.color = "red"; // Display the message in red color.
        }
    }
}


