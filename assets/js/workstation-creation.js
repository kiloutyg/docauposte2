document.addEventListener('turbo:load', function() {
    initWorkstationForm();
});

function initWorkstationForm() {
    const zoneSelect = document.querySelector('select[name="workstation[zone]"]');
    
    if (zoneSelect) {
        zoneSelect.addEventListener('change', function() {
            const form = zoneSelect.closest('form');
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
                const currentUploadSelect = document.querySelector('select[name="workstation[upload]"]');
                
                if (newUploadSelect && currentUploadSelect) {
                    // Replace the current upload select with the new one
                    const uploadParent = currentUploadSelect.parentNode;
                    uploadParent.innerHTML = newUploadSelect.parentNode.innerHTML;
                }
            })
            .catch(error => {
                console.error('Error updating upload field:', error);
            });
        });
    }
}