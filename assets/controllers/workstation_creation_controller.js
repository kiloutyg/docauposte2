import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["zone", "upload"];

    connect() {
        // This runs when the controller connects to the DOM
        console.log("Workstation form controller connected");
    }

    zoneChanged() {
        // This will be called when the zone select changes
        const form = this.element;
        const formData = new FormData(form);
        
        // Add a flag to indicate this is an AJAX request for zone change
        formData.append('ajax_zone_change', '1');
        
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
            
            // Find the upload select element in the response
            const newUploadSelect = doc.querySelector('select[name="workstation[upload]"]');
            
            if (newUploadSelect && this.hasUploadTarget) {
                // Replace the current upload select with the new one
                const uploadParent = this.uploadTarget.parentNode;
                uploadParent.innerHTML = newUploadSelect.parentNode.innerHTML;
            }
        })
        .catch(error => {
            console.error('Error updating upload field:', error);
        });
    }
}