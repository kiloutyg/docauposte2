import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["uapSelect", "form"]

    connect() {
        console.log('OperatorFormController: Connected');
        this.setupFormValidation();
    }

    setupFormValidation() {
        if (this.hasFormTarget) {
            this.formTarget.addEventListener('submit', this.validateForm.bind(this));
        }
    }

    validateForm(event) {
        console.log('OperatorFormController: Validating form');
        
        if (this.hasUapSelectTarget) {
            const selectedUAPS = Array.from(this.uapSelectTarget.selectedOptions);
            console.log('OperatorFormController: Selected UAPs', selectedUAPS.length);
            
            if (selectedUAPS.length === 0) {
                event.preventDefault();
                alert('Veuillez sélectionner au moins un UAP');
                this.uapSelectTarget.focus();
                return false;
            }
        }
        
        console.log('OperatorFormController: Form validation passed');
        return true;
    }
}