import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["selectAllCheckbox", "operatorCheckbox", "batchModifyButton", "batchDeleteButton"]

    connect() {
        console.log('BatchOperatorProcessorController: Connected');
        this.setupSelectAllCheckbox();
        this.logInitialState();
    }

    disconnect() {
        console.log('BatchOperatorProcessorController: Disconnected');
    }

    logInitialState() {
        const operatorCheckboxes = document.querySelectorAll('.batch-operator-checkbox');
        const selectAllCheckbox = document.getElementById('selectAllOperators');
        console.log('BatchOperatorProcessorController: Initial state', {
            operatorCheckboxesCount: operatorCheckboxes.length,
            selectAllCheckboxExists: !!selectAllCheckbox
        });
    }

    setupSelectAllCheckbox() {
        console.log('BatchOperatorProcessorController: Setting up select all checkbox');
        const selectAllCheckbox = document.getElementById('selectAllOperators');
        const operatorCheckboxes = document.querySelectorAll('.batch-operator-checkbox');

        if (!selectAllCheckbox) {
            console.warn('BatchOperatorProcessorController: Select all checkbox not found');
            return;
        }

        if (operatorCheckboxes.length === 0) {
            console.warn('BatchOperatorProcessorController: No operator checkboxes found');
            return;
        }

        console.log('BatchOperatorProcessorController: Found', operatorCheckboxes.length, 'operator checkboxes');

        // Remove existing event listeners to prevent duplicates
        selectAllCheckbox.removeEventListener('change', this.handleSelectAllChange);
        selectAllCheckbox.addEventListener('change', this.handleSelectAllChange.bind(this));

        // Setup individual checkbox listeners
        operatorCheckboxes.forEach((checkbox, index) => {
            checkbox.removeEventListener('change', this.handleIndividualCheckboxChange);
            checkbox.addEventListener('change', this.handleIndividualCheckboxChange.bind(this));
            console.log(`BatchOperatorProcessorController: Setup listener for checkbox ${index + 1}`, {
                id: checkbox.value,
                formId: checkbox.dataset.operatorForm
            });
        });

        console.log('BatchOperatorProcessorController: Select all checkbox setup completed');
    }

    handleSelectAllChange(event) {
        console.log('BatchOperatorProcessorController: Select all checkbox changed', {
            checked: event.target.checked
        });

        const operatorCheckboxes = document.querySelectorAll('.batch-operator-checkbox');
        let changedCount = 0;

        operatorCheckboxes.forEach(checkbox => {
            if (checkbox.checked !== event.target.checked) {
                checkbox.checked = event.target.checked;
                changedCount++;
            }
        });

        console.log('BatchOperatorProcessorController: Changed', changedCount, 'operator checkboxes');
        this.updateButtonStates();
    }

    handleIndividualCheckboxChange() {
        console.log('BatchOperatorProcessorController: Individual checkbox changed');

        const operatorCheckboxes = document.querySelectorAll('.batch-operator-checkbox');
        const checkedBoxes = document.querySelectorAll('.batch-operator-checkbox:checked');
        const selectAllCheckbox = document.getElementById('selectAllOperators');

        console.log('BatchOperatorProcessorController: Checkbox state', {
            total: operatorCheckboxes.length,
            checked: checkedBoxes.length
        });

        if (selectAllCheckbox) {
            const allChecked = checkedBoxes.length === operatorCheckboxes.length;
            const someChecked = checkedBoxes.length > 0 && checkedBoxes.length < operatorCheckboxes.length;

            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked;

            console.log('BatchOperatorProcessorController: Updated select all checkbox', {
                checked: allChecked,
                indeterminate: someChecked
            });
        }

        this.updateButtonStates();
    }

    updateButtonStates() {
        const checkedBoxes = document.querySelectorAll('.batch-operator-checkbox:checked');
        const hasSelection = checkedBoxes.length > 0;

        // Update button states if they exist
        const batchModifyButton = document.querySelector('[data-action*="batch-operator-processor#processBatchModification"]');
        const batchDeleteButton = document.querySelector('[data-action*="batch-operator-processor#processBatchDeletion"]');

        if (batchModifyButton) {
            batchModifyButton.disabled = !hasSelection;
        }
        if (batchDeleteButton) {
            batchDeleteButton.disabled = !hasSelection;
        }

        console.log('BatchOperatorProcessorController: Updated button states', {
            selectedCount: checkedBoxes.length,
            buttonsEnabled: hasSelection
        });
    }

    processBatchModification() {
        console.log('BatchOperatorProcessorController: Processing batch modification');
        const selectedOperators = this.getSelectedOperators();

        console.log('BatchOperatorProcessorController: Selected operators for modification', {
            count: selectedOperators.length,
            operators: selectedOperators
        });

        if (selectedOperators.length === 0) {
            console.warn('BatchOperatorProcessorController: No operators selected for modification');
            alert('Veuillez sélectionner au moins un opérateur à modifier.');
            return;
        }

        if (confirm(`Êtes-vous sûr de vouloir modifier ${selectedOperators.length} opérateur(s) ?`)) {
            console.log('BatchOperatorProcessorController: User confirmed batch modification');
            this.submitSelectedForms(selectedOperators);
        } else {
            console.log('BatchOperatorProcessorController: User cancelled batch modification');
        }
    }

    processBatchDeletion() {
        console.log('BatchOperatorProcessorController: Processing batch deletion');
        const selectedOperators = this.getSelectedOperators();

        console.log('BatchOperatorProcessorController: Selected operators for deletion', {
            count: selectedOperators.length,
            operators: selectedOperators
        });

        if (selectedOperators.length === 0) {
            console.warn('BatchOperatorProcessorController: No operators selected for deletion');
            alert('Veuillez sélectionner au moins un opérateur à supprimer.');
            return;
        }

        if (confirm(`Êtes-vous sûr de vouloir supprimer ${selectedOperators.length} opérateur(s) ? Cette action est irréversible.`)) {
            console.log('BatchOperatorProcessorController: User confirmed batch deletion');
            this.deleteSelectedOperators(selectedOperators);
        } else {
            console.log('BatchOperatorProcessorController: User cancelled batch deletion');
        }
    }

    getSelectedOperators() {
        console.log('BatchOperatorProcessorController: Getting selected operators');
        const checkedBoxes = document.querySelectorAll('.batch-operator-checkbox:checked');

        const selectedOperators = Array.from(checkedBoxes).map(checkbox => {
            const operator = {
                id: checkbox.value,
                formId: checkbox.dataset.operatorForm
            };

            // Validate that the form exists
            const form = document.getElementById(operator.formId);
            if (!form) {
                console.error('BatchOperatorProcessorController: Form not found for operator', operator);
            }

            return operator;
        });

        console.log('BatchOperatorProcessorController: Selected operators', {
            count: selectedOperators.length,
            operators: selectedOperators
        });

        return selectedOperators;
    }

    submitSelectedForms(selectedOperators) {
        console.log('BatchOperatorProcessorController: Submitting selected forms');
        const formData = new FormData();
        let processedForms = 0;

        selectedOperators.forEach(operator => {
            const form = document.getElementById(operator.formId);
            if (form) {
                console.log('BatchOperatorProcessorController: Processing form for operator', operator.id);
                const formDataEntries = new FormData(form);

                for (let [key, value] of formDataEntries.entries()) {
                    // Remove the "operator[" prefix and the closing "]" to get the clean field name
                    let cleanKey = key;
                    if (key.startsWith('operator[') && key.endsWith(']')) {
                        cleanKey = key.slice(9, -1); // Remove "operator[" (9 chars) and "]" (1 char)
                    }

                    const formattedKey = `operators[${operator.id}][${cleanKey}]`;
                    console.log(`Formatted key: ${formattedKey}`, value);
                    formData.append(formattedKey, value);
                    console.log('BatchOperatorProcessorController: Added form data', {
                        operatorId: operator.id,
                        originalKey: key,
                        cleanKey: cleanKey,
                        formattedKey: formattedKey,
                        value: value
                    });
                }
                processedForms++;
            } else {
                console.error('BatchOperatorProcessorController: Form not found for operator', operator.id);
            }
        });

        console.log('BatchOperatorProcessorController: Processed', processedForms, 'forms out of', selectedOperators.length);

        // Submit to batch processing endpoint
        console.log('BatchOperatorProcessorController: Sending batch edit request');
        fetch('/docauposte/operator/batch-edit', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                console.log('BatchOperatorProcessorController: Received response', {
                    status: response.status,
                    statusText: response.statusText
                });
                return response.json();
            })
            .then(data => {
                console.log('BatchOperatorProcessorController: Response data', data);
                if (data.success) {
                    console.log('BatchOperatorProcessorController: Batch modification successful, reloading page');
                    location.reload();
                } else {
                    console.error('BatchOperatorProcessorController: Batch modification failed', data.message);
                    alert('Erreur lors de la modification en lot: ' + data.message);
                }
            })
            .catch(error => {
                console.error('BatchOperatorProcessorController: Error during batch modification', error);
                alert('Une erreur est survenue lors de la modification en lot.');
            });
    }

    deleteSelectedOperators(selectedOperators) {
        console.log('BatchOperatorProcessorController: Deleting selected operators');
        const operatorIds = selectedOperators.map(op => op.id);

        console.log('BatchOperatorProcessorController: Operator IDs to delete', operatorIds);

        fetch('/wdocauposte/operator/batch-delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ operatorIds: operatorIds })
        })
            .then(response => {
                console.log('BatchOperatorProcessorController: Received delete response', {
                    status: response.status,
                    statusText: response.statusText
                });
                return response.json();
            })
            .then(data => {
                console.log('BatchOperatorProcessorController: Delete response data', data);
                if (data.success) {
                    console.log('BatchOperatorProcessorController: Batch deletion successful, reloading page');
                    location.reload();
                } else {
                    console.error('BatchOperatorProcessorController: Batch deletion failed', data.message);
                    alert('Erreur lors de la suppression en lot: ' + data.message);
                }
            })
            .catch(error => {
                console.error('BatchOperatorProcessorController: Error during batch deletion', error);
                alert('Une erreur est survenue lors de la suppression en lot.');
            });
    }
}