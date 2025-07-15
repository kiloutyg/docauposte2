import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["selectAllCheckbox",
        "operatorCheckbox",
        "batchModifyButton",
        "batchDeleteButton"]

    /**
     * Stimulus controller lifecycle method that runs when the controller is connected to the DOM.
     * Initializes the batch operator processor by setting up the select all checkbox functionality
     * and logging the initial state of the controller for debugging purposes.
     * 
     * @returns {void} This method does not return a value.
     */
    connect() {
        console.log('BatchOperatorProcessorController: Connected');
        this.setupSelectAllCheckbox();
        this.logInitialState();
    }

    /**
     * Stimulus controller lifecycle method that runs when the controller is disconnected from the DOM.
     * This method is called automatically by Stimulus when the controller element is removed from the DOM
     * or when the controller is otherwise disconnected. Currently logs the disconnection event for debugging purposes.
     * 
     * @returns {void} This method does not return a value.
     */
    disconnect() {
        console.log('BatchOperatorProcessorController: Disconnected');
    }

    /**
     * Logs the initial state of the batch operator processor controller for debugging purposes.
     * This method queries the DOM to find operator checkboxes and the select all checkbox,
     * then logs their count and existence status to the console. This is useful for
     * troubleshooting initialization issues and verifying that the expected elements
     * are present in the DOM when the controller is connected.
     * 
     * @returns {void} This method does not return a value.
     */
    logInitialState() {
        const operatorCheckboxes = document.querySelectorAll('.batch-operator-checkbox');
        const selectAllCheckbox = document.getElementById('selectAllOperators');
        console.log('BatchOperatorProcessorController: Initial state', {
            operatorCheckboxesCount: operatorCheckboxes.length,
            selectAllCheckboxExists: !!selectAllCheckbox
        });
    }

    /**
     * Sets up the select all checkbox functionality for batch operator processing.
     * This method initializes event listeners for both the master "select all" checkbox
     * and individual operator checkboxes. It ensures proper synchronization between
     * the select all checkbox and individual checkboxes, and prevents duplicate event
     * listeners by removing existing ones before adding new ones.
     * 
     * The method performs the following operations:
     * - Locates the select all checkbox and operator checkboxes in the DOM
     * - Validates that required elements exist before proceeding
     * - Sets up change event listeners for the select all checkbox
     * - Sets up change event listeners for each individual operator checkbox
     * - Logs setup progress and completion for debugging purposes
     * 
     * @returns {void} This method does not return a value. If required elements
     *                 are not found in the DOM, the method will log warnings and
     *                 return early without setting up event listeners.
     */
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

    /**
     * Handles the change event for the "select all" checkbox in the batch operator processor.
     * This method synchronizes all individual operator checkboxes with the state of the master
     * select all checkbox. When the select all checkbox is checked or unchecked, this method
     * ensures that all operator checkboxes are updated to match that state. It also tracks
     * how many checkboxes were actually changed and updates the batch action button states
     * accordingly.
     * 
     * @param {Event} event - The change event object from the select all checkbox.
     *                       The event.target.checked property indicates whether the
     *                       select all checkbox is now checked (true) or unchecked (false).
     * 
     * @returns {void} This method does not return a value. It performs DOM manipulation
     *                 to update checkbox states and calls updateButtonStates() to refresh
     *                 the batch action buttons.
     */
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

    /**
     * Handles the change event for individual operator checkboxes in the batch processor.
     * This method is triggered when any individual operator checkbox is checked or unchecked.
     * It synchronizes the master "select all" checkbox state based on the current selection
     * of individual checkboxes, setting it to checked if all are selected, unchecked if none
     * are selected, or indeterminate if some are selected. The method also updates the
     * batch action button states to reflect the current selection.
     * 
     * The method performs the following operations:
     * - Queries all operator checkboxes and counts checked ones
     * - Updates the select all checkbox state (checked, unchecked, or indeterminate)
     * - Calls updateButtonStates() to enable/disable batch action buttons
     * - Logs the checkbox state changes for debugging purposes
     * 
     * @returns {void} This method does not return a value. It performs DOM manipulation
     *                 to update the select all checkbox state and calls updateButtonStates()
     *                 to refresh the batch action buttons based on the current selection.
     */
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

    /**
     * Updates the enabled/disabled state of batch action buttons based on operator checkbox selections.
     * This method queries the DOM to find all checked operator checkboxes and enables or disables
     * the batch modification and batch deletion buttons accordingly. The buttons are enabled when
     * at least one operator checkbox is selected, and disabled when no checkboxes are selected.
     * This provides immediate visual feedback to users about whether batch actions are available.
     * 
     * The method performs the following operations:
     * - Counts the number of selected operator checkboxes
     * - Locates the batch modify and batch delete buttons in the DOM
     * - Updates the disabled property of each button based on selection state
     * - Logs the button state changes for debugging purposes
     * 
     * @returns {void} This method does not return a value. It performs DOM manipulation
     *                 to update button states and logs the changes to the console.
     */
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

    /**
     * Processes batch modification of selected operators by gathering selected operators,
     * validating the selection, and prompting the user for confirmation before proceeding
     * with the modification operation. This method serves as the main entry point for
     * batch modification operations triggered by user interaction with the batch modify button.
     * 
     * The method performs the following operations:
     * - Retrieves all currently selected operators from checkboxes
     * - Validates that at least one operator is selected
     * - Displays a confirmation dialog to the user
     * - Initiates the form submission process if confirmed
     * - Provides appropriate user feedback for various scenarios
     * 
     * @returns {void} This method does not return a value. If no operators are selected,
     *                 it displays an alert and returns early. If the user cancels the
     *                 confirmation dialog, it logs the cancellation and returns. Otherwise,
     *                 it calls submitSelectedForms() to process the modifications.
     */
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

    /**
     * Processes batch deletion of selected operators by gathering selected operators,
     * validating the selection, and prompting the user for confirmation before proceeding
     * with the deletion operation. This method serves as the main entry point for
     * batch deletion operations triggered by user interaction with the batch delete button.
     * 
     * The method performs the following operations:
     * - Retrieves all currently selected operators from checkboxes
     * - Validates that at least one operator is selected
     * - Displays a confirmation dialog warning about the irreversible nature of deletion
     * - Initiates the deletion process if confirmed by the user
     * - Provides appropriate user feedback for various scenarios
     * 
     * @returns {void} This method does not return a value. If no operators are selected,
     *                 it displays an alert and returns early. If the user cancels the
     *                 confirmation dialog, it logs the cancellation and returns. Otherwise,
     *                 it calls deleteSelectedOperators() to process the deletions.
     */
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

    /**
     * Retrieves information about all currently selected operators from the batch processor interface.
     * This method queries the DOM for checked operator checkboxes and extracts relevant data
     * including operator IDs and their associated form IDs. It also validates that the
     * corresponding forms exist in the DOM and logs warnings for any missing forms.
     * This method is essential for batch operations as it provides the data structure
     * needed to identify which operators should be processed.
     * 
     * The method performs the following operations:
     * - Queries all checked operator checkboxes using CSS selector
     * - Maps each checkbox to an operator object containing ID and form ID
     * - Validates that each operator's associated form exists in the DOM
     * - Logs the selection state and any validation errors for debugging
     * 
     * @returns {Array<Object>} An array of operator objects representing the selected operators.
     *                         Each object contains:
     *                         - id {string}: The unique identifier of the operator (from checkbox value)
     *                         - formId {string}: The ID of the form associated with this operator
     *                         Returns an empty array if no operators are selected.
     */
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

    /**
     * Submits the forms for selected operators to the batch processing endpoint for modification.
     * This method collects form data from all selected operators, reformats the field names to match
     * the expected batch processing format, and sends the data via a POST request to the server.
     * The method handles form data transformation by removing individual operator field prefixes
     * and restructuring them into a batch format suitable for server-side processing.
     * 
     * The method performs the following operations:
     * - Iterates through each selected operator and retrieves its associated form
     * - Extracts form data and transforms field names from individual format to batch format
     * - Consolidates all operator data into a single FormData object
     * - Sends the batch data to the server endpoint via fetch API
     * - Handles server response and provides user feedback
     * - Reloads the page on successful processing or displays error messages on failure
     * 
     * @param {Array<Object>} selectedOperators - An array of operator objects to be processed.
     *                                           Each object must contain:
     *                                           - id {string}: The unique identifier of the operator
     *                                           - formId {string}: The DOM ID of the form element associated with this operator
     * 
     * @returns {void} This method does not return a value. It performs asynchronous operations
     *                 including form data collection, HTTP request submission, and DOM manipulation
     *                 (page reload or alert display) based on the server response. If any operator
     *                 forms are not found in the DOM, errors are logged but processing continues
     *                 for the remaining valid operators.
     */
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

    /**
     * Deletes the selected operators by sending their IDs to the batch deletion endpoint.
     * This method extracts operator IDs from the selected operators array and sends them
     * to the server via a POST request for batch deletion processing. The method handles
     * the server response by reloading the page on successful deletion or displaying
     * error messages if the operation fails. All operations are logged for debugging purposes.
     * 
     * The method performs the following operations:
     * - Extracts operator IDs from the selected operators array
     * - Sends a JSON POST request to the batch deletion endpoint
     * - Processes the server response and handles success/error scenarios
     * - Reloads the page on successful deletion or shows error alerts
     * - Provides comprehensive logging throughout the deletion process
     * 
     * @param {Array<Object>} selectedOperators - An array of operator objects to be deleted.
     *                                           Each object must contain:
     *                                           - id {string}: The unique identifier of the operator to delete
     *                                           - formId {string}: The DOM ID of the form element (not used in deletion but part of operator object structure)
     * 
     * @returns {void} This method does not return a value. It performs asynchronous operations
     *                 including HTTP request submission and DOM manipulation (page reload or
     *                 alert display) based on the server response. The method handles all
     *                 success and error scenarios internally through promise chains.
     */
    deleteSelectedOperators(selectedOperators) {
        console.log('BatchOperatorProcessorController: Deleting selected operators');
        const operatorIds = selectedOperators.map(op => op.id);

        console.log('BatchOperatorProcessorController: Operator IDs to delete', operatorIds);

        fetch('/docauposte/operator/batch-delete', {
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