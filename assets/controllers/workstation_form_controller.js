import { Controller } from '@hotwired/stimulus';

export default class WorkstationFormController extends Controller {
    static targets = ["uap", "department", "product", "zone", "upload"];

    /**
     * This function is called when the controller connects to the DOM.
     * It logs a message to the console indicating that the workstation creation controller has been connected.
     *
     * @function connect
     * @memberof WorkstationFormController
     * @instance
     * @returns {void}
     */
    connect() {
        // This runs when the controller connects to the DOM
        console.log("Workstation creation controller connected");
    }

    /**
     * This function is called when the select fields in the workstation form change.
     * It sends an AJAX request to the server to update the UAP, Department, and Upload fields based on the selected zone.
     *
     * @function fieldsChanged
     * @memberof WorkstationFormController
     * @instance
     * @returns {void}
     */
    fieldsChanged() {
        // This will be called when the select changes
        const form = this.element;
        const formData = new FormData(form);
    
        // Add a flag to indicate this is an AJAX request for change
        formData.append('ajax_change', '1');
    
        // Use fetch API to submit the form data
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.text())
            .then(html => {
                // Parse the HTML response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
    
                // Update the UAP, Department and Uploads fields with the new options inherited from the zone select
                const newUploadSelect = doc.querySelector('select[name="workstation[upload]"]');
                if (newUploadSelect && this.hasUploadTarget) {
                    // Replace the current upload select with the new one
                    const uploadParent = this.uploadTarget.parentNode;
                    uploadParent.innerHTML = newUploadSelect.parentNode.innerHTML;
                }
    
                const newUapSelect = doc.querySelector('select[name="workstation[uap]"]');
                if (newUapSelect && this.hasUapTarget) {
                    const uapParent = this.uapTarget.parentNode;
                    uapParent.innerHTML = newUapSelect.parentNode.innerHTML;
                }
    
                const newDepartmentSelect = doc.querySelector('select[name="workstation[department]"]');
                if (newDepartmentSelect && this.hasDepartmentTarget) {
                    const departmentParent = this.departmentTarget.parentNode;
                    departmentParent.innerHTML = newDepartmentSelect.parentNode.innerHTML;
                }
            })
            .catch(error => {
                console.error('Error updating upload field:', error);
            });
    }
}