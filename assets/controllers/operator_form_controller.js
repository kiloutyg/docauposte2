import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["uapSelect", "form"]

    /**
     * Initializes the OperatorFormController when it connects to the DOM.
     * This lifecycle method is automatically called by Stimulus when the controller
     * is attached to its target element. Sets up form validation listeners.
     */
    connect() {
        console.log('OperatorFormController: Connected');
        this.setupFormValidation();
    }

    /**
     * Sets up form validation by attaching a submit event listener to the form target.
     * This method checks if a form target exists and binds the validateForm method
     * to the form's submit event to ensure validation occurs before form submission.
     */
    setupFormValidation() {
        if (this.hasFormTarget) {
            this.formTarget.addEventListener('submit', this.validateForm.bind(this));
        }
    }

    /**
     * Validates the operator form before submission by checking required fields.
     * This method ensures that at least one UAP (Unité Administrative de Proximité) 
     * is selected before allowing the form to be submitted. If validation fails,
     * it prevents form submission and displays an error message to the user.
     * 
     * @param {Event} event - The form submit event object that triggered the validation.
     *                       Used to prevent default form submission if validation fails.
     * @returns {boolean} Returns true if validation passes and form can be submitted,
     *                   false if validation fails and form submission should be prevented.
     */
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