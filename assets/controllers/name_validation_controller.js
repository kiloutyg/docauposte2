import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["teamUapName", "teamUapNameMessage", "productName", "productNameMessage", "workstationName", "workstationNameMessage", "saveButton"];

    validateTeamUapName() {
        const regex = /^(?!-)(?!.*--)[A-Z-]{3,}(?<!-)$/;
        let isValid = true;
        let name = this.teamUapNameTarget.value;

        if (name != '') {
            isValid = regex.test(name);
        }

        if (isValid) {
            this.teamUapNameMessageTarget.textContent = "";
        } else {
            this.teamUapNameMessageTarget.textContent = "Format invalide. Veuillez saisir sous la forme UAP ou TEAM";
            this.teamUapNameMessageTarget.style.color = "DarkRed"; // Display the message in red color.
        }
    }


    validateProductName() {
        const regex = /^[A-Z]+\d+$/;
        let isValid = true;
        let name = this.productNameTarget.value.toUpperCase();
        if (name != '') {
            isValid = regex.test(name);
        }
        if (isValid) {
            this.productNameMessageTarget.textContent = "";
            this.saveButtonTarget.disabled = false;
        } else {
            this.productNameMessageTarget.textContent = "Format invalide. Veuillez saisir sous la forme: ABC123";
            this.productNameMessageTarget.style.color = "DarkRed"; // Display the message in red color.
            this.saveButtonTarget.disabled = true;
        }
    }

    validateWorkstationName() {
        const regex = /^[A-Za-z0-9\s\-\/\(\)\+\.]+$/;
        let isValid = true;
        let name = this.workstationNameTarget.value.toUpperCase();
        if (name != '') {
            isValid = regex.test(name);
        }
        if (isValid) {
            this.workstationNameMessageTarget.textContent = "";
            this.saveButtonTarget.disabled = false;
        } else {
            this.workstationNameMessageTarget.textContent = "Format invalide. Veuillez saisir sous la forme: Assy-P674-Poinçonneuse Peau";
            this.workstationNameMessageTarget.style.color = "DarkRed"; // Display the message in red color.
            this.saveButtonTarget.disabled = true;
        }
    }
}