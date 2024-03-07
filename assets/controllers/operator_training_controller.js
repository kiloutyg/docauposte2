import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class extends Controller {
    static targets = ["newOperator", "newOperatorMessage", "newOperatorCode", "newOperatorMessageCode"];

    validateNewOperator() {
        const regex = /^[a-zA-Z]+\.[a-zA-Z]+$/;
        const isValid = regex.test(this.newOperatorTarget.value);

        if (isValid) {
            this.checkForExistingEntityByName();
            this.newOperatorMessageTarget.textContent = "";
        } else {
            this.newOperatorMessageTarget.textContent = "Veuillez saisir sous la forme prénom.nom";
            this.newOperatorMessageTarget.style.color = "red"; // Display the message in red color.
        }
    }


    validateNewOperatorCode() {
        const regex = /^[a-zA-Z0-9]+$/;
        const isValid = regex.test(this.newOperatorCodeTarget.value);

        if (isValid) {
            this.checkForExistingEntityByCode();
            this.newOperatorMessageCodeTarget.textContent = "";
        } else {
            this.newOperatorMessageCodeTarget.textContent = "Veuillez saisir un code correcte.";
            this.newOperatorMessageCodeTarget.style.color = "red"; // Display the message in red color.
        }
    }


    checkForExistingEntityByName() {
        const operatorName = this.newOperatorTarget.value;
        console.log(operatorName);
        axios.post('/docauposte/operator/check-duplicate-by-name', { name: operatorName })
            .then(response => {
                if (response.data.found) {
                    // Duplicate found, handle accordingly
                    this.newOperatorMessageTarget.textContent = response.data.message;
                    this.newOperatorMessageTarget.style.color = "red";

                    // Further actions to suggest the original entity to the user could go here
                    // e.g., display a modal or populate a form/input field with the found data

                } else {
                    // No duplicate, allow creation.
                    this.newOperatorMessageTarget.textContent = "No duplicates found, ready to create.";
                    this.newOperatorMessageTarget.style.color = "green";
                }
            })
            .catch(error => {
                console.error("There was an error checking for a duplicate operator!", error);
            });
    }


    checkForExistingEntityByCode() {
        const operatorCode = this.newOperatorCodeTarget.value;
        console.log(operatorCode)
        axios.post('/docauposte/operator/check-duplicate-by-code', { name: operatorCode })
            .then(response => {
                if (response.data.found) {
                    // Duplicate found, handle accordingly
                    this.newOperatorMessageCodeTarget.textContent = response.data.message;
                    this.newOperatorMessageCodeTarget.style.color = "red";

                    // Further actions to suggest the original entity to the user could go here
                    // e.g., display a modal or populate a form/input field with the found data

                } else {
                    // No duplicate, allow creation.
                    this.newOperatorMessageCodeTarget.textContent = "No duplicates found, ready to create.";
                    this.newOperatorMessageCodeTarget.style.color = "green";
                }
            })
            .catch(error => {
                console.error("There was an error checking for a duplicate operator!", error);
            });
    }
}